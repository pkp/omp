<?php

/**
 * @file UsersHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsersHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for user management functions.
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
			array(
				'users',
				'roles',
				'enrollment'
			)
		);
	}

	/**
	 * Handle user management requests.
	 * @param $args
	 * @param $request PKPRequest
	 */
	function users($args, &$request) {
		$this->setupTemplate(true);
		$press =& $request->getPress();

		import('lib.pkp.classes.user.PKPUserDAO');
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_GRID));

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByContextId($press->getId());
		$userGroupOptions = array('' => Locale::translate('grid.user.allRoles'));
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$userGroupOptions[$userGroup->getId()] = $userGroup->getLocalizedName();
		}

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
		$templateMgr->assign_by_ref('userGroupOptions', $userGroupOptions);
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign('matchOptions', $matchOptions);
		$templateMgr->assign('currentPage', 'users');
		$templateMgr->display('manager/users/users.tpl');
	}

	/**
	 * Handle role management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function roles($args, &$request) {
		$this->setupTemplate(true);
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleOptions = $roleDao->getPressRoleNames();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('roleOptions', $roleOptions);
		$templateMgr->assign('currentPage', 'roles');
		$templateMgr->display('manager/users/roles.tpl');
	}

	/**
	 * Handle user enrollment requests.
	 * @param $args array
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
