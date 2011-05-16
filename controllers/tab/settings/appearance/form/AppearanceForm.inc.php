<?php

/**
 * @file controllers/tab/settings/appearance/form/AppearanceForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AppearanceForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form to edit press appearance settings.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class AppearanceForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function AppearanceForm() {
		$settings = array(
			'homeHeaderTitleType' => 'int',
			'homeHeaderTitle' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/appearance/form/appearanceForm.tpl');
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('homeHeaderTitle');
	}
}

?>