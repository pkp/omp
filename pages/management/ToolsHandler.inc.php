<?php

/**
 * @file pages/management/ToolsHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ToolsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for Tool pages.
 */

// Import the base Handler.
import('classes.handler.Handler');

class ToolsHandler extends Handler {
	/**
	 * Constructor.
	 */
	function ToolsHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'tools',
			)
		);
	}


	//
	// Overridden methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Load grid-specific translations
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Route to other Tools operations
	 * @param $args array
	 */
	function tools($args) {
		$path = $args[0];
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
	function index() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/tools/index.tpl');
	}
}

?>
