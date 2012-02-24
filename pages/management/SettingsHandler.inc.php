<?php

/**
 * @file pages/management/SettingsHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for settings pages.
 */

// Import the base ManagementHandler.
import('pages.management.ManagementHandler');

class SettingsHandler extends ManagementHandler {
	/**
	 * Constructor.
	 */
	function SettingsHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'index',
				'catalogAdmin',
				'categories',
				'series',
				'settings',
				'access',
				'press',
				'website',
				'publication',
				'distribution'
			)
		);
	}


	//
	// Public handler methods
	//
	/**
	 * Display settings index page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function index() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/settings/index.tpl');
	}

	/**
	 * Display catalog admin page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function catalogAdmin($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('management/catalogAdmin.tpl');
	}

	/**
	 * Display categories admin page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function categories($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$jsonMessage = new JSONMessage(true, $templateMgr->fetch('management/categories.tpl'));
		return $jsonMessage->getString();
	}

	/**
	 * Display series admin page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function series($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$jsonMessage = new JSONMessage(true, $templateMgr->fetch('management/series.tpl'));
		return $jsonMessage->getString();
	}

	/**
	 * Route to other settings operations.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function settings($args) {
		$path = array_shift($args);
		switch($path) {
			case 'index':
				$this->index();
				break;
			case 'access':
				$this->access();
				break;
			case 'press':
				$this->press();
				break;
			case 'website':
				$this->website();
				break;
			case 'publication':
				$this->publication();
				break;
			case 'distribution':
				$this->distribution();
				break;
			default:
				assert(false);
		}
	}

	/**
	 * Display Access and Security page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function access() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/settings/access.tpl');
	}

	/**
	 * Display The Press page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function press() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/settings/press.tpl');
	}

	/**
	 * Display website page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function website() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/settings/website.tpl');
	}

	/**
	 * Display publication process page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function publication() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/settings/publication.tpl');
	}

	/**
	 * Display distribution process page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function distribution() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/settings/distribution.tpl');
	}
}

?>
