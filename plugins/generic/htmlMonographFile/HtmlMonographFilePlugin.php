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
use APP\core\Request;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\monograph\ChapterDAO;
use APP\observers\events\UsageEvent;
use APP\publication\Publication;
use APP\publicationFormat\PublicationFormat;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\plugins\Hook;
use PKP\submissionFile\SubmissionFile;

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
                echo $this->_getHTMLContents($request, $submission, $publicationFormat, $submissionFile);
                $returner = true;
                Hook::call('HtmlMonographFilePlugin::monographDownloadFinished', [&$returner]);

                $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
                $chapter = $chapterDao->getChapter($submissionFile->getData('chapterId'));
                event(new UsageEvent(Application::ASSOC_TYPE_SUBMISSION_FILE, $request->getContext(), $submission, $publicationFormat, $submissionFile, $chapter));
                return true;
            }
        }

        return false;
    }

    /**
     * Return string containing the contents of the HTML file.
     * This function performs any necessary filtering, like image URL replacement.
     *
     * @param Request $request
     * @param Submission $monograph
     * @param PublicationFormat $publicationFormat
     * @param SubmissionFile $submissionFile
     *
     * @return string
     */
    public function _getHTMLContents($request, $monograph, $publicationFormat, $submissionFile)
    {
        $contents = app()->get('file')->fs->read($submissionFile->getData('path'));

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
                Application::ASSOC_TYPE_SUBMISSION_FILE,
                [$submissionFile->getId()]
            );

        $embeddableFiles = array_merge(
            $proofCollector->getMany()->toArray(),
            $dependentCollector->getMany()->toArray()
        );

        foreach ($embeddableFiles as $embeddableFile) {
            $fileUrl = $request->url(null, 'catalog', 'download', [$monograph->getBestId(), 'version', $publicationFormat->getData('publicationId'), $publicationFormat->getBestId(), $embeddableFile->getBestId()], ['inline' => true]);
            $pattern = preg_quote($embeddableFile->getLocalizedData('name'), '/');

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
            switch (strtolower($urlParts[0])) {
                case 'press':
                    $url = $request->url(
                        $urlParts[1] ?? $request->getRouter()->getRequestedContextPath($request),
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
