<?php

/**
 * @file plugins/importexport/csv/CSVImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CSVImportExportPlugin
 * @ingroup plugins_importexport_csv
 *
 * @brief CSV import/export plugin
 */

import('lib.pkp.classes.plugins.ImportExportPlugin');

class CSVImportExportPlugin extends ImportExportPlugin {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'CSVImportExportPlugin';
	}

	function getDisplayName() {
		return __('plugins.importexport.csv.displayName');
	}

	function getDescription() {
		return __('plugins.importexport.csv.description');
	}

	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $actionArgs) {
		return []; // Not available via the web interface
	}

	/**
	 * Display the plugin.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		parent::display($args, $request);
		switch (array_shift($args)) {
			case 'index':
			case '':
				$templateMgr->display($this->getTemplateResource('index.tpl'));
				break;
		}
	}

	/**
	 * Execute import/export tasks using the command-line interface.
	 * @param $args Parameters to the plugin
	 */
	function executeCLI($scriptName, &$args) {

		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);

		$filename = array_shift($args);
		$username = array_shift($args);
		$basePath = dirname($filename);

		if (!$filename || !$username) {
			$this->usage($scriptName);
			exit();
		}

		if (!file_exists($filename)) {
			echo __('plugins.importexport.csv.fileDoesNotExist', ['filename' => $filename]) . "\n";
			exit();
		}

		$data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($data === false) {
			echo __('plugins.importexport.csv.fileDoesNotExist', ['filename' => $filename]) . "\n";
			exit();
		}
		str_getcsv(array_shift($data)); // Remove column headers in first row

		if (is_array($data) && count($data) > 0) {

			$pressDao = Application::getContextDAO();
			$genreDao = DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
			$userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
			$submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
			$publicationDao = DAORegistry::getDAO('PublicationDAO'); /* @var $publicationDao PublicationDAO */
			$seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
			$authorDao = DAORegistry::getDAO('AuthorDAO'); /* @var $authorDao AuthorDAO */
			$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /* @var $publicationFormatDao PublicationFormatDAO */
			import('lib.pkp.classes.submission.SubmissionFile'); // constants.
			$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /* @var $publicationDateDao PublicationDateDAO */
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */

			$user = $userDao->getByUsername($username);
			if (!$user) {
				echo __('plugins.importexport.csv.unknownUser', ['username' => $username]) . "\n";
				exit();
			}

			foreach ($data as $line) {
				$fields = str_getcsv($line);
				// Format is:
				// Press Path, Author string, title, abstract, series path, year, is_edited_volume, locale, URL to PDF, doi (optional), keywords list, subjects list, book cover image path, book cover image alt text, categories list
				list(
					$pressPath,
					$authorString,
					$title,
					$abstract,
					$seriesPath,
					$year,
					$isEditedVolume,
					$locale,
					$pdfUrl,
					$doi,
					$keywords,
					$subjects,
					$bookCoverImageName,
					$bookCoverImageAltText,
					$categories
				) = $fields;

				$press = $pressDao->getByPath($pressPath);

				if (!$press) {
					echo __('plugins.importexport.csv.unknownPress', ['pressPath' => $pressPath]) . "\n";
					continue;
				}

				$supportedLocales = $press->getSupportedSubmissionLocales();
				if (!is_array($supportedLocales) || count($supportedLocales) < 1) {
					$supportedLocales = [$press->getPrimaryLocale()];
				}

				if (!in_array($locale, $supportedLocales)) {
					echo __('plugins.importexport.csv.unknownLocale', ['locale' => $locale]) . "\n";
					continue;
				}

				// we need a Genre for the files.  Assume a key of MANUSCRIPT as a default.
				$genre = $genreDao->getByKey('MANUSCRIPT', $press->getId());
				if (!$genre) {
					echo __('plugins.importexport.csv.noGenre') . "\n";
					exit();
				}

				$authorGroup = $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_AUTHOR);

				if (!$authorGroup) {
					echo __('plugins.importexport.csv.noAuthorGroup', ['press' => $pressPath]) . "\n";
					continue;
				}

				$submission = $submissionDao->newDataObject();
				$submission->setContextId($press->getId());
				$submission->stampLastActivity();
				$submission->stampModified();
				$submission->setStatus(STATUS_PUBLISHED);
				$submission->setWorkType($isEditedVolume == 1 ? WORK_TYPE_EDITED_VOLUME : WORK_TYPE_AUTHORED_WORK);
				$submission->setCopyrightNotice($press->getLocalizedSetting('copyrightNotice'), $locale);
				$submission->setLocale($locale);
				$submission->setStageId(WORKFLOW_STAGE_ID_PRODUCTION);
				$submission->setSubmissionProgress(0);
				$submission->setData('abstract', $abstract, $locale);

				$series = $seriesPath ? $seriesDao->getByPath($seriesPath, $press->getId()) : null;
				if ($series) {
					$submission->setSeriesId($series->getId());
				}

				$submissionId = $submissionDao->insertObject($submission);

				$publication = $publicationDao->newDataObject();
				$publication->setData('submissionId', $submissionId);
				$publication->setData('version', 1);
				$publication->setData('status', STATUS_PUBLISHED);
				$publication->setData('datePublished', Core::getCurrentDate());
				$publicationId = $publicationDao->insertObject($publication);

				$submission->setData('currentPublicationId', $publicationId);
				$submissionDao->updateObject($submission);

				$contactEmail = $press->getContactEmail();
				$authorsString = trim($authorString, '"'); // remove double quotes if present.
				$authors = preg_split('/;/', $authorsString);
				$firstAuthor = true;

				foreach ($authors as $authorString) {
					// Examine the author string. Best case is: Given1 Family1 <email@address.com>, Given2 Family2 <email@address.com>, etc
					// But default to press email address based on press path if not present.
					$givenName = $familyName = $emailAddress = null;
					$authorString = trim($authorString); // whitespace.
					if (preg_match('/^([\w.\s]+)\s+([\w\s-]+)?\s*(<([^>]+)>)?$/', $authorString, $matches)) {
						$givenName = $matches[1]; // Mandatory
						if (count($matches) > 2) {
							$familyName = $matches[2];
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
					$author->setGivenName($givenName, $locale);
					$author->setFamilyName($familyName, $locale);
					$author->setEmail($emailAddress);
					$insertPrimaryContactId = false;

					if ($firstAuthor) {
						$author->setPrimaryContact(true);
						$firstAuthor = false;
						$insertPrimaryContactId = true;
					}


					$author->setData('publicationId', $publicationId);
					$authorDao->insertObject($author);

					if ($insertPrimaryContactId) {
						$publication->setData('primaryContactId', $author->getId());
					}
				} // Authors done.

				$publication->setData('abstract', $abstract, $locale);
				$publication->setData('title', $title, $locale);
				$publicationDao->updateObject($publication);

				// Submission is done.  Create a publication format for it.
				$publicationFormat = $publicationFormatDao->newDataObject();
				$publicationFormat->setData('submissionId', $submissionId);
				$publicationFormat->setData('publicationId', $publicationId);
				$publicationFormat->setPhysicalFormat(false);
				$publicationFormat->setIsApproved(true);
				$publicationFormat->setIsAvailable(true);
				$publicationFormat->setProductAvailabilityCode('20'); // ONIX code for Available.
				$publicationFormat->setEntryKey('DA'); // ONIX code for Digital
				$publicationFormat->setData('name', 'PDF', $submission->getLocale());
				$publicationFormat->setSequence(REALLY_BIG_NUMBER);

				$publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);

				if ($doi) {
					$publicationFormatDao->changePubId($publicationFormatId, 'doi', $doi);
				}


				// Create a publication format date for this publication format.
				$publicationDate = $publicationDateDao->newDataObject();
				$publicationDate->setDateFormat('05'); // List55, YYYY
				$publicationDate->setRole('01'); // List163, Publication Date
				$publicationDate->setDate($year);
				$publicationDate->setPublicationFormatId($publicationFormatId);
				$publicationDateDao->insertObject($publicationDate);

				// Submission File.
				import('lib.pkp.classes.file.FileManager');
				import('lib.pkp.classes.core.Core');
				import('classes.file.PublicFileManager');

				// Submission File.
				$fileManager = new FileManager();
				$publicFileManager = new PublicFileManager();
				$extension = $fileManager->parseFileExtension($pdfUrl);
				$dirNames = Application::getFileDirectories();
				$submissionDir = sprintf(
					'%s/%d/%s/%d',
					str_replace('/', '', $dirNames['context']),
					$press->getId(),
					str_replace('/', '', $dirNames['submission']),
					$submissionId
				);

				$filePath = $basePath . '/' . 'submissionPdfs/' . $pdfUrl;

				if (!file_exists($filePath) || !is_readable($filePath)) {
					echo __('plugins.importexport.csv.invalidPdfFilename', ['title' => $title]) . "\n";
					exit();
				}

				$mimeType = mime_content_type($filePath);

				/** @var \PKP\services\PKPFileService */
				$fileService = Services::get('file');
				$fileId = $fileService->add(
					$filePath,
					$submissionDir . '/' . uniqid() . '.' . $extension
				);

				$submissionFile = $submissionFileDao->newDataObject();
				$submissionFile->setData('submissionId', $submissionId);
				$submissionFile->setData('uploaderUserId', $user->getId());
				$submissionFile->setSubmissionLocale($submission->getLocale());
				$submissionFile->setGenreId($genre->getId());
				$submissionFile->setFileStage(SUBMISSION_FILE_PROOF);
				$submissionFile->setAssocType(ASSOC_TYPE_REPRESENTATION);
				$submissionFile->setData('assocId', $publicationFormatId);
				$submissionFile->setData('createdAt', Core::getCurrentDate());
				$submissionFile->setDateModified(Core::getCurrentDate());
				$submissionFile->setData('mimetype', $mimeType);
				$submissionFile->setData('fileId', $fileId);
				$submissionFile->setData('name', pathinfo($filePath, PATHINFO_FILENAME));

				// Assume open access, no price.
				$submissionFile->setDirectSalesPrice(0);
				$submissionFile->setSalesType('openAccess');

				$submissionFileDao->insertObject($submissionFile);

				// Keywords
				$keywordsList = [$locale => explode(';', $keywords)];
				if (count($keywordsList[$locale]) > 0) {
					$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO'); /* @var $submissionKeywordDao SubmissionKeywordDAO */
					$submissionKeywordDao->insertKeywords($keywordsList, $publicationId);
				}

				//Subjects
				$subjectsList = [$locale => explode(";", $subjects)];
				if (count($subjectsList[$locale]) > 0) {
					$submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO'); /* @var $submissionKeywordDao SubmissionKeywordDAO */
					$submissionSubjectDao->insertSubjects($subjectsList, $publicationId);
				}

				// Book Cover Image
				$allowedFileTypes = ['gif', 'jpg', 'png', 'webp'];
				$coverImgExtension = pathinfo(strtolower($bookCoverImageName), PATHINFO_EXTENSION);

				if (!in_array($coverImgExtension, $allowedFileTypes)) {
					echo __('plugins.importexport.common.error.invalidFileExtension');
					exit();
				}

				$coverImagelocale = [];
				$coverImage = [];

				if ($bookCoverImageName) {
					$sanitizedCoverImageName = str_replace([' ', '_', ':'], '-', strtolower($bookCoverImageName));
					$sanitizedCoverImageName = preg_replace("/[^a-z0-9\.\-]+/", '', $sanitizedCoverImageName);
					$sanitizedCoverImageName = basename($sanitizedCoverImageName);

					$coverImage['uploadName'] = uniqid() . '-' . $sanitizedCoverImageName;
					$coverImage['altText'] = $bookCoverImageAltText ?? '';

					$srcFilePath = $basePath . '/' . 'coverImages/' . $bookCoverImageName;

					if (!file_exists($srcFilePath) || !is_readable($srcFilePath)) {
						echo __('plugins.importexport.csv.invalidCoverImageFilename', ['title' => $title]) . "\n";
						exit();
					}

					$coverImageData = file_get_contents($srcFilePath);
					$coverImageBase64 = base64_encode($coverImageData);
					$destFilePath = $publicFileManager->getContextFilesPath($press->getId()) . '/' . $coverImage['uploadName'];
					file_put_contents($destFilePath, base64_decode($coverImageBase64));

					Services::get('publication')->makeThumbnail(
						$destFilePath,
						Services::get('publication')->getThumbnailFileName($coverImage['uploadName']),
						$press->getData('coverThumbnailsMaxWidth'),
						$press->getData('coverThumbnailsMaxHeight')
					);

					$coverImagelocale[$locale] = $coverImage;

					$publication->setData('coverImage', $coverImagelocale);
					$publicationDao->updateObject($publication);
				}

				// Categories
				$categoriesList = explode(';', $categories);
				if (!empty($categoriesList)) {
					$categoryDao = DAORegistry::getDAO('CategoryDAO'); /* @var $categoryDao CategoryDAO */

					foreach ($categoriesList as $categoryTitle) {
						$category = $categoryDao->getByTitle(trim($categoryTitle), $press->getId(), $locale);

						if (!$category) {
							echo __('plugins.importexport.csv.noCategorySelected', ['category' => trim($categoryTitle)]) . "\n";
							exit();
						}

						$categoryDao->insertPublicationAssignment($category->getId(), $publicationId);
					}
				}

				echo __('plugins.importexport.csv.import.submission', ['title' => $title]) . "\n";
			}
		}
	}

	/**
	 * Display the command-line usage information
	 */
	function usage($scriptName) {
		echo __('plugins.importexport.csv.cliUsage', [
			'scriptName' => $scriptName,
			'pluginName' => $this->getName()
		]) . "\n";
	}
}
