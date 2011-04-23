<?php

/**
 * @file controllers/tab/settings/PressSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Press page.
 */

// Import the base Handler.
import('classes.handler.Handler');

class PressSettingsTabHandler extends Handler {

	/**
	 * Constructor
	 */
	function PressSettingsTabHandler() {
		parent::Handler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array('masthead', 'contact', 'policies', 'guidelines', 'affiliationAndSupport', 'identification'));
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
	 * Handle masthead management requests.
	 * @param $args
	 * @param $request PKPRequest
	 */
	function masthead($args, &$request) {
	}

	/**
	 * Handle contact management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function contact($args, &$request) {
	}

	/**
	 * Handle policies management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function policies($args, &$request) {
	}

	/**
	 * Handle guidelines management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function guidelines($args, &$request) {
	}

	/**
	 * Handle affiliation and support management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function affiliationAndSupport($args, &$request) {
	}

	/**
	 * Handle identification management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function identification($args, &$request) {
	}
}
?>
