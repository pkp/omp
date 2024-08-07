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
			exit(1);
		}

		$userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */

		$user = $userDao->getByUsername($username);
		if (!$user) {
			echo __('plugins.importexport.csv.unknownUser', ['username' => $username]) . "\n";
			exit(1);
		}

		$file = null;

		try {
			$file = new SplFileObject($filename, 'r');
		}
		catch (Exception $e) {
			echo $e->getMessage();
			exit(1);
		}

		$file->setFlags(SplFileObject::READ_CSV);

		if ($file->eof()) {
			echo __('plugins.importexport.csv.fileDoesNotExist', ['filename' => $filename]) . "\n";
			exit;
		}

		$expectedHeaders = [
			'pressPath',
			'authorString',
			'title',
			'abstract',
			'seriesPath',
			'year',
			'isEditedVolume',
			'locale',
			'filename',
			'doi',
			'keywords',
			'subjects',
			'bookCoverImage',
			'bookCoverImageAltText',
			'categories',
		];
		$headers = $file->fgetcsv();

		$missingHeaders = array_diff($expectedHeaders, $headers);

		if (count($missingHeaders)) {
			echo __('pugin.importexport.csv.missingHeadersOnCsv', ['missingHeaders' => $missingHeaders]);
			exit(1);
		}

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

		// Cache variables
		$genres = [];
		$authorGroups = [];
		$series = [];
		$cachedCategories = [];
		$presses = [];

		$csvForInvalidRowsName = "{$basePath}/invalid_rows.csv";
		$invalidRowsFile = new SplFileObject($csvForInvalidRowsName, 'w');

		$invalidRowsFile->fputcsv(array_merge(array_pad($headers, 15, null), ['error']));

		foreach ($file as $index => $fields) {
			if (!$index) {
				continue; // Skip headers
			}

			if (empty(array_filter($fields))) {
				continue;
			}

			// Verifica se chegou ao final do conteúdo
			if (count($fields) < 15) {
				$invalidRowsFile->fputcsv(array_merge(array_pad($fields, 15, null), [__('plugins.importexport.csv.csvDoesntContainAllFields')]));
				continue;
			}

			$data = (object) array_combine($headers, array_pad(array_map('trim', $fields), count($headers), null));
			if (!$this->requiredFieldsPresent($data)) {
				$invalidRowsFile->fputcsv(array_merge(array_pad($fields, 15, null), [__('plugins.importexport.csv.requiredFieldsMissing')]));
				continue;
			}

			// Format is:
			// Press Path, Author string, title, abstract, series path, year, is_edited_volume, locale, URL to Asset, doi (optional), keywords list, subjects list, book cover image path, book cover image alt text, categories list
			$fieldsList = array_pad($fields, 15, null);


			$press = $presses[$data->pressPath] ?? $presses[$data->pressPath] = $pressDao->getByPath($data->pressPath);

			if (!$press) {
				$invalidRowsFile->fputcsv(array_merge($fieldsList, [__('plugins.importexport.csv.unknownPress', ['contextPath' => $data->pressPath])]));
				continue;
			}

			$supportedLocales = $press->getSupportedSubmissionLocales();
			if (!is_array($supportedLocales) || count($supportedLocales) < 1) {
				$supportedLocales = [$press->getPrimaryLocale()];
			}

			if (!in_array($data->locale, $supportedLocales)) {
				$invalidRowsFile->fputcsv(array_merge($fieldsList, [__('plugins.importexport.csv.unknownLocale', ['locale' => $data->locale])]));
				continue;
			}

			// we need a Genre for the files.  Assume a key of MANUSCRIPT as a default.
			$genre = $genres[$press->getId()]
				?? $genres[$press->getId()] = $genreDao->getByKey('MANUSCRIPT', $press->getId());

			if (!$genre) {
				$invalidRowsFile->fputcsv(array_merge($fieldsList, [__('plugins.importexport.csv.noGenre')]));
				continue;
			}

			$authorGroup = $authorGroups[$press->getId()]
				?? $authorGroups[$press->getId()] = $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_AUTHOR);

			if (!$authorGroup) {
				$invalidRowsFile->fputcsv(array_merge($fieldsList, [__('plugins.importexport.csv.noAuthorGroup', ['press' => $data->pressPath])]));
				continue;
			}

			$filePath = "{$basePath}/{$data->filename}";
			if (!is_readable($filePath)) {
				$invalidRowsFile->fputcsv(array_merge($fieldsList, [__('plugins.importexport.csv.invalidAssetFilename', ['title' => $data->title])]));
				continue;
			}

			$coverImgExtension = pathinfo(mb_strtolower($data->bookCoverImage), PATHINFO_EXTENSION);

			if (!in_array($coverImgExtension, $allowedFileTypes)) {
				$invalidRowsFile->fputcsv(array_merge($fieldsList, [__('plugins.importexport.common.error.invalidFileExtension')]));
				continue;
			}

			if ($data->bookCoverImage) {
				$srcFilePath = "{$basePath}/{$data->bookCoverImage}";

				if (!is_readable($srcFilePath)) {
					$invalidRowsFile->fputcsv(array_merge($fieldsList, [__('plugins.importexport.csv.invalidCoverImageFilename', ['title' => $data->title])]));
					continue;
				}
			}

			$categoriesList = explode(';', $data->categories);
			$dbCategories = [];

			foreach($categoriesList as $categoryTitle) {
				$dbCategory = $cachedCategories["{$categoryTitle}_{$press->getId()}"]
					?? $cachedCategories["{$categoryTitle}_{$press->getId()}"] = $categoryDao->getByTitle(trim($categoryTitle), $press->getId(), $data->locale);

				if (!is_null($dbCategory)) {
					$dbCategories[] = $dbCategory;
				}
			}

			if (count($categoriesList) < count($dbCategories)) {
				$invalidRowsFile->fputcsv(array_merge($fieldsList, [__('plugins.importexport.csv.allCategoriesMustExists')]));
				continue;
			}

			// All requirements passed. Start processing from here.
			$extension = $fileManager->parseFileExtension($data->filename);

			$submission = $submissionDao->newDataObject();
			$submission->setContextId($press->getId());
			$submission->stampLastActivity();
			$submission->stampModified();
			$submission->setStatus(STATUS_PUBLISHED);
			$submission->setWorkType($data->isEditedVolume == 1 ? WORK_TYPE_EDITED_VOLUME : WORK_TYPE_AUTHORED_WORK);
			$submission->setCopyrightNotice($press->getLocalizedSetting('copyrightNotice'), $data->locale);
			$submission->setLocale($data->locale);
			$submission->setStageId(WORKFLOW_STAGE_ID_PRODUCTION);
			$submission->setSubmissionProgress(0);
			$submission->setData('abstract', $data->abstract, $data->locale);

			$pressSeries = $series["{$data->seriesPath}_{$press->getId()}"]
				?? $series["{$data->seriesPath}_{$press->getId()}"] = $data->seriesPath
					? $seriesDao->getByPath($data->seriesPath, $press->getId())
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
			$authorsString = explode(";", $data->authorString);

			foreach ($authorsString as $index => $authorString) {
				// Examine the author string. Best case is: "Given1,Family1,email@address.com;Given2,Family2,email@address.com", etc
				// But default to press email address based on press path if not present.
				$givenName = $familyName = $emailAddress = null;
				$authorString = trim($authorString); // whitespace.
				[$givenName, $familyName, $emailAddress] = explode(",", $authorString);

				$givenName = trim($givenName);
				$familyName = trim($familyName);

				if (empty($emailAddress)) {
					$emailAddress = $contactEmail;
				}

				$emailAddress = trim($emailAddress);

				$author = $authorDao->newDataObject();
				$author->setSubmissionId($submissionId);
				$author->setUserGroupId($authorGroup->getId());
				$author->setGivenName($givenName, $data->locale);
				$author->setFamilyName($familyName, $data->locale);
				$author->setEmail($emailAddress);

				if (!$index) {
					$author->setPrimaryContact(true);
					$publication->setData('primaryContactId', $author->getId());
				}

				$author->setData('publicationId', $publicationId);
				$authorDao->insertObject($author);
			} // Authors done.

			$sanitizedAbstract = PKPString::stripUnsafeHtml($data->abstract);

			$publication->setData('abstract', $sanitizedAbstract, $data->locale);
			$publication->setData('title', $data->title, $data->locale);
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

			if ($data->doi) {
				$publicationFormatDao->changePubId($publicationFormatId, 'doi', $data->doi);
			}

			// Create a publication format date for this publication format.
			$publicationDate = $publicationDateDao->newDataObject();
			$publicationDate->setDateFormat('05'); // List55, YYYY
			$publicationDate->setRole('01'); // List163, Publication Date
			$publicationDate->setDate($data->year);
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
			$keywordsList = [$data->locale => explode(';', $data->keywords)];
			if (count($keywordsList[$data->locale]) > 0) {
				/** @var $submissionKeywordDao SubmissionKeywordDAO */
				$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
				$submissionKeywordDao->insertKeywords($keywordsList, $publicationId);
			}

			//Subjects
			$subjectsList = [$data->locale => explode(";", $data->subjects)];
			if (count($subjectsList[$data->locale]) > 0) {
				/** @var $submissionKeywordDao SubmissionKeywordDAO */
				$submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO');
				$submissionSubjectDao->insertSubjects($subjectsList, $publicationId);
			}

			$coverImagelocale = [];
			$coverImage = [];

			if ($data->bookCoverImage) {
				$sanitizedCoverImageName = str_replace([' ', '_', ':'], '-', mb_strtolower($data->bookCoverImage));
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

				$coverImagelocale[$data->locale] = $coverImage;

				$publication->setData('coverImage', $coverImagelocale);
				$publicationDao->updateObject($publication);
			}

			// Categories
			foreach ($dbCategories as $category) {
				$categoryDao->insertPublicationAssignment($category->getId(), $publicationId);
			}

			echo __('plugins.importexport.csv.import.submission', ['title' => $data->title]) . "\n";
		}

		$invalidRowsFileRead = new SplFileObject($csvForInvalidRowsName, 'r');
		$invalidRowsFileRead->fgetcsv(); // Retrieve again the headers

		$firstContentLine = $invalidRowsFileRead->fgetcsv();

		if (count($firstContentLine) === 1) {
			unlink($csvForInvalidRowsName);
		} else {
			echo __('plugin.importexport.csv.seeInvalidRowsFile') . "\n\n";
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
		 return !!$row->pressPath
		 	&& !!$row->authorString
			&& !!$row->title
			&& !!$row->abstract
			&& !!$row->locale
			&& !!$row->filename;
	}
}
