<?php

/**
 * @file controllers/tab/settings/AccessSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AccessSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Access and Security page.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');

class AccessSettingsTabHandler extends SettingsTabHandler {

	/**
	 * Constructor
	 */
	function AccessSettingsTabHandler() {
		parent::SettingsTabHandler();
		$pageTabs = array(
			'users' => 'controllers/tab/settings/users.tpl',
			'roles' => 'controllers/tab/settings/roles.tpl',
			'siteAccessOptions' => 'controllers.tab.settings.siteAccessOptions.form.siteAccessOptionsForm',
			'enrollment' => 'controllers/tab/settings/enrollment.tpl',
		);
		$this->setPageTabs($pageTabs);
	}
}

?>
