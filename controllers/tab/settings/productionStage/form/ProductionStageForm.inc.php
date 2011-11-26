<?php

/**
 * @file controllers/tab/settings/productionStage/form/ProductionStageForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionStageForm
 * @ingroup controllers_tab_settings_productionStage_form
 *
 * @brief Form to edit production stage settings.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class ProductionStageForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function ProductionStageForm($wizardMode = false) {
		// This form has no settings. Only necessary for saving listbuider data.
		$settings = array();

		parent::PressSettingsForm($settings, 'controllers/tab/settings/productionStage/form/productionStageForm.tpl', $wizardMode);
	}
}

?>