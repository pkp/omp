<?php

/**
 * @file pages/management/SettingsHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for settings pages.
 */

// Import the base Handler.
import('classes.handler.Handler');

class SettingsHandler extends Handler {
	/**
	 * Constructor.
	 */
	function SettingsHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'index',
				'categories',
				'series',
				'settings',
				'access',
				'press',
				'website',
				'publication',
				'distribution',
				'tools',
				'importExport'
			)
		);
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
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
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
	 * Display categories admin page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function categories($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('management/categories.tpl');
	}

	/**
	 * Display series admin page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function series($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('management/series.tpl');
	}

	/**
	 * Route to other settings operations.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function settings($args) {
		$path = $args[0];
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
	 * Route to other Tools operations
	 * @param $args array
	 */
	function tools($args) {
		$path = $args[0];
		switch ($path) {
			case 'index':
				$this->toolsIndex();
				break;
			case 'importExport':
				$this->importExport();
				break;
			default:
				assert(false);
		}
	}
	/**
	 * Display tools index page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function toolsIndex() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/tools/index.tpl');
	}

	/**
	 * Display Import/Export page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function importExport() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();
		$templateMgr->display('management/tools/importExport.tpl');
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
