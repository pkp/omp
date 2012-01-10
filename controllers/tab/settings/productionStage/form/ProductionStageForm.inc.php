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
		$settings = array('publisher' => 'string', 'location' => 'string', 'codeType' => 'string', 'codeValue' => 'string');

		parent::PressSettingsForm($settings, 'controllers/tab/settings/productionStage/form/productionStageForm.tpl', $wizardMode);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array();
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request, $params = null) {
		$templateMgr =& TemplateManager::getManager();
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codeTypes =& $onixCodelistItemDao->getCodes('List44'); // Name code types for publisher
		$templateMgr->assign('codeTypes', $codeTypes);

		return parent::fetch(&$request);
	}
}

?>