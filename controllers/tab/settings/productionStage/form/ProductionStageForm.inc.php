<?php

/**
 * @file controllers/tab/settings/productionStage/form/ProductionStageForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
		$settings = array('publisher' => 'string');

		parent::PressSettingsForm($settings, 'controllers/tab/settings/productionStage/form/productionStageForm.tpl', $wizardMode);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('publisher');
	}
}

?>