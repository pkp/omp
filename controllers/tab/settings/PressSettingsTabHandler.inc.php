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
import('controllers.tab.settings.SettingsTabHandler');
import('lib.pkp.classes.core.JSONMessage');
import('controllers.tab.settings.masthead.form.MastheadForm');

class PressSettingsTabHandler extends SettingsTabHandler {

	/**
	 * Constructor
	 */
	function PressSettingsTabHandler() {
		parent::Handler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
					'masthead',
					'contact',
					'policies',
					'guidelines',
					'affiliationAndSupport',
					'identification',
					'saveData'
				)
		);
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
		// Instantiate the files form.
		$mastheadForm = new MastheadForm();
		$mastheadForm->initData();
		$json = new JSONMessage(true, $mastheadForm->fetch($request));
		return $json->getString();
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
