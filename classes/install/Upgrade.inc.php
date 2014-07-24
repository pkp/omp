<?php

/**
 * @file classes/install/Upgrade.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Upgrade
 * @ingroup install
 *
 * @brief Perform system upgrade.
 */


import('lib.pkp.classes.install.Installer');

class Upgrade extends Installer {

	/**
	 * Constructor.
	 * @param $params array upgrade parameters
	 */
	function Upgrade($params, $installFile = 'upgrade.xml', $isPlugin = false) {
		parent::Installer($installFile, $params, $isPlugin);
	}


	/**
	 * Returns true iff this is an upgrade process.
	 * @return boolean
	 */
	function isUpgrade() {
		return true;
	}


	//
	// Specific upgrade actions
	//

	/**
	 * Fix broken submission filenames (bug #8461)
	 * @param $upgrade Upgrade
	 * @param $params array
	 * @param $dryrun boolean True iff only a dry run (displaying rather than executing changes) should be done.
	 * @return boolean
	 */
	function fixFilenames($upgrade, $params, $dryrun = false) {
		$pressDao = DAORegistry::getDAO('PressDAO');
		$submissionDao = DAORegistry::getDAO('MonographDAO');
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		DAORegistry::getDAO('GenreDAO'); // Load constants
		$siteDao = DAORegistry::getDAO('SiteDAO'); /* @var $siteDao SiteDAO */
		$site = $siteDao->getSite();
		$adminEmail = $site->getLocalizedContactEmail();

		import('lib.pkp.classes.file.SubmissionFileManager');

		$contexts = $pressDao->getAll();
		while ($context = $contexts->next()) {
			$submissions = $submissionDao->getByPressId($context->getId());
			while ($submission = $submissions->next()) {
				$submissionFileManager = new SubmissionFileManager($context->getId(), $submission->getId());
				$submissionFiles = $submissionFileDao->getBySubmissionId($submission->getId());
				foreach ($submissionFiles as $submissionFile) {
					$generatedFilename = $submissionFile->getServerFileName();
					$basePath = $submissionFileManager->getBasePath() . $submissionFile->_fileStageToPath($submissionFile->getFileStage()) . '/';
					$globPattern = $submissionFile->getSubmissionId() . '-' .
						'*' . '-' . // Genre name and designation globbed (together)
						$submissionFile->getFileId() . '-' .
						$submissionFile->getRevision() . '-' .
						$submissionFile->getFileStage() . '-' .
						date('Ymd', strtotime($submissionFile->getDateUploaded())) .
						'.' . strtolower_codesafe($submissionFile->getExtension());

					$matchedResults = glob($basePath . $globPattern);
					if (count($matchedResults)>1) {
						error_log("Duplicate potential files for \"$globPattern\"!", 1, $adminEmail);
						continue;
					}
					if (count($matchedResults) == 1) {
						// 1 result matched.
						$discoveredFilename = array_shift($matchedResults);
						if ($dryrun) {
							echo "Need to rename \"$discoveredFilename\" to \"$generatedFilename\".\n";
						} else {
							rename($discoveredFilename, $basePath . $generatedFilename);
						}
					} else {
						// 0 results matched.
						error_log("Unable to find a match for \"$globPattern\".\n", 1, $adminEmail);
						continue;
					}
				}
			}
		}
		return true;
	}

	/**
	 * Enable the default theme plugin for versions < 1.1.
	 * @return boolean
	 */
	function enableDefaultTheme() {
		$pressDao = DAORegistry::getDAO('PressDAO');
		$contexts = $pressDao->getAll();
		$pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');

		// Site-wide
		$pluginSettingsDao->updateSetting(0, 'defaultthemeplugin', 'enabled', '1', 'int');

		// For each press
		while ($context = $contexts->next()) {
			$pluginSettingsDao->updateSetting($context->getId(), 'defaultthemeplugin', 'enabled', '1', 'int');
		}
		return true;
	}

