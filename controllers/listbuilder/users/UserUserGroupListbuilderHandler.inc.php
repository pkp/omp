<?php

/**
 * @file controllers/listbuilder/users/UserUserGroupListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserUserGroupListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class assign/remove mappings of user user groups 
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class UserUserGroupListbuilderHandler extends ListbuilderHandler {

	/* @var the user id for which to map user groups */
	var $userId;

	/**
	 * Constructor
	 */
	function UserUserGroupListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
				ROLE_ID_PRESS_MANAGER,
				array('fetch', 'addItem', 'deleteItems'));
	}

	/* Load the right-hand list */
	function loadList(&$request) {
		$items = array();

		$press =& $request->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByUserId($this->userId, $press->getId());

		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$index = $userGroup->getId();
			$items[$index] = array(
				'item' => $userGroup->getLocalizedName(),
				'attribute' => $userGroup->getLocalizedAbbrev()
			);
			unset($userGroup);
		}
		$this->setData($items);
	}

	/* Get possible items for left-hand drop-down list */
	function getPossibleItemList() {
		return $this->possibleItems;
	}

	/* Load possible items for left-hand drop-down list */
	function loadPossibleItemList(&$request) {
		$press =& $request->getPress();
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$currentGroupIds = array();

		// Get user's current user group assignments 
		$currentGroups =& $userGroupDao->getByUserId($this->userId, $press->getId());
		while (!$currentGroups->eof()) {
			$currentGroup =& $currentGroups->next();
			$currentGroupIds[] = $currentGroup->getId();
			unset($currentGroup);
		}

		// Get all available user groups for this press
		$availableGroups =& $userGroupDao->getByPressId($press->getId());

		$itemList = array();
		while (!$availableGroups->eof()) {
			$availableGroup =& $availableGroups->next();
			if ( !in_array($availableGroup->getId(), $currentGroupIds)) {
				$itemList[] = $this->_buildListItemHTML($availableGroup->getId(), $availableGroup->getLocalizedName(), $availableGroup->getLocalizedAbbrev());
			}
			unset($availableGroup);
		}

		$this->possibleItems = $itemList;
	}

	//
	// Overridden template methods
	//

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$this->userId = $request->getUserVar('userId');

		// Basic configuration
		$this->setTitle($request->getUserVar('title'));
		$this->setSourceTitle('manager.users.availableRoles');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setListTitle('manager.users.currentRoles');
		$this->setAdditionalData(array('userId' => $this->userId));

		$this->loadPossibleItemList($request);
		$this->loadList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
		$this->addColumn(new GridColumn('attribute', 'common.designation'));
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
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
			empty($userGroupId)
			|| !$userGroupDao->pressHasGroup($press->getId(), $userGroupId)
			|| $userGroupDao->userInGroup($press->getId(), $userId, $userGroupId)
		) {
			$json = new JSON('false', Locale::translate('common.listbuilder.selectValidOption'));
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

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteItems($args, &$request) {
		$press =& $request->getPress();
		$pressId = $press->getId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$userIndex = 'additionalData-' . $this->getId() . '-userId';
		$userId = (int) $args[$userIndex];
		unset($args[$userIndex]);

		foreach($args as $userGroupId) {
			$userGroupDao->removeUserFromGroup($userId, (int) $userGroupId);
		}

		$json = new JSON('true');
		return $json->getString();
	}

}
?>
