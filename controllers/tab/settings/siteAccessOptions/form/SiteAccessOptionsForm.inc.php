<?php

/**
 * @file controllers/tab/settings/siteAccessOptions/form/SiteAccessOptionsForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteAccessOptionsForm
 * @ingroup controllers_tab_settings_siteAccessOptions_form
 *
 * @brief Form to edit site access options.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class SiteAccessOptionsForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function SiteAccessOptionsForm($wizardMode = false) {
		$settings = array(
			'disableUserReg' => 'bool',
			'allowRegAuthor' => 'bool',
			'allowRegReviewer' => 'bool',
			'restrictSiteAccess' => 'bool',
			'restrictMonographAccess' => 'bool',
			'showGalleyLinks' => 'bool'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/siteAccessOptions/form/siteAccessOptionsForm.tpl', $wizardMode);
	}

}

?>