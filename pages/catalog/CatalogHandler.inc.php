<?php

/**
 * @file pages/catalog/CatalogHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogHandler
 * @ingroup pages_catalog
 *
 * @brief Handle requests for the press-specific part of the public-facing
 *   catalog.
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.core.JSONMessage');

class CatalogHandler extends Handler {
	/**
	 * Constructor
	 */
	function CatalogHandler() {
		parent::Handler();
	}


	//
	// Public handler methods
	//
	/**
	 * Show the catalog home.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$press =& $request->getPress();

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByPressId($press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs->toAssociativeArray());

		// Expose the featured monograph IDs and associated params
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_PRESS, $press->getId());
		$templateMgr->assign('featuredMonographIds', $featuredMonographIds);

		// Display
		$templateMgr->display('catalog/index.tpl');
	}

	/**
	 * View the content of a category.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function category($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		$this->setupTemplate();

		// Get the category
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categoryPath = array_shift($args);
		$category =& $categoryDao->getByPath($categoryPath, $press->getId());
		$templateMgr->assign('category', $category);

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByCategoryId($category->getId(), $press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs->toAssociativeArray());

		// Expose the featured monograph IDs and associated params
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_CATEGORY, $category->getId());
		$templateMgr->assign('featuredMonographIds', $featuredMonographIds);
		// Display
		$templateMgr->display('catalog/category.tpl');
	}

	/**
	 * View the content of a series.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function series($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		$this->setupTemplate();

		// Get the series
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesPath = array_shift($args);
		$series =& $seriesDao->getByPath($seriesPath, $press->getId());
		$templateMgr->assign('series', $series);

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getBySeriesId($series->getId(), $press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs->toAssociativeArray());

		// Expose the featured monograph IDs and associated params
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_SERIES, $series->getId());
		$templateMgr->assign('featuredMonographIds', $featuredMonographIds);

		// Display
		$templateMgr->display('catalog/series.tpl');
	}

	/**
	 * Display a book cover.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function cover($args, &$request) {
		$press =& $request->getPress();

		// Get the book
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$monographId = (int) array_shift($args);
		$publishedMonograph =& $publishedMonographDao->getById($monographId, $press->getId());
		if (!$publishedMonograph) {
			$dispatcher =& $this->getDispatcher();
			$dispatcher->handle404();
		}

 		if (!$coverImage = $publishedMonograph->getCoverImage()) {
			$request->redirectUrl($request->getBaseUrl() . '/templates/images/book-default.png'); // Redirect to default image
		}

		import('file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($publishedMonograph->getPressId(), $publishedMonograph->getId());
		$simpleMonographFileManager->downloadFile($simpleMonographFileManager->getBasePath() . $coverImage['name'], null, true);
	}

	/**
	 * Display a published monograph in the public catalog.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function book($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		$this->setupTemplate();
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION); // submission.synopsis

		// Get the book
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$monographId = (int) array_shift($args);
		$publishedMonograph =& $publishedMonographDao->getById($monographId, $press->getId());
		$templateMgr->assign('publishedMonograph', $publishedMonograph);
		if (!$publishedMonograph) {
			$dispatcher =& $this->getDispatcher();
			$dispatcher->handle404();
		}

		// Get book categories
		$categories =& $publishedMonographDao->getCategories($monographId, $press->getId());
		$templateMgr->assign('categories', $categories);

		// Display
		$templateMgr->display('catalog/book/book.tpl');
	}
}

?>
