<?php

/**
 * @file controllers/tab/manageCatalog/ManageCatalogTabHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogTabHandler
 * @ingroup controllers_tab_manageCatalog
 *
 * @brief Handle requests for catalog management tabs.
 */

import('classes.handler.Handler');

class ManageCatalogTabHandler extends Handler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();

		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
			array(
				'catalog', 'series', 'category', 'spotlights'
			)
		);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @copydoc PKPHandler::initialize()
	 */
	function initialize($request, $args) {
		$this->setupTemplate($request);

		// Call parent method.
		parent::initialize($request, $args);
	}


	//
	// Public handler methods.
	//
	/**
	 * View the contents for the catalog tab.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function catalog($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		return $templateMgr->fetchJson('controllers/tab/manageCatalog/catalog.tpl');
	}

	/**
	 * View the contents for the category tab.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function category($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		return $templateMgr->fetchJson('controllers/tab/manageCatalog/category.tpl');
	}

	/**
	 * View the contents for the series tab.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function series($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		return $templateMgr->fetchJson('controllers/tab/manageCatalog/series.tpl');
	}

	/**
	 * View the contents for the spotlights tab.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function spotlights($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		return $templateMgr->fetchJson('controllers/tab/manageCatalog/spotlights.tpl');
	}
}

?>



