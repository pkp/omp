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

class CatalogHandler extends Handler {
	/**
	 * Constructor
	 */
	function CatalogHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('index')
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
	 * View the tab contents for the Series tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function series($args, &$request) {
		fatalError('UNIMPLEMENTED');
	}

	/**
	 * View the tab contents for the Search Results.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function search($args, &$request) {
		$searchText = array_shift($args);

		$templateMgr =& TemplateManager::getManager();
		AppLocale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));
		$press =& $request->getPress();

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByPressId($press->getId(), $searchText);
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Add the actions
		import('lib.pkp.classes.linkAction.request.NullAction');
		$templateMgr->assign(
			'organizeAction',
			new LinkAction(
				'organize',
				new NullAction(),
				__('common.organize'),
				'organize'
			)
		);
		$templateMgr->assign(
			'listViewAction',
			new LinkAction(
				'listView',
				new NullAction(),
				__('common.list'),
				'list_view'
			)
		);
		import('lib.pkp.classes.linkAction.request.NullAction');
		$templateMgr->assign(
			'gridViewAction',
			new LinkAction(
				'gridView',
				new NullAction(),
				__('common.grid'),
				'grid_view'
			)
		);

		// Display the monograph list
		$templateMgr->display('catalog/monographs.tpl');
	}
}

?>
