<?php

/**
 * @file controllers/tab/settings/emailTemplates/form/EmailTemplatesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadForm
 * @ingroup controllers_tab_settings_emailTemplates_form
 *
 * @brief Form to add/edit user group.
 */

import('lib.pkp.classes.form.Form');
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

		parent::PressSettingsForm($settings, 'controllers/tab/settings/emailTemplates/form/emailTemplatesForm.tpl');
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();

		return parent::fetch($request);
	}


	//
	// Overridden methods from PressSettingsForm.
	//
	/**
	 * @see PressSettingsForm::execute()
	 */
	function execute($request) {
		$press =& Request::getPress();

		parent::execute();
	}
}

