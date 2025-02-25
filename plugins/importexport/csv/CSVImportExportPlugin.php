<?php

/**
 * @file plugins/importexport/csv/CSVImportExportPlugin.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CSVImportExportPlugin
 * @ingroup plugins_importexport_csv
 *
 * @brief CSV import/export plugin
 */

namespace APP\plugins\importexport\csv;

use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\plugins\importexport\csv\classes\caches\CachedDaos;
use APP\plugins\importexport\csv\classes\caches\CachedEntities;
use APP\plugins\importexport\csv\classes\handlers\CSVFileHandler;
use APP\plugins\importexport\csv\classes\processors\AuthorsProcessor;
use APP\plugins\importexport\csv\classes\processors\KeywordsProcessor;
use APP\plugins\importexport\csv\classes\processors\PublicationDateProcessor;
use APP\plugins\importexport\csv\classes\processors\PublicationFileProcessor;
use APP\plugins\importexport\csv\classes\processors\PublicationFormatProcessor;
use APP\plugins\importexport\csv\classes\processors\PublicationProcessor;
use APP\plugins\importexport\csv\classes\processors\SubjectsProcessor;
use APP\plugins\importexport\csv\classes\processors\SubmissionProcessor;
use APP\plugins\importexport\csv\classes\validations\CategoryValidations;
use APP\plugins\importexport\csv\classes\validations\InvalidRowValidations;
use APP\plugins\importexport\csv\classes\validations\SubmissionHeadersValidation;
use APP\publication\Repository as PublicationService;
use APP\template\TemplateManager;
use Exception;
use PKP\core\PKPString;
use PKP\file\FileManager;
use PKP\plugins\ImportExportPlugin;
use PKP\services\PKPFileService;
use SplFileObject;

class CSVImportExportPlugin extends ImportExportPlugin {
    /**
     * The file directory array map used by the application.
     *
     * @var string[]
     */
    private array $dirNames;

    /** The default format for the publication file path */
    private string $format;

    private FileManager $fileManager;

    private PublicFileManager $publicFileManager;

    private PKPFileService $fileService;

    private PublicationService $publicationService;

    private SplFileObject $invalidRowsFile;

    private int $failedRowsCount;

