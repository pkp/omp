<?php

/**
 * @file controllers/tab/settings/information/form/InformationForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationForm
 * @ingroup controllers_tab_settings_information_form
 *
 * @brief Form to edit press information.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class InformationForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function InformationForm($wizardMode = false) {
		$settings = array(
			'readerInformation' => 'string',
			'authorInformation' => 'string',
			'librarianInformation' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/information/form/informationForm.tpl', $wizardMode);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('readerInformation', 'authorInformation', 'librarianInformation');
	}
}

?>
