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
			exit;
		}

		if (!file_exists($filename)) {
			echo __('plugins.importexport.csv.fileDoesNotExist', ['filename' => $filename]) . "\n";
			exit;
		}

		$userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */

		$user = $userDao->getByUsername($username);
		if (!$user) {
			echo __('plugins.importexport.csv.unknownUser', ['username' => $username]) . "\n";
			exit;
		}

		$file = new SplFileObject($filename, 'r');
		$file->setFlags(SplFileObject::READ_CSV);

		$file->rewind();

		if ($file->eof()) {
			echo __('plugins.importexport.csv.fileDoesNotExist', ['filename' => $filename]) . "\n";
			exit;
		}

		if (!$file->eof()) {
			// Imports
			import('lib.pkp.classes.submission.SubmissionFile'); // constants.
			import('lib.pkp.classes.file.FileManager');
			import('lib.pkp.classes.core.Core');
			import('classes.file.PublicFileManager');

			// DAOs
			$pressDao = Application::getContextDAO();
			$genreDao = DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
			$userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
			$submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
			$publicationDao = DAORegistry::getDAO('PublicationDAO'); /* @var $publicationDao PublicationDAO */
			$seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
			$authorDao = DAORegistry::getDAO('AuthorDAO'); /* @var $authorDao AuthorDAO */
			$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /* @var $publicationFormatDao PublicationFormatDAO */
			$publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /* @var $publicationDateDao PublicationDateDAO */
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$categoryDao = DAORegistry::getDAO('CategoryDAO'); /* @var $categoryDao CategoryDAO */

			// Static variables
			$fileManager = new FileManager();
			$publicFileManager = new PublicFileManager();
			$dirNames = Application::getFileDirectories();
			$format = trim($dirNames['context'], '/') . '/%d/' . trim($dirNames['submission'], '/') . '/%d';
			$allowedFileTypes = ['gif', 'jpg', 'png', 'webp'];

			/** @var \PKP\services\PKPFileService */
			$fileService = Services::get('file');
			/** @var \PKP\services\PKPPublicationService */
			$publicationService = Services::get('publication');

			$invalidRows = [];

			// Cache variables
			$genres = [];
			$authorGroups = [];
			$series = [];
			$cachedCategories = [];

			$headers = $file->fgets(); // Retrieve headers in case of invalid rows;

			while(!$file->eof()) {
				$position = $file->ftell();
				$rowString = $file->fgets();

				$file->fseek($position);

				$fields = $file->fgetcsv();

				if (empty(array_filter($fields))) {
					continue;
				}

				if (count($fields) < 15) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.csvDoesntContainAllFields', ['row' => $rowString])];
					continue;
				}

				if (!$this->requiredFieldsPresent($fields)) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.requiredFieldsMissing', ['row' => $rowString])];
					continue;
				}

				// Format is:
				// Press Path, Author string, title, abstract, series path, year, is_edited_volume, locale, URL to Asset, doi (optional), keywords list, subjects list, book cover image path, book cover image alt text, categories list
				[
					$pressPath,
					$authorString,
					$title,
					$abstract,
					$seriesPath,
					$year,
					$isEditedVolume,
					$locale,
					$filename,
					$doi,
					$keywords,
					$subjects,
					$bookCoverImageName,
					$bookCoverImageAltText,
					$categories
				] = array_pad($fields, 15, null);

				$press = $pressDao->getByPath($pressPath);

				if (!$press) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.unknownPress', ['pressPath' => $pressPath])];
					continue;
				}

				$supportedLocales = $press->getSupportedSubmissionLocales();
				if (!is_array($supportedLocales) || count($supportedLocales) < 1) {
					$supportedLocales = [$press->getPrimaryLocale()];
				}

				if (!in_array($locale, $supportedLocales)) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.unknownLocale', ['locale' => $locale])];
					continue;
				}

				// we need a Genre for the files.  Assume a key of MANUSCRIPT as a default.
				$genre = $genres[$press->getId()]
					?? $genres[$press->getId()] = $genreDao->getByKey('MANUSCRIPT', $press->getId());

				if (!$genre) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.noGenre')];
					continue;
				}

				$authorGroup = $authorGroups[$press->getId()]
					?? $authorGroups[$press->getId()] = $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_AUTHOR);

				if (!$authorGroup) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.noAuthorGroup', ['press' => $pressPath])];
					continue;
				}

				$filePath = "{$basePath}/{$filename}";
				if (!is_readable($filePath)) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.invalidAssetFilename', ['title' => $title])];
					continue;
				}

				$coverImgExtension = pathinfo(mb_strtolower($bookCoverImageName), PATHINFO_EXTENSION);

				if (!in_array($coverImgExtension, $allowedFileTypes)) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.common.error.invalidFileExtension')];
					continue;
				}

				if ($bookCoverImageName) {
					$srcFilePath = "{$basePath}/{$bookCoverImageName}";

					if (!is_readable($srcFilePath)) {
						$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.invalidCoverImageFilename', ['title' => $title])];
						continue;
					}
				}

				$categoriesList = explode(';', $categories);
				$dbCategories = [];

				foreach($categoriesList as $categoryTitle) {
					$dbCategory = $cachedCategories["{$categoryTitle}_{$press->getId()}"]
						?? $cachedCategories["{$categoryTitle}_{$press->getId()}"] = $categoryDao->getByTitle(trim($categoryTitle), $press->getId(), $locale);

					if (!is_null($dbCategory)) {
						$dbCategories[] = $dbCategory;
					}
				}

				if (count($categoriesList) < count($dbCategories)) {
					$invalidRows[] = ['row' => $rowString, 'reason' => __('plugins.importexport.csv.allCategoriesMustExists')];
					continue;
				}

				// All requirements passed. Start processing from here.
				$extension = $fileManager->parseFileExtension($filename);
				$sanitizedAbstract = PKPString::stripUnsafeHtml($abstract);

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
				$submission->setData('abstract', $sanitizedAbstract, $locale);

				$pressSeries = $series["{$seriesPath}_{$press->getId}"]
					?? $series["{$seriesPath}_{$press->getId}"] = $seriesPath
						? $seriesDao->getByPath($seriesPath, $press->getId())
						: null;

				if ($pressSeries) {
					$submission->setSeriesId($pressSeries->getId());
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

				$authorsString = explode(";", $authorString);

				foreach ($authorsString as $index => $authorString) {
					// Examine the author string. Best case is: "Given1,Family1,email@address.com;Given2,Family2,email@address.com", etc
					// But default to press email address based on press path if not present.
					$givenName = $familyName = $emailAddress = null;
					$authorString = trim($authorString); // whitespace.
					[$givenName, $familyName, $emailAddress] = explode(',', $authorString);

					$givenName = trim($givenName);
					$familyName = trim($familyName);

					if (empty($emailAddress)) {
						$emailAddress = $contactEmail;
					}

					$emailAddress = trim($emailAddress);

					$author = $authorDao->newDataObject();
					$author->setSubmissionId($submissionId);
					$author->setUserGroupId($authorGroup->getId());
					$author->setGivenName($givenName, $locale);
					$author->setFamilyName($familyName, $locale);
					$author->setEmail($emailAddress);

					if (!$index) {
						$author->setPrimaryContact(true);
						$publication->setData('primaryContactId', $author->getId());
					}

					$author->setData('publicationId', $publicationId);
					$authorDao->insertObject($author);
				} // Authors done.

				$publication->setData('abstract', $sanitizedAbstract, $locale);
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
				$publicationFormat->setData('name', mb_strtoupper($extension), $submission->getLocale());
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
				$submissionDir = sprintf($format, $press->getId(), $submissionId);
				$fileId = $fileService->add($filePath, $submissionDir . '/' . uniqid() . '.' . $extension);
				$mimeType = PKPString::mime_content_type($filePath);

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
					/** @var $submissionKeywordDao SubmissionKeywordDAO */
					$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
					$submissionKeywordDao->insertKeywords($keywordsList, $publicationId);
				}

				//Subjects
				$subjectsList = [$locale => explode(';', $subjects)];
				if (count($subjectsList[$locale]) > 0) {
					/** @var $submissionKeywordDao SubmissionKeywordDAO */
					$submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO');
					$submissionSubjectDao->insertSubjects($subjectsList, $publicationId);
				}

				$coverImagelocale = [];
				$coverImage = [];

				if ($bookCoverImageName) {
					$sanitizedCoverImageName = str_replace([' ', '_', ':'], '-', mb_strtolower($bookCoverImageName));
					$sanitizedCoverImageName = PKPstring::regexp_replace("/[^a-z0-9\.\-]+/", '', $sanitizedCoverImageName);
					$sanitizedCoverImageName = basename($sanitizedCoverImageName);

					$coverImage['uploadName'] = uniqid() . '-' . $sanitizedCoverImageName;
					$coverImage['altText'] = $bookCoverImageAltText ?? '';

					$destFilePath = $publicFileManager->getContextFilesPath($press->getId()) . '/' . $coverImage['uploadName'];
					file_put_contents($destFilePath, file_get_contents($srcFilePath));

					$publicationService->makeThumbnail(
						$destFilePath,
						$publicationService->getThumbnailFileName($coverImage['uploadName']),
						$press->getData('coverThumbnailsMaxWidth'),
						$press->getData('coverThumbnailsMaxHeight')
					);

					$coverImagelocale[$locale] = $coverImage;

					$publication->setData('coverImage', $coverImagelocale);
					$publicationDao->updateObject($publication);
				}

				// Categories

				foreach ($dbCategories as $category) {
					$categoryDao->insertPublicationAssignment($category->getId(), $publicationId);
				}

				echo __('plugins.importexport.csv.import.submission', ['title' => $title]) . "\n";
			}

			if (count($invalidRows) > 0) {
				$csvForInvalidRowsName = "{$basePath}/invalid_rows.csv";
				$csvDataWithHeaders = [...$invalidRows];

				$file = new SplFileObject($csvForInvalidRowsName, 'w');

				$file->fwrite($headers);

				echo __('plugin.importexport.csv.toolFoundErrorForFollowingLines') . "\n\n";

				foreach ($csvDataWithHeaders as $row) {
					echo $row['row'];
					echo $row['reason'] . "\n\n";

					$file->fwrite($row['row']);
				}

				echo __('plugin.importexport.csv.toolGeneratedCsvFileForIncorrectRows') . "\n";
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

	private function requiredFieldsPresent($row)
	{
		[
			$pressPath,
			$authorString,
			$title,
			$abstract,
			$seriesPath,
			$year,
			$isEditedVolume,
			$locale,
			$filename,
			$doi,
			$keywords,
			$subjects,
			$bookCoverImageName,
			$bookCoverImageAltText,
			$categories
		 ] = array_pad($row, 15, null);

		 return !is_null($pressPath)
		 	&& !is_null($authorString)
			&& !is_null($title)
			&& !is_null($abstract)
			&& !is_null($locale)
			&& !is_null($filename);
	}
}
