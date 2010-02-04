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

// $Id$

import('pages.manager.ManagerHandler');

class PeopleHandler extends ManagerHandler {
	/**
	 * Constructor
	 **/
	function PeopleHandler() {
		parent::ManagerHandler();
	}
	
	/**
	 * Display list of people in the selected role.
	 * @param $args array first parameter is the role ID to display
	 */	
	function people($args) {
		$this->validate();
		$this->setupTemplate(true);

		$press =& Request::getPress();
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		if (Request::getUserVar('roleSymbolic') != null) $roleSymbolic = Request::getUserVar('roleSymbolic');
		else $roleSymbolic = isset($args[0]) ? $args[0] : 'all';

		$customRoleId = null;
		$role = null;

		if ($roleSymbolic != 'all') {
			if (Request::getUserVar('customRoleId') != null && $roleSymbolic == FLEXIBLE_ROLE_DEFAULT_PATH || is_numeric($roleSymbolic)) {
				$customRoleId = is_numeric($roleSymbolic) ? $roleSymbolic : Request::getUserVar('customRoleId');
				$role =& $flexibleRoleDao->getById($customRoleId);
			} else {
				$role =& $flexibleRoleDao->getByPath($roleSymbolic, $press->getId());
			}
			if ($role == null) {
				Request::redirect(null, null, null, 'all');
			}
			$roleName = $role->getLocalizedName();
		} else {
			$roleName = Locale::translate('manager.people.allUsers');
		}

		$isReviewer = $role && $role->getRoleId() == ROLE_ID_REVIEWER ? true : false;
		$templateMgr =& TemplateManager::getManager();

		$searchType = null;
		$searchMatch = null;
		$search = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (!empty($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} elseif (!empty($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo = Handler::getRangeInfo('users');

		if ($role) {
			$users =& $roleDao->getUsersByRoleId($role->getRoleId(), $press->getId(), $searchType, $search, $searchMatch, $rangeInfo, $customRoleId);
			$templateMgr->assign('customRoleId', $customRoleId);
			switch($role->getRoleId()) {
				case ROLE_ID_PRESS_MANAGER:
					$helpTopicId = 'press.roles.pressManager';
					break;
				case ROLE_ID_EDITOR:
					$helpTopicId = 'press.roles.editor';
					break;
				case ROLE_ID_SERIES_EDITOR:
					$helpTopicId = 'press.roles.seriesEditor';
					break;
				case ROLE_ID_PRODUCTION_EDITOR:
					$helpTopicId = 'press.roles.productionEditor';
					break;
				case ROLE_ID_DESIGNER:
					$helpTopicId = 'press.roles.designer';
					break;
				case ROLE_ID_REVIEWER:
					$helpTopicId = 'press.roles.reviewer';
					break;
				case ROLE_ID_COPYEDITOR:
					$helpTopicId = 'press.roles.copyeditor';
					break;
				case ROLE_ID_PROOFREADER:
					$helpTopicId = 'press.roles.proofreader';
					break;
				case ROLE_ID_AUTHOR:
					$helpTopicId = 'press.roles.author';
					break;
				case ROLE_ID_READER:
					$helpTopicId = 'press.roles.reader';
					break;
				case ROLE_ID_DIRECTOR:
					$helpTopicId = 'press.roles.director';
					break;
				case ROLE_ID_INDEXER:
					$helpTopicId = 'press.roles.indexer';
					break;					
				default:
					$helpTopicId = 'press.roles.index';
					break;
			}
		} else {
			$users =& $roleDao->getUsersByPressId($press->getId(), $searchType, $search, $searchMatch, $rangeInfo);
			$helpTopicId = 'press.users.allUsers';
		}

		$templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', Request::getUser());
		$templateMgr->assign('isReviewer', $isReviewer);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		if ($isReviewer) {
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$templateMgr->assign('rateReviewerOnQuality', $press->getSetting('rateReviewerOnQuality'));
			$templateMgr->assign('qualityRatings', $press->getSetting('rateReviewerOnQuality') ? $reviewAssignmentDao->getAverageQualityRatings($press->getId()) : null);
		}
		$templateMgr->assign('helpTopicId', $helpTopicId);
		$fieldOptions = Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_INTERESTS => 'user.interests',
			USER_FIELD_EMAIL => 'user.email'
		);
		if ($isReviewer) $fieldOptions = array_merge(array(USER_FIELD_INTERESTS => 'user.interests'), $fieldOptions);
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));

		$roles =& $flexibleRoleDao->getEnabledByPressId($press->getId());
		$templateMgr->assign('roles', $roles);

		$templateMgr->assign('contextRole', $role);
		$templateMgr->assign('roleName', $roleName);
		$templateMgr->assign('roleSymbolic', $roleSymbolic);

		$session =& Request::getSession();
		$session->setSessionVar('enrolmentReferrer', Request::getRequestedArgs());

		$templateMgr->display('manager/people/enrollment.tpl');
	}

