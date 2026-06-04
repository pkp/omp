<?php

/**
 * @file plugins/generic/htmlMonographFile/classes/HtmlGalleyHelper.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class HtmlGalleyHelper
 *
 * @brief Helper for HTML monograph files. Reads an HTML publication-format file
 *   and returns its contents with embeddable-file references (proof, dependent
 *   and publication-level media files) rewritten to download URLs, omp:// URLs
 *   resolved, context styles injected, and {$pressTitle}/{$siteTitle}/
 *   {$currentUrl} placeholders substituted. Shared between the htmlMonographFile
 *   plugin (separate-page rendering) and themes that embed the body inline.
 */

namespace APP\plugins\generic\htmlMonographFile\classes;

use APP\core\Application;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\template\TemplateManager;
use PKP\submissionFile\enums\MediaVariantType;
use PKP\submissionFile\SubmissionFile;

class HtmlGalleyHelper
{
    /**
     * Return the contents of the HTML file with embeddable-file URLs, omp://
     * URLs, context styles and template placeholders all resolved.
     *
     * @param \APP\core\Request $request
     * @param \APP\submission\Submission $monograph
     * @param \APP\publicationFormat\PublicationFormat $publicationFormat
     * @param \PKP\submissionFile\SubmissionFile $submissionFile
     */
    public function getHTMLContents($request, $monograph, $publicationFormat, $submissionFile): string
    {
        $contents = app()->get('file')->fs->read($submissionFile->getData('path'));

        // Collect embeddable files (proof, dependent and publication-level
        // media) whose references are rewritten to download URLs below.
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

        // Publication-level media files can be referenced from any HTML file of
        // the same publication. Embed only the web variant; high-resolution
        // variants are reserved for download/export use cases.
        $mediaFiles = Repo::submissionFile()
            ->getCollector()
            ->filterByAssoc(
                Application::ASSOC_TYPE_PUBLICATION,
                [$publicationFormat->getData('publicationId')]
            )
            ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_MEDIA])
            ->filterByMediaVariantTypes([MediaVariantType::WEB])
            ->getMany();

        // Dedupe by filename in increasing precedence (media < dependent < proof), so the
        // highest-precedence file wins. collect() makes keyBy() dedupe eagerly; toArray()
        // yields a plain array.
        $embeddableFiles = $mediaFiles
            ->concat($dependentCollector->getMany())
            ->concat($proofCollector->getMany())
            ->collect()
            ->keyBy(fn ($file) => $file->getLocalizedData('name'))
            ->toArray();

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

        // Perform replacement for omp://... URLs
        $contents = preg_replace_callback(
            '/(<[^<>]*")[Oo][Mm][Pp]:\/\/([^"]+)("[^<>]*>)/',
            $this->handleOmpUrl(...),
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

    protected function handleOmpUrl(array $matchArray): string
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
