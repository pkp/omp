<?php

/**
 * @file plugins/generic/htmlMonographFile/HtmlMonographFilePlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class HtmlMonographFilePlugin
 *
 * @brief Class for HtmlMonographFile plugin
 */

namespace APP\plugins\generic\htmlMonographFile;

use APP\core\Application;
use APP\monograph\ChapterDAO;
use APP\observers\events\UsageEvent;
use APP\plugins\generic\htmlMonographFile\classes\HtmlGalleyHelper;
use APP\publication\Publication;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\plugins\Hook;

class HtmlMonographFilePlugin extends \PKP\plugins\GenericPlugin
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
                Hook::add('CatalogBookHandler::view', [$this, 'viewCallback']);
                Hook::add('CatalogBookHandler::download', [$this, 'downloadCallback']);
            }
            return true;
        }
        return false;
    }

    /**
     * Install default settings on press creation.
     *
     * @return string
     */
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.generic.htmlMonographFile.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription()
    {
        return __('plugins.generic.htmlMonographFile.description');
    }

    /**
     * Callback to view the HTML content rather than downloading.
     *
     * @param string $hookName
     *
     * @return bool
     */
    public function viewCallback($hookName, $params)
    {
        $submission = &$params[1];
        $publicationFormat = &$params[2];
        $submissionFile = &$params[3];
        $inline = &$params[4];
        $request = Application::get()->getRequest();

        $mimetype = $submissionFile->getData('mimetype');
        if ($submissionFile && $mimetype == 'text/html') {
            /** @var ?Publication */
            $filePublication = null;
            foreach ($submission->getData('publications') as $publication) {
                if ($publication->getId() === $publicationFormat->getData('publicationId')) {
                    $filePublication = $publication;
                    break;
                }
            }
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign([
                'pluginUrl' => $request->getBaseUrl() . '/' . $this->getPluginPath(),
                'monograph' => $submission,
                'publicationFormat' => $publicationFormat,
                'downloadFile' => $submissionFile,
                'isLatestPublication' => $submission->getData('currentPublicationId') === $publicationFormat->getData('publicationId'),
                'filePublication' => $filePublication,
            ]);
            $templateMgr->display($this->getTemplateResource('display.tpl'));
            return true;
        }

        return false;
    }

    /**
     * Callback to rewrite and serve HTML content.
     *
     * @param string $hookName
     *
     * @hook HtmlMonographFilePlugin::monographDownload [[&$this, &$submission, &$publicationFormat, &$submissionFile, &$inline]]
     * @hook HtmlMonographFilePlugin::monographDownloadFinished [[&$returner]]
     */
    public function downloadCallback($hookName, $params)
    {
        $submission = &$params[1];
        $publicationFormat = &$params[2];
        $submissionFile = &$params[3];
        $inline = &$params[4];
        $request = Application::get()->getRequest();

        $mimetype = $submissionFile->getData('mimetype');
        if ($submissionFile && $mimetype == 'text/html') {
            if (!Hook::call('HtmlMonographFilePlugin::monographDownload', [&$this, &$submission, &$publicationFormat, &$submissionFile, &$inline])) {
                echo (new HtmlGalleyHelper())->getHTMLContents($request, $submission, $publicationFormat, $submissionFile);
                $returner = true;
                Hook::call('HtmlMonographFilePlugin::monographDownloadFinished', [&$returner]);

                $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
                $chapterId = $submissionFile->getData('chapterId');
                $chapter = $chapterId ? $chapterDao->getChapter((int) $chapterId) : null;
                event(new UsageEvent(Application::ASSOC_TYPE_SUBMISSION_FILE, $request->getContext(), $submission, $publicationFormat, $submissionFile, $chapter));
                return true;
            }
        }

        return false;
    }
}
