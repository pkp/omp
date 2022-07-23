<?php

/**
 * @file pages/catalog/CatalogHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CatalogHandler
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the press-specific part of the public-facing
 *   catalog.
 */

import('lib.pkp.pages.catalog.PKPCatalogHandler');

use APP\core\Application;
use APP\facades\Repo;
use APP\observers\events\Usage;
use APP\submission\Collector;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\file\ContextFileManager;

class CatalogHandler extends PKPCatalogHandler
{
    //
    // Public handler methods
    //
    /**
     * Show the catalog home.
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args, $request)
    {
        return $this->page($args, $request, true);
    }

    /**
     * Show a page of the catalog
     *
     * @param array $args [
     *		@option int Page number if available
     * ]
     *
     * @param PKPRequest $request
     * @param bool $isFirstPage Return the first page of results
     */
    public function page($args, $request, $isFirstPage = false)
    {
        $page = null;
        if ($isFirstPage) {
            $page = 1;
        } elseif ($args[0]) {
            $page = (int) $args[0];
        }

        if (!$isFirstPage && (empty($page) || $page < 2)) {
            $request->getDispatcher()->handle404();
        }

        $templateMgr = TemplateManager::getManager($request);
        $this->setupTemplate($request);
        $context = $request->getContext();

        $orderOption = $context->getData('catalogSortOption') ? $context->getData('catalogSortOption') : Collector::ORDERBY_DATE_PUBLISHED . '-' . Collector::ORDER_DIR_DESC;
        [$orderBy, $orderDir] = explode('-', $orderOption);

        $count = $context->getData('itemsPerPage') ? $context->getData('itemsPerPage') : Config::getVar('interface', 'items_per_page');
        $offset = $page > 1 ? ($page - 1) * $count : 0;

        $collector = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->orderBy($orderBy, $orderDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC')
            ->orderByFeatured();

        $total = Repo::submission()->getCount($collector);
        $submissions = Repo::submission()->getMany($collector->limit($count)->offset($offset));

        $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
        $featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_PRESS, $context->getId());

        $this->_setupPaginationTemplate($request, $submissions->count(), $page, $count, $offset, $total);

        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $seriesIterator = $seriesDao->getByContextId($context->getId(), null, false, true);

        $templateMgr->assign([
            'publishedSubmissions' => $submissions->toArray(),
            'featuredMonographIds' => $featuredMonographIds,
            'contextSeries' => $seriesIterator->toArray(),
        ]);

        $templateMgr->display('frontend/pages/catalog.tpl');
        event(new Usage(Application::ASSOC_TYPE_PRESS, $context));
        return;
    }

    /**
     * Show the catalog new releases.
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function newReleases($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $this->setupTemplate($request);
        $press = $request->getPress();

        // Provide a list of new releases to browse
        $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
        $newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_PRESS, $press->getId());
        $templateMgr->assign('publishedSubmissions', $newReleases);

        // Display
        $templateMgr->display('frontend/pages/catalogNewReleases.tpl');
    }

    /**
     * View the content of a series.
     *
     * @param array $args [
     *		@option string Series path
     *		@option int Page number if available
     * ]
     *
     * @param PKPRequest $request
     *
     * @return string
     */
    public function series($args, $request)
    {
        $seriesPath = $args[0];
        $page = isset($args[1]) ? (int) $args[1] : 1;
        $templateMgr = TemplateManager::getManager($request);
        $context = $request->getContext();

        // Get the series
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $series = $seriesDao->getByPath($seriesPath, $context->getId());

        if (!$series) {
            $request->redirect(null, 'catalog');
        }

        $this->setupTemplate($request);

        $orderOption = $series->getSortOption() ? $series->getSortOption() : Collector::ORDERBY_DATE_PUBLISHED . '-' . Collector::ORDER_DIR_DESC;
        [$orderBy, $orderDir] = explode('-', $orderOption);

        $count = $context->getData('itemsPerPage') ? $context->getData('itemsPerPage') : Config::getVar('interface', 'items_per_page');
        $offset = $page > 1 ? ($page - 1) * $count : 0;

        $collector = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->filterBySeriesIds([$series->getId()])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->orderBy($orderBy, $orderDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC')
            ->orderByFeatured();

        $total = Repo::submission()->getCount($collector);
        $submissions = Repo::submission()->getMany($collector->limit($count)->offset($offset));

        $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
        $featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_SERIES, $series->getId());

        // Provide a list of new releases to browse
        $newReleases = [];
        if ($page === 1) {
            $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
            $newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_SERIES, $series->getId());
        }

