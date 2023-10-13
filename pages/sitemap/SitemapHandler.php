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
use APP\facades\Repo;
use APP\submission\Submission;
use PKP\db\DAORegistry;
use PKP\pages\sitemap\PKPSitemapHandler;
use PKP\plugins\Hook;

class SitemapHandler extends PKPSitemapHandler
{
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
        $submissions = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$pressId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getMany();

        foreach ($submissions as $submission) {
            // Book
            $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'book', [$submission->getBestId()])));
            // Chapters
            $chapters = $submission->getLatestPublication()->getData('chapters');
            if ($chapters && count($chapters) > 0) {
                foreach ($chapters as $chapter) {
                    if ($chapter->isPageEnabled()) {
                        $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'book', [$submission->getBestId(), 'chapter', $chapter->getId()])));
                    }
                }
            }
            // Files
            // Get publication formats
            /** @var \APP\publicationFormat\PublicationFormatDAO $publicationFormatDao */
            $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
            $publicationFormats = $publicationFormatDao->getApprovedByPublicationId($submission->getCurrentPublication()->getId())->toArray();
            foreach ($publicationFormats as $format) {
                // Consider only available publication formats
                if ($format->getIsAvailable()) {
                    // Consider only available publication format files
                    $submissionFiles = Repo::submissionFile()
                        ->getCollector()
                        ->filterByAssoc(
                            Application::ASSOC_TYPE_PUBLICATION_FORMAT,
                            [$format->getId()]
                        )
                        ->filterBySubmissionIds([$submission->getId()])
                        ->getMany()
                        ->toArray();

                    $availableFiles = array_filter(
                        $submissionFiles,
                        function ($a) {
                            return $a->getDirectSalesPrice() !== null;
                        }
                    );
                    foreach ($availableFiles as $file) {
                        $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'view', [$submission->getBestId(), $format->getBestId(), $file->getBestId()])));
                    }
                }
            }
        }

        // New releases
        $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'newReleases')));
        // Browse by series
        $seriesResult = Repo::section()
            ->getCollector()
            ->filterByContextIds([$pressId])
            ->getMany();
        foreach ($seriesResult as $series) {
            $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'series', $series->getPath())));
        }
        // Browse by categories
        $categories = Repo::category()->getCollector()
            ->filterByContextIds([$pressId])
            ->getMany();

        foreach ($categories as $category) {
            $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'category', $category->getPath())));
        }

        $doc->appendChild($root);

        // Enable plugins to change the sitemap
        Hook::call('SitemapHandler::createPressSitemap', [&$doc]);

        return $doc;
    }
}
