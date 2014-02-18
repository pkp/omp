<?php

/**
 * @file pages/management/ToolsHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ToolsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for Tool pages.
 */

// Import the base ManagementHandler.
import('pages.management.ManagementHandler');

class ToolsHandler extends ManagementHandler {
	/**
	 * Constructor.
	 */
	function ToolsHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_MANAGER,
			array('tools')
		);
	}


	//
	// Public handler methods.
	//
	/**
	 * Route to other Tools operations
	 * @param $args array
	 */
	function tools($args) {
		$path = array_shift($args);
		switch ($path) {
			case 'index':
				$this->index();
				break;
			default:
				assert(false);
		}
	}

	/**
	 * Display tools index page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function index($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->display('management/tools/index.tpl');
	}
}

?>
