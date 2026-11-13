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
use APP\submission\Submission;
use DOMDocument;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use PKP\db\DAORegistry;
use PKP\pages\sitemap\PKPSitemapHandler;
use PKP\plugins\Hook;
use PKP\services\PKPSchemaService;

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

        // Books, chapters and files. Only the ids are read here: loading the submissions
        // would also load every publication with its chapters, formats, DOIs and authors.
        $submissionIds = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$pressId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getIds()
            ->all();
        foreach (array_chunk($submissionIds, self::SUBMISSIONS_PER_BATCH) as $batch) {
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
     * Add the book, chapter and file URLs of published submissions, in the given order,
     * reading only the columns the URLs need.
     *
     * @param int[] $submissionIds
     */
    protected function _addSubmissionUrls(DOMDocument $doc, Request $request, array $submissionIds): void
    {
        $root = $doc->documentElement;
        $pressPath = $request->getPress()->getPath();

        // The URL path of the current publication (see Submission::getBestId())
        $submissions = DB::table('submissions AS s')
            ->leftJoin('publications AS p', 'p.publication_id', '=', 's.current_publication_id')
            ->whereIn('s.submission_id', $submissionIds)
            ->get(['s.submission_id', 's.current_publication_id', 'p.url_path'])
            ->keyBy('submission_id');

        // Chapters of the current publication with a landing page (see Chapter::isPageEnabled())
        /** @var ChapterDAO $chapterDao */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $chapterIds = [];
        $chapters = DB::table('submission_chapters AS sc')
            ->leftJoin(
                'submission_chapter_settings AS scs',
                fn (JoinClause $join) => $join->on('scs.chapter_id', '=', 'sc.chapter_id')
                    ->where('scs.setting_name', '=', 'isPageEnabled')
                    ->whereRaw("COALESCE(scs.locale, '') = ''")
            )
            ->leftJoin('dois AS d', 'd.doi_id', '=', 'sc.doi_id')
            ->whereIn('sc.publication_id', $submissions->pluck('current_publication_id')->filter()->all())
            ->orderBy('sc.seq')
            ->orderBy('sc.chapter_id')
            ->get(['sc.chapter_id', 'sc.source_chapter_id', 'sc.publication_id', 'scs.setting_name', 'scs.setting_value', 'scs.setting_type', 'd.doi']);
        foreach ($chapters as $chapter) {
            $isPageEnabled = $chapter->setting_name === null ? null : $chapterDao->convertFromDB($chapter->setting_value, $chapter->setting_type);
            if ($isPageEnabled || !empty($chapter->doi)) {
                // Chapter URLs use the id that is shared by all versions (see Chapter::getSourceChapterId())
                $chapterIds[$chapter->publication_id][$chapter->chapter_id] = $chapter->source_chapter_id ?: $chapter->chapter_id;
            }
        }

        // Approved and available formats of the current publication
        // (see PublicationFormatDAO::getApprovedByPublicationId() and PublicationFormat::getIsAvailable())
        $formats = [];
        $formatIds = [];
        $formatRows = DB::table('publication_formats')
            ->whereIn('publication_id', $submissions->pluck('current_publication_id')->filter()->all())
            ->where('is_approved', 1)
            ->orderBy('seq')
            ->orderBy('publication_format_id')
            ->get(['publication_format_id', 'publication_id', 'url_path', 'is_available']);
        foreach ($formatRows as $format) {
            if ($format->is_available) {
                $formats[$format->publication_id][] = $format;
                $formatIds[] = $format->publication_format_id;
            }
        }

        // Files of those formats that have a direct sales price
        $files = [];
        if (count($formatIds)) {
            $fileQuery = Repo::submissionFile()
                ->getCollector()
                ->filterByAssoc(Application::ASSOC_TYPE_PUBLICATION_FORMAT, $formatIds)
                ->filterBySubmissionIds($submissionIds)
                ->getQueryBuilder()
                ->whereNotNull('sf.direct_sales_price')
                ->select(['sf.submission_file_id', 'sf.submission_id', 'sf.assoc_id', 'f.mimetype']);
            // SubmissionFile::getBestId() only finds a publisher id that is part of the schema
            $schema = app()->get('schema')->get(PKPSchemaService::SCHEMA_SUBMISSION_FILE);
            if (isset($schema->properties->{'pub-id::publisher-id'})) {
                $fileQuery->leftJoin(
                    'submission_file_settings AS sfs',
                    fn (JoinClause $join) => $join->on('sfs.submission_file_id', '=', 'sf.submission_file_id')
                        ->where('sfs.setting_name', '=', 'pub-id::publisher-id')
                        ->whereRaw("COALESCE(sfs.locale, '') = ''")
                );
                $fileQuery->addSelect('sfs.setting_value AS publisher_id');
            }
            foreach ($fileQuery->get() as $file) {
                $files[$file->submission_id][$file->assoc_id][$file->submission_file_id] = $file;
            }
        }

        foreach ($submissionIds as $submissionId) {
            $submission = $submissions->get($submissionId);
            if (!$submission) {
                continue;
            }
            $bestId = strlen((string) $submission->url_path) ? $submission->url_path : $submissionId;
            // Book
            $root->appendChild($this->_createUrlTree($doc, $request->url($pressPath, 'catalog', 'book', [$bestId])));
            // Chapters
            foreach ($chapterIds[$submission->current_publication_id] ?? [] as $chapterId) {
                $root->appendChild($this->_createUrlTree($doc, $request->url($pressPath, 'catalog', 'book', [$bestId, 'chapter', $chapterId])));
            }
            // Files
            foreach ($formats[$submission->current_publication_id ?? 0] ?? [] as $format) {
                $formatBestId = strlen((string) $format->url_path) ? $format->url_path : $format->publication_format_id;
                foreach ($files[$submissionId][$format->publication_format_id] ?? [] as $file) {
                    $fileBestId = strlen((string) ($file->publisher_id ?? '')) ? $file->publisher_id : $file->submission_file_id;
                    // Same PDF URL as citation_pdf_url, which Google Scholar indexes
                    $url = $file->mimetype === 'application/pdf'
                        ? $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $pressPath, 'catalog', 'download', [$bestId, $formatBestId, $fileBestId], urlLocaleForPage: '')
                        : $request->url($pressPath, 'catalog', 'view', [$bestId, $formatBestId, $fileBestId]);
                    $root->appendChild($this->_createUrlTree($doc, $url));
                }
            }
        }
    }
}
