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
				'category',
				'getSeries', 'series',
				'search'
			)
		);
	}


	//
	// Implement template methods from PKPHandler
	//
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
	 * View the tab contents for the Category tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function category($args, &$request) {
		fatalError('UNIMPLEMENTED');
	}

	/**
	 * List the available series.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function getSeries($args, &$request) {
		$press =& $request->getPress();
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesIterator =& $seriesDao->getByPressId($press->getId());
		$seriesArray = array();
		while ($series =& $seriesIterator->next()) {
			$seriesArray[$series->getId()] = $series->getLocalizedTitle();
			unset($series);
		}
		$json = new JSONMessage(true, $seriesArray);
		return $json->getString();
	}

	/**
	 * View the content of a series.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function series($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->_setupMonographsTemplate(true);
		$press =& $request->getPress();

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByPressId($press->getId(), $searchText);
		$templateMgr->assign('publishedMonographs', $publishedMonographs);


		// Display the monograph list
		$templateMgr->display('catalog/monographs.tpl');
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
		if ($includeOrganizeAction) $templateMgr->assign(
			'organizeAction',
			new LinkAction(
				'organize',
				new NullAction(),
				__('common.organize'),
				'organize'
			)
		);

		// List View action
		$templateMgr->assign(
			'listViewAction',
			new LinkAction(
				'listView',
				new NullAction(),
				__('common.list'),
				'list_view'
			)
		);

		// Grid View action
		$templateMgr->assign(
			'gridViewAction',
			new LinkAction(
				'gridView',
				new NullAction(),
				__('common.grid'),
				'grid_view'
			)
		);
	}
}

?>
