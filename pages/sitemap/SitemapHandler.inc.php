<?php

/**
 * @file pages/sitemap/SitemapHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SitemapHandler
 * @ingroup pages_sitemap
 *
 * @brief Produce a sitemap in XML format for submitting to search engines.
 */

import('lib.pkp.pages.sitemap.PKPSitemapHandler');

use APP\facades\Repo;
use APP\submission\Submission;
use PKP\db\DAORegistry;

class SitemapHandler extends PKPSitemapHandler
{
    /**
     * @copydoc PKPSitemapHandler::_createContextSitemap()
     */
    public function _createContextSitemap($request)
    {
        $doc = parent::_createContextSitemap($request);
        $root = $doc->documentElement;

        $press = $request->getPress();
        $pressId = $press->getId();

        // Catalog
        $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog')));
        $submissions = Repo::submission()->getMany(
            Repo::submission()
                ->getCollector()
                ->filterByContextIds([$pressId])
                ->filterByStatus([Submission::STATUS_PUBLISHED])
        );
        foreach ($submissions as $submission) {
            // Book
            $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'book', [$submission->getBestId()])));
            // Files
            // Get publication formats
            $publicationFormats = DAORegistry::getDAO('PublicationFormatDAO')->getApprovedByPublicationId($submission->getCurrentPublication()->getId())->toArray();
            foreach ($publicationFormats as $format) {
                // Consider only available publication formats
                if ($format->getIsAvailable()) {
                    // Consider only available publication format files
                    $collector = Repo::submissionFiles()
                        ->getCollector()
                        ->filterByAssoc(
                            ASSOC_TYPE_PUBLICATION_FORMAT,
                            [$format->getId()]
                        )
                        ->filterBySubmissionIds([$submission->getId()]);

                    $data = Repo::submissionFiles()->getMany($collector);
                    $availableFiles = array_filter(
                        iterator_to_array($data),
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
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
        $seriesResult = $seriesDao->getByPressId($pressId);
        while ($series = $seriesResult->next()) {
            $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'series', $series->getPath())));
        }
        // Browse by categories
        $categories = Repo::category()->getMany(Repo::category()->getCollector()
            ->filterByContextIds([$pressId]));
        foreach ($categories as $category) {
            $root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'category', $category->getPath())));
        }

        $doc->appendChild($root);

        // Enable plugins to change the sitemap
        HookRegistry::call('SitemapHandler::createPressSitemap', [&$doc]);

        return $doc;
    }
}