	/**
	 * Synchronize the ASSOC_TYPE_SERIES constant to ASSOC_TYPE_SECTION defined in PKPApplication.
	 * @return boolean
	 */
	function syncSeriesAssocType() {
		// Can be any DAO.
		$dao =& DAORegistry::getDAO('UserDAO'); /* @var $dao DAO */
		$tablesToUpdate = array(
			'features',
			'data_object_tombstone_oai_set_objects',
			'new_releases',
			'spotlights',
			'notifications',
			'email_templates',
			'email_templates_data',
			'controlled_vocabs',
			'event_log',
			'email_log',
			'metadata_descriptions',
			'notes',
			'item_views');

		foreach ($tablesToUpdate as $tableName) {
			$dao->update('UPDATE ' . $tableName . ' SET assoc_type = ' . ASSOC_TYPE_SERIES . ' WHERE assoc_type = ' . "'526'");
		}

		return true;
	}

	/**
	 * Fix incorrectly stored author settings. (See bug #8663.)
	 * @return boolean
	 */
	function fixAuthorSettings() {
		$authorDao = DAORegistry::getDAO('AuthorDAO');

		// Get all authors with broken data
		$result = $authorDao->retrieve(
			'SELECT DISTINCT author_id
			FROM	author_settings
			WHERE	(setting_name = ? OR setting_name = ?)
				AND setting_type = ?',
			array('affiliation', 'biography', 'object')
		);

		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$authorId = $row['author_id'];
			$result->MoveNext();

			$author = $authorDao->getById($authorId);
			if (!$author) continue; // Bonehead check (DB integrity)

			foreach ((array) $author->getAffiliation(null) as $locale => $affiliation) {
				if (is_array($affiliation)) foreach($affiliation as $locale => $s) {
					$author->setAffiliation($s, $locale);
				}
			}

			foreach ((array) $author->getBiography(null) as $locale => $biography) {
				if (is_array($biography)) foreach($biography as $locale => $s) {
					$author->setBiography($s, $locale);
				}
			}
			$authorDao->updateObject($author);
		}

