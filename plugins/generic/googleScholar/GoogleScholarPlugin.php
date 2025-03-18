<?php

/**
 * @file plugins/generic/googleScholar/GoogleScholarPlugin.php
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class GoogleScholarPlugin
 *
 * @brief Inject Google Scholar meta tags into monograph views to facilitate indexing.
 */

namespace APP\plugins\generic\googleScholar;

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\citation\CitationDAO;
use PKP\db\DAORegistry;
use PKP\i18n\LocaleConversion;
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
     *
     * @hook GoogleScholarPlugin::references [[&$outputReferences, $submission->getId()]]
     */
    public function monographView($hookName, $args)
    {
        $request = $args[0];
        $submission = $args[1];

        // Only add Google Scholar metadata tags to the canonical URL for the latest version
        // See discussion: https://github.com/pkp/pkp-lib/issues/4870
        $requestArgs = $request->getRequestedArgs();
        if (in_array('version', $requestArgs)) {
            return false;
        }

        $templateMgr = TemplateManager::getManager($request);

        $publication = $submission->getCurrentPublication();
        $press = $request->getContext();
        $series = $templateMgr->getTemplateVars('series');
        $availableFiles = $templateMgr->getTemplateVars('availableFiles');
        $isChapterRequest = $templateMgr->getTemplateVars('isChapterRequest');
        $chapter = $templateMgr->getTemplateVars('chapter');
        $publicationLocale = $publication->getData('locale');

        // Google scholar metadata  revision
        $templateMgr->addHeader('googleScholarRevision', '<meta name="gs_meta_revision" content="1.1"/>');

        // Book/Edited volume or Chapter title of the submission
        $title = $isChapterRequest ? $chapter->getLocalizedFullTitle($publicationLocale) : $publication->getLocalizedFullTitle($publicationLocale);
        $templateMgr->addHeader('googleScholarTitle', '<meta name="citation_title" content="' . htmlspecialchars($title) . '"/>');

        // Language
        $templateMgr->addHeader('googleScholarLanguage', '<meta name="citation_language" content="' . htmlspecialchars(LocaleConversion::toBcp47($publicationLocale)) . '"/>');

        // Publication date
        $datePublished = $isChapterRequest
            ? ($submission->getEnableChapterPublicationDates() && $chapter->getDatePublished()
                ? $chapter->getDatePublished()
                : $publication->getData('datePublished'))
            : $publication->getData('datePublished');
        if ($datePublished) {
            $templateMgr->addHeader('googleScholarDate', '<meta name="citation_publication_date" content="' . date('Y-m-d', strtotime($datePublished)) . '"/>');
        }

        // Authors in order
        $authors = $isChapterRequest ? $templateMgr->getTemplateVars('chapterAuthors') : $publication->getData('authors');
        foreach ($authors as $i => $author) {
            $templateMgr->addHeader('googleScholarAuthor' . $i++, '<meta name="citation_author" content="' . htmlspecialchars($author->getFullName(false, false, $publicationLocale)) . '"/>');
            foreach ($author->getAffiliations() as $affiliation) {
                $templateMgr->addHeader(
                    'googleScholarAuthor' . $i++ . 'Affiliation' . $affiliation->getId(),
                    '<meta name="citation_author_institution" content="' . htmlspecialchars($affiliation->getLocalizedName($publicationLocale)) . '"/>'
                );
            }
        }

        // Abstract
        $abstract = $isChapterRequest ? $chapter->getLocalizedData('abstract', $publicationLocale) : $publication->getLocalizedData('abstract', $publicationLocale);
        if ($abstract != '') {
            $templateMgr->addHeader('googleScholarAbstract', '<meta name="citation_abstract" xml:lang="' . htmlspecialchars(LocaleConversion::toBcp47($publicationLocale)) . '" content="' . htmlspecialchars(strip_tags($abstract)) . '"/>');
        }

        // Publication DOI
        if ($doi = $publication->getDoi()) {
            $templateMgr->addHeader('googleScholarPublicationDOI', '<meta name="citation_doi" content="' . htmlspecialchars($doi) . '"/>');
        }

        // Subjects
        if ($subjects = $publication->getData('subjects')) {
            foreach ($subjects as $locale => $localeSubjects) {
                foreach ($localeSubjects as $i => $subject) {
                    $templateMgr->addHeader('googleScholarSubject' . $i++, '<meta name="citation_keywords" xml:lang="' . htmlspecialchars(LocaleConversion::toBcp47($locale)) . '" content="' . htmlspecialchars($subject) . '"/>');
                }
            }
        }

        // Keywords
        if ($keywords = $publication->getData('keywords')) {
            foreach ($keywords as $locale => $localeKeywords) {
                foreach ($localeKeywords as $i => $keyword) {
                    $templateMgr->addHeader('googleScholarKeyword' . $i++, '<meta name="citation_keywords" xml:lang="' . htmlspecialchars(LocaleConversion::toBcp47($locale)) . '" content="' . htmlspecialchars($keyword) . '"/>');
                }
            }
        }

        // Publication URL and ISBN numbers
        $publicationFormats = $publication->getData('publicationFormats');
        $i = 0;
        foreach ($availableFiles as $availableFile) {
            foreach ($publicationFormats as $publicationFormat) {
                if ((int)$publicationFormat->getId() == (int)$availableFile->getData('assocId')) {
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
                        if ($chapter->getId() == $availableFile->getData('chapterId')) {
                            $this->_setFileUrl($availableFile, $templateMgr, $i, $request, $submission);
                        }
                    }
                }
            }
        }

        // Publisher
        $templateMgr->addHeader('googleScholarPublisher', '<meta name="citation_publisher" content="' . htmlspecialchars($press->getName($press->getPrimaryLocale())) . '"/>');

        // Series ISSN (online)
        if ($series && $issn = $series->getOnlineISSN()) {
            $templateMgr->addHeader('googleScholarIssn', '<meta name="citation_issn" content="' . htmlspecialchars($issn) . '"/> ');
        }

        // Citations
        $outputReferences = [];
        $citationDao = DAORegistry::getDAO('CitationDAO'); /** @var CitationDAO $citationDao */
        $parsedCitations = $citationDao->getByPublicationId($publication->getId());
        while ($citation = $parsedCitations->next()) {
            $outputReferences[] = $citation->getRawCitation();
        }
        Hook::call('GoogleScholarPlugin::references', [&$outputReferences, $submission->getId()]);

        foreach ($outputReferences as $i => $outputReference) {
            $templateMgr->addHeader('googleScholarReference' . $i++, '<meta name="citation_reference" content="' . htmlspecialchars($outputReference) . '"/>');
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

    private function _setFileUrl($availableFile, TemplateManager $templateMgr, int $i, \APP\Core\Request $request, \APP\submission\Submission $submission): void
    {
        switch ($availableFile->getData('mimetype')) {
            case 'application/pdf':
                $templateMgr->addHeader('googleScholarPdfUrl' . $i++, '<meta name="citation_pdf_url" content="' . $request->getDispatcher()->url($request, Application::ROUTE_PAGE, null, 'catalog', 'download', [$submission->getId(), $availableFile->getData('assocId'), $availableFile->getId()], urlLocaleForPage: '') . '"/>');
                break;
            case 'text/xml' or 'text/html':
                $templateMgr->addHeader('googleScholarHtmlUrl' . $i++, '<meta name="citation_fulltext_html_url" content="' . $request->getDispatcher()->url($request, Application::ROUTE_PAGE, null, 'catalog', 'download', [$submission->getId(), $availableFile->getData('assocId'), $availableFile->getId()], urlLocaleForPage: '') . '"/>');
                break;
        }
    }
}
