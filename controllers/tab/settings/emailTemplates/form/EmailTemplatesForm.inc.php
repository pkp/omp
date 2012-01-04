<?php

/**
 * @file controllers/tab/settings/emailTemplates/form/EmailTemplatesForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplatesForm
 * @ingroup controllers_tab_settings_emailTemplates_form
 *
 * @brief Form to edit email identification settings.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class EmailTemplatesForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function EmailTemplatesForm($wizardMode = false) {
		$settings = array(
			'emailSignature' => 'string',
			'envelopeSender' => 'string'
		);

		$this->addCheck(new FormValidatorEmail($this, 'envelopeSender', 'optional', 'user.profile.form.emailRequired'));

		parent::PressSettingsForm($settings, 'controllers/tab/settings/emailTemplates/form/emailTemplatesForm.tpl', $wizardMode);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see PressSettingsForm::fetch()
	 */
	function fetch(&$request) {
		$params = array('envelopeSenderDisabled' => !Config::getVar('email', 'allow_envelope_sender'));

		return parent::fetch(&$request, $params);
	}
}

?>