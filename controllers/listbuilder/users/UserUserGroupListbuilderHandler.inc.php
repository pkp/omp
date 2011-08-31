<?php

/**
 * @file controllers/listbuilder/users/UserUserGroupListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserUserGroupListbuilderHandler
 * @ingroup controllers_listbuilder_users
 *
 * @brief Class assign/remove mappings of user user groups
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class UserUserGroupListbuilderHandler extends ListbuilderHandler {
	/** @var integer the user id for which to map user groups */
	var $_userId;

	/** @var $press Press */
	var $_press;


	/**
	 * Constructor
	 */
	function UserUserGroupListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('fetch', 'fetchRow', 'fetchOptions', 'save')
		);
	}


	//
	// Setters and Getters
	//
	/**
	 * Set the user id
	 * @param $userId integer
	 */
	function setUserId($userId) {
		$this->_userId = $userId;
	}


	/**
	 * Get the user id
	 * @return integer
	 */
	function getUserId() {
		return $this->_userId;
	}


	/**
	 * Set the press
	 * @param $press Press
	 */
	function setPress(&$press) {
		$this->_press =& $press;
	}


	/**
	 * Get the press
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}


	//
	// Overridden parent class functions
	//
	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		return array(
			'userId' => $this->getUserId()
		);
	}


	/**
	 * @see ListbuilderHandler::getOptions
	 * @param $includeDesignations boolean
	 */
	function getOptions($includeDesignations = false) {
		// Initialize the object to return
		$items = array(
			array(), // Names
			array() // Designations
		);

		// Fetch the user groups
		$press =& $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByContextId($press->getId());

		// Assemble the array to return
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$userGroupId = $userGroup->getId();

			$items[0][$userGroupId] = $userGroup->getLocalizedName();
			if ($includeDesignations) {
				$items[1][$userGroupId] = $userGroup->getLocalizedAbbrev();
			}

			unset($userGroup);
		}

		return $items;
	}


	/**
	 * Initialize the grid with the currently selected set of user groups.
	 */
	function loadData() {
		$press =& $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByUserId($this->getUserId(), $press->getId());

		return $userGroups;
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
		// FIXME Bug #6199
		$this->setUserId((int) $request->getUserVar('userId'));

		$this->setPress($request->getPress());
		parent::initialize($request);

		// Basic configuration
		$this->setTitle($request->getUserVar('title'));
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);

		import('controllers.listbuilder.users.UserGroupListbuilderGridCellProvider');
		$cellProvider = new UserGroupListbuilderGridCellProvider();

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');
		$nameColumn->setCellProvider($cellProvider);
		$this->addColumn($nameColumn);

		// Designation column
		$designationColumn = new ListbuilderGridColumn($this,
			'designation',
			'common.designation',
			null,
			'controllers/listbuilder/listbuilderNonEditGridCell.tpl'
		);
		$designationColumn->setCellProvider($cellProvider);
		$this->addColumn($designationColumn);
	}

	/**
	 * @see GridHandler::getRowDataElement
	 * Get the data element that corresponds to the current request
	 * Allow for a blank $rowId for when creating a not-yet-persisted row
	 */
	function getRowDataElement(&$request, $rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the $newRowId
		$newRowId = $this->getNewRowId($request);
		$userGroupId = $newRowId['name'];
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press =& $this->getPress();
		$userGroup =& $userGroupDao->getById($userGroupId, $press->getId());
		return $userGroup;
	}

	/**
	 * Persist a new entry insert.
	 * @see Listbuilder::insertentry
	 */
	function insertEntry(&$request, $newRowId) {
		$press =& $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$userGroupId = (int) $newRowId['name'];
		$userId = (int) $this->getUserId();

		// Ensure that:
		// $userGroupId is not empty
		// $userGroupId is valid for this press
		// user group assignment does not already exist
		if (
			empty($userGroupId) ||
			!$userGroupDao->contextHasGroup($press->getId(), $userGroupId) ||
			$userGroupDao->userInGroup($userId, $userGroupId)
		) {
			return false;
		} else {
			// Add the assignment
			$userGroupDao->assignUserToGroup($userId, $userGroupId);
		}

		return true;
	}

	/**
	 * Delete an entry.
	 * @see Listbuilder::deleteEntry
	 */
	function deleteEntry(&$request, $rowId) {
		$userGroupId = (int) $rowId['name'];
		$userId = (int) $this->getUserId();

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press =& $this->getPress();

		$userGroupDao->removeUserFromGroup(
			$userId,
			(int) $userGroupId,
			$press->getId()
		);

		return true;
	}
}

?>
