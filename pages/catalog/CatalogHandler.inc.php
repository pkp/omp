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

use PKP\submission\PKPSubmission;
use PKP\submission\PKPSubmissionDAO;
use PKP\file\ContextFileManager;

use APP\template\TemplateManager;

class CatalogHandler extends PKPCatalogHandler
{
    //
    // Public handler methods
    //
    /**
     * Show the catalog home.
     *
     * @param $args array
     * @param $request PKPRequest
     */
    public function index($args, $request)
    {
        return $this->page($args, $request, true);
    }

    /**
     * Show a page of the catalog
     *
     * @param $args array [
     *		@option int Page number if available
     * ]
     *
     * @param $request PKPRequest
     * @param $isFirstPage boolean Return the first page of results
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

        $orderOption = $context->getData('catalogSortOption') ? $context->getData('catalogSortOption') : PKPSubmissionDAO::ORDERBY_DATE_PUBLISHED . '-' . SORT_DIRECTION_DESC;
        [$orderBy, $orderDir] = explode('-', $orderOption);

        $count = $context->getData('itemsPerPage') ? $context->getData('itemsPerPage') : Config::getVar('interface', 'items_per_page');
        $offset = $page > 1 ? ($page - 1) * $count : 0;

        import('classes.core.Services');
        $submissionService = Services::get('submission');

        $params = [
            'contextId' => $context->getId(),
            'orderByFeatured' => true,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC',
            'count' => $count,
            'offset' => $offset,
            'status' => PKPSubmission::STATUS_PUBLISHED,
        ];
        $submissionsIterator = $submissionService->getMany($params);
        $total = $submissionService->getMax($params);

        $featureDao = DAORegistry::getDAO('FeatureDAO'); /* @var $featureDao FeatureDAO */
        $featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_PRESS, $context->getId());

        $this->_setupPaginationTemplate($request, count($submissionsIterator), $page, $count, $offset, $total);

        $templateMgr->assign([
            'publishedSubmissions' => iterator_to_array($submissionsIterator),
            'featuredMonographIds' => $featuredMonographIds,
        ]);

        $templateMgr->display('frontend/pages/catalog.tpl');
    }

    /**
     * Show the catalog new releases.
     *
     * @param $args array
     * @param $request PKPRequest
     */
    public function newReleases($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $this->setupTemplate($request);
        $press = $request->getPress();

        // Provide a list of new releases to browse
        $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /* @var $newReleaseDao NewReleaseDAO */
        $newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_PRESS, $press->getId());
        $templateMgr->assign('publishedSubmissions', $newReleases);

        // Display
        $templateMgr->display('frontend/pages/catalogNewReleases.tpl');
    }

    /**
     * View the content of a series.
     *
     * @param $args array [
     *		@option string Series path
     *		@option int Page number if available
     * ]
     *
     * @param $request PKPRequest
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
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
        $series = $seriesDao->getByPath($seriesPath, $context->getId());

        if (!$series) {
            $request->redirect(null, 'catalog');
        }

        $this->setupTemplate($request);

        $orderOption = $series->getSortOption() ? $series->getSortOption() : PKPSubmissionDAO::ORDERBY_DATE_PUBLISHED . '-' . SORT_DIRECTION_DESC;
        [$orderBy, $orderDir] = explode('-', $orderOption);

        $count = $context->getData('itemsPerPage') ? $context->getData('itemsPerPage') : Config::getVar('interface', 'items_per_page');
        $offset = $page > 1 ? ($page - 1) * $count : 0;

        import('classes.core.Services');
        $submissionService = Services::get('submission');

        $params = [
            'contextId' => $context->getId(),
            'seriesIds' => $series->getId(),
            'orderByFeatured' => true,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC',
            'count' => $count,
            'offset' => $offset,
            'status' => PKPSubmission::STATUS_PUBLISHED,
        ];
        $submissionsIterator = $submissionService->getMany($params);
        $total = $submissionService->getMax($params);

        $featureDao = DAORegistry::getDAO('FeatureDAO'); /* @var $featureDao FeatureDAO */
        $featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_SERIES, $series->getId());

        // Provide a list of new releases to browse
        $newReleases = [];
        if ($page === 1) {
            $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /* @var $newReleaseDao NewReleaseDAO */
            $newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_SERIES, $series->getId());
        }

        $this->_setupPaginationTemplate($request, count($submissionsIterator), $page, $count, $offset, $total);

        $templateMgr->assign([
            'series' => $series,
            'publishedSubmissions' => iterator_to_array($submissionsIterator),
            'featuredMonographIds' => $featuredMonographIds,
            'newReleasesMonographs' => $newReleases,
        ]);

        return $templateMgr->display('frontend/pages/catalogSeries.tpl');
    }

    /**
     * @deprecated Since OMP 3.2.1, use pages/search instead.
     *
     * @param $args array
     * @param $request PKPRequest
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
                $categoryDao = DAORegistry::getDAO('CategoryDAO'); /* @var $categoryDao CategoryDAO */
                $category = $categoryDao->getById($id, $press->getId());
                if ($category) {
                    $imageInfo = $category->getImage();
                }
                break;
            case 'series':
                $path = '/series/';
                $seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
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
                $categoryDao = DAORegistry::getDAO('CategoryDAO'); /* @var $categoryDao CategoryDAO */
                $category = $categoryDao->getById($id, $press->getId());
                if ($category) {
                    $imageInfo = $category->getImage();
                }
                break;
            case 'series':
                $path = '/series/';
                $seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
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
     * @param $request PKPRequest
     * @param $submissionsCount int Number of submissions being shown
     * @param $page int Page number being shown
     * @param $count int Max number of monographs being shown
     * @param $offset int Starting position of monographs
     * @param $total int Total number of monographs available
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
