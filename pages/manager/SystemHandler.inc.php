<?php

/**
 * @file SystemHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SystemHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for system management functions.
 */

import('pages.manager.ManagerHandler');

class SystemHandler extends ManagerHandler {

	/**
	 * Constructor
	 **/
	function SystemHandler() {
		parent::ManagerHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'system',
				'languages',
				'preparedEmails',
				'reviewForms',
				'readingTools',
				'payments',
				'plugins',
				'archiving'
			)
		);
	}

	/**
	 * Handle system settings management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function system($args, &$request) {
		// Default page is language settings
		$this->languages($args, $request);
	}

	/**
	 * Handle language settings management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function languages($args, &$request) {
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentPage', 'languages');
		$templateMgr->display('manager/system/languages.tpl');
	}

	/**
	 * Handle prepared email settings management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function preparedEmails($args, &$request) {
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentPage', 'preparedEmails');
		$templateMgr->display('manager/system/preparedEmails.tpl');
	}

	/**
	 * Handle review forms settings management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function reviewForms($args, &$request) {
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentPage', 'reviewForms');
		$templateMgr->display('manager/system/reviewForms.tpl');
	}

	/**
	 * Handle reading tools settings management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function readingTools($args, &$request) {
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentPage', 'readingTools');
		$templateMgr->display('manager/system/readingTools.tpl');
	}

	/**
	 * Handle payment settings management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function payments($args, &$request) {
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentPage', 'payments');
		$templateMgr->display('manager/system/payments.tpl');
	}

	/**
	 * Handle plugin settings management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function plugins($args, &$request) {
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentPage', 'plugins');
		$templateMgr->display('manager/system/plugins.tpl');
	}

	/**
	 * Handle archiving settings management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function archiving($args, &$request) {
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentPage', 'archiving');
		$templateMgr->display('manager/system/archiving.tpl');
	}
}

?>
