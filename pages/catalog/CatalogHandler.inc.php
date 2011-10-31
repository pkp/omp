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
		$templateMgr->display('catalog/index.tpl');
	}
}

?>
