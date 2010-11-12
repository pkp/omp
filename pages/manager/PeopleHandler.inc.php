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

class PeopleHandler extends ManagerHandler {
	/**
	 * Constructor
	 **/
	function PeopleHandler() {
		parent::ManagerHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array('people', 'enrollSearch', 'showNoRole', 'enroll', 'unEnroll',
				'createUser', 'suggestUsername', 'editUser', 'mergeUsers', 'disableUser',
				'enableUser', 'removeUser', 'updateUser', 'userProfile'));
	}

	/**
	 * Display list of people in the selected role.
	 * @param $args array first parameter is the role ID to display
	 * @param $request PKPRequest
	 */
	function people($args, &$request) {
		$this->setupTemplate(true);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		if ($request->getUserVar('userGroupId')!=null) $userGroupId = $request->getUserVar('userGroupId');
		else $userGroupId = isset($args[0])?$args[0]:'all';

		$sort = $request->getUserVar('sort');
		$sort = isset($sort) ? $sort : 'name';
		$sortDirection = $request->getUserVar('sortDirection');

		$press =& $request->getPress();
		if (is_numeric($userGroupId)) {
			// use the pressId to ensure this group belongs to this press
			$userGroup =& $userGroupDao->getById($userGroupId, $press->getId());
			// in case an incorrect id was passed in
			if ( !$userGroup ) {
				$request->redirect(null, null, null, 'all');
			}
		} else {
			// unset userGroup and userGroupId
			$userGroupId = 'all';
			$userGroup = null;
		}

		$templateMgr =& TemplateManager::getManager();

		$searchType = null;
		$searchMatch = null;
		$search = $request->getUserVar('search');
		$searchInitial = $request->getUserVar('searchInitial');
		if (!empty($search)) {
			$searchType = $request->getUserVar('searchField');
			$searchMatch = $request->getUserVar('searchMatch');

		} elseif (!empty($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo = $this->getRangeInfo('users');

		if ($userGroup) {
			$users =& $userGroupDao->getUsersById($userGroup->getId(), $press->getId(), $searchType, $search, $searchMatch, $rangeInfo, $sort);
			switch($userGroup->getRoleId()) {
				case ROLE_ID_PRESS_MANAGER:
					$helpTopicId = 'press.roles.pressManager';
					break;
				case ROLE_ID_EDITOR:
					$helpTopicId = 'press.roles.editor';
					break;
				case ROLE_ID_SERIES_EDITOR:
					$helpTopicId = 'press.roles.sectionEditor';
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
				default:
					$helpTopicId = 'press.roles.index';
					break;
			}
		} else {
			$users =& $userGroupDao->getUsersByPressId($press->getId(), $searchType, $search, $searchMatch, $rangeInfo, $sort);
			$helpTopicId = 'press.users.allUsers';
		}

		$templateMgr->assign('currentUrl', $request->url(null, null, 'people', 'all'));
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', $request->getUser());

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', $request->getUserVar('searchInitial'));

		$isReviewer = $userGroup && ($userGroup->getRoleId() == ROLE_ID_REVIEWER);
		$templateMgr->assign('isReviewer', $isReviewer);
		if ($isReviewer) {
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$templateMgr->assign('rateReviewerOnQuality', $press->getSetting('rateReviewerOnQuality'));
			$templateMgr->assign('qualityRatings', $press->getSetting('rateReviewerOnQuality') ? $reviewAssignmentDao->getAverageQualityRatings($press->getId()) : null);
		}
		$templateMgr->assign('helpTopicId', $helpTopicId);

		// set the user group options for the HTML select
		$userGroupOptions = array();
		$userGroupPaths = array();
		$allUserGroups =& $userGroupDao->getByPressId($press->getId());
		while ( !$allUserGroups->eof() ) {
			$tmpUserGroup =& $allUserGroups->next();
			$userGroupOptions[$tmpUserGroup->getId()] = $tmpUserGroup->getLocalizedName();
			$userGroupPaths[$tmpUserGroup->getPath()] = $tmpUserGroup->getLocalizedName();
			unset($tmpUserGroup);
		}
		$templateMgr->assign('userGroupOptions', $userGroupOptions);
		$templateMgr->assign('userGroupPaths', $userGroupPaths);

		$searchOptions = array('is' => 'form.is',
								'contains' => 'form.contains',
								'startsWith' => 'form.startsWith');
		$templateMgr->assign('searchOptions', $searchOptions);

		$fieldOptions = Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_INTERESTS => 'user.interests',
			USER_FIELD_EMAIL => 'user.email'
		);
		if ($isReviewer) $fieldOptions = array_merge(array(USER_FIELD_INTERESTS => 'user.interests'), $fieldOptions);
		$templateMgr->assign('fieldOptions', $fieldOptions);

		$templateMgr->assign_by_ref('userGroup', $userGroup);
		$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
		$templateMgr->assign('sort', $sort);

		$session =& $request->getSession();
		$session->setSessionVar('enrolmentReferrer', $request->getRequestedArgs());

		$templateMgr->display('manager/people/enrollment.tpl');
	}

	/**
	 * Allow the Press Manager to merge user accounts, including attributed monographs etc.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function mergeUsers($args, &$request) {
		$this->setupTemplate(true);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& $request->getPress();
		$pressId = $press->getId();
		$templateMgr =& TemplateManager::getManager();

		$oldUserIds = (array) $request->getUserVar('oldUserIds');
		$newUserId = $request->getUserVar('newUserId');

		// Ensure that we have administrative priveleges over the specified user(s).
		$canAdministerAll = true;
		foreach ($oldUserIds as $oldUserId) {
			if (!Validation::canAdminister($pressId, $oldUserId)) $canAdministerAll = false;
		}

		if (
			(!empty($oldUserIds) && !$canAdministerAll) ||
			(!empty($newUserId) && !Validation::canAdminister($pressId, $newUserId))
		) {
			$templateMgr->assign('pageTitle', 'manager.people');
			$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
			$templateMgr->assign('backLink', $request->url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
			return $templateMgr->display('common/error.tpl');
		}

		if (!empty($oldUserIds) && !empty($newUserId)) {
			import('classes.user.UserAction');
			foreach ($oldUserIds as $oldUserId) {
				UserAction::mergeUsers($oldUserId, $newUserId);
			}
			$request->redirect(null, 'manager');
		}

		// The manager must select one or both IDs.
		if ($request->getUserVar('userGroupId')!=null) $userGroupId = $request->getUserVar('userGroupId');
		else $userGroupId = isset($args[0])?$args[0]:'all';

		if ($userGroupId != 'all' && is_numeric($userGroupId)) {
			$userGroup =& $userGroupDao->getById($userGroupId);
			if ($userGroupId == null) {
				$request->redirect(null, null, null, 'all');
			}
		} else {
			$userGroup = null;
		}

		$sort = $request->getUserVar('sort');
		$sort = isset($sort) ? $sort : 'name';
		$sortDirection = $request->getUserVar('sortDirection');

		$searchType = null;
		$searchMatch = null;
		$search = $request->getUserVar('search');
		$searchInitial = $request->getUserVar('searchInitial');
		if (!empty($search)) {
			$searchType = $request->getUserVar('searchField');
			$searchMatch = $request->getUserVar('searchMatch');

		} else if (!empty($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo = $this->getRangeInfo('users');

		if ($userGroup) {
			$users =& $userGroupDao->getUsersById($userGroupId, $pressId, $searchType, $search, $searchMatch, $rangeInfo, $sort);
			$templateMgr->assign_by_ref('userGroup', $userGroup);
			$isReviewer = $userGroup->getRoleId() == ROLE_ID_REVIEWER;
		} else {
			$users =& $userGroupDao->getUsersByPressId($pressId, $searchType, $search, $searchMatch, $rangeInfo, $sort);
			$isReviewer = false;
		}

		//$templateMgr->assign_by_ref('roleSettings', $this->retrieveRoleAssignmentPreferences($press->getId()));

		$templateMgr->assign('currentUrl', $request->url(null, null, 'people', 'all'));
		$templateMgr->assign('helpTopicId', 'press.managementPages.mergeUsers');
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', $request->getUser());
		$templateMgr->assign('isReviewer', $isReviewer);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', $request->getUserVar('searchInitial'));

		if ($isReviewer) {
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
		$templateMgr->assign('oldUserIds', $oldUserIds);
		$templateMgr->assign('userGroupId', $userGroupId);
		$templateMgr->assign('sort', $sort);
		$templateMgr->assign('sortDirection', $sortDirection);
		$templateMgr->display('manager/people/selectMergeUser.tpl');
	}

	/**
	 * Disable a user's account.
	 * @param $args array the ID of the user to disable
	 * @param $request PKPRequest
	 */
	function disableUser($args, &$request) {
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:$request->getUserVar('userId');
		$user =& $request->getUser();
		$press =& $request->getPress();

		if ($userId != null && $userId != $user->getId()) {
			if (!Validation::canAdminister($press->getId(), $userId)) {
				// We don't have administrative rights
				// over this user. Display an error.
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('pageTitle', 'manager.people');
				$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
				$templateMgr->assign('backLink', $request->url(null, null, 'people', 'all'));
				$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
				return $templateMgr->display('common/error.tpl');
			}
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId);
			if ($user) {
				$user->setDisabled(1);
				$user->setDisabledReason($request->getUserVar('reason'));
			}
			$userDao->updateObject($user);
		}

		$request->redirect(null, null, 'people', 'all');
	}

	/**
	 * Enable a user's account.
	 * @param $args array the ID of the user to enable
	 * @param $request PKPRequest
	 */
	function enableUser($args, &$request) {
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:null;
		$user =& $request->getUser();

		if ($userId != null && $userId != $user->getId()) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId, true);
			if ($user) {
				$user->setDisabled(0);
			}
			$userDao->updateObject($user);
		}

		$request->redirect(null, null, 'people', 'all');
	}

	/**
	 * Remove a user from all roles for the current press.
	 * @param $args array the ID of the user to remove
	 * @param $request PKPRequest
	 */
	function removeUser($args, &$request) {
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:null;
		$user =& $request->getUser();
		$press =& $request->getPress();

		if ($userId != null && $userId != $user->getId()) {
			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$roleDao->deleteRoleByUserId($userId, $press->getId());
		}

		$request->redirect(null, null, 'people', 'all');
	}

	/**
	 * Save changes to a user profile.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateUser($args, &$request) {
		$this->setupTemplate(true);

		$press =& $request->getPress();
		$userId = $request->getUserVar('userId');

		if (!empty($userId) && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights
			// over this user. Display an error.
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('pageTitle', 'manager.people');
			$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
			$templateMgr->assign('backLink', $request->url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
			return $templateMgr->display('common/error.tpl');
		}

		import('classes.manager.form.UserManagementForm');

		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$userForm = new UserManagementForm($userId);
		} else {
			$userForm =& new UserManagementForm($userId);
		}

		$userForm->readInputData();

		if ($userForm->validate($args, $request)) {
			$userForm->execute($args, $request);

			if ($request->getUserVar('createAnother')) {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('currentUrl', $request->url(null, null, 'people', 'all'));
				$templateMgr->assign('userCreated', true);
				unset($userForm);
				if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
					$userForm = new UserManagementForm();
				} else {
					$userForm =& new UserManagementForm();
				}
				$userForm->initData($args, $request);
				$userForm->display($args, $request);

			} else {
				if ($source = $request->getUserVar('source')) $request->redirectUrl($source);
				else $request->redirect(null, null, 'people', 'all');
			}
		} else {
			$userForm->display($args, $request);
		}
	}

	/**
	 * Display a user's profile.
	 * @param $args array first parameter is the ID or username of the user to display
	 * @param $request PKPRequest
	 */
	function userProfile($args, &$request) {
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentUrl', $request->url(null, null, 'people', 'all'));
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
			$templateMgr->assign('backLink', $request->url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
			$templateMgr->display('common/error.tpl');

		} else {
			$site =& $request->getSite();
			$press =& $request->getPress();
			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$roles =& $roleDao->getRolesByUserId($user->getId(), $press->getId());

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
