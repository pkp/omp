<?php

/**
 * @file controllers/listbuilder/settings/UserGroupStageAssignmentListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupStageAssignmentListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for assigning/removing mappings of user groups to publication stages
 */

//import publication stage id constants
import('controllers.listbuilder.settings.SetupListbuilderHandler');

class UserGroupStageAssignmentListbuilderHandler extends SetupListbuilderHandler {
	/* @var the submission stage being assigned to/from */
	var $stageId;

	/* @var the role id that can be used for this stage */
	var $roleId;

	/**
	 * Constructor
	 */
	function UserGroupStageAssignmentListbuilderHandler() {
		parent::SetupListbuilderHandler();
	}

	/* Load the list from an external source into the listbuilder structure */
	function loadList(&$request) {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$userGroupStageAssignmentDao =& DAORegistry::getDAO('UserGroupStageAssignmentDAO');

		$press =& $request->getPress();

		// Get items to populate listBuilder current item list
		$userGroups =& $userGroupStageAssignmentDao->getUserGroupsByStage($press->getId(), $this->stageId);

		$items = array();
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$items[$userGroup->getId()] = array('item' => $userGroup->getLocalizedName(), 'attribute' => $userGroup->getLocalizedAbbrev());
			unset($userGroup);
		}
		$this->setGridDataElements($items);
	}

	/* Get possible items to populate drop-down list with */
	function getPossibleItemList() {
		return $this->possibleItems;
	}

	/* Load possible items to populate drop-down list with */
	function loadPossibleItemList(&$request) {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroupStageAssignmentDao =& DAORegistry::getDAO('UserGroupStageAssignmentDAO');

		$press =& $request->getPress();

		// Get items to populate possible items list with
		$currentGroups =& $userGroupStageAssignmentDao->getUserGroupsByStage($press->getId(), $this->stageId);
		$currentGroupIds = array();
		while (!$currentGroups->eof()) {
			$currentGroup =& $currentGroups->next();
			$currentGroupIds[] = $currentGroup->getId();
			unset($currentGroup);
		}

		// all available groups
		$availableGroups =& $userGroupDao->getByRoleId($press->getId(), $this->roleId);

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

		$this->roleId = $request->getUserVar('roleId');
		$this->stageId = $request->getUserVar('stageId');

		// Basic configuration
		$this->setTitle($request->getUserVar('title'));
		$this->setSourceTitle('manager.setup.availableRoles');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); //Select from drop-down list
		$this->setListTitle('manager.setup.currentRoles');

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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addItem($args, &$request) {
		$this->setupTemplate();
		$userGroupStageAssignmentDao =& DAORegistry::getDAO('UserGroupStageAssignmentDAO');
		$press =& $request->getPress();

		$index = 'selectList-' . $this->getId();
		$userGroupId = (int) $args[$index];

		// check if $userGroupId is empty or if the assignment already exists
		if(empty($userGroupId) || $userGroupStageAssignmentDao->assignmentExists($press->getId(), $userGroupId, $this->stageId)) {
			$json = new JSON(false, Locale::translate('common.listbuilder.selectValidOption'));
			return $json->getString();
		} else {
			// Insert the assignment
			$userGroupStageAssignmentDao->assignGroupToStage($press->getId(), $userGroupId, $this->stageId);

			// Return JSON with formatted HTML to insert into list
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
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

	/*
	 * Handle deleting items from the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteItems($args, &$request) {
		$userGroupStageAssignmentDao =& DAORegistry::getDAO('UserGroupStageAssignmentDAO');

		$press =& $request->getPress();
		$pressId = $press->getId();
		foreach($args as $userGroupId) {
			$userGroupStageAssignmentDao->removeGroupFromStage($pressId, $userGroupId, $this->stageId);
		}

		$json = new JSON(true);
		return $json->getString();
	}

}
?>
