<?php

/**
 * @file controllers/tab/settings/SettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on settings pages, under administration or management pages.
 */

// Import the base PKPSettingsTabHandler.
import('lib.pkp.classes.controllers.tab.settings.PKPSettingsTabHandler');

class SettingsTabHandler extends PKPSettingsTabHandler {
	/**
	 * Constructor
	 * @param $role string The role keys to be used in role assignment.
	 */
	function SettingsTabHandler($role) {
		parent::PKPSettingsTabHandler($role);
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PkpContextAccessPolicy');
		$this->addPolicy(new PkpContextAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}
}

?>
