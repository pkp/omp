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
		$this->setupTemplate($request);
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
	 * Show the catalog new releases.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function newReleases($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate($request);
		$press =& $request->getPress();

		// Provide a list of new releases to browse
		$newReleaseDao =& DAORegistry::getDAO('NewReleaseDAO');
		$newReleases =& $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_PRESS, $press->getId());
		$templateMgr->assign('publishedMonographs', $newReleases);

		// Display
		$templateMgr->display('catalog/newReleases.tpl');
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
		$this->setupTemplate($request);

		// Get the category
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categoryPath = array_shift($args);
		$category =& $categoryDao->getByPath($categoryPath, $press->getId());
		if (isset($category)) {
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
		}
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
		$this->setupTemplate($request);

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
	 * View the results of a search operation.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function results($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		$this->setupTemplate($request);

		$query = $request->getUserVar('query');
		$templateMgr->assign('searchQuery', $query);

		// Fetch the monographs to display
		import('classes.search.MonographSearch');
		$keywords = array(MonographSearch::parseQuery($query));

		$resultsIterator =& MonographSearch::retrieveResults($press, $keywords);
		$publishedMonographs = array();
		while ($result =& $resultsIterator->next()) {
			$publishedMonograph =& $result['publishedMonograph'];
			if ($publishedMonograph) {
				$publishedMonographs[$publishedMonograph->getId()] =& $publishedMonograph;
			}
			unset($result, $publishedMonograph);
		}
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Display
		$templateMgr->display('catalog/results.tpl');
	}

	function setupTemplate(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		$templateMgr->assign('pressCurrency', $press->getSetting('pressCurrency'));
		parent::setupTemplate();
	}
}

?>
