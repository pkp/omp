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

	/**
	 * Constructor
	 */
	function UserUserGroupListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('fetch', 'addItem', 'deleteItems')
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

		$userId = (int)$request->getUserVar('userId');
		$this->setUserId($userId);

		// Basic configuration
		$this->setTitle($request->getUserVar('title'));
		$this->setSourceTitle('manager.users.availableRoles');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setListTitle('manager.users.currentRoles');
		$this->setAdditionalData(array('userId' => $userId));

		$this->_loadPossibleItemList($request);
		$this->_loadList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
		$this->addColumn(new GridColumn('attribute', 'common.designation'));
	}


	//
	// Public grid actions
	//
	/*
	 * Handle adding an item to the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addItem($args, &$request) {
		$this->setupTemplate();
		$press =& $request->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$userGroupIndex = 'selectList-' . $this->getId();
		$userIndex = 'additionalData-' . $this->getId() . '-userId';
		$userGroupId = (int) $args[$userGroupIndex];
		$userId = (int) $args[$userIndex];

		// Ensure that:
		// $userGroupId is not empty
		// $userGroupId is valid for this press
		// user group assignment does not already exist
		if (
			empty($userGroupId) ||
			!$userGroupDao->contextHasGroup($press->getId(), $userGroupId) ||
			$userGroupDao->userInGroup($press->getId(), $userId, $userGroupId)
		) {
			$json = new JSON(false, Locale::translate('common.listbuilder.selectValidOption'));
			return $json->getString();
		} else {
			// Add the assignment
			$userGroupDao->assignUserToGroup($userId, $userGroupId);

			// Return JSON with formatted HTML to insert into list
			$userGroup =& $userGroupDao->getById($userGroupId);

			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($userGroupId);
			$rowData = array('item' => $userGroup->getLocalizedName(), 'attribute' => $userGroup->getLocalizedAbbrev());
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON(true, $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	}

	/**
	 * Handle deleting items from the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteItems($args, &$request) {
		$press =& $request->getPress();
		$pressId = $press->getId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$userIndex = 'additionalData-' . $this->getId() . '-userId';
		$userId = (int) $args[$userIndex];
		// FIXME: authorize userId before deleting it!

		foreach($args as $userGroupId) {
			$userGroupDao->removeUserFromGroup($userId, (int) $userGroupId);
		}

		$json = new JSON(true);
		return $json->getString();
	}


	//
	// Private helper methods
	//
	/**
	 * Load the right-hand list
	 * @param $request Request
	 */
	function _loadList(&$request) {
		$items = array();

		$press =& $request->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByUserId($this->getUserId(), $press->getId());

		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$index = $userGroup->getId();
			$items[$index] = array(
				'item' => $userGroup->getLocalizedName(),
				'attribute' => $userGroup->getLocalizedAbbrev()
			);
			unset($userGroup);
		}
		$this->setGridDataElements($items);
	}

	/**
	 * Load possible items for
	 * left-hand drop-down list
	 * @param $request Request
	 */
	function _loadPossibleItemList(&$request) {
		$press =& $request->getPress();
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$currentGroupIds = array();

		// Get user's current user group assignments
		$currentGroups =& $userGroupDao->getByUserId($this->getUserId(), $press->getId());
		while (!$currentGroups->eof()) {
			$currentGroup =& $currentGroups->next();
			$currentGroupIds[] = $currentGroup->getId();
			unset($currentGroup);
		}

		// Get all available user groups for this press
		$availableGroups =& $userGroupDao->getByContextId($press->getId());

		$itemList = array();
		while (!$availableGroups->eof()) {
			$availableGroup =& $availableGroups->next();
			if ( !in_array($availableGroup->getId(), $currentGroupIds)) {
				$itemList[$availableGroup->getId()] = $availableGroup->getLocalizedName().' ('.$availableGroup->getLocalizedAbbrev().')';
			}
			unset($availableGroup);
		}
		$this->setPossibleItemList($itemList);
	}
}
?>
