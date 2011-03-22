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
		$this->addRoleAssignment(array(
			ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'editUser', 'updateUser', 'updateUserRoles',
				'editDisableUser', 'disableUser', 'removeUser', 'addUser',
				'editEmail', 'sendEmail', 'suggestUsername')
		);
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

		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addUser',
				new AjaxModal(
					$router->url($request, null, null, 'addUser', null, null),
					__('grid.user.add'),
					'addUser',
					true
					),
				__('grid.user.add'),
				'addUser')
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
	// Implement methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return UserGridRow
	 */
	function &getRowInstance() {
		$row = new UserGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 * @param $request PKPRequest
	 * @return array Grid data.
	 */
	function loadData($request, $filter) {
		// Get the press
		$press =& $request->getPress();
		$pressId = $press->getId();

		// Get all users for this press that match search criteria
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$rangeInfo = $this->getRangeInfo('users');
		$users =& $userGroupDao->getUsersById(
			$filter['userGroup'],
			$pressId,
			$filter['searchField'],
			$filter['search'],
			$filter['searchMatch'],
			$rangeInfo
		);

		$rowData = array();
		while ($user =& $users->next()) {
			$rowData[$user->getId()] = $user;
		}

		return $rowData;
	}

	/**
	 * @see GridHandler::renderFilter()
	 */
	function renderFilter($request) {
		$press =& $request->getPress();
		$pressId = $press->getId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByContextId($press->getId());
		$userGroupOptions = array('' => Locale::translate('grid.user.allRoles'));
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$userGroupOptions[$userGroup->getId()] = $userGroup->getLocalizedName();
		}

		// Import PKPUserDAO to define the USER_FIELD_* constants.
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

		$filterData = array(
			'userGroupOptions' => $userGroupOptions,
			'fieldOptions' => $fieldOptions,
			'matchOptions' => $matchOptions
		);

		return parent::renderFilter($request, $filterData);
	}

	/**
	 * @see GridHandler::getFilterSelectionData()
	 * @return array Filter selection data.
	 */
	function getFilterSelectionData($request) {
		// Get the search terms
		$userGroup = $request->getUserVar('userGroup') ? (int)$request->getUserVar('userGroup') : null;
		$searchField = $request->getUserVar('searchField');
		$searchMatch = $request->getUserVar('searchMatch');
		$search = $request->getUserVar('search');

		return $filterSelectionData = array(
			'userGroup' => $userGroup,
			'searchField' => $searchField,
			'searchMatch' => $searchMatch,
			'search' => $search
		);
	}

	/**
	 * @see GridHandler::getFilterForm()
	 * @return string Filter template.
	 */
	function getFilterForm() {
		return 'controllers/grid/users/user/userGridFilter.tpl';
	}


	//
	// Public grid actions
	//
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
			$json = new JSON(false, Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserForm');
			$userForm = new UserForm($userId);
			$userForm->initData($args, $request);

			$json = new JSON(true, $userForm->display($args, $request));
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
			$json = new JSON(false, Locale::translate('grid.user.cannotAdminister'));
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
					$json = new JSON(false, $userRoleForm->display($args, $request));
				} else {
					// Successful edit of an existing user
					// Prepare the grid row data
					$row =& $this->getRowInstance();
					$row->setGridId($this->getId());
					$row->setId($user->getId());
					$row->setData($user);
					$row->initialize($request);

					$json = new JSON(true, $this->_renderRowInternally($request, $row));
				}
			} else {
				$json = new JSON(false, $userForm->display($args, $request));
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
			$json = new JSON(false, Locale::translate('grid.user.cannotAdminister'));
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

				$json = new JSON(true, $this->_renderRowInternally($request, $row));
			} else {
				$json = new JSON(false, $userForm->display($args, $request));
			}
		}
		return $json->getString();
	}

	/**
	 * Edit enable/disable user form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editDisableUser($args, &$request) {
		// Identify the press
		$press =& $request->getPress();

		// Identify the user Id
		$userId = $request->getUserVar('rowId');
		if (!$userId) $userId = $request->getUserVar('userId');

		// Are we enabling or disabling this user
		$enable = isset($args['enable']) ? (bool) $args['enable'] : false;

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights over this user.
			$json = new JSON(false, Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserDisableForm');
			$userForm = new UserDisableForm($userId, $enable);

			$userForm->initData($args, $request);

			$json = new JSON(true, $userForm->display($args, $request));
		}
		return $json->getString();
	}

	/**
	 * Enable/Disable an existing user
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function disableUser($args, &$request) {
		// Identify the press
		$press =& $request->getPress();

		// Identify the user Id
		$userId = $request->getUserVar('userId');

		// Are we enabling or disabling this user
		$enable = (bool) $request->getUserVar('enable');

		if ($userId !== null && !Validation::canAdminister($press->getId(), $userId)) {
			// We don't have administrative rights over this user.
			$json = new JSON(false, Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserDisableForm');
			$userForm = new UserDisableForm($userId, $enable);

			$userForm->readInputData();

			if ($userForm->validate()) {
				$user =& $userForm->execute($args, $request);

				// Successful enable/disable of an existing user
				// Update grid row data
				$row =& $this->getRowInstance();
				$row->setGridId($this->getId());
				$row->setId($user->getId());
				$row->setData($user);
				$row->initialize($request);

				$json = new JSON(true, $this->_renderRowInternally($request, $row));
			} else {
				$json = new JSON(false, $userForm->display($args, $request));
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
			$json = new JSON(false, Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Remove user from all user group assignments for this press
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

			// Check if this user has any user group assignments for this press
			if (!$userGroupDao->userInAnyGroup($userId, $pressId)) {
				$json = new JSON(false, Locale::translate('grid.user.userNoRoles'));
			} else {
				$userGroupDao->deleteAssignmentsByContextId($pressId, $userId);
				$json = new JSON(true);
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
			$json = new JSON(false, Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserEmailForm');
			$userEmailForm = new UserEmailForm($userId);
			$userEmailForm->initData($args, $request);

			$json = new JSON(true, $userEmailForm->display($args, $request));
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
			$json = new JSON(false, Locale::translate('grid.user.cannotAdminister'));
		} else {
			// Form handling
			import('controllers.grid.users.user.form.UserEmailForm');
			$userEmailForm = new UserEmailForm($userId);
			$userEmailForm->readInputData();

			if ($userEmailForm->validate()) {
				$userEmailForm->execute($args, $request);
				$json = new JSON(true);
			} else {
				$json = new JSON(false, $userEmailForm->display($args, $request));
			}
		}
		return $json->getString();
	}
}

?>
