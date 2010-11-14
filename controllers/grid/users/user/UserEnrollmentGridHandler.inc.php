<?php

/**
 * @file controllers/grid/users/user/UserEnrollmentGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserEnrollmentGridHandler
 * @ingroup controllers_grid_users_user
 *
 * @brief Handle user enrollment grid requests.
 */

import('controllers.grid.users.user.UserGridHandler');
import('controllers.grid.users.user.UserEnrollmentGridRow');

class UserEnrollmentGridHandler extends UserGridHandler {

	/**
	 * Constructor
	 */
	function UserEnrollmentGridHandler() {
		parent::UserGridHandler();

		$this->addRoleAssignment(
			array(
				ROLE_ID_PRESS_MANAGER
			),
			array(
				'enrollUser',
				'enrollUserFinish'
			)
		);
	}


	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Initialize the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration
		$this->setTitle('grid.user.currentEnrollment');

		// Grid actions
		$router =& $request->getRouter();
		$press =& $request->getPress();

		// Enroll user
		$this->addAction(
			new LinkAction(
				'enrollUser',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_REDIRECT,
				$router->url($request, null, null, 'enrollUser', null, null),
				'grid.user.enroll'
			)
		);

		// Grid Columns

		// User roles
		import('controllers.grid.users.user.UserEnrollmentGridCellProvider');
		$cellProvider = new UserEnrollmentGridCellProvider($press->getId());
		$this->addColumn(
			new GridColumn(
				'roles',
				'user.roles',
				null,
				'controllers/grid/users/user/userGroupsList.tpl',
				$cellProvider
			)
		);
	}

	//
	// Overridden methods from GridHandler
	//

	/**
	 * @see GridHandler::getRowInstance()
	 * @return UserGridRow
	 */
	function &getRowInstance() {
		$row = new UserEnrollmentGridRow();
		return $row;
	}

	//
	// Public User Grid Actions
	//

	/*
	 * List all site users based on optional search criteria
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetchGrid($args, &$request) {
		// Get the search terms
		$searchField = $request->getUserVar('searchField');
		$searchMatch = $request->getUserVar('searchMatch');
		$search = $request->getUserVar('search');

		// Get all users for this site that match search criteria
		$userDao =& DAORegistry::getDAO('UserDAO');
		$rangeInfo = $this->getRangeInfo('users');
		$users =& $userDao->getUsersByField(
			$searchField,
			$searchMatch,
			$search,
			true,
			$rangeInfo
		);

		$rowData = array();
		while ($user =& $users->next()) {
			$rowData[$user->getId()] = $user;
		}
		$this->setData($rowData);

		return GridHandler::fetchGrid($args, $request);
	}

	/**
	 * Enroll a user
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function enrollUser($args, &$request) {
		// Identify the press
		$press =& $request->getPress();

		// Form handling
		import('controllers.grid.users.user.form.UserEnrollmentForm');
		$userEnrollmentForm = new UserEnrollmentForm();
		$userEnrollmentForm->initData($args, $request);

		$json = new JSON('true', $userEnrollmentForm->display($args, $request));
		return $json->getString();
	}

	/**
	 * Finish enrolling users
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function enrollUserFinish($args, &$request) {
		// Identify the user Id
		$userId = $request->getUserVar('userId');

		// If editing a user, save changes
		if ($userId) {
			$this->updateUser($args, $request);
		}

		$json = new JSON('true');
		return $json->getString();
	}

	/**
	 * Remove all user group assignments for a press for a given user
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function removeUser($args, &$request) {
		// Identify the press
		$press =& $request->getPress();
		$pressId = $press->getId();

		// Identify the user Id
		$userId = $request->getUserVar('rowId');

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights over this user.
			$json = new JSON('false', Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Remove user from all user group assignments for this press
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

			// Check if this user has any user group assignments for this press
			if (!$userGroupDao->userInAnyGroup($userId, $pressId)) {
				$json = new JSON('false', Locale::translate('grid.user.userNoRoles'));
			} else {
				$userGroupDao->deleteAssignmentsByPressId($pressId, $userId);

				// Successfully removed user's user group assignments
				// Refresh the grid row data to indicate this
				$userDao =& DAORegistry::getDAO('UserDAO');
				$user =& $userDao->getUser($userId);

				$row =& $this->getRowInstance();
				$row->setGridId($this->getId());
				$row->setId($user->getId());
				$row->setData($user);
				$row->initialize($request);

				$json = new JSON('true', $this->_renderRowInternally($request, $row));
			}
		}
		return $json->getString();
	}
}