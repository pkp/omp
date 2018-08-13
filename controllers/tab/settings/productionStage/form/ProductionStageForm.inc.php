<?php

/**
 * @file controllers/tab/settings/productionStage/form/ProductionStageForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionStageForm
 * @ingroup controllers_tab_settings_productionStage_form
 *
 * @brief Form to edit production stage settings.
 */

import('lib.pkp.classes.controllers.tab.settings.form.ContextSettingsForm');

class ProductionStageForm extends ContextSettingsForm {

	/**
	 * Constructor.
	 */
	function __construct($wizardMode = false) {
		$settings = array('publisher' => 'string', 'location' => 'string', 'codeType' => 'string', 'codeValue' => 'string');

		parent::__construct($settings, 'controllers/tab/settings/productionStage/form/productionStageForm.tpl', $wizardMode);
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
	 * @copydoc ContextSettingsForm::fetch()
	 */
	function fetch($request, $template = null, $display = false, $params = null) {
		$templateMgr = TemplateManager::getManager($request);
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codeTypes = $onixCodelistItemDao->getCodes('List44'); // Name code types for publisher
		$templateMgr->assign('codeTypes', $codeTypes);

		return parent::fetch($request, $template, $display, $params);
	}
}

?>
