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

use PKP\submission\SubmissionFile;
use PKP\submission\PKPSubmission;
use PKP\file\TemoraryFileManager;
use PKP\file\FileManager;

use APP\template\TemplateManager;

import('lib.pkp.classes.plugins.ImportExportPlugin');

class CSVImportExportPlugin extends ImportExportPlugin
{
    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     *
     * @return String name of plugin
     */
    public function getName()
    {
        return 'CSVImportExportPlugin';
    }

    public function getDisplayName()
    {
        return __('plugins.importexport.csv.displayName');
    }

    public function getDescription()
    {
        return __('plugins.importexport.csv.description');
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $actionArgs)
    {
        return []; // Not available via the web interface
    }

    /**
     * Display the plugin.
     *
     * @param $args array
     * @param $request PKPRequest
     */
    public function display($args, $request)
    {
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
     *
     * @param $args Parameters to the plugin
     */
    public function executeCLI($scriptName, &$args)
    {
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);

        $filename = array_shift($args);
        $username = array_shift($args);

        if (!$filename || !$username) {
            $this->usage($scriptName);
            exit();
        }

        if (!file_exists($filename)) {
            echo __('plugins.importexport.csv.fileDoesNotExist', ['filename' => $filename]) . "\n";
            exit();
        }

        $data = file($filename);

        if (is_array($data) && count($data) > 0) {
            $userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
            $user = $userDao->getByUsername($username);
            if (!$user) {
                echo __('plugins.importexport.csv.unknownUser', ['username' => $username]) . "\n";
                exit();
            }

            $submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
            $authorDao = DAORegistry::getDAO('AuthorDAO'); /* @var $authorDao AuthorDAO */
            $pressDao = Application::getContextDAO();
            $userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
            $seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
            $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /* @var $publicationFormatDao PublicationFormatDAO */
            $submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
            import('lib.pkp.classes.submission.SubmissionFile'); // constants.
            $genreDao = DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
            $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /* @var $publicationDateDao PublicationDateDAO */

            foreach ($data as $csvLine) {
                // Format is:
                // Press Path, Author string, title, series path, year, is_edited_volume, locale, URL to PDF, doi (optional)
                [$pressPath, $authorString, $title, $seriesPath, $year, $isEditedVolume, $locale, $pdfUrl, $doi] = preg_split('/\t/', $csvLine);

                $press = $pressDao->getByPath($pressPath);

                if ($press) {
                    $supportedLocales = $press->getSupportedSubmissionLocales();
                    if (!is_array($supportedLocales) || count($supportedLocales) < 1) {
                        $supportedLocales = [$press->getPrimaryLocale()];
                    }
                    $authorGroup = $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_AUTHOR);

                    // we need a Genre for the files.  Assume a key of MANUSCRIPT as a default.
                    $genre = $genreDao->getByKey('MANUSCRIPT', $press->getId());

                    if (!$genre) {
                        echo __('plugins.importexport.csv.noGenre') . "\n";
                        exit();
                    }
                    if (!$authorGroup) {
                        echo __('plugins.importexport.csv.noAuthorGroup', ['press' => $pressPath]) . "\n";
                        continue;
                    }
                    if (in_array($locale, $supportedLocales)) {
                        $submission = $submissionDao->newDataObject();
                        $submission->setContextId($press->getId());
                        $submission->setUserId($user->getId());
                        $submission->stampLastActivity();
                        $submission->setStatus(PKPSubmission::STATUS_PUBLISHED);
                        $submission->setWorkType($isEditedVolume == 1 ? WORK_TYPE_EDITED_VOLUME : WORK_TYPE_AUTHORED_WORK);
                        $submission->setCopyrightNotice($press->getLocalizedSetting('copyrightNotice'), $locale);
                        $submission->setLocale($locale);

                        $series = $seriesDao->getByPath($seriesPath, $press->getId());
                        if ($series) {
                            $submission->setSeriesId($series->getId());
                        } else {
                            echo __('plugins.importexport.csv.noSeries', ['seriesPath' => $seriesPath]) . "\n";
                        }

                        $submissionId = $submissionDao->insertObject($submission);

                        $contactEmail = $press->getContactEmail();
                        $authorString = trim($authorString, '"'); // remove double quotes if present.
                        $authors = preg_split('/,\s*/', $authorString);
                        $firstAuthor = true;
                        foreach ($authors as $authorString) {
                            // Examine the author string. Best case is: Given1 Family1 <email@address.com>, Given2 Family2 <email@address.com>, etc
                            // But default to press email address based on press path if not present.
                            $givenName = $familyName = $emailAddress = null;
                            $authorString = trim($authorString); // whitespace.
                            if (preg_match('/^(\w+)(\s+\w+)?\s*(<([^>]+)>)?$/', $authorString, $matches)) {
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
                        $publicationFormat->setSequence(REALLY_BIG_NUMBER);
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
                        $publicationDate->setPublicationFormatId($publicationFormatId);
                        $publicationDateDao->insertObject($publicationDate);

                        // Submission File.
                        $temporaryFileManager = new TemporaryFileManager();
                        $temporaryFilename = tempnam($temporaryFileManager->getBasePath(), 'remote');
                        $temporaryFileManager->copyFile($pdfUrl, $temporaryFilename);
                        $submissionFile = $submissionFileDao->newDataObject();
                        $submissionFile->setSubmissionId($submissionId);
                        $submissionFile->setSubmissionLocale($submission->getLocale());
                        $submissionFile->setGenreId($genre->getId());
                        $submissionFile->setFileStage(SubmissionFile::SUBMISSION_FILE_PROOF);
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
                        $fileManager->deleteByPath($temporaryFilename);

                        echo __('plugins.importexport.csv.import.submission', ['title' => $title]) . "\n";
                    } else {
                        echo __('plugins.importexport.csv.unknownLocale', ['locale' => $locale]) . "\n";
                    }
                } else {
                    echo __('plugins.importexport.csv.unknownPress', ['pressPath' => $pressPath]) . "\n";
                }
            }
        }
    }

    /**
     * Display the command-line usage information
     */
    public function usage($scriptName)
    {
        echo __('plugins.importexport.csv.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }
    /**
     * Define the appropriate import filter given the imported XML file path
     *
     * @param string $xmlFile
     *
     * @return array Containing the filter and the xmlString of the imported file
     */
    public function getImportFilter($xmlFile)
    {
        throw new BadMethodCallException();
    }

    /**
     * Define the appropriate export filter given the export operation
     *
     * @param string $exportType
     *
     * @return string
     */
    public function getExportFilter($exportType)
    {
        throw new BadMethodCallException();
    }

    /**
     * Get the application specific deployment object
     *
     * @param Context $context
     * @param User $user
     *
     * @return PKPImportExportDeployment
     */
    public function getAppSpecificDeployment($context, $user)
    {
        throw new BadMethodCallException();
    }
}
