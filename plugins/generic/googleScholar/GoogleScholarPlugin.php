<?php

/**
 * @file plugins/generic/googleScholar/GoogleScholarPlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class GoogleScholarPlugin
 * @brief Inject Google Scholar meta tags into monograph views to facilitate indexing.
 */

namespace APP\plugins\generic\googleScholar;

use APP\facades\Repo;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class GoogleScholarPlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                Hook::add('CatalogBookHandler::book', [&$this, 'monographView']);
            }
            return true;
        }
        return false;
    }

    /**
     * Get the name of the settings file to be installed on new context
     * creation.
     *
     * @return string
     */
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Inject Google Scholar metadata into monograph/edited volume landing page
     *
     * @param string $hookName
     * @param array $args
     *
     * @return bool
     */
    public function monographView($hookName, $args)
    {
        $request = $args[0];
        $submission = $args[1];
        $templateMgr = TemplateManager::getManager($request);

        $publication = $submission->getCurrentPublication();
        $press = $request->getContext();
        $series = $templateMgr->getTemplateVars('series');
        $availableFiles = $templateMgr->getTemplateVars('availableFiles');
        $isChapterRequest = $templateMgr->getTemplateVars('isChapterRequest');
        $chapter = $templateMgr->getTemplateVars('chapter');


        // Google scholar metadata  revision
        $templateMgr->addHeader('googleScholarRevision', '<meta name="gs_meta_revision" content="1.1"/>');

        // Book/Edited volume or Chapter title of the submission
        $title = $isChapterRequest ? $chapter->getLocalizedFullTitle($publication->getData('locale')) : $publication->getLocalizedFullTitle($publication->getData('locale'));
        $templateMgr->addHeader('googleScholarTitle', '<meta name="citation_title" content="' . htmlspecialchars($title) . '"/>');
        // Language
        if ($locale = $publication->getData('locale')) {
            $templateMgr->addHeader('googleScholarLanguage', '<meta name="citation_language" content="' . htmlspecialchars(substr($locale, 0, 2)) . '"/>');
        }

        // Publication date
        $templateMgr->addHeader('googleScholarDate', '<meta name="citation_publication_date" content="' . date('Y-m-d', strtotime($publication->getData('datePublished'))) . '"/>');

        // Authors in order
        $authors = $isChapterRequest ? $templateMgr->getTemplateVars('chapterAuthors') : Repo::author()->getSubmissionAuthors($submission);
        $i = 0;
        foreach ($authors as $author) {
            $templateMgr->addHeader('googleScholarAuthor' . $i++, '<meta name="citation_author" content="' . htmlspecialchars($author->getFullName(false)) . '"/>');
        }

        // Abstract
        $i = 0;
        $abstracts = $isChapterRequest ? $chapter->getData('abstract') : $publication->getData('abstract');
        foreach ($abstracts as $locale => $abstract) {
            $templateMgr->addHeader('googleScholarAbstract' . $i++, '<meta name="citation_abstract" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($abstract)) . '"/>');
        }


        // Publication DOI
        if ($publication->getData('pub-id::doi')) {
            $templateMgr->addHeader('googleScholarPublicationDOI', '<meta name="citation_doi" content="' . htmlspecialchars($publication->getData('pub-id::doi')) . '"/>');
        }

        // Subjects
        $i = 0;
        $submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO');
        /** @var SubmissionSubjectDAO $submissionSubjectDao */
        $supportedLocales = array_keys(Locale::getSupportedFormLocales());
        if ($subjects = $submissionSubjectDao->getSubjects($publication->getId(), $supportedLocales)) {
            foreach ($subjects as $locale => $subjectLocale) {
                foreach ($subjectLocale as $gsKeyword) {
                    $templateMgr->addHeader('googleScholarSubject' . $i++, '<meta name="citation_keywords" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($gsKeyword) . '"/>');
                }
            }
        }

        // Keywords
        $i = 0;
        $submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
        /** @var SubmissionKeywordDAO $submissionKeywordDao */
        if ($keywords = $submissionKeywordDao->getKeywords($publication->getId(), $supportedLocales)) {
            foreach ($keywords as $locale => $keywordLocale) {
                foreach ($keywordLocale as $gsKeyword) {
                    $templateMgr->addHeader('googleScholarKeyword' . $i++, '<meta name="citation_keywords" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($gsKeyword) . '"/>');
                }
            }
        }

        // Publication URL and ISBN numbers
        $publicationFormats = $publication->getData('publicationFormats');
        $i = 0;
        foreach ($availableFiles as $availableFile) {
            foreach ($publicationFormats as $publicationFormat) {
                if ((int)$publicationFormat->getData('id') == (int)$availableFile->getData('assocId')) {
                    if (!$isChapterRequest && $availableFile->getData('chapterId') == false) {
                        $identificationCodes = $publicationFormat->getIdentificationCodes();
                        while ($identificationCode = $identificationCodes->next()) {
                            if ($identificationCode->getCode() == '02' || $identificationCode->getCode() == '15') {
                                // 02 and 15: ONIX codes for ISBN-10 or ISBN-13
                                $templateMgr->addHeader('googleScholarIsbn' . $i++, '<meta name="citation_isbn" content="' . htmlspecialchars($identificationCode->getValue()) . '"/>');
                            }
                        }
                        $this->_setFileUrl($availableFile, $templateMgr, $i, $request, $submission);
                    } elseif ($isChapterRequest) {
                        $chapter = $templateMgr->getTemplateVars('chapter');
                        if ($chapter->getData('id') == $availableFile->getData('chapterId')) {
                            $this->_setFileUrl($availableFile, $templateMgr, $i, $request, $submission);
                        }
                    }
                }
            }
        }
        // Publisher
        $templateMgr->addHeader('googleScholarPublisher', '<meta name="citation_publisher" content="' . htmlspecialchars($press->getName($press->getPrimaryLocale())) . '"/>');

        // Series ISSN (online)
        $series = $templateMgr->getTemplateVars('series');
        if ($series && $issn = $series->getOnlineISSN()) {
            $templateMgr->addHeader('googleScholarIssn', '<meta name="citation_issn" content="' . htmlspecialchars($issn) . '"/> ');
        }

        return false;
    }

    /**
     * Get the display name of this plugin
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.generic.googleScholar.name');
    }

    /**
     * Get the description of this plugin
     *
     * @return string
     */
    public function getDescription()
    {
        return __('plugins.generic.googleScholar.description');
    }

    private function _setFileUrl($availableFile, \APP\template\TemplateManager $templateMgr, int $i, \APP\Core\Request $request, \APP\submission\Submission $submission): void
    {
        switch ($availableFile->getData('mimetype')) {
            case 'application/pdf':
                $templateMgr->addHeader('googleScholarPdfUrl' . $i++, '<meta name="citation_pdf_url" content="' . $request->url(null, 'catalog', 'download', [$submission->getData('id'), $availableFile->getData('assocId'), $availableFile->getData('id')]) . '"/>');
                break;
            case 'text/xml' or 'text/html':
                $templateMgr->addHeader('googleScholarPdfUrl' . $i++, '<meta name="citation_fulltext_html_url" content="' . $request->url(null, 'catalog', 'download', [$submission->getData('id'), $availableFile->getData('assocId'), $availableFile->getData('id')]) . '"/>');
                break;
        }
    }
}
