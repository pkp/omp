<?php

/**
 * @file pages/admin/AdminHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for site administration functions.
 */



import('classes.handler.Handler');

class AdminHandler extends Handler {
	/**
	 * Constructor
	 */
	function AdminHandler() {
		parent::Handler();

		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_SITE_ADMIN)));
	}

	/**
	 * Display site admin index page.
	 */
	function index() {
		$this->validate();
		$this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.index');

		// Verifies if this is a multiple press installation.
		$pressDao = DAORegistry::getDAO('PressDAO');
		if (count($pressDao->getPressNames()) > 1) {
			$templateMgr->assign('isMultiplePress', true);
		}

		$templateMgr->display('admin/index.tpl');
	}

	/**
	 * Display the administration settings page.
	 */
	function settings() {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);
		$templateMgr->display('admin/adminSettings.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_ADMIN));
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_ADMIN));

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy',
			$subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'admin'), 'admin.siteAdmin'))
				: array(array(Request::url(null, 'user'), 'navigation.user'))
		);
	}
}

?>