    private int $processedRowsCount;

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
     * @copydoc ImportExportPlugin::executeCLI()
    */
    function executeCLI($scriptName, &$args) {
        [$filename, $username, $basePath] = $this->parseCommandLineArguments($scriptName, $args);

        $this->validateUser($username);
        $file = CSVFileHandler::createAndValidateCSVFile($filename);

        $csvForInvalidRowsName = "{$basePath}/invalid_rows.csv";
        $this->invalidRowsFile = CSVFileHandler::createInvalidCSVFile($csvForInvalidRowsName);

        $this->processedRowsCount = 0;
        $this->failedRowsCount = 0;

        foreach ($file as $index => $fields) {
            if (!$index) {
                continue; // Skip headers
            }

            if (empty(array_filter($fields))) {
                continue; // End of file
            }

            ++$this->processedRowsCount;

            $reason = InvalidRowValidations::validateRowContainAllFields($fields);
            if ($reason) {
                CSVFileHandler::processInvalidRows($fields, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                continue;
            }

            $fieldsList = array_pad(array_map('trim', $fields), count(SubmissionHeadersValidation::$expectedHeaders), null);
            $data = (object) array_combine(SubmissionHeadersValidation::$expectedHeaders, $fieldsList);

            $reason = InvalidRowValidations::validateRowHasAllRequiredFields($data);
            if ($reason) {
                CSVFileHandler::processInvalidRows($fields, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                continue;
            }

            $press = CachedEntities::getCachedPress($data->pressPath);

            $reason = InvalidRowValidations::validatePresIsValid($data->pressPath, $press);
            if ($reason) {
                CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                continue;
            }

            $reason = InvalidRowValidations::validatePressLocales($data->locale, $press);
            if ($reason) {
                CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                continue;
            }

            $pressId = $press->getId();

            // we need a Genre for the files.  Assume a key of MANUSCRIPT as a default.
            $genreName = mb_strtoupper($data->genreName ?? 'MANUSCRIPT');
            $genreId = CachedEntities::getCachedGenreId($pressId, $genreName);

            $reason = InvalidRowValidations::validateGenreIsValid($genreId, $genreName);
            if ($reason) {
                CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                continue;
            }

            $userGroupId = CachedEntities::getCachedUserGroupId($pressId, $data->pressPath);

            $reason = InvalidRowValidations::validateUserGroupId($userGroupId, $data->pressPath);
            if ($reason) {
                CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                continue;
            }

            $filePath = "{$basePath}/{$data->filename}";

            $reason = InvalidRowValidations::validateAssetFile($filePath, $data->title);
            if ($reason) {
                CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                continue;
            }

            $pressSeriesId = null;
            if ($data->seriesPath) {
                $pressSeriesId = CachedEntities::getCachedSeriesId($data->seriesPath, $pressId);

                $reason = InvalidRowValidations::validateSeriesId($pressSeriesId, $data->seriesPath);
                if ($reason) {
                    CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                    continue;
                }
            }

            $this->initializeStaticVariables();

            if ($data->bookCoverImage) {
                $reason = InvalidRowValidations::validateBookCoverImageInRightFormat($data->bookCoverImage);
                if ($reason) {
                    CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                    continue;
                }

                $srcFilePath = "{$basePath}/{$data->bookCoverImage}";

                $reason = InvalidRowValidations::validateBookCoverImageIsReadable($srcFilePath, $data->title);
                if ($reason) {
                    CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                    continue;
                }

                $sanitizedCoverImageName = str_replace([' ', '_', ':'], '-', mb_strtolower($data->bookCoverImage));
                $sanitizedCoverImageName = PKPString::regexp_replace('/[^a-z0-9\.\-]+/', '', $sanitizedCoverImageName);
                $sanitizedCoverImageName = basename($sanitizedCoverImageName);

                $coverImageUploadName = uniqid() . '-' . $sanitizedCoverImageName;

                $destFilePath = $this->publicFileManager->getContextFilesPath($pressId) . '/' . $coverImageUploadName;
                $bookCoverImageSaved =  $this->fileManager->copyFile($srcFilePath, $destFilePath);

                if (!$bookCoverImageSaved) {
                    $reason = __('plugin.importexport.csv.erroWhileSavingBookCoverImage');
                    CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);

                    continue;
                }

                // Try to create the book cover image thumbnail. If it fails for some reason, add this row as an invalid
                // and the book cover image will be deleted before jump for the next CSV row.
                try {
                    $this->publicationService->makeThumbnail(
                        $destFilePath,
                        $this->publicationService->getThumbnailFileName($coverImageUploadName),
                        $press->getData('coverThumbnailsMaxWidth'),
                        $press->getData('coverThumbnailsMaxHeight')
                    );
                } catch (Exception $exception) {
                    $reason = __('plugin.importexport.csv.errorWhileSavingThumbnail');
                    CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);

                    unlink($destFilePath);

                    continue;
                }
            }

            $dbCategoryIds = CategoryValidations::getCategoryDataForValidRow($data->categories, $pressId, $data->locale);

            $reason = InvalidRowValidations::validateAllCategoriesExists($dbCategoryIds);
            if ($reason) {
                CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);
                continue;
            }

            // All requirements passed. Start processing from here.
            $submission = SubmissionProcessor::process($data, $pressId);
            $submissionId = $submission->getId();

            // Copy Submission file. If an error occured, save this row as invalid, delete the saved submission and continue the loop.
            try {
                $extension = $this->fileManager->parseFileExtension($data->filename);
                $submissionDir = sprintf($this->format, $pressId, $submissionId);
                $fileId = $this->fileService->add($filePath, $submissionDir . '/' . uniqid() . '.' . $extension);
            } catch (Exception $exception) {
                $reason = __('plugin.importexport.csv.errorWhileSavingSubmissionFile');
                CSVFileHandler::processInvalidRows($fieldsList, $reason, $this->invalidRowsFile, $this->failedRowsCount);

                $submissionDao = CachedDaos::getSubmissionDao();
                $submissionDao->deleteById($submissionId);

                continue;
            }

            $publication = PublicationProcessor::process($submission, $data, $press, $pressSeriesId);
            $publicationId = $publication->getId();
            AuthorsProcessor::process($data, $press->getContactEmail(), $submissionId, $publication, $userGroupId);

            // Submission is done.  Create a publication format for it.
            $publicationFormatId = PublicationFormatProcessor::process($submissionId, $publicationId, $extension, $data);

            PublicationDateProcessor::process($data->year, $publicationFormatId);

            // Submission File.
            PublicationFileProcessor::process($data, $submissionId, $filePath, $publicationFormatId, $genreId, $fileId);

            KeywordsProcessor::process($data, $publicationId);
            SubjectsProcessor::process($data, $publicationId);

            if ($data->bookCoverImage) {
                PublicationProcessor::updateBookCoverImage($publication, $coverImageUploadName, $data);
            }

            $categoryDao = CachedDaos::getCategoryDao();
            foreach ($dbCategoryIds as $categoryId) {
                $categoryDao->insertPublicationAssignment($categoryId, $publicationId);
            }

            echo __('plugins.importexport.csv.import.submission', ['title' => $data->title]) . "\n";
        }

        if ($this->failedRowsCount === 0) {
            echo __('plugin.importexport.csv.allDataSuccessfullyImported', ['processedRows' => $this->processedRowsCount]) . "\n\n";
            unlink($csvForInvalidRowsName);
        } else {
            echo __('plugin.importexport.csv.seeInvalidRowsFile', ['processedRows' => $this->processedRowsCount - $this->failedRowsCount, 'failedRows' => $this->failedRowsCount]) . "\n\n";
        }
    }

    /** Display the command-line usage information */
    function usage($scriptName) {
        echo __('plugins.importexport.csv.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }

    /**
     * Parse and validate initial command args
     *
     * @return string[]
     */
    private function parseCommandLineArguments(string $scriptName, array $args): array
    {
        $filename = array_shift($args);
        $username = array_shift($args);
        $basePath = dirname($filename);

        if (!$filename || !$username) {
            $this->usage($scriptName);
            exit(1);
        }

        return [$filename, $username, $basePath];
    }

    /** Retrieve and validate the User by username */
    private function validateUser(string $username): void
    {
        if (!CachedEntities::getCachedUser($username)) {
            echo __('plugins.importexport.csv.unknownUser', ['username' => $username]) . "\n";
            exit(1);
        }
    }

    /** Insert static data that will be used for the submission processing */
    private function initializeStaticVariables(): void
    {
        $this->dirNames ??= Application::getFileDirectories();
        $this->format ??= trim($this->dirNames['context'], '/') . '/%d/' . trim($this->dirNames['submission'], '/') . '/%d';
        $this->fileManager ??= new FileManager();
        $this->publicFileManager ??= new PublicFileManager();
        $this->fileService ??= Services::get('file');
        $this->publicationService ??= Repo::publication();
    }
}
