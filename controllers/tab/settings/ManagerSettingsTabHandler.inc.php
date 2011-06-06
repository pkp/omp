<?php

/**
 * @file controllers/tab/settings/ManagerSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManagerSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on press manangement settings pages.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');

class ManagerSettingsTabHandler extends SettingsTabHandler {

	/**
	 * Constructor
	 */
	function ManagerSettingsTabHandler() {
		$role = array(ROLE_ID_PRESS_MANAGER);
		parent::SettingsTabHandler($role);
	}


	//
	// Extended methods from SettingsTabHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Load grid-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));
	}
}