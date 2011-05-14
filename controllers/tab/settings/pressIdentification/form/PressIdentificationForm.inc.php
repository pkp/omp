<?php

/**
 * @file controllers/tab/settings/pressIdentification/form/PressIdentificationForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressIdentificationForm
 * @ingroup controllers_tab_settings_pressIdentification_form
 *
 * @brief Form to edit press identification information.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class PressIdentificationForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function PressIdentificationForm() {
		$settings = array(
			'enablePublicMonographId' => 'bool',
			'enablePublicGalleyId' => 'bool',
			'enablePageNumber' => 'bool'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/pressIdentification/form/pressIdentificationForm.tpl');
	}
}

?>