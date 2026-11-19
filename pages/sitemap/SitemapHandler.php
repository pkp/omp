<?php

/**
 * @file pages/sitemap/SitemapHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SitemapHandler
 *
 * @ingroup pages_sitemap
 *
 * @brief Produce a sitemap in XML format for submitting to search engines.
 */

namespace APP\pages\sitemap;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\monograph\ChapterDAO;
use APP\publicationFormat\PublicationFormatDAO;
use APP\submission\Submission;
use DOMDocument;
use Illuminate\Support\Collection;
use PKP\db\DAORegistry;
use PKP\pages\sitemap\PKPSitemapHandler;
use PKP\plugins\Hook;

class SitemapHandler extends PKPSitemapHandler
{
    /** Number of submissions whose URLs are read with one set of queries */
    public const SUBMISSIONS_PER_BATCH = 1000;

    /**
     * @copydoc PKPSitemapHandler::_createContextSitemap()
     *
     * @hook SitemapHandler::createPressSitemap [[&$doc]]
     */
    public function _createContextSitemap($request)
    {
        $doc = parent::_createContextSitemap($request);
        $root = $doc->documentElement;

        $press = $request->getPress();
        $pressId = $press->getId();

        // Catalog
        $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog')));

        // Books, chapters and files. Only the best ids are read here: loading the submissions
        // would also load every publication with its chapters, formats, DOIs and authors.
        $submissionBestIds = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$pressId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getBestIds();
        foreach ($submissionBestIds->chunk(self::SUBMISSIONS_PER_BATCH) as $batch) {
            $this->_addSubmissionUrls($doc, $request, $batch);
        }

        // New releases
        $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'newReleases')));
        // Browse by series
        $seriesResult = Repo::section()
            ->getCollector()
            ->filterByContextIds([$pressId])
            ->getMany();
        foreach ($seriesResult as $series) {
            $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'series', [$series->getPath()])));
        }
        // Browse by categories
        $categories = Repo::category()->getCollector()
            ->filterByContextIds([$pressId])
            ->getMany();

        foreach ($categories as $category) {
            $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'category', [$category->getPath()])));
        }

        $doc->appendChild($root);

        // Enable plugins to change the sitemap
        Hook::call('SitemapHandler::createPressSitemap', [&$doc]);

        return $doc;
    }

    /**
     * Add the book, chapter and file URLs of published submissions, in the given order
     *
     * @param Collection<int,object{currentPublicationId:?int,bestId:int|string}> $submissionBestIds See Submission\DAO::getBestIds()
     */
    protected function _addSubmissionUrls(DOMDocument $doc, Request $request, Collection $submissionBestIds): void
    {
        $root = $doc->documentElement;
        $pressPath = $request->getPress()->getPath();

        $publicationIds = $submissionBestIds->pluck('currentPublicationId')->filter()->all();

        /** @var ChapterDAO $chapterDao */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $sourceChapterIds = $chapterDao->getSourceChapterIdsWithPage($publicationIds);

        /** @var PublicationFormatDAO $formatDao */
        $formatDao = DAORegistry::getDAO('PublicationFormatDAO');
        $formatBestIds = $formatDao->getAvailableBestIds($publicationIds);

        // Only files with a direct sales price are offered in the catalog
        $formatIds = $formatBestIds->flatten(1)->pluck('formatId')->all();
        $fileBestIds = count($formatIds)
            ? Repo::submissionFile()
                ->getCollector()
                ->filterByHasDirectSalesPrice(true)
                ->filterBySubmissionIds($submissionBestIds->keys()->all())
                ->filterByAssoc(Application::ASSOC_TYPE_PUBLICATION_FORMAT, $formatIds)
                ->getBestIds()
                ->groupBy('assocId')
            : collect();

        foreach ($submissionBestIds as $submissionBestId) {
            // Book
            $root->appendChild($this->_createUrlTree($doc, $request->url($pressPath, 'catalog', 'book', [$submissionBestId->bestId])));
            // Chapters
            foreach ($sourceChapterIds->get($submissionBestId->currentPublicationId, []) as $sourceChapterId) {
                $root->appendChild($this->_createUrlTree($doc, $request->url($pressPath, 'catalog', 'book', [$submissionBestId->bestId, 'chapter', $sourceChapterId])));
            }
            // Files
            foreach ($formatBestIds->get($submissionBestId->currentPublicationId, []) as $formatBestId) {
                foreach ($fileBestIds->get($formatBestId->formatId, []) as $fileBestId) {
                    // Same PDF URL as citation_pdf_url, which Google Scholar indexes
                    $url = $fileBestId->mimetype === 'application/pdf'
                        ? $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $pressPath, 'catalog', 'download', [$submissionBestId->bestId, $formatBestId->bestId, $fileBestId->bestId], urlLocaleForPage: '')
                        : $request->url($pressPath, 'catalog', 'view', [$submissionBestId->bestId, $formatBestId->bestId, $fileBestId->bestId]);
                    $root->appendChild($this->_createUrlTree($doc, $url));
                }
            }
        }
    }
}
