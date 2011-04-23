<?php

/**
 * @file pages/management/SettingsHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for settings pages.
 */

// Import the base Handler.
import('classes.handler.Handler');

class SettingsHandler extends Handler {
	/**
	 * Constructor.
	 */
	function SettingsHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'index',
				'settings',
				'access',
				'press'
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
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));
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


	//
	// Public handler methods
	//
	/**
	 * Display settings index page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function index() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);
		$templateMgr->display('management/settings/index.tpl');
	}

	/**
	 * Route to other settings operations.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function settings($args) {
		$path = $args[0];
		switch($path) {
			case 'index';
				$this->index();
				break;
			case 'access';
				$this->access();
				break;
			case 'press';
				$this->press();
				break;
		}
	}

	/**
	 * Display Access and Security page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function access() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);
		$templateMgr->display('management/settings/access.tpl');
	}

	/**
	 * Display The Press page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function press() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);
		$templateMgr->display('management/settings/press.tpl');
	}
}

?>
