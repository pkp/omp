<?php

/**
 * @file controllers/tab/settings/AdminSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on administration settings pages.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');

class AdminSettingsTabHandler extends SettingsTabHandler {

	/**
	 * Constructor
	 */
	function AdminSettingsTabHandler() {
		$role = array(ROLE_ID_SITE_ADMIN);
		parent::SettingsTabHandler($role);
		$pageTabs = array(
			'siteSetup' => 'controllers.tab.settings.siteSetup.form.SiteSetupForm',
			'languages' => 'controllers.tab.settings.languages.form.LanguagesForm'
		);
		$this->setPageTabs($pageTabs);
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
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_ADMIN, LOCALE_COMPONENT_OMP_ADMIN));
	}
}