<?php

/**
 * @file controllers/grid/users/user/UserGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGridHandler
 * @ingroup controllers_grid_users_user
 *
 * @brief Handle user grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

import('controllers.grid.users.user.UserGridRow');


class UserGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function UserGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'editUser', 'updateUser', 'updateUserRoles',
						'removeUser', 'addUser', 'editEmail', 'sendEmail',
						'suggestUsername'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load user-related translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_USER));

		// Basic grid configuration
		$this->setTitle('grid.user.currentUsers');

		// Grid actions
		$router =& $request->getRouter();

		$this->addAction(
			new LinkAction(
				'addUser',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addUser', null, null),
				'grid.user.add'
			)
		);

		//
		// Grid columns
		//

		// First Name
		$cellProvider = new DataObjectGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'firstName',
				'user.firstName',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		// Last Name
		$cellProvider = new DataObjectGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'lastName',
				'user.lastName',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		// Email
		$cellProvider = new DataObjectGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'email',
				'user.email',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Implement template methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return UserGridRow
	 */
	function &getRowInstance() {
		$row = new UserGridRow();
		return $row;
	}


	//
	// Public grid actions
	//
	/*
	 * List users based on optional search criteria
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetchGrid($args, &$request) {
		// Get the press
		$press =& $request->getPress();
		$pressId = $press->getId();

		// Get the search terms
		$searchField = $request->getUserVar('searchField');
		$searchMatch = $request->getUserVar('searchMatch');
		$search = $request->getUserVar('search');

		// Get all users for this press that match search criteria
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$rangeInfo = $this->getRangeInfo('users');
		$users =& $userGroupDao->getUsersByPressId(
			$pressId,
			$searchField,
			$search,
			$searchMatch,
			$rangeInfo
		);

		$rowData = array();
		while ($user =& $users->next()) {
			$rowData[$user->getId()] = $user;
		}
		$this->setData($rowData);

		return parent::fetchGrid($args, $request);
	}

	/**
	 * Get a suggested username, making sure it's not
	 * already used by the system. (Poor-man's AJAX.)
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function suggestUsername($args, &$request) {
		$suggestion = Validation::suggestUsername(
			$request->getUserVar('firstName'),
			$request->getUserVar('lastName')
		);
		echo $suggestion;
	}

	/**
	 * Add a new user
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addUser($args, &$request) {
		// Calling editUser with an empty row id will add a new user.
		return $this->editUser($args, $request);
	}

	/**
	 * Edit an existing user
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editUser($args, &$request) {
		// Identify the press
		$press =& $request->getPress();

		// Identify the user Id
		$userId = $request->getUserVar('rowId');
		if (!$userId) $userId = $request->getUserVar('userId');

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights over this user.
			$json = new JSON('false', Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserForm');
			$userForm = new UserForm($userId);
			$userForm->initData($args, $request);

			$json = new JSON('true', $userForm->display($args, $request));
		}
		return $json->getString();
	}

	/**
	 * Update an existing user
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateUser($args, &$request) {
		// Identify the press
		$press =& $request->getPress();

		// Identify the user Id
		$userId = $request->getUserVar('userId');

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights over this user.
			$json = new JSON('false', Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserForm');
			$userForm = new UserForm($userId);
			$userForm->readInputData();

			if ($userForm->validate()) {
				$user =& $userForm->execute($args, $request);

				// If this is a newly created user, show role management form
				if (!$userId) {
					import('controllers.grid.users.user.form.UserRoleForm');
					$userRoleForm = new UserRoleForm($user->getId());
					$userRoleForm->initData($args, $request);
					$json = new JSON('false', $userRoleForm->display($args, $request));
				} else {
					// Successful edit of an existing user
					// Prepare the grid row data
					$row =& $this->getRowInstance();
					$row->setGridId($this->getId());
					$row->setId($user->getId());
					$row->setData($user);
					$row->initialize($request);

					$json = new JSON('true', $this->_renderRowInternally($request, $row));
				}
			} else {
				$json = new JSON('false', $userForm->display($args, $request));
			}
		}
		return $json->getString();
	}

	/**
	 * Update a newly created user's roles
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateUserRoles($args, &$request) {
		// Identify the press
		$press =& $request->getPress();

		// Identify the user Id
		$userId = $request->getUserVar('userId');

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights over this user.
			$json = new JSON('false', Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserRoleForm');
			$userRoleForm = new UserRoleForm($userId);
			$userRoleForm->readInputData();

			if ($userRoleForm->validate()) {
				$user =& $userRoleForm->execute($args, $request);

				// Successfully managed newly created user's roles
				// Prepare the grid row data
				$row =& $this->getRowInstance();
				$row->setGridId($this->getId());
				$row->setId($user->getId());
				$row->setData($user);
				$row->initialize($request);

				$json = new JSON('true', $this->_renderRowInternally($request, $row));
			} else {
				$json = new JSON('false', $userForm->display($args, $request));
			}
		}
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
				$json = new JSON('true');
			}
		}
		return $json->getString();
	}

	/**
	 * Displays a modal to edit an email message to the user
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editEmail($args, &$request) {
		// Identify the press
		$press =& $request->getPress();

		// Identify the user Id
		$userId = $request->getUserVar('rowId');

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights over this user.
			$json = new JSON('false', Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserEmailForm');
			$userEmailForm = new UserEmailForm($userId);
			$userEmailForm->initData($args, $request);

			$json = new JSON('true', $userEmailForm->display($args, $request));
		}
		return $json->getString();
	}

	/**
	 * Send the user email and close the modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendEmail($args, &$request) {
		// Identify the press
		$press =& $request->getPress();

		// Identify the user Id
		$userId = $request->getUserVar('userId');

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights over this user.
			$json = new JSON('false', Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserEmailForm');
			$userEmailForm = new UserEmailForm($userId);
			$userEmailForm->readInputData();

			if ($userEmailForm->validate()) {
				$userEmailForm->execute($args, $request);
				$json = new JSON('true');
			} else {
				$json = new JSON('false', $userEmailForm->display($args, $request));
			}
		}
		return $json->getString();
	}
}