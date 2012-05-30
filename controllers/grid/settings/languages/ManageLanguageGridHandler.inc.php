<?php

/**
 * @file controllers/grid/settings/languages/ManageLanguageGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageLanguageGridHandler
 * @ingroup controllers_grid_settings_languages
 *
 * @brief Handle language management grid requests only.
 */

import('classes.controllers.grid.languages.LanguageGridHandler');

import('controllers.grid.languages.LanguageGridRow');

class ManageLanguageGridHandler extends LanguageGridHandler {
	/**
	 * Constructor
	 */
	function ManageLanguageGridHandler() {
		parent::LanguageGridHandler();
		$this->addRoleAssignment(array(
			ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow'));
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Implement methods from GridHandler.
	//
	/**
	 * @see GridHandler::loadData()
	 */
	function loadData(&$request, $filter) {
		$site =& $request->getSite();
		$press =& $request->getPress();

		$allLocales = AppLocale::getAllLocales();
		$supportedLocales = $site->getSupportedLocales();
		$pressPrimaryLocale = $press->getPrimaryLocale();
		$data = array();

		foreach ($supportedLocales as $locale) {
			$data[$locale] = array();
			$data[$locale]['name'] = $allLocales[$locale];
			$data[$locale]['supported'] = true;
			$data[$locale]['primary'] = ($locale == $pressPrimaryLocale);
		}

		$data = $this->addManagementData($request, $data);
		return $data;
	}

	//
	// Extended methods from LanguageGridHandler.
	//
	/**
	 * @see LanguageGridHandler::initialize()
	 */
	function initialize($request) {
		parent::initialize($request);

		$this->setInstructions('manager.languages.languageInstructions');

		$this->addNameColumn();
		$this->addPrimaryColumn('pressPrimary');
		$this->addManagementColumns();
	}
}
?>
