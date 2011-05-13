<?php

/**
 * @file controllers/tab/settings/homepage/form/HomepageForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HomepageForm
 * @ingroup controllers_tab_settings_homepage_form
 *
 * @brief Form to edit press homepage information and settings.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class HomepageForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function HomepageForm() {
		$settings = array(
			'enableAnnouncements' => 'boolean',
			'enableAnnouncementsHomepage' => 'boolean',
			'announcementsIntroduction' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/homepage/form/homepageForm.tpl');
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('announcementsIntroduction');
	}
}

?>