        $this->_setupPaginationTemplate($request, $submissions->count(), $page, $count, $offset, $total);

        $templateMgr->assign([
            'series' => $series,
            'publishedSubmissions' => $submissions->toArray(),
            'featuredMonographIds' => $featuredMonographIds,
            'newReleasesMonographs' => $newReleases,
        ]);

        $templateMgr->display('frontend/pages/catalogSeries.tpl');
        event(new Usage(Application::ASSOC_TYPE_SERIES, $context, null, null, null, null, $series));
        return;
    }

    /**
     * @deprecated Since OMP 3.2.1, use pages/search instead.
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return string
     */
    public function results($args, $request)
    {
        $request->redirect(null, 'search');
    }

    /**
     * Serve the image for a category or series.
     */
    public function fullSize($args, $request)
    {
        $press = $request->getPress();
        $type = $request->getUserVar('type');
        $id = $request->getUserVar('id');
        $imageInfo = [];
        $path = null;

        switch ($type) {
            case 'category':
                $path = '/categories/';
                $category = Repo::category()->get((int) $id);
                if ($category && $category->getContextId() == $press->getId()) {
                    $imageInfo = $category->getImage();
                }
                break;
            case 'series':
                $path = '/series/';
                $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
                $series = $seriesDao->getById($id, $press->getId());
                if ($series) {
                    $imageInfo = $series->getImage();
                }
                break;
            default:
                fatalError('invalid type specified');
                break;
        }

        if ($imageInfo) {
            $pressFileManager = new ContextFileManager($press->getId());
            $pressFileManager->downloadByPath($pressFileManager->getBasePath() . $path . $imageInfo['name'], null, true);
        }
    }

    /**
     * Serve the thumbnail for a category or series.
     */
    public function thumbnail($args, $request)
    {
        $press = $request->getPress();
        $type = $request->getUserVar('type');
        $id = $request->getUserVar('id');
        $imageInfo = [];
        $path = null; // Scrutinizer

        switch ($type) {
            case 'category':
                $path = '/categories/';
                $category = Repo::category()->get((int) $id);
                if ($category && $category->getContextId() == $press->getId()) {
                    $imageInfo = $category->getImage();
                }
                break;
            case 'series':
                $path = '/series/';
                $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
                $series = $seriesDao->getById($id, $press->getId());
                if ($series) {
                    $imageInfo = $series->getImage();
                }
                break;
            default:
                fatalError('invalid type specified');
                break;
        }

        if ($imageInfo) {
            $pressFileManager = new ContextFileManager($press->getId());
            $pressFileManager->downloadByPath($pressFileManager->getBasePath() . $path . $imageInfo['thumbnailName'], null, true);
        }
    }

    /**
     * Set up the basic template.
     */
    public function setupTemplate($request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $press = $request->getPress();
        if ($press) {
            $templateMgr->assign('currency', $press->getSetting('currency'));
        }
        parent::setupTemplate($request);
    }

    /**
     * Assign the pagination template variables
     *
     * @param PKPRequest $request
     * @param int $submissionsCount Number of submissions being shown
     * @param int $page Page number being shown
     * @param int $count Max number of monographs being shown
     * @param int $offset Starting position of monographs
     * @param int $total Total number of monographs available
     */
    public function _setupPaginationTemplate($request, $submissionsCount, $page, $count, $offset, $total)
    {
        $showingStart = $offset + 1;
        $showingEnd = min($offset + $count, $offset + $submissionsCount);
        $nextPage = $total > $showingEnd ? $page + 1 : null;
        $prevPage = $showingStart > 1 ? $page - 1 : null;

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'showingStart' => $showingStart,
            'showingEnd' => $showingEnd,
            'total' => $total,
            'nextPage' => $nextPage,
            'prevPage' => $prevPage,
        ]);
    }
}
