<?php

/**
 * @file RoleHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleHandler
 * @ingroup pages_role
 *
 * @brief Handle custom role requests.
 */

// $Id$


import('handler.Handler');

class RoleHandler extends Handler {
	/**
	 * Constructor
	 */
	function RoleHandler() {
		parent::Handler();

		$this->addCheck(new HandlerValidatorPress($this));
		$this->addCheck(new HandlerValidatorCustomRole($this, true, null, null));
	}

	/**
	 * Display the index page for the custom role.
	 */
	function index($args) {
		$this->validate();
		$this->setupTemplate();

		$session =& Request::getSession();
		$roleId = $session->getSessionVar('customRoleId');

		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		$role = $flexibleRoleDao->getById($roleId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('roleName', $role->getLocalizedName());
		$templateMgr->display('role/index.tpl');
	}
}

?>
