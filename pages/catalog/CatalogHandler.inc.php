<?php

/**
 * @file pages/catalog/CatalogHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogHandler
 * @ingroup pages_catalog
 *
 * @brief Handle requests for catalog management.
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.request.AjaxModal');
import('lib.pkp.classes.core.JSONMessage');

class CatalogHandler extends Handler {
	/**
	 * Constructor
	 */
	function CatalogHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array(
				'index',
				'features', 'newReleases',
				'getCategories', 'category',
				'getSeries', 'series',
				'search'
			)
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		$this->setupTemplate($request);

		// Call parent method.
		parent::initialize($request, $args);
	}


	//
	// Public handler methods
	//
	/**
	 * Show the catalog management home.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		// Render the view.
		$templateMgr =& TemplateManager::getManager();

		import('controllers.modals.submissionMetadata.linkAction.MonographlessCatalogEntryLinkAction');
		$catalogEntryAction = new MonographlessCatalogEntryLinkAction($request);
		$templateMgr->assign('catalogEntryAction', $catalogEntryAction);

		$templateMgr->display('catalog/index.tpl');
	}

	/**
	 * View the tab contents for the Features tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function features($args, &$request) {
		fatalError('UNIMPLEMENTED');
	}

	/**
	 * View the tab contents for the New Releases tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function newReleases($args, &$request) {
		fatalError('UNIMPLEMENTED');
	}

	/**
	 * List the available categories.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function getCategories($args, &$request) {
		$press =& $request->getPress();
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categoryIterator =& $categoryDao->getByPressId($press->getId());
		$categoryArray = array();
		while ($category =& $categoryIterator->next()) {
			$categoryArray[$category->getPath()] = $category->getLocalizedTitle();
			unset($category);
		}
		$json = new JSONMessage(true, $categoryArray);
		return $json->getString();
	}

	/**
	 * List the available series.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function getSeries($args, &$request) {
		$press =& $request->getPress();
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesIterator =& $seriesDao->getByPressId($press->getId());
		$seriesArray = array();
		while ($series =& $seriesIterator->next()) {
			$seriesArray[$series->getPath()] = $series->getLocalizedTitle();
			unset($series);
		}
		$json = new JSONMessage(true, $seriesArray);
		return $json->getString();
	}

	/**
	 * View the content of a category.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function category($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->_setupMonographsTemplate(true);
		$press =& $request->getPress();

		// Get the category
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categoryPath = array_shift($args);
		$category =& $categoryDao->getByPath($categoryPath, $press->getId());

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByCategoryId($category->getId(), $press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Fetch the current features
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$features = $featureDao->getMonographIdsByAssoc(ASSOC_TYPE_CATEGORY, $category->getId);
		$templateMgr->assign('features', $features);

		// Return the monograph list as a JSON message
		$json = new JSONMessage(true, $templateMgr->fetch('catalog/monographs.tpl'));
		return $json->getString();
	}

	/**
	 * View the content of a series.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function series($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->_setupMonographsTemplate(true);
		$press =& $request->getPress();

		// Get the series
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesPath = array_shift($args);
		$series =& $seriesDao->getByPath($seriesPath, $press->getId());

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getBySeriesId($series->getId(), $press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Fetch the current features
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$features = $featureDao->getMonographIdsByAssoc(ASSOC_TYPE_SERIES, $series->getId());
		$templateMgr->assign('features', $features);

		// Return the monograph list as a JSON message
		$json = new JSONMessage(true, $templateMgr->fetch('catalog/monographs.tpl'));
		return $json->getString();
	}

	/**
	 * View the tab contents for the Search Results.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function search($args, &$request) {
		$searchText = array_shift($args);
		$this->_setupMonographsTemplate(false);

		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByPressId($press->getId(), $searchText);
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Display the monograph list
		$templateMgr->display('catalog/monographs.tpl');
	}

	//
	// Private functions
	//
	/**
	 * Set up template including link actions for the catalog view
	 * @param $includeOrganizeAction boolean
	 */
	function _setupMonographsTemplate($includeOrganizeAction) {
		// Loadubmission locale content for monograph listing
		AppLocale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));

		$templateMgr =& TemplateManager::getManager();
		import('lib.pkp.classes.linkAction.request.NullAction');

		// Organize action (if enabled)
		$templateMgr->assign('includeOrganizeAction', $includeOrganizeAction);
	}
}

?>
