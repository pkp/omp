<?php

/**
 * @file pages/catalog/CatalogHandler.php
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CatalogHandler
 *
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the press-specific part of the public-facing
 *   catalog.
 */

namespace APP\pages\catalog;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\observers\events\UsageEvent;
use APP\press\FeatureDAO;
use APP\press\NewReleaseDAO;
use APP\submission\Collector;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\file\ContextFileManager;
use PKP\userGroup\UserGroup;

class CatalogHandler extends Handler
{
    //
    // Public handler methods
    //
    /**
     * Show the catalog home.
     *
     * @param array $args
     * @param Request $request
     */
    public function index($args, $request)
    {
        return $this->page($args, $request, true);
    }

    /**
     * Show a page of the catalog
     *
     * @param array $args [
     *
     *        @option int Page number if available
     * ]
     *
     * @param Request $request
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
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
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
            ->orderBy($orderBy, $orderDir)
            ->orderByFeatured();

        $total = $collector->getCount();
        $submissions = $collector->limit($count)->offset($offset)->getMany();

        $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
        $featuredMonographIds = $featureDao->getSequencesByAssoc(Application::ASSOC_TYPE_PRESS, $context->getId());

        $this->_setupPaginationTemplate($request, $submissions->count(), $page, $count, $offset, $total);

        $seriesIterator = Repo::section()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->withPublished(true)
            ->getMany();

        $templateMgr->assign([
            'publishedSubmissions' => $submissions->toArray(),
            'authorUserGroups' => UserGroup::withRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])
                ->withContextIds([$context->getId()])
                ->get(),
            'featuredMonographIds' => $featuredMonographIds,
            'contextSeries' => $seriesIterator->toArray(),
        ]);

        $templateMgr->display('frontend/pages/catalog.tpl');
        event(new UsageEvent(Application::ASSOC_TYPE_PRESS, $context));
        return;
    }

    /**
     * Show the catalog of new releases.
     *
     * @param array $args
     * @param Request $request
     */
    public function newReleases($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $this->setupTemplate($request);
        $press = $request->getPress();

        // Provide a list of new releases to browse
        $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
        $newReleases = $newReleaseDao->getMonographsByAssoc(Application::ASSOC_TYPE_PRESS, $press->getId());
        $templateMgr->assign([
            'publishedSubmissions' => $newReleases,
            'authorUserGroups' => UserGroup::withRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])
                ->withContextIds([$press->getId()])
                ->get(),
        ]);

        // Display
        $templateMgr->display('frontend/pages/catalogNewReleases.tpl');
    }

    /**
     * View the content of a series.
     *
     * @param array $args [
     *
     *        @option string Series path
     *        @option int Page number if available
     * ]
     *
     * @param Request $request
     */
    public function series($args, $request)
    {
        $seriesPath = $args[0];
        $page = isset($args[1]) ? (int) $args[1] : 1;
        $templateMgr = TemplateManager::getManager($request);
        $context = $request->getContext();

        $series = Repo::section()->getCollector()
            ->filterByUrlPaths([$seriesPath])
            ->filterByContextIds([$context->getId()])
            ->getMany()
            ->first();

        if (!$series) {
            $request->getDispatcher()->handle404();
            exit;
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
            ->orderBy($orderBy, $orderDir)
            ->orderByFeatured();

        $total = $collector->getCount();
        $submissions = $collector->limit($count)->offset($offset)->getMany();

        $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
        $featuredMonographIds = $featureDao->getSequencesByAssoc(Application::ASSOC_TYPE_SERIES, $series->getId());

        // Provide a list of new releases to browse
        $newReleases = [];
        if ($page === 1) {
            $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
            $newReleases = $newReleaseDao->getMonographsByAssoc(Application::ASSOC_TYPE_SERIES, $series->getId());
        }

        $this->_setupPaginationTemplate($request, $submissions->count(), $page, $count, $offset, $total);

        $templateMgr->assign([
            'series' => $series,
            'publishedSubmissions' => $submissions->toArray(),
            'featuredMonographIds' => $featuredMonographIds,
            'newReleasesMonographs' => $newReleases,
            'authorUserGroups' => UserGroup::withRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])
                ->withContextIds([$context->getId()])
                ->get(),
        ]);

        $templateMgr->display('frontend/pages/catalogSeries.tpl');
        event(new UsageEvent(Application::ASSOC_TYPE_SERIES, $context, null, null, null, null, $series));
        return;
    }

    /**
     * @deprecated Since OMP 3.2.1, use pages/search instead.
     *
     * @param array $args
     * @param Request $request
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
                $series = Repo::section()->get($id, $press->getId());
                if ($series) {
                    $imageInfo = $series->getImage();
                }
                break;
            default:
                throw new \Exception('invalid type specified');
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
                $series = Repo::section()->get($id, $press->getId());
                if ($series) {
                    $imageInfo = $series->getImage();
                }
                break;
            default:
                throw new \Exception('invalid type specified');
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
     * @param Request $request
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
