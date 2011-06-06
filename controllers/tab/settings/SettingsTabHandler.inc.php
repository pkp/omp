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
 * @brief Handle AJAX operations for tabs on settings pages, under administration or management pages.
 */

// Import the base Handler.
import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class SettingsTabHandler extends Handler {

	/** @var string */
	var $_currentTab;

	/** @var array */
	var $_pageTabs;

	/** @var boolean */
	var $_wizardMode;


	/**
	 * Constructor
	 * @param $role string The role keys to be used in role assignment.
	 */
	function SettingsTabHandler($role) {
		parent::Handler();
		$this->addRoleAssignment($role,
				array(
					'saveFormData',
					'showTab'
				)
		);

		$this->setCurrentTab(Request::getUserVar('tab'));
		$this->setWizardMode(Request::getUserVar('wizardMode'));
	}


	//
	// Getters and Setters
	//
	/**
	 * Get if the current tab is in wizard mode.
	 * @return boolean
	 */
	function getWizardMode() {
		return $this->_wizardMode;
	}

	/**
	 * Set if the current tab is in wizard mode.
	 * @param $wizardMode boolean
	 */
	function setWizardMode($wizardMode) {
		$this->_wizardMode = $wizardMode;
	}

	/**
	 * Get the current tab name.
	 * @return string
	 */
	function getCurrentTab() {
		return $this->_currentTab;
	}

	/**
	 * Set the current tab name.
	 * @param $currentTab string
	 */
	function setCurrentTab($currentTab) {
		$this->_currentTab = $currentTab;
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
	// Extended methods from Handler
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
	// Public handler methods
	//
	/**
	 * Show a tab.
	 */
	function showTab($args, &$request) {
		if ($this->_isValidTab()) {
			if ($this->_isTabTemplate()) {
				$this->setupTemplate(true);
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('wizardMode', $this->getWizardMode());
				return $templateMgr->fetchJson($this->_getTabTemplate());
			} else {
				$tabForm = $this->getTabForm();
				$tabForm->initData();
				$json = new JSONMessage(true, $tabForm->fetch($request));
				return $json->getString();
			}
		}
	}

	/**
	 * Handle forms data (save or edit).
	 * @param $request Request
	 */
	function saveFormData($args, &$request) {
		$json = new JSONMessage();

		if ($this->_isValidTab()) {
			$tabForm = $this->getTabForm();

			// Try to save the form data.
			$tabForm->readInputData();
			if($tabForm->validate()) {
				$tabForm->execute($request);
			} else {
				$json->setStatus(false);
			}
		}

		return $json->getString();
	}

	/**
	 * Return an instance of the form based on the current tab.
	 * @return Form
	 */
	function getTabForm() {
		$currentTab = $this->getCurrentTab();
		$pageTabs = $this->getPageTabs();

		// Search for a form using the tab name.
		import($pageTabs[$currentTab]);
		$tabFormClassName = $this->_getFormClassName($pageTabs[$currentTab]);
		$tabForm = new $tabFormClassName($this->getWizardMode());

		assert(is_a($tabForm, 'Form'));

		return $tabForm;
	}

	/**
	 * Return an instance of the form based on its name.
	 * @param $formName The class name of the form.
	 * @return mixed Form or false
	 */
	function getTabFormByName($formName) {
		$pageTabs = $this->getPageTabs();

		// Search for a form using its own class name.
		$returnFormPath = null;
		foreach ($pageTabs as $tab => $tabFormPath) {
			$tabFormClassName = $this->_getFormClassName($tabFormPath);
			if ($tabFormClassName == $formName) {
				$returnFormPath = $tabFormPath;
			}
		}

		if (!is_null($returnFormPath)) {
			import($returnFormPath);
			$returnForm = new $formName();

			assert(is_a($returnForm, 'Form'));

			return $returnForm;
		}

		return false;
	}


	//
	// Private helper methods.
	//
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
