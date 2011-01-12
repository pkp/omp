<?php

/**
 * @file controllers/grid/users/user/form/UserRoleForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserRoleForm
 * @ingroup controllers_grid_users_user_form
 *
 * @brief Form for managing roles for a newly created user.
 */

import('lib.pkp.classes.form.Form');

class UserRoleForm extends Form {

	/* @var the user id for which to map user groups */
	var $userId;

	/**
	 * Constructor.
	 */
	function UserRoleForm($userId) {
		parent::Form('controllers/grid/users/user/form/userRoleForm.tpl');

		$this->userId = (int) $userId;
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, &$request) {
		$helpTopicId = 'press.users.createNewUser';
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('userId', $this->userId);
		$templateMgr->assign('helpTopicId', $helpTopicId);

		return $this->fetch($request);
	}

	/**
	 * Update user's roles.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function &execute($args, &$request) {
		// Role management handled by listbuilder, just return user
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUser($this->userId);
		return $user;
	}
}

?>
