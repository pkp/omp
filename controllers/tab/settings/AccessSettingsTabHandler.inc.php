<?php

/**
 * @file controllers/tab/settings/AccessSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AccessSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Access and Security page.
 */

// Import the base Handler.
import('controllers.tab.settings.ManagerSettingsTabHandler');

class AccessSettingsTabHandler extends ManagerSettingsTabHandler {

	/**
	 * Constructor
	 */
	function AccessSettingsTabHandler() {
		parent::ManagerSettingsTabHandler();
		$pageTabs = array(
			'users' => 'controllers/tab/settings/users.tpl',
			'roles' => 'controllers/tab/settings/roles.tpl',
			'siteAccessOptions' => 'controllers.tab.settings.siteAccessOptions.form.SiteAccessOptionsForm'
		);
		$this->setPageTabs($pageTabs);
	}
}

?>
