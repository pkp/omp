<?php

/**
 * @file controllers/tab/settings/DistributionSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DistributionSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Distribution Process page.
 */

// Import the base Handler.
import('controllers.tab.settings.ManagerSettingsTabHandler');

class DistributionSettingsTabHandler extends ManagerSettingsTabHandler {


	/**
	 * Constructor
	 */
	function DistributionSettingsTabHandler() {
		parent::ManagerSettingsTabHandler();
		$pageTabs = array(
			'indexing' => 'controllers.tab.settings.indexing.form.IndexingForm'
		);
		$this->setPageTabs($pageTabs);

	}
}

?>