		$result->Close();
		return true;
	}

	/**
	 * Import CSV data, instead of using the native XSD format.
	 * @param boolean $dryrun
	 * @param string $filename
	 * @param string $username
	 */
	function importCSVData($dryrun, $filename, $username) {

		if (!file_exists($filename)) { // sanity check, performed in CLI, but check again.
			return false;
		}

		$data = file($filename);

		if (is_array($data) && count($data) > 0) {

			$userDao = DAORegistry::getDAO('UserDAO');
			$user = $userDao->getByUsername($username);
			if (!$user) {
				echo "Unknown user: ${username}.\n";
				exit();
			}

			$submissionDao = Application::getSubmissionDAO();
			$authorDao = DAORegistry::getDAO('AuthorDAO');
			$pressDao = Application::getContextDAO();
			$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
			import('lib.pkp.classes.submission.SubmissionFile'); // constants.
			$genreDao = DAORegistry::getDAO('GenreDAO');
			$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO');

			foreach ($data as $csvLine) {
				// Format is:
				// Press Path, Author string, title, series path, year, is_edited_volume, locale, URL to PDF, doi (optional)
				list($pressPath, $authorString, $title, $seriesPath, $year, $isEditedVolume, $locale, $pdfUrl, $doi) = preg_split('/\t/', $csvLine);

				$press = $pressDao->getByPath($pressPath);

				if ($press) {

					$supportedLocales = $press->getSupportedSubmissionLocales();
					if (!is_array($supportedLocales) || count($supportedLocales) < 1) $supportedLocales = array($press->getPrimaryLocale());
					$authorGroup = $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_AUTHOR);

					// we need a Genre for the files.  Assume a key of MANUSCRIPT as a default.
					$genre = $genreDao->getByKey('MANUSCRIPT', $press->getId());

					if (!$genre) {
						echo "There is no genre for manuscripts present ... Exiting\n";
						exit();
					}
					if (!$authorGroup) {
						echo "No default author group ... Skipping.\n";
						continue;
					}
					if (in_array($locale, $supportedLocales)) {
						$submission = $submissionDao->newDataObject();
						$submission->setContextId($press->getId());
						$submission->setUserId($user->getId());
						$submission->stampStatusModified();
						$submission->setStatus(STATUS_PUBLISHED);
						$submission->setWorkType($isEditedVolume == 1?WORK_TYPE_EDITED_VOLUME:WORK_TYPE_AUTHORED_WORK);
						$submission->setCopyrightNotice($press->getLocalizedSetting('copyrightNotice'), $locale);
						$submission->setLocale($locale);

						$series = $seriesDao->getByPath($seriesPath, $press->getId());
						if ($series) {
							$submission->setSeriesId($series->getId());
						} else {
							echo "Series ${seriesPath} does not exist ... not added to submission.\n";
						}
						$submissionId = $submissionDao->insertObject($submission);

						$contactEmail = $press->getContactEmail();
						$authorString = trim($authorString, '"'); // remove double quotes if present.
						$authors = preg_split('/,\s*/', $authorString);
						$firstAuthor = true;
						foreach ($authors as $authorString) {
							// Examine the author string. Best case is: First1 Last1 <email@address.com>, First2 Last2 <email@address.com>, etc
							// But default to press email address based on press path if not present.
							$firstName = $lastName = $emailAddress = null;
							$authorString = trim($authorString); // whitespace.
							if (preg_match('/^(\w+)(\s+\w+)?\s*(<([^>]+)>)?$/', $authorString, $matches)) {
								$firstName = $matches[1]; // Mandatory
								if (count($matches) > 2) {
									$lastName = $matches[2];
								}
								if (count($matches) == 5) {
									$emailAddress = $matches[4];
								} else {
									$emailAddress = $contactEmail;
								}
							}
							$author = $authorDao->newDataObject();
							$author->setSubmissionId($submissionId);
							$author->setUserGroupId($authorGroup->getId());
							$author->setFirstName($firstName);
							$author->setLastName($lastName);
							$author->setEmail($emailAddress);
							if ($firstAuthor) {
								$author->setPrimaryContact(1);
								$firstAuthor = false;
							}
							$authorDao->insertObject($author);
						} // Authors done.

						$submission->setTitle($title, $locale);
						$submissionDao->updateObject($submission);

						// Submission is done.  Create a publication format for it.
						$publicationFormat = $publicationFormatDao->newDataObject();
						$publicationFormat->setPhysicalFormat(false);
						$publicationFormat->setIsApproved(true);
						$publicationFormat->setIsAvailable(true);
						$publicationFormat->setSubmissionId($submissionId);
						$publicationFormat->setProductAvailabilityCode('20'); // ONIX code for Available.
						$publicationFormat->setEntryKey('DA'); // ONIX code for Digital
						$publicationFormat->setData('name', 'PDF', $submission->getLocale());
						$publicationFormat->setSeq(REALLY_BIG_NUMBER);
						$publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);

						if ($doi) {
							$publicationFormat->setStoredPubId('doi', $doi);
						}

						$publicationFormatDao->updateObject($publicationFormat);

						// Create a publication format date for this publication format.
						$publicationDate = $publicationDateDao->newDataObject();
						$publicationDate->setDateFormat('05'); // List55, YYYY
						$publicationDate->setRole('01'); // List163, Publication Date
						$publicationDate->setDate($year);
						$publicationDate->setPublicationformatId($publicationFormatId);
						$publicationDateDao->insertObject($publicationDate);

						// Submission File.
						import('lib.pkp.classes.file.TemporaryFileManager');
						import('lib.pkp.classes.file.FileManager');

						$temporaryFileManager = new TemporaryFileManager();
						$temporaryFilename = tempnam($temporaryFileManager->getBasePath(), 'remote');
						$temporaryFileManager->copyFile($pdfUrl, $temporaryFilename);
						$submissionFile = $submissionFileDao->newDataObjectByGenreId($genre->getId());
						$submissionFile->setSubmissionId($submissionId);
						$submissionFile->setGenreId($genre->getId());
						$submissionFile->setFileStage(SUBMISSION_FILE_PROOF);
						$submissionFile->setDateUploaded(Core::getCurrentDate());
						$submissionFile->setDateModified(Core::getCurrentDate());
						$submissionFile->setAssocType(ASSOC_TYPE_REPRESENTATION);
						$submissionFile->setAssocId($publicationFormatId);
						$submissionFile->setFileType('application/pdf');

						// Assume open access, no price.
						$submissionFile->setDirectSalesPrice(0);
						$submissionFile->setSalesType('openAccess');

						$submissionFileDao->insertObject($submissionFile, $temporaryFilename);
						$fileManager = new FileManager();
						$fileManager->deleteFile($temporaryFilename);

						echo "Submission:  '${title}' successfully imported.\n";
					} else {
						echo "Unknown submission locale: ${locale} ... skipping.\n";
					}
				} else {
					echo "Unknown Press Path: ${pressPath} ... skipping.\n";
				}
			}
		}
	}
}

?>