	/**
	 * Search for users to enroll in a specific role.
	 * @param $args array first parameter is the selected role ID
	 */
	function enrollSearch($args) {
		$this->validate();

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$flexibleRoleId = (int)(isset($args[0]) ? $args[0] : Request::getUserVar('flexibleRoleId'));
		$press =& $pressDao->getPressByPath(Request::getRequestedPressPath());

		$role = null;
		$role =& $flexibleRoleDao->getById($flexibleRoleId);
		$isReviewer = $role && $role->getRoleId() == ROLE_ID_REVIEWER ? true : false;

		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);

		$searchType = null;
		$searchMatch = null;
		$search = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (!empty($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} elseif (!empty($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo = Handler::getRangeInfo('users');

		$users =& $userDao->getUsersByField($searchType, $searchMatch, $search, true, $rangeInfo);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		$templateMgr->assign('flexibleRoleId', $flexibleRoleId);
		$templateMgr->assign('roleName', $role ? $role->getLocalizedName() : '');
		$templateMgr->assign('contextRole', $role);

		$fieldOptions = Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email'
		);
		if ($isReviewer) $fieldOptions = array_merge(array(USER_FIELD_INTERESTS => 'user.interests'), $fieldOptions);
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', Request::getUser());
		$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
		$templateMgr->assign('helpTopicId', 'press.users.index');

		$roles =& $flexibleRoleDao->getEnabledByPressId($press->getId());
		$templateMgr->assign('roles', $roles);

		$session =& Request::getSession();
		$referrerUrl = $session->getSessionVar('enrolmentReferrer');
		$templateMgr->assign('enrolmentReferrerUrl', isset($referrerUrl) ? Request::url(null,'manager','people',$referrerUrl) : Request::url(null,'manager'));
		$session->unsetSessionVar('enrolmentReferrer');

		$templateMgr->display('manager/people/searchUsers.tpl');
	}

	/**
	 * Show users with no role.
	 */
	function showNoRole() {
		$this->validate();

		$userDao =& DAORegistry::getDAO('UserDAO');

		$templateMgr =& TemplateManager::getManager();

		parent::setupTemplate(true);

		$rangeInfo = Handler::getRangeInfo('users');

		$users =& $userDao->getUsersWithNoRole(true, $rangeInfo);

		$templateMgr->assign('omitSearch', true);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', Request::getUser());
		$templateMgr->assign('helpTopicId', 'press.users.index');
		$templateMgr->display('manager/people/searchUsers.tpl');
	}

	/**
	 * Enroll a user in a role.
	 */
	function enroll($args) {
		$this->validate();
		$flexibleRoleId = (int)(isset($args[0]) ? $args[0] : Request::getUserVar('flexibleRoleId'));

		// Get a list of users to enroll -- either from the
		// submitted array 'users', or the single user ID in
		// 'userId'
		$users = Request::getUserVar('users');
		if (!isset($users) && Request::getUserVar('userId') != null) {
			$users = array(Request::getUserVar('userId'));
		}

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$press =& $pressDao->getPressByPath(Request::getRequestedPressPath());

		$flexibleRole =& $flexibleRoleDao->getById($flexibleRoleId);
		$roleId = $flexibleRole ? $flexibleRole->getRoleId() : 0;
		$rolePath = $flexibleRole ? $flexibleRole->getPath() : '';

		if ($users != null && is_array($users) && $roleId != 0 && $roleId != ROLE_ID_SITE_ADMIN) {
			for ($i=0; $i<count($users); $i++) {
				if (!$roleDao->roleExists($press->getId(), $users[$i], $roleId, $flexibleRoleId)) {
					$role = new Role();
					$role->setPressId($press->getId());
					$role->setUserId($users[$i]);
					$role->setRoleId($roleId);
					$role->setFlexibleRoleId($flexibleRoleId);

					$roleDao->insertRole($role);
				}
			}
		}

		if ($roleId == ROLE_ID_FLEXIBLE_ROLE) {
			Request::redirect(null, null, 'people', $rolePath, array('customRoleId' => $flexibleRoleId));
		} else {
			Request::redirect(null, null, 'people', $rolePath);
		}
	}

	/**
	 * Unenroll a user from a role.
	 */
	function unEnroll($args) {
		$flexibleRoleId = isset($args[0]) ? $args[0] : 0;
		$this->validate();

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$press =& $pressDao->getPressByPath(Request::getRequestedPressPath());
		$flexibleRole =& $flexibleRoleDao->getById($flexibleRoleId);

		if ($flexibleRole) {
			if ($flexibleRole->getRoleId() != ROLE_ID_SITE_ADMIN) {
				$roleDao->deleteRoleByUserId(Request::getUserVar('userId'), $press->getId(), $flexibleRole->getRoleId(), $flexibleRoleId);
			}

		}

		if ($flexibleRole->getRoleId() == ROLE_ID_FLEXIBLE_ROLE) {
			Request::redirect(null, null, 'people', $flexibleRole->getPath(), array('customRoleId' => $flexibleRoleId));
		} else {
			Request::redirect(null, null, 'people', $flexibleRole->getPath());
		}
	}

	/**
	 * Show form to synchronize user enrollment with another press.
	 */
	function enrollSyncSelect($args) {
		$this->validate();
		$this->setupTemplate(true);

		$rolePath = isset($args[0]) ? $args[0] : '';
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleId = $roleDao->getRoleIdFromPath($rolePath);
		if ($roleId) {
			$roleName = $roleDao->getRoleName($roleId, true);
		} else {
			$rolePath = '';
			$roleName = '';
		}

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$pressNames =& $pressDao->getPressNames();

		$press =& Request::getPress();
		unset($pressNames[$press->getId()]);

		$templateMgr =& TemplateManager::getManager();

		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		$roles =& $flexibleRoleDao->getEnabledByPressId($press->getId());

		$templateMgr->assign('rolePath', $rolePath);
		$templateMgr->assign('roleName', $roleName);
		$templateMgr->assign_by_ref('roles', $roles);

		$templateMgr->assign('pressOptions', $pressNames);
		$templateMgr->display('manager/people/enrollSync.tpl');
	}

	/**
	 * Synchronize user enrollment with another press.
	 */
	function enrollSync($args) {
		$this->validate();

		$press =& Request::getPress();
		$rolePath = Request::getUserVar('rolePath');
		$syncPress = Request::getUserVar('syncPress');

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$roleId = $roleDao->getRoleIdFromPath($rolePath);

		if ((!empty($roleId) || $rolePath == 'all') && !empty($syncPress)) {
			$roles =& $roleDao->getRolesByPressId($syncPress == 'all' ? null : $syncPress, $roleId);
			while (!$roles->eof()) {
				$role =& $roles->next();
				$role->setPressId($press->getId());

				$flexibleRole =& $flexibleRoleDao->getByRoleId($role->getRoleId(), $press->getId());

				if ($role->getRoleId() != ROLE_ID_FLEXIBLE_ROLE && $role->getRoleId() != ROLE_ID_SITE_ADMIN && !$roleDao->roleExists($role->getPressId(), $role->getUserId(), $role->getRoleId(), $flexibleRole->getId())) {
					$roleDao->insertRole($role);
				}
				unset($flexibleRole);
			}
		}

		Request::redirect(null, null, 'people', $rolePath);
	}

	/**
	 * Display form to create a new user.
	 */
	function createUser() {
		$this->editUser();
	}

	/**
	 * Get a suggested username, making sure it's not
	 * already used by the system. (Poor-man's AJAX.)
	 */
	function suggestUsername() {
		$this->validate();
		$suggestion = Validation::suggestUsername(
			Request::getUserVar('firstName'),
			Request::getUserVar('lastName')
		);
		echo $suggestion;
	}

	/**
	 * Display form to create/edit a user profile.
	 * @param $args array optional, if set the first parameter is the ID of the user to edit
	 */
	function editUser($args = array()) {
		$this->validate();
		$this->setupTemplate(true);

		$press =& Request::getPress();

		$userId = isset($args[0]) ? $args[0] : null;

		$templateMgr =& TemplateManager::getManager();

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights
			// over this user. Display an error.
			$templateMgr->assign('pageTitle', 'manager.people');
			$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
			$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
			return $templateMgr->display('common/error.tpl');
		}

		import('manager.form.UserManagementForm');

		$templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$userForm = new UserManagementForm($userId);
		} else {
			$userForm =& new UserManagementForm($userId);
		}
		if ($userForm->isLocaleResubmit()) {
			$userForm->readInputData();
		} else {
			$userForm->initData();
		}
		$userForm->display();
	}

	/**
	 * Allow the Press Manager to merge user accounts, including attributed monographs etc.
	 */
	function mergeUsers($args) {
		$this->validate();
		$this->setupTemplate(true);

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& Request::getPress();
		$pressId = $press->getId();
		$templateMgr =& TemplateManager::getManager();

		$oldUserId = Request::getUserVar('oldUserId');
		$newUserId = Request::getUserVar('newUserId');

		// Ensure that we have administrative priveleges over the specified user(s).
		if (
			(!empty($oldUserId) && !Validation::canAdminister($pressId, $oldUserId)) ||
			(!empty($newUserId) && !Validation::canAdminister($pressId, $newUserId))
		) {
			$templateMgr->assign('pageTitle', 'manager.people');
			$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
			$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
			return $templateMgr->display('common/error.tpl');
		}

		if (!empty($oldUserId) && !empty($newUserId)) {
			import('user.UserAction');
			UserAction::mergeUsers($oldUserId, $newUserId);
			Request::redirect(null, 'manager');
		}

		if (!empty($oldUserId)) {
			// Get the old username for the confirm prompt.
			$oldUser =& $userDao->getUser($oldUserId);
			$templateMgr->assign('oldUsername', $oldUser->getUsername());
			unset($oldUser);
		}

		// The manager must select one or both IDs.
		if (Request::getUserVar('roleSymbolic')!=null) $roleSymbolic = Request::getUserVar('roleSymbolic');
		else $roleSymbolic = isset($args[0])?$args[0]:'all';

		if ($roleSymbolic != 'all' && String::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
			$roleId = $roleDao->getRoleIdFromPath($matches[1]);
			if ($roleId == null) {
				Request::redirect(null, null, null, 'all');
			}
			$roleName = $roleDao->getRoleName($roleId, true);
		} else {
			$roleId = 0;
			$roleName = 'manager.people.allUsers';
		}

		$searchType = null;
		$searchMatch = null;
		$search = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (!empty($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} else if (!empty($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo = Handler::getRangeInfo('users');

		if ($roleId) {
			$users =& $roleDao->getUsersByRoleId($roleId, $pressId, $searchType, $search, $searchMatch, $rangeInfo);
			$templateMgr->assign('roleId', $roleId);
		} else {
			$users =& $roleDao->getUsersByPressId($pressId, $searchType, $search, $searchMatch, $rangeInfo);
		}

		$templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
		$templateMgr->assign('helpTopicId', 'press.managementPages.mergeUsers');
		$templateMgr->assign('roleName', $roleName);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', Request::getUser());
		$templateMgr->assign('isReviewer', $roleId == ROLE_ID_REVIEWER);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		if ($roleId == ROLE_ID_REVIEWER) {
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$templateMgr->assign('rateReviewerOnQuality', $press->getSetting('rateReviewerOnQuality'));
			$templateMgr->assign('qualityRatings', $press->getSetting('rateReviewerOnQuality') ? $reviewAssignmentDao->getAverageQualityRatings($pressId) : null);
		}
		$templateMgr->assign('fieldOptions', Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email',
			USER_FIELD_INTERESTS => 'user.interests'
		));
		$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
		$templateMgr->assign('oldUserId', $oldUserId);
		$templateMgr->assign('rolePath', $roleDao->getRolePath($roleId));
		$templateMgr->assign('roleSymbolic', $roleSymbolic);
		$templateMgr->display('manager/people/selectMergeUser.tpl');
	}

	/**
	 * Disable a user's account.
	 * @param $args array the ID of the user to disable
	 */
	function disableUser($args) {
		$this->validate();
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:Request::getUserVar('userId');
		$user =& Request::getUser();
		$press =& Request::getPress();

		if ($userId != null && $userId != $user->getId()) {
			if (!Validation::canAdminister($press->getId(), $userId)) {
				// We don't have administrative rights
				// over this user. Display an error.
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('pageTitle', 'manager.people');
				$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
				$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
				$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
				return $templateMgr->display('common/error.tpl');
			}
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId);
			if ($user) {
				$user->setDisabled(1);
				$user->setDisabledReason(Request::getUserVar('reason'));
			}
			$userDao->updateObject($user);
		}

		Request::redirect(null, null, 'people', 'all');
	}

	/**
	 * Enable a user's account.
	 * @param $args array the ID of the user to enable
	 */
	function enableUser($args) {
		$this->validate();
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:null;
		$user =& Request::getUser();

		if ($userId != null && $userId != $user->getId()) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId, true);
			if ($user) {
				$user->setDisabled(0);
			}
			$userDao->updateObject($user);
		}

		Request::redirect(null, null, 'people', 'all');
	}

	/**
	 * Remove a user from all roles for the current press.
	 * @param $args array the ID of the user to remove
	 */
	function removeUser($args) {
		$this->validate();
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:null;
		$user =& Request::getUser();
		$press =& Request::getPress();

		if ($userId != null && $userId != $user->getId()) {
			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$roleDao->deleteRoleByUserId($userId, $press->getId());
		}

		Request::redirect(null, null, 'people', 'all');
	}

	/**
	 * Save changes to a user profile.
	 */
	function updateUser() {
		$this->validate();
		$press =& Request::getPress();
		$userId = Request::getUserVar('userId');
		$this->setupTemplate(true);

		if (!empty($userId) && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights
			// over this user. Display an error.
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('pageTitle', 'manager.people');
			$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
			$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
			return $templateMgr->display('common/error.tpl');
		}

		import('manager.form.UserManagementForm');

		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$userForm = new UserManagementForm($userId);
		} else {
			$userForm =& new UserManagementForm($userId);
		}
		$userForm->readInputData();

		if ($userForm->validate()) {
			$userForm->execute();

			if (Request::getUserVar('createAnother')) {
				$this->setupTemplate(true);
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
				$templateMgr->assign('userCreated', true);
				if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
					$userForm = new UserManagementForm();
				} else {
					$userForm =& new UserManagementForm();
				}
				$userForm->initData();
				$userForm->display();

			} else {
				if ($source = Request::getUserVar('source')) Request::redirectUrl($source);
				else Request::redirect(null, null, 'people', 'all');
			}
		} else {
			$userForm->display();
		}
	}

	/**
	 * Display a user's profile.
	 * @param $args array first parameter is the ID or username of the user to display
	 */
	function userProfile($args) {
		$this->validate();
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
		$templateMgr->assign('helpTopicId', 'press.users.index');

		$userDao =& DAORegistry::getDAO('UserDAO');
		$userId = isset($args[0]) ? $args[0] : 0;
		if (is_numeric($userId)) {
			$userId = (int) $userId;
			$user = $userDao->getUser($userId);
		} else {
			$user = $userDao->getUserByUsername($userId);
		}


		if ($user == null) {
			// Non-existent user requested
			$templateMgr->assign('pageTitle', 'manager.people');
			$templateMgr->assign('errorMsg', 'manager.people.invalidUser');
			$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
			$templateMgr->display('common/error.tpl');

		} else {
			$site =& Request::getSite();
			$press =& Request::getPress();
			$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
			$roles =& $flexibleRoleDao->getByUserId($user->getId(), $press->getId());

			$countryDao =& DAORegistry::getDAO('CountryDAO');
			$country = null;
			if ($user->getCountry() != '') {
				$country = $countryDao->getCountry($user->getCountry());
			}
			$templateMgr->assign('country', $country);

			$templateMgr->assign_by_ref('user', $user);
			$templateMgr->assign_by_ref('userRoles', $roles);
			$templateMgr->assign('localeNames', Locale::getAllLocales());
			$templateMgr->display('manager/people/userProfile.tpl');
		}
	}
}

?>
