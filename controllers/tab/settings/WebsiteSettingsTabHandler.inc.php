<?php

/**
 * @file controllers/tab/settings/WebsiteSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WebsiteSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Website page.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');
import('lib.pkp.classes.core.JSONMessage');

class WebsiteSettingsTabHandler extends SettingsTabHandler {

	/**
	 * Constructor
	 */
	function WebsiteSettingsTabHandler() {
		parent::SettingsTabHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
					'homepage',
					'appearance',
					'languages'
				)
		);
	}


	//
	// Public handler methods
	//
	/**
	 * Handle homepage management requests.
	 * @param $args
	 * @param $request PKPRequest
	 */
	function homepage($args, &$request) {
	}

	/**
	 * Handle appearance management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function appearance($args, &$request) {
	}

	/**
	 * Handle language management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function languages($args, &$request) {
	}
}
?>
