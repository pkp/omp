<?php

/**
 * @file pages/catalog/CatalogHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogHandler
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the press-specific part of the public-facing
 *   catalog.
 */

import('lib.pkp.pages.catalog.PKPCatalogHandler');

class CatalogHandler extends PKPCatalogHandler {
	//
	// Public handler methods
	//
	/**
	 * Show the catalog home.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, $request) {
		return $this->page($args, $request, true);
	}

	/**
	 * Show a page of the catalog
	 * @param $args array [
	 *		@option int Page number if available
	 * ]
	 * @param $request PKPRequest
	 * @param $isFirstPage boolean Return the first page of results
	 */
	public function page($args, $request, $isFirstPage = false) {
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

		import('lib.pkp.classes.submission.Submission'); // STATUS_ constants
		import('classes.monograph.PublishedMonographDAO'); // ORDERBY_ constants

		$orderOption = $context->getSetting('catalogSortOption') ? $context->getSetting('catalogSortOption') : ORDERBY_DATE_PUBLISHED . '-' . SORT_DIRECTION_DESC;
		list($orderBy, $orderDir) = explode('-', $orderOption);

		$count = $context->getSetting('itemsPerPage') ? $context->getSetting('itemsPerPage') : Config::getVar('interface', 'items_per_page');
		$offset = $page > 1 ? ($page - 1) * $count : 0;

		import('classes.core.ServicesContainer');
		$submissionService = ServicesContainer::instance()->get('submission');

		$params = array(
			'orderByFeatured' => true,
			'orderBy' => $orderBy,
			'orderDirection' => $orderDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC',
			'count' => $count,
			'offset' => $offset,
			'status' => STATUS_PUBLISHED,
			'returnObject' => SUBMISSION_RETURN_PUBLISHED,
		);
		$publishedMonographs = $submissionService->getSubmissions($context->getId(), $params);
		$total = $submissionService->getSubmissionsMaxCount($context->getId(), $params);

		$featureDao = DAORegistry::getDAO('FeatureDAO');
		$featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_PRESS, $context->getId());

		$this->_setupPaginationTemplate($request, $publishedMonographs, $page, $count, $offset, $total);

		$templateMgr->assign(array(
			'publishedMonographs' => $publishedMonographs,
			'featuredMonographIds' => $featuredMonographIds,
		));

