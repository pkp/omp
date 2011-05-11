<?php

/**
 * @file controllers/tab/settings/emailTemplates/form/EmailTemplatesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	function EmailTemplatesForm() {
		$settings = array(
			'emailSignature' => 'string',
			'envelopeSender' => 'string'
		);

		$this->addCheck(new FormValidatorEmail($this, 'envelopeSender', 'optional', 'user.profile.form.emailRequired'));

		parent::PressSettingsForm($settings, 'controllers/tab/settings/emailTemplates/form/emailTemplatesForm.tpl');
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::display()
	 */
	function display() {
		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();
		if (Config::getVar('email', 'allow_envelope_sender'))
			$templateMgr->assign('envelopeSenderEnabled', true);
		$templateMgr->assign('pressEnabled', $press->getEnabled());

		parent::display();
	}
}

