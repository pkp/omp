<?php

/**
 * @file controllers/tab/settings/policies/form/PoliciesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PoliciesForm
 * @ingroup controllers_tab_settings_policies_form
 *
 * @brief Form to edit press policies information.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class PoliciesForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function PoliciesForm($wizardMode = false) {
		$settings = array(
			'focusScopeDesc' => 'string',
			'openAccessPolicy' => 'string',
			'reviewPolicy' => 'string',
			'copyrightNotice' => 'string',
			'includeCreativeCommons' => 'bool',
			'copyrightNoticeAgree' => 'bool',
			'competingInterestsPolicy' => 'string',
			'privacyStatement' => 'string'
		);

		AppLocale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON));

		parent::PressSettingsForm($settings, 'controllers/tab/settings/policies/form/policiesForm.tpl', $wizardMode);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('focusScopeDesc', 'openAccessPolicy', 'reviewPolicy', 'copyrightNotice', 'privacyStatement', 'competingInterestsPolicy');
	}
}

?>