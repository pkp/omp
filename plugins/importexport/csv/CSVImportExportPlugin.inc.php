<?php

/**
 * @file plugins/importexport/csv/CSVImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
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
	 * The expected headers coming from the CSV file, in their respective order
	 *
	 * @var string[]
	 */
	private $_expectedHeaders = [
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

	/**
	 * The expected size for a valid Submission row on CSV file
	 *
	 * @var int
	 */
	private $_expectedRowSize;

	/**
	 * Array for caching already initialized DAOs.
	 *
	 * @var DAO[]
	 */
	private $_daos = [];

	/**
	 * Array for caching already retreived Presses.
	 *
	 * @var Press[]
	 */
	private $_presses = [];

	/**
	 * Array for caching already retreived Genre IDs.
	 *
	 * @var int[]
	 */
	private $_genreIds = [];

	/**
	 * Array for caching already retreived UserGroup IDs.
	 *
	 * @var int[]
	 */
	private $_userGroupIds = [];

	/**
	 * Array for caching already retreived Serie IDs.
	 *
	 * @var int[]
	 */
	private $_serieIds = [];

	/**
	 * Allowed image formats for the book cover image.
	 *
	 * @var string[]
	*/
	private $_allowedFileTypes = ['gif', 'jpg', 'png', 'webp'];

	/**
	 * The file directory array map used by the application.
	 *
	 * @var string[]
	 */
	private $_dirNames;

	/**
	 * The default format for the publication file path
	 *
	 * @var string
	 */
	private $_format;

	/**
	 * @var FileManager
	 */
	private $_fileManager;

	/**
	 * @var PublicFileManager
	 */
	private $_publicFileManager;

	/**
	 * @var \PKP\services\PKPFileService
	 */
	private $_fileService;

	/**
	 * @var APP\Services\PublicationService
	 */
	private $_publicationService;

	/**
	 * @var int
	 */
	private $_userId;

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

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.importexport.csv.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
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

		[$filename, $username, $basePath] = $this->parseCommandLineArguments($scriptName, $args);

		$this->validateUser($username);
		$file = $this->createAndValidateCSVFile($filename);

		$csvForInvalidRowsName = "{$basePath}/invalid_rows.csv";
		$invalidRowsFile = $this->createAndValidateCSVFileInvalidRows($csvForInvalidRowsName);

		// Imports
		import('lib.pkp.classes.submission.SubmissionFile'); // constants.
		import('lib.pkp.classes.file.FileManager');
		import('lib.pkp.classes.core.Core');
		import('classes.file.PublicFileManager');

		// Cache variables
		$processedRows = 0;
		$failedRows = 0;
		$this->_expectedRowSize = count($this->_expectedHeaders);

		foreach ($file as $index => $fields) {
			if (!$index) {
				continue; // Skip headers
			}

			if (empty(array_filter($fields))) {
				continue; // End of file
			}

			$processedRows++;

			if (count($fields) < $this->_expectedRowSize) {
				$reason = __('plugins.importexport.csv.csvDoesntContainAllFields');
				$failedRows = $this->processFailedRow($invalidRowsFile, $fields, $reason, $failedRows);

				continue;
			}

			$data = (object) array_combine($this->_expectedHeaders, array_pad(array_map('trim', $fields), $this->_expectedRowSize, null));
			if (!$this->requiredFieldsPresent($data)) {
				$reason = __('plugins.importexport.csv.requiredFieldsMissing');
				$failedRows = $this->processFailedRow($invalidRowsFile, $fields, $reason, $failedRows);

				continue;
			}

			// Format is:
			// Press Path, Author string, title, abstract, series path, year, is_edited_volume, locale, URL to Asset, doi (optional), keywords list, subjects list, book cover image path, book cover image alt text, categories list
			$fieldsList = array_pad($fields, $this->_expectedRowSize, null);

			$press = $this->getCachedPress($data->pressPath);
			if (!$press) {
				$reason = __('plugins.importexport.csv.unknownPress', ['contextPath' => $data->pressPath]);
				$failedRows = $this->processFailedRow($invalidRowsFile, $fieldsList, $reason, $failedRows);

				continue;
			}

			$supportedLocales = $press->getSupportedSubmissionLocales();
			if (!is_array($supportedLocales) || count($supportedLocales) < 1) {
				$supportedLocales = [$press->getPrimaryLocale()];
			}

			if (!in_array($data->locale, $supportedLocales)) {
				$reason = __('plugins.importexport.csv.unknownLocale', ['locale' => $data->locale]);
				$failedRows = $this->processFailedRow($invalidRowsFile, $fieldsList, $reason, $failedRows);

				continue;
			}

			$pressId = $press->getId();

			// we need a Genre for the files.  Assume a key of MANUSCRIPT as a default.
			$genreId = $this->getCachedGenreId($pressId);
			if (!$genreId) {
				$reason = __('plugins.importexport.csv.noGenre');
				$failedRows = $this->processFailedRow($invalidRowsFile, $fieldsList, $reason, $failedRows);

				continue;
			}

			$userGroupId = $this->getCachedUserGroupId($pressId);
			if (!$userGroupId) {
				$reason = __('plugins.importexport.csv.noAuthorGroup', ['press' => $data->pressPath]);
				$failedRows = $this->processFailedRow($invalidRowsFile, $fieldsList, $reason, $failedRows);

				continue;
			}

			$filePath = "{$basePath}/{$data->filename}";
			if (!is_readable($filePath)) {
				$reason = __('plugins.importexport.csv.invalidAssetFilename', ['title' => $data->title]);
				$failedRows = $this->processFailedRow($invalidRowsFile, $fieldsList, $reason, $failedRows);

				continue;
			}

			if ($data->bookCoverImage) {
				$coverImgExtension = pathinfo(mb_strtolower($data->bookCoverImage), PATHINFO_EXTENSION);
				if (!in_array($coverImgExtension, $this->_allowedFileTypes)) {
					$reason = __('plugins.importexport.common.error.invalidFileExtension');
					$failedRows = $this->processFailedRow($invalidRowsFile, $fieldsList, $reason, $failedRows);

					continue;
				}

				$srcFilePath = "{$basePath}/{$data->bookCoverImage}";
				if (!is_readable($srcFilePath)) {
					$reason = __('plugins.importexport.csv.invalidCoverImageFilename', ['title' => $data->title]);
					$failedRows = $this->processFailedRow($invalidRowsFile, $fieldsList, $reason, $failedRows);

					continue;
				}
			}

			$dbCategoryIds = $this->getCategoryDataForValidRow($data->categories, $pressId, $data->locale);
			if (!$dbCategoryIds) {
				$reason = __('plugins.importexport.csv.allCategoriesMustExists');
				$failedRows = $this->processFailedRow($invalidRowsFile, $fieldsList, $reason, $failedRows);

				continue;
			}

			// All requirements passed. Start processing from here.
			$this->initializeStaticVariables();
			$submission = $this->processSubmission($data, $pressId);
			$publication = $this->processPublication($submission, $data, $press);
			$this->processAuthors($data, $press->getContactEmail(), $submission->getId(), $publication, $userGroupId);

			// Submission is done.  Create a publication format for it.
			$extension = $this->_fileManager->parseFileExtension($data->filename);
			$publicationFormatId = $this->processPublicationFormat($submission->getId(), $publication->getId(), $extension, $data);

			$this->processPublicationDate($data->year, $publicationFormatId);

			// Submission File.
			$this->processPublicationFile(
				$data,
				$submission->getId(),
				$filePath,
				$extension,
				$publicationFormatId,
				$pressId,
				$genreId
			);

			$this->processKeywords($data, $publication->getId());
			$this->processSubjects($data, $publication->getId());

			if ($data->bookCoverImage) {
				$this->processBookCoverImage($data, $srcFilePath, $publication);
			}

			/** @var CategoryDAO $categoryDao */
			$categoryDao = $this->getCachedDao('CategoryDAO');
			foreach ($dbCategoryIds as $categoryId) {
				$categoryDao->insertPublicationAssignment($categoryId, $publication->getId());
			}

			echo __('plugins.importexport.csv.import.submission', ['title' => $data->title]) . "\n";
		}

		if ($failedRows === 0) {
			echo __('plugin.importexport.csv.allDataSuccessfullyImported', ['processedRows' => $processedRows]) . "\n\n";
			unlink($csvForInvalidRowsName);
		} else {
			echo __('plugin.importexport.csv.seeInvalidRowsFile', ['processedRows' => $processedRows - $failedRows, 'failedRows' => $failedRows]) . "\n\n";
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

	/**
	 * Parse and validate initial command args
	 *
	 * @param string $scriptName
	 * @param array $args
	 * @return string[]
	 */
	private function parseCommandLineArguments($scriptName, $args) {
		$filename = array_shift($args);
		$username = array_shift($args);
		$basePath = dirname($filename);

		if (!$filename || !$username) {
			$this->usage($scriptName);
			exit(1);
		}

		return [$filename, $username, $basePath];
	}

	/**
	 * Retrieve and validate the User by username
	 *
	 * @param string $username
	 */
	private function validateUser($username) {
		$user = $this->getUser($username);
		if (!$user) {
			echo __('plugins.importexport.csv.unknownUser', ['username' => $username]) . "\n";
			exit(1);
		}

		$this->_userId = $user->getId();
	}

	/**
	 * @param string $filename
	 * @param 'r'|'w' $mode
	 */
	private function createNewFile($filename, $mode) {
		try {
			return new SplFileObject($filename, $mode);
		} catch (Exception $e) {
			echo $e->getMessage();
			exit(1);
		}
	}

	/**
	 * @param string $filename
	 */
	private function createAndValidateCSVFile($filename) {
		$file = $this->createNewFile($filename, 'r');
		$file->setFlags(SplFileObject::READ_CSV);

		$headers = $file->fgetcsv();

		$missingHeaders = array_diff($this->_expectedHeaders, $headers);

		if (count($missingHeaders)) {
			echo __('plugin.importexport.csv.missingHeadersOnCsv', ['missingHeaders' => $missingHeaders]);
			exit(1);
		}

		return $file;
	}

	/**
	 * @param string $basePath
	 */
	private function createAndValidateCSVFileInvalidRows($csvForInvalidRowsName) {
		$invalidRowsFile = $this->createNewFile($csvForInvalidRowsName, 'w');
		$invalidRowsFile->fputcsv(array_merge(array_pad($this->_expectedHeaders, $this->_expectedRowSize, null), ['error']));

		return $invalidRowsFile;
	}

	/**
	 * Insert static data that will be used for the submission processing
	 */
	private function initializeStaticVariables() {
		$this->_dirNames = Application::getFileDirectories();
		$this->_format = trim($this->_dirNames['context'], '/') . '/%d/' . trim($this->_dirNames['submission'], '/') . '/%d';

		$this->_fileManager = new FileManager();
		$this->_publicFileManager = new PublicFileManager();

		$this->_fileService = Services::get('file');
		$this->_publicationService = Services::get('publication');
	}

	/**
	 * Returns a cached DAO or create a new one, if it isn't initialized yet.
	 *
	 * @param string $daoType
	 */
	private function getCachedDao($daoType) {
		return $this->_daos[$daoType] ?? $this->_daos[$daoType] = DAORegistry::getDAO($daoType);
	}

	/**
	 * Returns a cached Press or create a new one, if it isn't retreived yet.
	 *
	 * @param string $pressPath
	 */
	private function getCachedPress($pressPath) {
		return $this->_presses[$pressPath] ?? $this->_presses[$pressPath] = $this->getPressByPath($pressPath);
	}

	/**
	 * Returns a cached Genre or create a new one, if it isn't retreived yet.
	 *
	 * @param string $pressPath
	 */
	private function getCachedGenreId($pressId) {
		return $this->_genreIds[$pressId] ?? $this->_genreIds[$pressId] = $this->getGenreIdByPressId($pressId);
	}

	/**
	 * Returns a cached Serie ID or create a new one, if it isn't retreived yet.
	 *
	 * @param string $seriesPath
	 * @param int $pressId
	 */
	private function getCachedSerieId($seriesPath, $pressId) {
		$key = "{$seriesPath}_{$pressId}";
		return $this->_serieIds[$key] ?? $this->_serieIds[$key] = $this->getSerieIdByPathAndPressId($seriesPath, $pressId);
	}

	/**
	 * Returns a cached UserGroup ID or create a new one, if it isn't retreived yet.
	 *
	 * @param string $pressPath
	 */
	private function getCachedUserGroupId($pressId) {
		return $this->_userGroupIds[$pressId] ?? $this->_userGroupIds[$pressId] = $this->getUserGroupIdByPressId($pressId);
	}

	/**
	 * Insert data on the invalid_rows.csv file and increase the failed rows counter
	 *
	 * @param SplFileObject $invalidRowsFile
	 * @param array $fields
	 * @param int $failedRows
	 * @param string $reason
	 */
	private function processFailedRow($invalidRowsFile, $fields, $reason, $failedRows) {
		$invalidRowsFile->fputcsv(array_merge(array_pad($fields, $this->_expectedRowSize, null), [$reason]));

		return $failedRows + 1;
	}

	/**
	 * Verify if all required fields are present on a CSV row
	 *
	 * @param object $row
	 */
	private function requiredFieldsPresent($row) {
		 return !!$row->pressPath
		 	&& !!$row->authorString
			&& !!$row->title
			&& !!$row->abstract
			&& !!$row->locale
			&& !!$row->filename;
	}

	/**
	 * Retrives a user by username
	 *
	 * @param string $username
	 */
	private function getUser($username) {
		/** @var UserDAO $userDao  */
		$userDao = $this->getCachedDao('UserDAO');
		return $userDao->getByUsername($username);
	}

	/**
	 * Retrieves a Press by path.
	 *
	 * @param string $pressPath
	 * @return Press
	 */
	private function getPressByPath($pressPath) {
		/** @var PressDAO $pressDao */
		$pressDao = $this->getCachedDao('PressDAO');
		return $pressDao->getByPath($pressPath);
	}

	/**
	 * Retrieves a Genre ID by Press ID. If the Genre doesn't exist, the result
	 * will be false
	 *
	 * @param int $pressId
	 */
	private function getGenreIdByPressId($pressId) {
		/** @var GenreDAO $genreDao */
		$genreDao = $this->getCachedDao('GenreDAO');
		$genre = $genreDao->getByKey('MANUSCRIPT', $pressId);

		return !$genre ? false : $genre->getId();
	}

	/**
	 * Retrieves a UserGroup ID by press ID. If the UserGroup doesn't exist,
	 * the result will be false
	 *
	 * @param int $pressId
	 */
	private function getUserGroupIdByPressId($pressId) {
		/** @var UserGroupDAO $userGroupDao */
		$userGroupDao = $this->getCachedDao('UserGroupDAO');
		$userGroup = $userGroupDao->getDefaultByRoleId($pressId, ROLE_ID_AUTHOR);

		return !$userGroup ? false : $userGroup->getId();
	}

	/**
	 * Retrieves a Serie ID by a Serie path and a press ID. If the Serie doesn't exist,
	 * the result will be false
	 *
	 * @param string $seriesPath
	 * @param int $pressId
	 */
	private function getSerieIdByPathAndPressId($seriesPath, $pressId) {
		/** @var SeriesDAO $seriesDao */
		$seriesDao = $this->getCachedDao('SeriesDAO');
		$serie = $seriesDao->getByPath($seriesPath, $pressId);

		return is_null($serie) ? false : $serie->getId();
	}

	/**
	 * Retrieves all category IDs by an array of category titles, a Press ID and
	 * a locale. If any of categories provided doesn't exist, the result will
	 * be false. If all categories are registered on the database, the method will
	 * return all Category database IDs and the CategoryDAO.
	 *
	 * @param string $categories
	 * @param int $pressId
	 * @param string $locale
	 */
	private function getCategoryDataForValidRow($categories, $pressId, $locale) {
		/** @var CategoryDAO $categoryDao */
		$categoryDao = $this->getCachedDao('CategoryDAO');
		$cachedCategories = [];

		$categoriesList = explode(';', $categories);
		$dbCategoryIds = [];

		foreach($categoriesList as $categoryTitle) {
			$categoryCacheKey = "{$categoryTitle}_{$pressId}";
			$dbCategory = $cachedCategories[$categoryCacheKey]
				?? $cachedCategories[$categoryCacheKey] = $categoryDao->getByTitle(trim($categoryTitle), $pressId, $locale);

			if (!is_null($dbCategory)) {
				$dbCategoryIds[] = $dbCategory->getId();
			}
		}

		return count($categoriesList) < count($dbCategoryIds) ? false : $dbCategoryIds;
	}

	/**
	 * Process initial data for Submission
	 *
	 * @param object $data
	 * @param int $pressId
	 * @return Submission
	 */
	private function processSubmission($data, $pressId) {
		/** @var SubmissionDAO $submissionDao */
		$submissionDao = $this->getCachedDao('SubmissionDAO');

		$submission = $submissionDao->newDataObject();
		$submission->setData('contextId', $pressId);
		$submission->stampLastActivity();
		$submission->stampModified();
		$submission->setData('status', STATUS_PUBLISHED);
		$submission->setData('workType', $data->isEditedVolume == 1 ? WORK_TYPE_EDITED_VOLUME : WORK_TYPE_AUTHORED_WORK);
		$submission->setData('locale', $data->locale);
		$submission->setData('stageId', WORKFLOW_STAGE_ID_PRODUCTION);
		$submission->setData('submissionProgress', 0);
		$submission->setData('abstract', $data->abstract, $data->locale);
		$submissionDao->insertObject($submission);

		return $submission;
	}

	/**
	 * Process initial data for Publication
	 *
	 * @param Submission $submissionId
	 * @param object $data
	 * @param Press $press
	 * @return Publication $publicationData
	 */
	private function processPublication($submission, $data, $press) {
		/** @var PublicationDAO $publicationDao */
		$publicationDao = $this->getCachedDao('PublicationDAO');
		$sanitizedAbstract = PKPString::stripUnsafeHtml($data->abstract);
		$locale = $data->locale;

		$publication = $publicationDao->newDataObject();
		$publication->setData('submissionId', $submission->getId());
		$publication->setData('version', 1);
		$publication->setData('status', STATUS_PUBLISHED);
		$publication->setData('datePublished', Core::getCurrentDate());
		$publication->setData('abstract', $sanitizedAbstract, $locale);
		$publication->setData('title', $data->title, $locale);
		$publication->setData('copyrightNotice', $press->getLocalizedData('copyrightNotice', $locale), $locale);

		if ($data->seriesPath) {
			$pressSeriesId = $this->getCachedSerieId($data->seriesPath, $press->getId());
			$publication->setData('seriesId', $pressSeriesId);
		}

		$publicationDao->insertObject($publication);

		// Add this publication as the current one, now that we have its ID
		$submission->setData('currentPublicationId', $publication->getId());

		/** @var SubmissionDAO $submissionDao */
		$submissionDao = $this->getCachedDao('SubmissionDAO');
		$submissionDao->updateObject($submission);

		return $publication;
	}

	/**
	 * Process data for Submission authors
	 *
	 * @param object $data
	 * @param string $contactEmail
	 * @param int $submissionId
	 * @param Publication $publication
	 * @param int $userGroupId
	 */
	private function processAuthors($data, $contactEmail, $submissionId, $publication, $userGroupId) {
		/** @var AuthorDAO $authorDao */
		$authorDao = $this->getCachedDao('AuthorDAO');
		$authorsString = explode(';', $data->authorString);

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
			/** @var Author $author */
			$author = $authorDao->newDataObject();
			$author->setSubmissionId($submissionId);
			$author->setUserGroupId($userGroupId);
			$author->setGivenName($givenName, $data->locale);
			$author->setFamilyName($familyName, $data->locale);
			$author->setEmail($emailAddress);
			$author->setData('publicationId', $publication->getId());
			$authorDao->insertObject($author);

			if (!$index) {
				$author->setPrimaryContact(true);
				$authorDao->updateObject($author);

				$publication->setData('primaryContactId', $author->getId());

				/** @var PublicationDAO $publicationDao */
				$publicationDao = $this->getCachedDao('PublicationDAO');
				$publicationDao->updateObject($publication);
			}
		}
	}

	/**
	 * Process data for the PublicationFormat
	 *
	 * @param int $submissionId
	 * @param int $publicationId
	 * @param string $extension
	 * @param object $data
	 */
	private function processPublicationFormat($submissionId, $publicationId, $extension, $data) {
		/** @var PublicationFormatDAO $publicationFormatDao */
		$publicationFormatDao = $this->getCachedDao('PublicationFormatDAO');

		$publicationFormat = $publicationFormatDao->newDataObject();
		$publicationFormat->setData('submissionId', $submissionId);
		$publicationFormat->setData('publicationId', $publicationId);
		$publicationFormat->setPhysicalFormat(false);
		$publicationFormat->setIsApproved(true);
		$publicationFormat->setIsAvailable(true);
		$publicationFormat->setProductAvailabilityCode('20'); // ONIX code for Available.
		$publicationFormat->setEntryKey('DA'); // ONIX code for Digital
		$publicationFormat->setData('name', mb_strtoupper($extension), $data->locale);
		$publicationFormat->setSequence(REALLY_BIG_NUMBER);

		$publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);

		if ($data->doi) {
			$publicationFormatDao->changePubId($publicationFormatId, 'doi', $data->doi);
		}

		return $publicationFormat->getId();
	}

	/**
	 * Process data for the PublicationDate
	 *
	 * @param int $year
	 * @param int $publicationFormatId
	 */
	private function processPublicationDate($year, $publicationFormatId) {
		/** @var PublicationDateDAO $publicationDateDao */
		$publicationDateDao = $this->getCachedDao('PublicationDateDAO');

		$publicationDate = $publicationDateDao->newDataObject();
		$publicationDate->setDateFormat('05'); // List55, YYYY
		$publicationDate->setRole('01'); // List163, Publication Date
		$publicationDate->setDate($year);
		$publicationDate->setPublicationFormatId($publicationFormatId);
		$publicationDateDao->insertObject($publicationDate);
	}

	/**
	 * Process data for the PublicationFile
	 *
	 * @param object $data
	 * @param int $submissionId
	 * @param string $filePath
	 * @param string $extension
	 * @param int $publicationFormatId
	 * @param int $pressId
	 * @param int $genreId
	 */
	private function processPublicationFile($data, $submissionId, $filePath, $extension, $publicationFormatId, $pressId, $genreId) {
		$submissionDir = sprintf($this->_format, $pressId, $submissionId);
		$fileId = $this->_fileService->add($filePath, $submissionDir . '/' . uniqid() . '.' . $extension);
		$mimeType = PKPString::mime_content_type($filePath);

		/** @var SubmissionFileDAO $submissionFileDao */
		$submissionFileDao = $this->getCachedDao('SubmissionFileDAO');

		/** @var SubmissionFile $submissionFile */
		$submissionFile = $submissionFileDao->newDataObject();
		$submissionFile->setData('submissionId', $submissionId);
		$submissionFile->setData('uploaderUserId', $this->_userId);
		$submissionFile->setSubmissionLocale($data->locale);
		$submissionFile->setGenreId($genreId);
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
	}

	/**
	 * Process data for Keywords
	 *
	 * @param object $data
	 * @param int $publicationId
	 */
	private function processKeywords($data, $publicationId) {
		$keywordsList = [$data->locale => explode(';', $data->keywords)];

		if (count($keywordsList[$data->locale]) > 0) {
			/** @var SubmissionKeywordDAO $submissionKeywordDao */
			$submissionKeywordDao = $this->getCachedDao('SubmissionKeywordDAO');
			$submissionKeywordDao->insertKeywords($keywordsList, $publicationId);
		}
	}

	/**
	 * Process data for Subjects
	 *
	 * @param object $data
	 * @param int $publicationId
	 */
	private function processSubjects($data, $publicationId) {
		$subjectsList = [$data->locale => explode(';', $data->subjects)];

		if (count($subjectsList[$data->locale]) > 0) {
			/** @var SubmissionSubjectDAO $submissionSubjectDao */
			$submissionSubjectDao = $this->getCachedDao('SubmissionSubjectDAO');
			$submissionSubjectDao->insertSubjects($subjectsList, $publicationId);
		}
	}

	/**
	 * Process data for the Book Cover Image
	 *
	 * @param object $data
	 * @param string $srcFilePath
	 * @param Publication $publication
	 */
	private function processBookCoverImage($data, $srcFilePath, $publication) {
		$press = $this->_presses[$data->pressPath];
		$coverImagelocale = [];
		$coverImage = [];

		$sanitizedCoverImageName = str_replace([' ', '_', ':'], '-', mb_strtolower($data->bookCoverImage));
		$sanitizedCoverImageName = PKPstring::regexp_replace('/[^a-z0-9\.\-]+/', '', $sanitizedCoverImageName);
		$sanitizedCoverImageName = basename($sanitizedCoverImageName);

		$coverImage['uploadName'] = uniqid() . '-' . $sanitizedCoverImageName;
		$coverImage['altText'] = $bookCoverImageAltText ?? '';

		$destFilePath = $this->_publicFileManager->getContextFilesPath($press->getId()) . '/' . $coverImage['uploadName'];
		copy($srcFilePath, $destFilePath);

		$this->_publicationService->makeThumbnail(
			$destFilePath,
			$this->_publicationService->getThumbnailFileName($coverImage['uploadName']),
			$press->getData('coverThumbnailsMaxWidth'),
			$press->getData('coverThumbnailsMaxHeight')
		);

		$coverImagelocale[$data->locale] = $coverImage;

		$publication->setData('coverImage', $coverImagelocale);

		/** @var PublicationDAO $publicationDao */
		$publicationDao = $this->getCachedDao('PublicationDAO');
		$publicationDao->updateObject($publication);
	}
}
