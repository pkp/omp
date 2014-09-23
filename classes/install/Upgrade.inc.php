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
		$genreDao = DAORegistry::getDAO('GenreDAO');
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
	 * Localize the URLs associated with footer links. (See bug #8867.)
	 * @return boolean
	 */
	function localizeFooterLinks() {
		$pressDao = DAORegistry::getDAO('PressDAO');
		$footerLinkDao = DAORegistry::getDAO('FooterLinkDAO');

		$contexts = $pressDao->getAll();
		while ($context = $contexts->next()) {

			$result = $footerLinkDao->retrieve(
				'SELECT footerlink_id, url
				FROM	footerlinks
				WHERE	context_id = ?',
				array((int) $context->getId())
			);

			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$footerLinkId = $row['footerlink_id'];
				$url = $row['url'];
				$result->MoveNext();

				foreach ($context->getSupportedLocales() as $locale) {
					$footerLinkDao->update(
						'INSERT INTO footerlink_settings VALUES (?, ?, ?, ?, ?)',
						array((int) $footerLinkId, $locale, 'url', $url, 'string')
					);
				}
			}
			$result->Close();
		}
		return true;
	}
}

?>
