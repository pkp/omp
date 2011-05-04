<?php

/**
 * @file controllers/tab/settings/SettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on settings pages.
 */

// Import the base Handler.
import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class SettingsTabHandler extends Handler {

	/** @var string */
	var $_currentTab;

	/** @var array */
	var $_pageTabsAndForms;


	/**
	 * Constructor
	 */
	function SettingsTabHandler() {
		parent::Handler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
					'saveFormData',
					'showTab'
				)
		);

		$this->_currentTab = Request::getUserVar('tab');
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the current tab name.
	 * @return string
	 */
	function getCurrentTab() {
		return $this->_currentTab;
	}

	/**
	 * Get an array with current page tabs and its respective forms.
	 * @return array
	 */
	function getPageTabsAndForms() {
		return $this->_pageTabsAndForms;
	}

	/**
	 * Set an array with current page tabs and its respective forms.
	 * @param array
	 */
	function setPageTabsAndForms($pageTabsAndForms) {
		$this->_pageTabsAndForms = $pageTabsAndForms;
	}


	//
	// Overridden methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Load grid-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Show a tab
	 */
	function showTab($request) {
		$tabForm = $this->_getTabForm();
		$tabForm->initData();
		$json = new JSONMessage(true, $tabForm->fetch($request));
		return $json->getString();

	}

	/**
	 * Handle forms data saving.
	 * @param $request Request
	 * @param $tabForm Form the current tab form
	 */
	function saveFormData(&$request) {
		$tabForm = $this->_getTabForm();
		$tabForm->readInputData();
		if($tabForm->validate()) {
			$tabForm->execute($request);
			return DAO::getDataChangedEvent();
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Return an instance of the form based on the current tab.
	 * @return Form
	 */
	function _getTabForm() {
		$currentTab = $this->getCurrentTab();
		$pageTabsAndForms = $this->getPageTabsAndForms();

		// Search for a form using the tab name.
		if (array_key_exists($currentTab, $pageTabsAndForms)) {
			import($pageTabsAndForms[$currentTab]);
			$tabFormClassName = $this->_getFormClassName($pageTabsAndForms[$currentTab]);
			$tabForm = new $tabFormClassName;
		}
		assert(is_a($tabForm, 'Form'));

		return $tabForm;
	}

	/**
	 * Return the form class name based on the current tab name.
	 * @param $classPath string
	 * @return string
	 */
	function _getFormClassName($classPath) {
		$needle = '.form.';
		$formClassName = strstr($classPath, $needle);
		$formClassName = trim(str_replace($needle, ' ', $formClassName));
		return $formClassName;
	}
}
?>
