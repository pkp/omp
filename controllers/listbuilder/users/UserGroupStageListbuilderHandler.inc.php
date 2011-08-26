<?php

/**
 * @file controllers/listbuilder/users/UserGroupStageListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupStageListbuilderHandler
 * @ingroup controllers_listbuilder_users
 *
 * @brief Class assign/remove mappings of user group stages
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class UserGroupStageListbuilderHandler extends ListbuilderHandler {

	/** @var integer the user group id for which to map stages */
	var $_userGroupId;

	/** @var $press Press */
	var $_press;


	/**
	 * Constructor
	 */
	function UserGroupStageListbuilderHandler() {
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
	 * Set the user group id
	 * @param $userGroupId integer
	 */
	function setUserGroupId($userGroupId) {
		$this->_userGroupId = $userGroupId;
	}


	/**
	 * Get the user group id
	 * @return integer
	 */
	function getUserGroupId() {
		return $this->_userGroupId;
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
			'userGroupId' => $this->getUserGroupId()
		);
	}


	/**
	 * @see ListbuilderHandler::getOptions
	 */
	function getOptions() {
		// Initialize the object to return
		$items = array(
			array() // Stages
		);

		// Fetch the stages
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$stages = $userGroupDao->getWorkflowStageTranslationKeys();

		// Assemble the array to return
		foreach ($stages as $id => $translationKey) {
			$items[0][$id] = Locale::translate($translationKey);
		}

		return $items;
	}


	/**
	 * Initialize the grid with the currently selected set of user groups.
	 */
	function loadData() {
		$press =& $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$assignedStages =& $userGroupDao->getAssignedStagesByUserGroupId($press->getId(), $this->getUserGroupId());

		return $assignedStages;
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
		$this->setPress($request->getPress());

		$userGroupId = ((int) $request->getUserVar('userGroupId'));
		$press = $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		if ($userGroupId != 0) {
			// Ensure that $userGroupId is valid for this press
			if (!$userGroupDao->contextHasGroup($press->getId(), $userGroupId)) {
				fatalError('Invalid user group id!');
			} else {
				$this->setUserGroupId($userGroupId);
			}
		}

		Locale::requireComponents(array(
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_OMP_MANAGER)
		);

		parent::initialize($request);

		// Basic configuration
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('stages');
		$this->setTitle($request->getUserVar('title'));
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);

		// Stage column
		import('controllers.listbuilder.users.StageListbuilderGridCellProvider');
		$cellProvider =& new StageListbuilderGridCellProvider();

		$stageColumn = new ListbuilderGridColumn($this, 'stage', 'common.stage');
		$stageColumn->setCellProvider($cellProvider);
		$this->addColumn($stageColumn);
	}

	/**
	 * @see GridHandler::getRowDataElement
	 * Get the data element that corresponds to the current request
	 * Allow for a blank $rowId for when creating a not-yet-persisted row
	 */
	function getRowDataElement(&$request, $rowId) {
		$press = $this->getPress();
		$userGroupId = $this->getUserGroupId();

		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the $newRowId
		$stageId = (int) $this->getNewRowId($request);
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$stages =& $userGroupDao->getWorkflowStageTranslationKeys();

		return $stages[$stageId];
	}
}

?>
