<?php

/**
 * @file plugins/importexport/csv/CSVImportExportPlugin.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CSVImportExportPlugin
 *
 * @brief CSV import/export plugin
 */

namespace APP\plugins\importexport\csv;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\publicationFormat\PublicationDateDAO;
use APP\publicationFormat\PublicationFormatDAO;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\file\FileManager;
use PKP\plugins\ImportExportPlugin;
use PKP\security\Role;
use PKP\submission\GenreDAO;
use PKP\submission\PKPSubmission;
use PKP\submissionFile\SubmissionFile;

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
     * @return string name of plugin
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
     * @param array $args
     * @param Request $request
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
     * @param array $args Parameters to the plugin
     */
    public function executeCLI($scriptName, &$args)
    {
        $filename = array_shift($args);
        $username = array_shift($args);

        if (!$filename || !$username) {
            $this->usage($scriptName);
            exit;
        }

        if (!file_exists($filename)) {
            echo __('plugins.importexport.csv.fileDoesNotExist', ['filename' => $filename]) . "\n";
            exit;
        }

        $data = file($filename);

        if (is_array($data) && count($data) > 0) {
            $user = Repo::user()->getByUsername($username);
            if (!$user) {
                echo __('plugins.importexport.csv.unknownUser', ['username' => $username]) . "\n";
                exit;
            }

            $pressDao = Application::getContextDAO();
            $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO'); /** @var PublicationFormatDAO $publicationFormatDao */
            $submissionFileDao = Repo::submissionFile()->dao;
            $genreDao = DAORegistry::getDAO('GenreDAO'); /** @var GenreDAO $genreDao */
            $publicationDateDao = DAORegistry::getDAO('PublicationDateDAO'); /** @var PublicationDateDAO $publicationDateDao */

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
                    $authorGroup = Repo::userGroup()->getCollector()
                        ->filterByContextIds([$press->getId()])
                        ->filterByRoleIds([Role::ROLE_ID_AUTHOR])
                        ->filterByIsDefault(true)
                        ->getMany()
                        ->first();

                    // we need a Genre for the files.  Assume a key of MANUSCRIPT as a default.
                    $genre = $genreDao->getByKey('MANUSCRIPT', $press->getId());

                    if (!$genre) {
                        echo __('plugins.importexport.csv.noGenre') . "\n";
                        exit;
                    }
                    if (!$authorGroup) {
                        echo __('plugins.importexport.csv.noAuthorGroup', ['press' => $pressPath]) . "\n";
                        continue;
                    }
                    if (in_array($locale, $supportedLocales)) {
                        $submission = Repo::submission()->newDataObject();
                        $submission->setContextId($press->getId());
                        $submission->setData('uploaderUserId', $user->getId());
                        $submission->stampLastActivity();
                        $submission->setStatus(PKPSubmission::STATUS_PUBLISHED);
                        $submission->setWorkType($isEditedVolume == 1 ? Submission::WORK_TYPE_EDITED_VOLUME : Submission::WORK_TYPE_AUTHORED_WORK);
                        $submission->setCopyrightNotice($press->getLocalizedSetting('copyrightNotice'), $locale);
                        $submission->setLocale($locale);

                        $series = $seriesPath ? Repo::section()->getByPath($seriesPath, $press->getId()) : null;
                        if ($series) {
                            $submission->setSeriesId($series->getId());
                        } else {
                            // It is possible to have submission without series
                            // echo __('plugins.importexport.csv.noSeries', ['seriesPath' => $seriesPath]) . "\n";
                        }

                        $submissionId = Repo::submission()->dao->insert($submission);

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
                            $author = Repo::author()->newDataObject();
                            $author->setSubmissionId($submissionId);
                            $author->setUserGroupId($authorGroup->getId());
                            $author->setGivenName($givenName, $locale);
                            $author->setFamilyName($familyName, $locale);
                            $author->setEmail($emailAddress);
                            if ($firstAuthor) {
                                $author->setPrimaryContact(1);
                                $firstAuthor = false;
                            }
                            Repo::author()->add($author);
                        } // Authors done.

                        $submission->setTitle($title, $locale);
                        Repo::submission()->dao->update($submission);

                        // Submission is done.  Create a publication format for it.
                        $publicationFormat = $publicationFormatDao->newDataObject();
                        $publicationFormat->setPhysicalFormat(false);
                        $publicationFormat->setIsApproved(true);
                        $publicationFormat->setIsAvailable(true);
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
                        $fileManager = new FileManager();
                        $extension = $fileManager->parseFileExtension($_FILES['uploadedFile']['name']);
                        $submissionDir = Repo::submissionFile()->getSubmissionDir($press->getId(), $submissionId);
                        /** @var PKPFileService */
                        $fileService = Services::get('file');
                        $fileId = $fileService->add(
                            $pdfUrl,
                            $submissionDir . '/' . uniqid() . '.' . $extension
                        );

                        $submissionFile = $submissionFileDao->newDataObject();
                        $submissionFile->setData('submissionId', $submissionId);
                        $submissionFile->setSubmissionLocale($submission->getLocale());
                        $submissionFile->setGenreId($genre->getId());
                        $submissionFile->setFileStage(SubmissionFile::SUBMISSION_FILE_PROOF);
                        $submissionFile->setAssocType(Application::ASSOC_TYPE_REPRESENTATION);
                        $submissionFile->setData('assocId', $publicationFormatId);
                        $submissionFile->setData('mimetype', 'application/pdf');
                        $submissionFile->setData('fileId', $fileId);

                        // Assume open access, no price.
                        $submissionFile->setDirectSalesPrice(0);
                        $submissionFile->setSalesType('openAccess');

                        Repo::submissionFile()
                            ->add($submissionFile);

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
}
