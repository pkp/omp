<?php

/**
 * @file PeopleHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PeopleHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for people management functions.
 */


import('pages.manager.ManagerHandler');

class UsersHandler extends ManagerHandler {

	/**
	 * Constructor
	 **/
	function UsersHandler() {
		parent::ManagerHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array (
				'users',
				'roles',
				'enrollment'
			)
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Handle user management requests.
	 * @param $args array first parameter is the management path (e.g. users, roles, etc.)
	 * @param $request PKPRequest
	 */
	function users($args, &$request) {
		$this->setupTemplate(true);

		import('lib.pkp.classes.user.PKPUserDAO');

		$fieldOptions = array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email'
		);

		$matchOptions = array(
			'contains' => 'form.contains',
			'is' => 'form.is'
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign('matchOptions', $matchOptions);
		$templateMgr->assign('currentPage', 'users');
		$templateMgr->display('manager/users/users.tpl');
	}

	/**
	 * Handle role management requests.
	 * @param $args array first parameter is the management path (e.g. users, roles, etc.)
	 * @param $request PKPRequest
	 */
	function roles($args, &$request) {
		$this->setupTemplate(true);
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleOptions = $roleDao->getRoleNames();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('roleOptions', $roleOptions);
		$templateMgr->assign('currentPage', 'roles');
		$templateMgr->display('manager/users/roles.tpl');
	}

	/**
	 * Handle user enrollment requests.
	 * @param $args array first parameter is the management path (e.g. users, roles, etc.)
	 * @param $request PKPRequest
	 */
	function enrollment($args, &$request) {
		$this->setupTemplate(true);

		import('lib.pkp.classes.user.PKPUserDAO');

		$fieldOptions = array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email'
		);

		$matchOptions = array(
			'contains' => 'form.contains',
			'is' => 'form.is'
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign('matchOptions', $matchOptions);
		$templateMgr->assign('currentPage', 'enrollment');
		$templateMgr->display('manager/users/enrollment.tpl');
	}
}

?>
