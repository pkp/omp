<?php

/**
 * @file controllers/tab/settings/affiliation/form/AffiliationForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AffiliationForm
 * @ingroup controllers_tab_settings_affiliation_form
 *
 * @brief Form to edit press affiliation and support information.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class AffiliationForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function AffiliationForm() {
		$settings = array(
			'sponsorNote' => 'string',
			'contributorNote' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/affiliation/form/affiliationForm.tpl');
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('sponsorNote', 'contributorNote');
	}
}

?>