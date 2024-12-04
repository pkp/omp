<?php

/**
 * @file plugins/generic/dublinCoreMeta/DublinCoreMetaPlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DublinCoreMetaPlugin
 *
 * @brief Inject Dublin Core meta tags into monograph views to facilitate indexing.
 */

namespace APP\plugins\generic\dublinCoreMeta;

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class DublinCoreMetaPlugin extends GenericPlugin
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
                Hook::add('CatalogBookHandler::view', [$this, 'monographFileView']);
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
     * Inject Dublin Core metadata into monograph view
     *
     * @param string $hookName
     * @param array $args
     *
     * @return bool
     */
    public function monographView($hookName, $args)
    {
        $request = $args[0];
        $monograph = $args[1];
        $requestArgs = $request->getRequestedArgs();
        $press = $request->getContext();

        // Only add Google Scholar metadata tags to the canonical URL for the latest version
        // See discussion: https://github.com/pkp/pkp-lib/issues/4870
        if (count($requestArgs) > 1 && $requestArgs[1] === 'version') {
            return false;
        }

        $publication = $monograph->getCurrentPublication();
        $publicationLocale = $publication->getData('locale');
        $submissionBestId = strlen($urlPath = (string) $publication->getData('urlPath')) ? $urlPath : $monograph->getId();

        $templateMgr = TemplateManager::getManager($request);
        $isChapterRequest = $templateMgr->getTemplateVars('isChapterRequest');
        $chapter = $templateMgr->getTemplateVars('chapter');

        $templateMgr->addHeader('dublinCoreSchema', '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />');

        if ($supportingAgencies = $publication->getData('supportingAgencies')) {
            foreach ($supportingAgencies as $locale => $localeSupportingAgencies) {
                foreach ($localeSupportingAgencies as $i => $supportingAgency) {
                    $templateMgr->addHeader('dublinCoreSponsor' . $locale . $i++, '<meta name="DC.Contributor.Sponsor" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars($supportingAgency) . '"/>');
                }
            }
        }

        if ($coverages = $publication->getData('coverage')) {
            foreach ($coverages as $locale => $coverage) {
                if ($coverage != '') {
                    $templateMgr->addHeader('dublinCoreCoverage' . $locale, '<meta name="DC.Coverage" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars(strip_tags($coverage)) . '"/>');
                }
            }
        }

        $authors = $isChapterRequest ? $templateMgr->getTemplateVars('chapterAuthors') : $publication->getData('authors');
        foreach ($authors as $i => $author) {
            $templateMgr->addHeader('dublinCoreAuthor' . $i++, '<meta name="DC.Creator.PersonalName" content="' . htmlspecialchars($author->getFullName(false, false, $publicationLocale)) . '"/>');
        }

        $datePublished = $isChapterRequest
            ? ($monograph->getEnableChapterPublicationDates() && $chapter->getDatePublished()
                ? $chapter->getDatePublished()
                : $publication->getData('datePublished'))
            : $publication->getData('datePublished');
        if ($datePublished) {
            $templateMgr->addHeader('dublinCoreDateCreated', '<meta name="DC.Date.created" scheme="ISO8601" content="' . date('Y-m-d', strtotime($datePublished)) . '"/>');
        }
        $templateMgr->addHeader('dublinCoreDateSubmitted', '<meta name="DC.Date.dateSubmitted" scheme="ISO8601" content="' . date('Y-m-d', strtotime($monograph->getData('dateSubmitted'))) . '"/>');
        if ($dateModified = $publication->getData('lastModified')) {
            $templateMgr->addHeader('dublinCoreDateModified', '<meta name="DC.Date.modified" scheme="ISO8601" content="' . date('Y-m-d', strtotime($dateModified)) . '"/>');
        }

        $abstracts = $isChapterRequest ? $chapter->getData('abstract') : $publication->getData('abstract');
        foreach ($abstracts ?: [] as $locale => $abstract) {
            if ($abstract != '') {
                $templateMgr->addHeader('dublinCoreAbstract' . $locale, '<meta name="DC.Description" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars(strip_tags($abstract)) . '"/>');
            }
        }

        $templateMgr->addHeader('dublinCoreIdentifier', '<meta name="DC.Identifier" content="' . htmlspecialchars($submissionBestId) . '"/>');

        $doi = $isChapterRequest ? $chapter->getDoi() : $publication->getDoi();
        if ($doi) {
            $templateMgr->addHeader('dublinCorePubIdDOI', '<meta name="DC.Identifier.DOI" content="' . htmlspecialchars($doi) . '"/>');
        }
        foreach ((array) $templateMgr->getTemplateVars('pubIdPlugins') as $pubIdPlugin) {
            if ($pubId = $isChapterRequest ? $chapter->getStoredPubId($pubIdPlugin->getPubIdType()) : $publication->getStoredPubId($pubIdPlugin->getPubIdType())) {
                $templateMgr->addHeader('dublinCorePubId' . $pubIdPlugin->getPubIdDisplayType(), '<meta name="DC.Identifier.' . htmlspecialchars($pubIdPlugin->getPubIdDisplayType()) . '" content="' . htmlspecialchars($pubId) . '"/>');
            }
        }

        $templateMgr->addHeader('dublinCoreUri', '<meta name="DC.Identifier.URI" content="' . $request->getDispatcher()->url($request, Application::ROUTE_PAGE, null, 'catalog', 'book', [$submissionBestId], urlLocaleForPage: '') . '"/>');

        $templateMgr->addHeader('dublinCoreLanguage', '<meta name="DC.Language" scheme="rfc5646" content="' . str_replace(['_', '@'], '-', $publicationLocale) . '"/>');

        if (($copyrightHolder = $publication->getData('copyrightHolder', $publicationLocale)) && ($copyrightYear = $publication->getData('copyrightYear'))) {
            $templateMgr->addHeader('dublinCoreCopyright', '<meta name="DC.Rights" content="' . htmlspecialchars(__('submission.copyrightStatement', ['copyrightHolder' => $copyrightHolder, 'copyrightYear' => $copyrightYear])) . '"/>');
        }
        if ($licenseURL = $publication->getData('licenseUrl')) {
            $templateMgr->addHeader('dublinCorePagesLicenseUrl', '<meta name="DC.Rights" content="' . htmlspecialchars($licenseURL) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreSource', '<meta name="DC.Source" content="' . htmlspecialchars($press->getName($press->getPrimaryLocale())) . '"/>');
        $templateMgr->addHeader('dublinCoreSourceUri', '<meta name="DC.Source.URI" content="' . $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $press->getPath(), urlLocaleForPage: '') . '"/>');

        if ($subjects = $publication->getData('subjects')) {
            foreach ($subjects as $locale => $localeSubjects) {
                foreach ($localeSubjects as $i => $subject) {
                    $templateMgr->addHeader('dublinCoreSubject' . $locale . $i++, '<meta name="DC.Subject" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars($subject) . '"/>');
                }
            }
        }
        if ($keywords = $publication->getData('keywords')) {
            foreach ($keywords as $locale => $localeKeywords) {
                foreach ($localeKeywords as $i => $keyword) {
                    $templateMgr->addHeader('dublinCoreKeyword' . $locale . $i++, '<meta name="DC.Subject" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars($keyword) . '"/>');
                }
            }
        }

        $title = $isChapterRequest ? $chapter->getLocalizedFullTitle($publicationLocale) : $publication->getLocalizedFullTitle($publicationLocale);
        $templateMgr->addHeader('dublinCoreTitle', '<meta name="DC.Title" content="' . htmlspecialchars($title) . '"/>');
        $titles = $isChapterRequest ? $chapter->getFullTitles() : $publication->getFullTitles();
        foreach ($titles as $locale => $altTitle) {
            if ($title != '' && $locale != $publicationLocale) {
                $templateMgr->addHeader('dublinCoreAltTitle' . $locale, '<meta name="DC.Title.Alternative" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars($altTitle) . '"/>');
            }
        }

        $templateMgr->addHeader('dublinCoreType', '<meta name="DC.Type" content="Text.Book"/>');
        if ($types = $publication->getData('type')) {
            foreach ($types as $locale => $type) {
                if ($type != '') {
                    $templateMgr->addHeader('dublinCoreType' . $locale, '<meta name="DC.Type" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars(strip_tags($type)) . '"/>');
                }
            }
        }

        return false;
    }

    /**
     * Inject Dublin Core metadata into monograph file view
     *
     * @param string $hookName
     * @param array $args
     *
     * @return bool
     */
    public function monographFileView($hookName, $args)
    {
        $monograph = $args[1];
        $publicationFormat = $args[2];
        $submissionFile = $args[3];

        $publication = $monograph->getCurrentPublication();

        // Only add Google Scholar metadata tags to the canonical URL for the latest version
        // See discussion: https://github.com/pkp/pkp-lib/issues/4870
        if ($publicationFormat->getData('publicationId') != $publication->getId()) {
            return false;
        }

        $request = Application::get()->getRequest();
        $press = $request->getContext();

        $publicationLocale = $publication->getData('locale');
        $submissionBestId = strlen($urlPath = (string) $publication->getData('urlPath')) ? $urlPath : $monograph->getId();

        $templateMgr = TemplateManager::getManager($request);
        $chapter = $templateMgr->getTemplateVars('chapter');
        $series = $templateMgr->getTemplateVars('series');

        $templateMgr->addHeader('dublinCoreSchema', '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />');

        if ($supportingAgencies = $publication->getData('supportingAgencies')) {
            foreach ($supportingAgencies as $locale => $localeSupportingAgencies) {
                foreach ($localeSupportingAgencies as $i => $supportingAgency) {
                    $templateMgr->addHeader('dublinCoreSponsor' . $locale . $i++, '<meta name="DC.Contributor.Sponsor" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars($supportingAgency) . '"/>');
                }
            }
        }

        if ($coverages = $publication->getData('coverage')) {
            foreach ($coverages as $locale => $coverage) {
                if ($coverage != '') {
                    $templateMgr->addHeader('dublinCoreCoverage' . $locale, '<meta name="DC.Coverage" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars(strip_tags($coverage)) . '"/>');
                }
            }
        }

        $authors = $chapter ? $chapter->getAuthors()->toArray() : $publication->getData('authors');
        foreach ($authors as $i => $author) {
            $templateMgr->addHeader('dublinCoreAuthor' . $i++, '<meta name="DC.Creator.PersonalName" content="' . htmlspecialchars($author->getFullName(false, false, $publicationLocale)) . '"/>');
        }

        $datePublished = $chapter
            ? ($monograph->getEnableChapterPublicationDates() && $chapter->getDatePublished()
                ? $chapter->getDatePublished()
                : $publication->getData('datePublished'))
            : $publication->getData('datePublished');
        if ($datePublished) {
            $templateMgr->addHeader('dublinCoreDateCreated', '<meta name="DC.Date.created" scheme="ISO8601" content="' . date('Y-m-d', strtotime($datePublished)) . '"/>');
        }
        $templateMgr->addHeader('dublinCoreDateSubmitted', '<meta name="DC.Date.dateSubmitted" scheme="ISO8601" content="' . date('Y-m-d', strtotime($monograph->getData('dateSubmitted'))) . '"/>');
        if ($dateModified = $publication->getData('lastModified')) {
            $templateMgr->addHeader('dublinCoreDateModified', '<meta name="DC.Date.modified" scheme="ISO8601" content="' . date('Y-m-d', strtotime($dateModified)) . '"/>');
        }

        $abstracts = $chapter ? $chapter->getData('abstract') : $publication->getData('abstract');
        foreach ($abstracts ?: [] as $locale => $abstract) {
            if ($abstract != '') {
                $templateMgr->addHeader('dublinCoreAbstract' . $locale, '<meta name="DC.Description" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars(strip_tags($abstract)) . '"/>');
            }
        }

        $templateMgr->addHeader('dublinCoreIdentifier', '<meta name="DC.Identifier" content="' . htmlspecialchars($submissionBestId . '/' . $publicationFormat->getId() . '/' . $submissionFile->getId()) . '"/>');

        $pages = $chapter ? $chapter->getData('pages') : $publication->getData('pages');
        if ($pages) {
            $templateMgr->addHeader('dublinCorePages', '<meta name="DC.Identifier.pageNumber" content="' . htmlspecialchars($pages) . '"/>');
        }

        $doi = $submissionFile->getDoi() ?? $chapter ? $chapter->getDoi() : $publication->getDoi();
        if ($doi) {
            $templateMgr->addHeader('dublinCorePubIdDOI', '<meta name="DC.Identifier.DOI" content="' . htmlspecialchars($doi) . '"/>');
        }

        foreach ((array) $templateMgr->getTemplateVars('pubIdPlugins') as $pubIdPlugin) {
            if ($pubId = $submissionFile->getStoredPubId($pubIdPlugin->getPubIdType()) ?? $chapter ? $chapter->getDoi() : $publication->getStoredPubId($pubIdPlugin->getPubIdType())) {
                $templateMgr->addHeader('dublinCorePubId' . $pubIdPlugin->getPubIdDisplayType(), '<meta name="DC.Identifier.' . htmlspecialchars($pubIdPlugin->getPubIdDisplayType()) . '" content="' . htmlspecialchars($pubId) . '"/>');
            }
        }

        $templateMgr->addHeader('dublinCoreUri', '<meta name="DC.Identifier.URI" content="' . $request->getDispatcher()->url($request, Application::ROUTE_PAGE, null, 'catalog', 'book', [$submissionBestId, $publicationFormat->getId(), $submissionFile->getId()], urlLocaleForPage: '') . '"/>');

        $templateMgr->addHeader('dublinCoreLanguage', '<meta name="DC.Language" scheme="ISO639-1" content="' . str_replace(['_', '@'], '-', $publicationLocale) . '"/>');

        if (($copyrightHolder = $publication->getData('copyrightHolder', $publicationLocale)) && ($copyrightYear = $publication->getData('copyrightYear'))) {
            $templateMgr->addHeader('dublinCoreCopyright', '<meta name="DC.Rights" content="' . htmlspecialchars(__('submission.copyrightStatement', ['copyrightHolder' => $copyrightHolder, 'copyrightYear' => $copyrightYear])) . '"/>');
        }
        if ($licenseURL = $publication->getData('licenseUrl')) {
            $templateMgr->addHeader('dublinCorePagesLicenseUrl', '<meta name="DC.Rights" content="' . htmlspecialchars($licenseURL) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreSource', '<meta name="DC.Source" content="' . htmlspecialchars($press->getName($press->getPrimaryLocale())) . '"/>');
        if ($series && $issn = $series->getOnlineISSN()) {
            $templateMgr->addHeader('dublinCoreIssn', '<meta name="DC.Source.ISSN" content="' . htmlspecialchars($issn) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreSourceUri', '<meta name="DC.Source.URI" content="' . $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $press->getPath(), urlLocaleForPage: '') . '"/>');

        if ($subjects = $publication->getData('subjects')) {
            foreach ($subjects as $locale => $localeSubjects) {
                foreach ($localeSubjects as $i => $subject) {
                    $templateMgr->addHeader('dublinCoreSubject' . $locale . $i++, '<meta name="DC.Subject" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars($subject) . '"/>');
                }
            }
        }
        if ($keywords = $publication->getData('keywords')) {
            foreach ($keywords as $locale => $localeKeywords) {
                foreach ($localeKeywords as $i => $keyword) {
                    $templateMgr->addHeader('dublinCoreKeyword' . $locale . $i++, '<meta name="DC.Subject" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars($keyword) . '"/>');
                }
            }
        }


        $title = $chapter ? $chapter->getLocalizedFullTitle($publicationLocale) : $publication->getLocalizedFullTitle($publicationLocale);
        $templateMgr->addHeader('dublinCoreTitle', '<meta name="DC.Title" content="' . htmlspecialchars($title) . '"/>');
        $titles = $chapter ? $chapter->getFullTitles() : $publication->getFullTitles();
        foreach ($titles as $locale => $altTitle) {
            if ($title != '' && $locale != $publicationLocale) {
                $templateMgr->addHeader('dublinCoreAltTitle' . $locale, '<meta name="DC.Title.Alternative" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars($altTitle) . '"/>');
            }
        }

        $templateMgr->addHeader('dublinCoreType', '<meta name="DC.Type" content="Text.Chapter"/>');
        if ($types = $publication->getData('type')) {
            foreach ($types as $locale => $type) {
                if ($type != '') {
                    $templateMgr->addHeader('dublinCoreType' . $locale, '<meta name="DC.Type" xml:lang="' . htmlspecialchars(str_replace(['_', '@'], '-', $locale)) . '" content="' . htmlspecialchars(strip_tags($type)) . '"/>');
                }
            }
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
        return __('plugins.generic.dublinCoreMeta.name');
    }

    /**
     * Get the description of this plugin
     *
     * @return string
     */
    public function getDescription()
    {
        return __('plugins.generic.dublinCoreMeta.description');
    }
}
