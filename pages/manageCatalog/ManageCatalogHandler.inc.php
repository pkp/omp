<?php

/**
 * @file pages/manageCatalog/ManageCatalogHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogHandler
 * @ingroup pages_manageCatalog
 *
 * @brief Handle requests for catalog management.
 */

import('classes.handler.Handler');

class ManageCatalogHandler extends Handler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();

		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
			array('index')
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
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @copydoc PKPHandler::initialize()
	 */
	function initialize($request) {
		$this->setupTemplate($request);

		// Call parent method.
		parent::initialize($request);
	}


	//
	// Public handler methods
	//
	/**
	 * Show the catalog management home.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function index($args, $request) {
		// Render the view.
		$templateMgr = TemplateManager::getManager($request);

		// Catalog list
		import('controllers.list.submissions.CatalogSubmissionsListHandler');
		$catalogListHandler = new CatalogSubmissionsListHandler(array(
			'title' => 'submission.list.monographs',
		));
		$templateMgr->assign('catalogListData', json_encode($catalogListHandler->getConfig()));


		return $templateMgr->display('manageCatalog/index.tpl');
	}
}


