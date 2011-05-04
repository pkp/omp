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

		parent::PressSettingsForm($settings, 'controllers/tab/settings/emailTemplates/form/emailTemplatesForm.tpl');
	}
}

