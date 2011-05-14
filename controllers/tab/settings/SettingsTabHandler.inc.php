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
	var $_pageTabs;


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
	 * Get an array with current page tabs and its respective forms or templates.
	 * @return array
	 */
	function getPageTabs() {
		return $this->_pageTabs;
	}

	/**
	 * Set an array with current page tabs and its respective forms or templates.
	 * @param array
	 */
	function setPageTabs($pageTabs) {
		$this->_pageTabs = $pageTabs;
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
	 * Show a tab.
	 */
	function showTab($request) {
		if ($this->_isValidTab()) {
			if ($this->_isTabTemplate()) {
				$this->setupTemplate(true);
				$templateMgr =& TemplateManager::getManager();
				return $templateMgr->fetchJson($this->_getTabTemplate());
			} else {
				$tabForm = $this->_getTabForm();
				$tabForm->initData();
				$json = new JSONMessage(true, $tabForm->fetch($request));
				return $json->getString();
			}
		}
	}

	/**
	 * Handle forms data saving.
	 * @param $request Request
	 * @param $tabForm Form the current tab form
	 */
	function saveFormData(&$request) {
		if ($this->_isValidTab()) {
			$tabForm = $this->_getTabForm();
			$tabForm->readInputData();
			if($tabForm->validate()) {
				$tabForm->execute($request);
				return DAO::getDataChangedEvent();
			}
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
		$pageTabs = $this->getPageTabs();

		// Search for a form using the tab name.
		import($pageTabs[$currentTab]);
		$tabFormClassName = $this->_getFormClassName($pageTabs[$currentTab]);
		$tabForm = new $tabFormClassName;

		assert(is_a($tabForm, 'Form'));

		return $tabForm;
	}

	/**
	 * Return the tab template file
	 * @return string
	 */
	function _getTabTemplate() {
		$currentTab = $this->getCurrentTab();
		$pageTabs = $this->getPageTabs();

		return $pageTabs[$currentTab];
	}

	/**
	 * Check if the current tab value exists in pageTabsAndForms array.
	 * @return boolean
	 */
	function _isValidTab() {
		if (array_key_exists($this->getCurrentTab(), $this->getPageTabs())) {
			return true;
		} else {
			assert(false);
			return false;
		}
	}

	/**
	 * Check if the tab use a template or not.
	 * @return boolean
	 */
	function _isTabTemplate() {
		$currentTab = $this->getCurrentTab();
		$pageTabs = $this->getPageTabs();

		return (strstr($pageTabs[$currentTab], '.tpl'));
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