		$templateMgr->display('frontend/pages/catalog.tpl');
	}

	/**
	 * Show the catalog new releases.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function newReleases($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$press = $request->getPress();

		// Provide a list of new releases to browse
		$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
		$newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_PRESS, $press->getId());
		$templateMgr->assign('publishedMonographs', $newReleases);

		// Display
		$templateMgr->display('frontend/pages/catalogNewReleases.tpl');
	}

	/**
	 * @copydoc parent::category()
	 */
	function category($args, $request) {
		// Get the category
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$category = $categoryDao->getByPath($args[0], $request->getContext()->getId());

		import('classes.monograph.PublishedMonographDAO'); // ORDERBY_ constants

		$featureDao = DAORegistry::getDAO('FeatureDAO');
		$featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_CATEGORY, $category->getId());

		// Provide a list of new releases to browse
		$newReleases = array();

		$page = isset($args[1]) ? (int) $args[1] : 1;
		if ($page === 1) {
			$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
			$newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_CATEGORY, $category->getId());
		}

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'featuredMonographIds' => $featuredMonographIds,
			'newReleasesMonographs' => $newReleases,
		));

		return parent::category($args, $request);
	}

	/**
	 * View the content of a series.
	 * @param $args array [
	 *		@option string Series path
	 *		@option int Page number if available
	 * ]
	 * @param $request PKPRequest
	 * @return string
	 */
	function series($args, $request) {
		$seriesPath = $args[0];
		$page = isset($args[1]) ? (int) $args[1] : 1;
		$templateMgr = TemplateManager::getManager($request);
		$context = $request->getContext();

		// Get the series
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getByPath($seriesPath, $context->getId());

		if (!$series) {
			$request->redirect(null, 'catalog');
		}

		$this->setupTemplate($request);
		import('lib.pkp.classes.submission.Submission'); // STATUS_ constants
		import('classes.monograph.PublishedMonographDAO'); // ORDERBY_ constants

		$orderOption = $series->getSortOption() ? $series->getSortOption() : ORDERBY_DATE_PUBLISHED . '-' . SORT_DIRECTION_DESC;
		list($orderBy, $orderDir) = explode('-', $orderOption);

		$count = $context->getSetting('itemsPerPage') ? $context->getSetting('itemsPerPage') : Config::getVar('interface', 'items_per_page');
		$offset = $page > 1 ? ($page - 1) * $count : 0;

		import('classes.core.ServicesContainer');
		$submissionService = ServicesContainer::instance()->get('submission');

		$params = array(
			'seriesIds' => $series->getId(),
			'orderByFeatured' => true,
			'orderBy' => $orderBy,
			'orderDirection' => $orderDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC',
			'count' => $count,
			'offset' => $offset,
			'status' => STATUS_PUBLISHED,
			'returnObject' => SUBMISSION_RETURN_PUBLISHED,
		);
		$publishedMonographs = $submissionService->getSubmissions($context->getId(), $params);
		$total = $submissionService->getSubmissionsMaxCount($context->getId(), $params);

		$featureDao = DAORegistry::getDAO('FeatureDAO');
		$featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_SERIES, $series->getId());

		// Provide a list of new releases to browse
		$newReleases = array();
		if ($page === 1) {
			$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
			$newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_SERIES, $series->getId());
		}

		$this->_setupPaginationTemplate($request, $publishedMonographs, $page, $count, $offset, $total);

		$templateMgr->assign(array(
			'series' => $series,
			'publishedMonographs' => $publishedMonographs,
			'featuredMonographIds' => $featuredMonographIds,
			'newReleasesMonographs' => $newReleases,
		));

		return $templateMgr->display('frontend/pages/catalogSeries.tpl');
	}

	/**
	 * View the results of a search operation.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function results($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$press = $request->getPress();
		$this->setupTemplate($request);

		$query = $request->getUserVar('query');
		$templateMgr->assign('searchQuery', $query);

		// Fetch the monographs to display
		import('classes.search.MonographSearch');
		$monographSearch = new MonographSearch();
		$error = null;
		$resultsIterator = $monographSearch->retrieveResults($request, $press, array(null => $query), $error);

		$publishedMonographs = array();
		while ($result = $resultsIterator->next()) {
			$publishedMonograph = $result['publishedMonograph'];
			if ($publishedMonograph) {
				$publishedMonographs[$publishedMonograph->getId()] = $publishedMonograph;
			}
		}
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Display
		$templateMgr->display('frontend/pages/searchResults.tpl');
	}

	/**
	 * Serve the image for a category or series.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fullSize($args, $request) {
		$context = $request->getContext();
		switch ($request->getUserVar('type')) {
			case 'series':
				$seriesDao = DAORegistry::getDAO('SeriesDAO');
				$series = $seriesDao->getById($request->getUserVar('id'), $context->getId());
				if (!$series) $this->getDispatcher()->handle404();
				$imageInfo = $series->getImage();
				import('lib.pkp.classes.file.ContextFileManager');
				$contextFileManager = new ContextFileManager($context->getId());
				$contextFileManager->downloadByPath($contextFileManager->getBasePath() . '/series/' . $imageInfo['name'], null, true);
				break;
			default:
				parent::fullSize($args, $request);
				break;
		}
	}

	/**
	 * Serve the thumbnail for a category or series.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function thumbnail($args, $request) {
		$context = $request->getContext();
		switch ($request->getUserVar('type')) {
			case 'series':
				$seriesDao = DAORegistry::getDAO('SeriesDAO');
				$series = $seriesDao->getById($request->getUserVar('id'), $context->getId());
				if (!$series) $this->getDispatcher()->handle404();
				$imageInfo = $series->getImage();
				import('lib.pkp.classes.file.ContextFileManager');
				$contextFileManager = new ContextFileManager($context->getId());
				$contextFileManager->downloadByPath($contextFileManager->getBasePath() . '/series/' . $imageInfo['thumbnailName'], null, true);
				break;
			default:
				parent::thumbnail($args, $request);
				break;
		}
	}

	/**
	 * Set up the basic template.
	 */
	function setupTemplate($request) {
		$templateMgr = TemplateManager::getManager($request);
		$press = $request->getPress();
		if ($press) {
			$templateMgr->assign('currency', $press->getSetting('currency'));
		}
		parent::setupTemplate($request);
	}
}


