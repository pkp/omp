<?php

/**
 * @file plugins/generic/htmlMonographFile/HtmlMonographFilePlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class HtmlMonographFilePlugin
 * @ingroup plugins_generic_htmlMonographFile
 *
 * @brief Class for HtmlMonographFile plugin
 */

use APP\core\Application;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\observers\events\Usage;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\plugins\GenericPlugin;
use PKP\plugins\HookRegistry;
use PKP\submissionFile\SubmissionFile;

import('lib.pkp.classes.plugins.GenericPlugin');

class HtmlMonographFilePlugin extends GenericPlugin
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
                HookRegistry::register('CatalogBookHandler::view', [$this, 'viewCallback']);
                HookRegistry::register('CatalogBookHandler::download', [$this, 'downloadCallback']);
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
        $submission = & $params[1];
        $publicationFormat = & $params[2];
        $submissionFile = & $params[3];
        $inline = & $params[4];
        $request = Application::get()->getRequest();

        $mimetype = $submissionFile->getData('mimetype');
        if ($submissionFile && $mimetype == 'text/html') {
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
     */
    public function downloadCallback($hookName, $params)
    {
        $submission = & $params[1];
        $publicationFormat = & $params[2];
        $submissionFile = & $params[3];
        $inline = & $params[4];
        $request = Application::get()->getRequest();

        $mimetype = $submissionFile->getData('mimetype');
        if ($submissionFile && $mimetype == 'text/html') {
            if (!HookRegistry::call('HtmlMonographFilePlugin::monographDownload', [&$this, &$submission, &$publicationFormat, &$submissionFile, &$inline])) {
                echo $this->_getHTMLContents($request, $submission, $publicationFormat, $submissionFile);
                $returner = true;
                HookRegistry::call('HtmlMonographFilePlugin::monographDownloadFinished', [&$returner]);

                $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
                $chapter = $chapterDao->getChapter($submissionFile->getData('chapterId'));
                event(new Usage(Application::ASSOC_TYPE_SUBMISSION_FILE, $request->getContext(), $submission, $publicationFormat, $submissionFile, $chapter));
                return true;
            }
        }

        return false;
    }
    /**
     * Return string containing the contents of the HTML file.
     * This function performs any necessary filtering, like image URL replacement.
     *
     * @param PKPRequest $request
     * @param Monograph $monograph
     * @param PublicationFormat $publicationFormat
     * @param SubmissionFile $submissionFile
     *
     * @return string
     */
    public function _getHTMLContents($request, $monograph, $publicationFormat, $submissionFile)
    {
        $contents = Services::get('file')->fs->read($submissionFile->getData('path'));

        // Replace media file references
        $proofCollector = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$monograph->getId()])
            ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF]);

        $dependentCollector = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$monograph->getId()])
            ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_DEPENDENT])
            ->filterByAssoc(
                ASSOC_TYPE_SUBMISSION_FILE,
                [$submissionFile->getId()]
            );

        $embeddableFiles = array_merge(
            Repo::submissionFile()->getMany($proofCollector),
            Repo::submissionFile()->getMany($dependentCollector)
        );

        foreach ($embeddableFiles as $embeddableFile) {
            $fileUrl = $request->url(null, 'catalog', 'download', [$monograph->getBestId(), $publicationFormat->getBestId(), $embeddableFile->getBestId()], ['inline' => true]);
            $pattern = preg_quote($embeddableFile->getLocalizedData('name'));

            $contents = preg_replace(
                '/([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $pattern . ')"/',
                '\1="' . $fileUrl . '"',
                $contents
            );

            // Replacement for Flowplayer
            $contents = preg_replace(
                '/[Uu][Rr][Ll]\s*\:\s*\'(' . $pattern . ')\'/',
                'url:\'' . $fileUrl . '\'',
                $contents
            );

            // Replacement for other players (tested with odeo; yahoo and google player won't work w/ OJS URLs, might work for others)
            $contents = preg_replace(
                '/[Uu][Rr][Ll]=([^"]*' . $pattern . ')/',
                'url=' . $fileUrl,
                $contents
            );
        }

        // Perform replacement for ojs://... URLs
        $contents = preg_replace_callback(
            '/(<[^<>]*")[Oo][Mm][Pp]:\/\/([^"]+)("[^<>]*>)/',
            [&$this, '_handleOmpUrl'],
            $contents
        );

        $templateMgr = TemplateManager::getManager($request);
        $contents = $templateMgr->loadHtmlGalleyStyles($contents, $embeddableFiles);

        // Perform variable replacement for press, publication format, site info
        $press = $request->getPress();
        $site = $request->getSite();

        $paramArray = [
            'pressTitle' => $press->getLocalizedName(),
            'siteTitle' => $site->getLocalizedTitle(),
            'currentUrl' => $request->getRequestUrl()
        ];

        foreach ($paramArray as $key => $value) {
            $contents = str_replace('{$' . $key . '}', $value, $contents);
        }

        return $contents;
    }

    public function _handleOmpUrl($matchArray)
    {
        $request = Application::get()->getRequest();
        $url = $matchArray[2];
        $anchor = null;
        if (($i = strpos($url, '#')) !== false) {
            $anchor = substr($url, $i + 1);
            $url = substr($url, 0, $i);
        }
        $urlParts = explode('/', $url);
        if (isset($urlParts[0])) {
            switch (strtolower_codesafe($urlParts[0])) {
            case 'press':
                $url = $request->url(
                    $urlParts[1] ?? $request->getRequestedPressPath(),
                    null,
                    null,
                    null,
                    null,
                    $anchor
                );
                break;
            case 'monograph':
                if (isset($urlParts[1])) {
                    $url = $request->url(
                        null,
                        'catalog',
                        'book',
                        $urlParts[1],
                        null,
                        $anchor
                    );
                }
                break;
            case 'sitepublic':
                array_shift($urlParts);
                $publicFileManager = new PublicFileManager();
                $url = $request->getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath() . '/' . implode('/', $urlParts) . ($anchor ? '#' . $anchor : '');
                break;
            case 'public':
                array_shift($urlParts);
                $press = $request->getPress();
                $publicFileManager = new PublicFileManager();
                $url = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($press->getId()) . '/' . implode('/', $urlParts) . ($anchor ? '#' . $anchor : '');
                break;
        }
        }
        return $matchArray[1] . $url . $matchArray[3];
    }
}
