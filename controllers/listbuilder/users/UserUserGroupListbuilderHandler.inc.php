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
	 * @see ListbuilderHandler::fetch()
	 */
	function fetch($args, &$request) {
		$router =& $request->getRouter();

		$monographId = $request->getUserVar('monographId');
		$chapterId = $request->getUserVar('chapterId');
		$additionalVars = array(
			'addUrl' => $router->url($request, array(), null, 'addItem', null, array('monographId' => $monographId, 'chapterId' => $chapterId)),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, array('monographId' => $monographId, 'chapterId' => $chapterId))
		);

		return parent::fetch($args, &$request, $additionalVars);
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
	function loadList() {
		$items = array();

		$press =& $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByUserId($this->getUserId(), $press->getId());

		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$index = $userGroup->getId();
			$items[$index] = array(
				'name' => $userGroup->getLocalizedName(),
				'designation' => $userGroup->getLocalizedAbbrev()
			);
			unset($userGroup);
		}
		$this->setGridDataElements($items);
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
		$this->setUserId((int) $request->getUserVar('userId'));
		$this->setPress($request->getPress());

		parent::initialize($request);

		// Load the listbuilder contents
		$this->loadList();

		// Basic configuration
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->addColumn(new ListbuilderGridColumn($this, 'name', 'common.name'));
		$this->addColumn(new ListbuilderGridColumn($this, 'designation', 'common.designation'));
	}


	/**
	 * Create a new data element from a request. This is used to format
	 * new rows prior to their insertion.
	 * @param $request PKPRequest
	 * @param $elementId int
	 * @return object
	 */
	function &getDataElementFromRequest(&$request, &$elementId) {
		$options = $this->getOptions(true);
		$nameIndex = $request->getUserVar('name');
		assert($nameIndex == '' || isset($options[0][$nameIndex]));
		$newItem = array(
			'name' => $nameIndex == ''?'':$options[0][$nameIndex],
			'designation' => $nameIndex == ''?'':$options[1][$nameIndex],
			'id' => $nameIndex
		);
		$elementId = $request->getUserVar('rowId');
		return $newItem;
	}


	/**
	 * Persist a new entry insert.
	 * @param $entry mixed New entry with data to persist
	 * @return boolean
	 */
	function insertEntry($entry) {
		$press =& $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$userGroupId = (int) $entry->name;
		$userId = (int) $this->getUserId();

		// Ensure that:
		// $userGroupId is not empty
		// $userGroupId is valid for this press
		// user group assignment does not already exist
		if (
			empty($userGroupId) ||
			!$userGroupDao->contextHasGroup($press->getId(), $userGroupId) ||
			$userGroupDao->userInGroup($press->getId(), $userId, $userGroupId)
		) {
			print_r($_POST);
			die (!$userGroupDao->contextHasGroup($press->getId(), $userGroupId)?'true':'false');
			return false;
		} else {
			// Add the assignment
			$userGroupDao->assignUserToGroup($userId, $userGroupId);
		}

		return true;
	}

	/**             
         * Delete an entry.
         * @param $rowId mixed ID of row to modify
         * @return boolean
         */
	function deleteEntry($rowId) {
		$userGroupId = (int) $rowId;
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
