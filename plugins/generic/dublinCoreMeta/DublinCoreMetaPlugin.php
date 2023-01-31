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
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
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
        $publication = $monograph->getCurrentPublication();
        $press = $request->getContext();
        $templateMgr = TemplateManager::getManager($request);
        $isChapterRequest = $templateMgr->getTemplateVars('isChapterRequest');
        $chapter = $templateMgr->getTemplateVars('chapter');

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->addHeader('dublinCoreSchema', '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />');

        $i = 0;
        if ($sponsors = $publication->getData('supportingAgencies')) {
            foreach ($sponsors as $locale => $sponsor) {
                $templateMgr->addHeader('dublinCoreSponsor' . $i++, '<meta name="DC.Contributor.Sponsor" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($sponsor)) . '"/>');
            }
        }

        $i = 0;
        if ($coverages = $publication->getData('coverage')) {
            foreach ($coverages as $locale => $coverage) {
                $templateMgr->addHeader('dublinCoreCoverage' . $i++, '<meta name="DC.Coverage" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($coverage)) . '"/>');
            }
        }

        $i = 0;
        $authors = $isChapterRequest ? $templateMgr->getTemplateVars('chapterAuthors') : $publication->getData('authors');
        foreach ($authors as $author) {
            $templateMgr->addHeader('dublinCoreAuthor' . $i++, '<meta name="DC.Creator.PersonalName" content="' . htmlspecialchars($author->getFullName(false)) . '"/>');
        }

        if ($datePublished = $publication->getData('datePublished')) {
            $templateMgr->addHeader('dublinCoreDateCreated', '<meta name="DC.Date.created" scheme="ISO8601" content="' . date('Y-m-d', strtotime($datePublished)) . '"/>');
        }
        $templateMgr->addHeader('dublinCoreDateSubmitted', '<meta name="DC.Date.dateSubmitted" scheme="ISO8601" content="' . date('Y-m-d', strtotime($monograph->getDateSubmitted())) . '"/>');
        if ($dateModified = $publication->getData('lastModified')) {
            $templateMgr->addHeader('dublinCoreDateModified', '<meta name="DC.Date.modified" scheme="ISO8601" content="' . date('Y-m-d', strtotime($dateModified)) . '"/>');
        }
        $i = 0;
        $abstracts = $isChapterRequest ? $chapter->getData('abstract') : $publication->getData('abstract');
        foreach ($abstracts as $locale => $abstract) {
            $templateMgr->addHeader('dublinCoreAbstract' . $i++, '<meta name="DC.Description" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($abstract)) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreIdentifier', '<meta name="DC.Identifier" content="' . htmlspecialchars($monograph->getBestId()) . '"/>');

        foreach ((array) $templateMgr->getTemplateVars('pubIdPlugins') as $pubIdPlugin) {
            if ($pubId = $monograph->getStoredPubId($pubIdPlugin->getPubIdType())) {
                $templateMgr->addHeader('dublinCorePubId' . $pubIdPlugin->getPubIdDisplayType(), '<meta name="DC.Identifier.' . htmlspecialchars($pubIdPlugin->getPubIdDisplayType()) . '" content="' . htmlspecialchars($pubId) . '"/>');
            }
        }

        $doi = $isChapterRequest ? $chapter->getDoi() : $publication->getDoi();
        if ($doi) {
            $templateMgr->addHeader('dublinCorePubIdDOI', '<meta name="DC.Identifier.DOI" content="' . htmlspecialchars($doi) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreUri', '<meta name="DC.Identifier.URI" content="' . $request->url(null, 'catalog', 'book', [$monograph->getBestId()]) . '"/>');
        $templateMgr->addHeader('dublinCoreLanguage', '<meta name="DC.Language" scheme="ISO639-1" content="' . substr($publication->getData('locale'), 0, 2) . '"/>');
        $templateMgr->addHeader('dublinCoreCopyright', '<meta name="DC.Rights" content="' . htmlspecialchars(__('submission.copyrightStatement', ['copyrightHolder' => $monograph->getCopyrightHolder($publication->getData('locale')), 'copyrightYear' => $monograph->getCopyrightYear()])) . '"/>');
        $templateMgr->addHeader('dublinCorePagesLicenseUrl', '<meta name="DC.Rights" content="' . htmlspecialchars($monograph->getLicenseURL()) . '"/>');
        $templateMgr->addHeader('dublinCoreSource', '<meta name="DC.Source" content="' . htmlspecialchars($press->getName($press->getPrimaryLocale())) . '"/>');

        $templateMgr->addHeader('dublinCoreSourceUri', '<meta name="DC.Source.URI" content="' . $request->url($press->getPath()) . '"/>');

        $i = 0;
        $submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO'); /** @var SubmissionSubjectDAO $submissionSubjectDao */
        $supportedLocales = array_keys(Locale::getSupportedFormLocales());
        if ($subjects = $submissionSubjectDao->getSubjects($publication->getId(), $supportedLocales)) {
            foreach ($subjects as $locale => $subjectLocale) {
                foreach ($subjectLocale as $subject) {
                    $templateMgr->addHeader('dublinCoreSubject' . $i++, '<meta name="DC.Subject" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($subject) . '"/>');
                }
            }
        }

        $i = 0;
        $submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO'); /** @var SubmissionKeywordDAO $submissionKeywordDao */
        if ($keywords = $submissionKeywordDao->getKeywords($publication->getId(), $supportedLocales)) {
            foreach ($keywords as $locale => $keywordLocale) {
                foreach ($keywordLocale as $keyword) {
                    $templateMgr->addHeader('dublinCoreKeyword' . $i++, '<meta name="DC.Subject" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($keyword) . '"/>');
                }
            }
        }

        $title = $isChapterRequest ? $chapter->getLocalizedFullTitle($publication->getData('locale')) : $publication->getLocalizedFullTitle($publication->getData('locale'));
        $templateMgr->addHeader('dublinCoreTitle', '<meta name="DC.Title" content="' . htmlspecialchars($title) . '"/>');
        $i = 0;
        foreach ($publication->getFullTitles() as $locale => $altTitle) {
            if (empty($title) || $locale === $publication->getData('locale')) {
                continue;
            }
            $templateMgr->addHeader('dublinCoreAltTitle' . $i++, '<meta name="DC.Title.Alternative" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($altTitle) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreType', '<meta name="DC.Type" content="Text.Book"/>');
        $i = 0;
        if ($types = $publication->getData('type')) {
            foreach ($types as $locale => $type) {
                $templateMgr->addHeader('dublinCoreType' . $i++, '<meta name="DC.Type" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($type)) . '"/>');
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
        $request = Application::get()->getRequest();
        $monograph = $args[1];
        $publication = $monograph->getCurrentPublication();
        $publicationFormat = $args[2];
        $submissionFile = $args[3];
        $press = $request->getContext();

        $templateMgr = TemplateManager::getManager($request);
        $chapter = $templateMgr->getTemplateVars('chapter');
        $series = $templateMgr->getTemplateVars('series');

        $templateMgr->addHeader('dublinCoreSchema', '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />');

        $i = 0;
        if ($sponsors = $monograph->getSponsor(null)) {
            foreach ($sponsors as $locale => $sponsor) {
                $templateMgr->addHeader('dublinCoreSponsor' . $i++, '<meta name="DC.Contributor.Sponsor" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($sponsor)) . '"/>');
            }
        }

        $i = 0;
        if ($coverages = $monograph->getCoverage(null)) {
            foreach ($coverages as $locale => $coverage) {
                $templateMgr->addHeader('dublinCoreCoverage' . $i++, '<meta name="DC.Coverage" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($coverage)) . '"/>');
            }
        }

        $i = 0;
        foreach ($chapter ? $chapter->getAuthors()->toArray() : $publication->getData('authors') as $author) {
            $templateMgr->addHeader('dublinCoreAuthor' . $i++, '<meta name="DC.Creator.PersonalName" content="' . htmlspecialchars($author->getFullName(false)) . '"/>');
        }

        if ($monograph instanceof Submission && ($datePublished = $monograph->getDatePublished())) {
            $templateMgr->addHeader('dublinCoreDateCreated', '<meta name="DC.Date.created" scheme="ISO8601" content="' . date('Y-m-d', strtotime($datePublished)) . '"/>');
        }
        $templateMgr->addHeader('dublinCoreDateSubmitted', '<meta name="DC.Date.dateSubmitted" scheme="ISO8601" content="' . date('Y-m-d', strtotime($monograph->getDateSubmitted())) . '"/>');
        if ($dateModified = $monograph->getData('dateLastActivity')) {
            $templateMgr->addHeader('dublinCoreDateModified', '<meta name="DC.Date.modified" scheme="ISO8601" content="' . date('Y-m-d', strtotime($dateModified)) . '"/>');
        }

        $i = 0;
        $abstracts = ($chapter != null) ? $chapter->getData('abstract') : $publication->getData('abstract');
        foreach ($abstracts as $locale => $abstract) {
            $templateMgr->addHeader('dublinCoreAbstract' . $i++, '<meta name="DC.Description" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($abstract)) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreIdentifier', '<meta name="DC.Identifier" content="' . htmlspecialchars($monograph->getBestId() . '/' . $publicationFormat->getId() . '/' . $submissionFile->getId()) . '"/>');

        if ($pages = $monograph->getPages()) {
            $templateMgr->addHeader('dublinCorePages', '<meta name="DC.Identifier.pageNumber" content="' . htmlspecialchars($pages) . '"/>');
        }

        foreach ((array) $templateMgr->getTemplateVars('pubIdPlugins') as $pubIdPlugin) {
            if ($pubId = $monograph->getStoredPubId($pubIdPlugin->getPubIdType())) {
                $templateMgr->addHeader('dublinCorePubId' . $pubIdPlugin->getPubIdDisplayType(), '<meta name="DC.Identifier.' . htmlspecialchars($pubIdPlugin->getPubIdDisplayType()) . '" content="' . htmlspecialchars($pubId) . '"/>');
            }
        }

        $templateMgr->addHeader('dublinCoreUri', '<meta name="DC.Identifier.URI" content="' . $request->url(null, 'catalog', 'book', [$monograph->getBestId(), $publicationFormat->getId(), $submissionFile->getId()]) . '"/>');
        $templateMgr->addHeader('dublinCoreLanguage', '<meta name="DC.Language" scheme="ISO639-1" content="' . substr($monograph->getLocale(), 0, 2) . '"/>');
        $templateMgr->addHeader('dublinCoreCopyright', '<meta name="DC.Rights" content="' . htmlspecialchars(__('submission.copyrightStatement', ['copyrightHolder' => $monograph->getCopyrightHolder($monograph->getLocale()), 'copyrightYear' => $monograph->getCopyrightYear()])) . '"/>');
        $templateMgr->addHeader('dublinCorePagesLicenseUrl', '<meta name="DC.Rights" content="' . htmlspecialchars($monograph->getLicenseURL()) . '"/>');
        $templateMgr->addHeader('dublinCoreSource', '<meta name="DC.Source" content="' . htmlspecialchars($press->getName($press->getPrimaryLocale())) . '"/>');
        if ($series && $issn = $series->getOnlineISSN()) {
            $templateMgr->addHeader('dublinCoreIssn', '<meta name="DC.Source.ISSN" content="' . htmlspecialchars($issn) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreSourceUri', '<meta name="DC.Source.URI" content="' . $request->url($press->getPath()) . '"/>');

        $i = 0;
        $submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO'); /** @var SubmissionSubjectDAO $submissionSubjectDao */
        $supportedLocales = array_keys(Locale::getSupportedFormLocales());
        if ($subjects = $submissionSubjectDao->getSubjects($monograph->getId(), $supportedLocales)) {
            foreach ($subjects as $locale => $subjectLocale) {
                foreach ($subjectLocale as $subject) {
                    $templateMgr->addHeader('dublinCoreSubject' . $i++, '<meta name="DC.Subject" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($subject) . '"/>');
                }
            }
        }

        $i = 0;
        $submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO'); /** @var SubmissionKeywordDAO $submissionKeywordDao */
        if ($keywords = $submissionKeywordDao->getKeywords($monograph->getId(), $supportedLocales)) {
            foreach ($keywords as $locale => $keywordLocale) {
                foreach ($keywordLocale as $keyword) {
                    $templateMgr->addHeader('dublinCoreKeyword' . $i++, '<meta name="DC.Subject" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($keyword) . '"/>');
                }
            }
        }

        $title = $chapter ? $chapter->getLocalizedFullTitle($monograph->getLocale()) : $publication->getLocalizedFullTitle($monograph->getLocale());
        $templateMgr->addHeader('dublinCoreTitle', '<meta name="DC.Title" content="' . htmlspecialchars($title) . '"/>');
        $i = 0;
        $titles = $chapter ? $chapter->getFullTitles() : $publication->getFullTitles();
        foreach ($titles as $locale => $altTitle) {
            if (empty($title) || $locale == $monograph->getLocale()) {
                continue;
            }
            $templateMgr->addHeader('dublinCoreAltTitle' . $i++, '<meta name="DC.Title.Alternative" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars($altTitle) . '"/>');
        }

        $templateMgr->addHeader('dublinCoreType', '<meta name="DC.Type" content="Text.Chapter"/>');
        $i = 0;
        if ($types = $monograph->getType(null)) {
            foreach ($types as $locale => $type) {
                $templateMgr->addHeader('dublinCoreType' . $i++, '<meta name="DC.Type" xml:lang="' . htmlspecialchars(substr($locale, 0, 2)) . '" content="' . htmlspecialchars(strip_tags($type)) . '"/>');
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
