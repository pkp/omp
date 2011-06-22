<?php

/**
 * @file controllers/listbuilder/users/StageParticipantListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding participants to a stage.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class StageParticipantListbuilderHandler extends ListbuilderHandler {
	/** @var integer The user group ID that we'll filter stage participants on **/
	var $_userGroupId;

	/**
	 * Constructor
	 */
	function StageParticipantListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetch', 'fetchRow', 'fetchOptions', 'save')
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * Get the authorized workflow stage.
	 * @return integer
	 */
	function getStageId() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
	}

	/**
	 * Set the user group id
	 * @param $userGroupId int
	 */
	function setUserGroupId($userGroupId) {
		$this->_userGroupId = $userGroupId;
	}

	/**
	 * Get the user group id
	 * @return int
	 */
	function getUserGroupId() {
		return $this->_userGroupId;
	}

	//
	// Overridden parent class functions
	//
	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->getStageId(),
			'userGroupId' => $this->getUserGroupId()
		);
	}

	/**
	 * @see GridHandler::getRowDataElement
	 * Get the data element that corresponds to the current request
	 * Allow for a blank $rowId for when creating a not-yet-persisted row
	 */
	function &getRowDataElement(&$request, $rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the newRowId
		$userId = (int) $request->getUserVar('newRowId');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUser($userId);
		return $user;
	}

	/**
	 * @see ListbuilderHandler::getOptions
	 * @params $userGroupId int A user group id to filter by (defaults to URL)
	 */
	function getOptions() {

		// Initialize the object to return
		$items = array(
			array()
		);

		// Retrieve all users that belong to the current user group
		// FIXME #6000: If user group is in the series editor role, only allow it
		// if the series editor is assigned to the monograph's series.
		$userStageAssignmentDao =& DAORegistry::getDAO('UserStageAssignmentDAO');
		$monograph =& $this->getMonograph();
		$userGroupId = $this->getUserGroupId();

		$users =& $userStageAssignmentDao->getUsersNotAssignedToStageInUserGroup(
			$monograph->getId(), $this->getStageId(), $userGroupId
		);

		while ( !$users->eof() ) {
			$user =& $users->next();
			$items[0][$user->getId()] = $user->getFullName();
			unset($user);
		}
		unset($users);

		return $items;
	}

	//
	// Implement protected template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_USER));

		// Basic configuration.
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);

		// FIXME: #6199 authorize userGroupId
		$this->setUserGroupId((int) $request->getUserVar('userGroupId'));

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'lastName', 'common.name');
		import('controllers.listbuilder.users.UserListbuilderGridCellProvider');
		$cellProvider =& new UserListbuilderGridCellProvider();
		$nameColumn->setCellProvider($cellProvider);
		$this->addColumn($nameColumn);

	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int)$request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		// Get each default user group ID, then load users by that user group ID
		$userStageAssignmentDao = & DAORegistry::getDAO('UserStageAssignmentDAO');
		$monograph = $this->getMonograph();
		$users =& $userStageAssignmentDao->getUsersBySubmissionAndStageId(
			$monograph->getId(),
			$this->getStageId(),
			$this->getUserGroupId()
		);

		return $users;
	}

	/**
	 * Persist a new entry insert.
	 * @param $entry mixed New entry with data to persist
	 * @return boolean
	 */
	function insertEntry($entry) {
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();
		$userGroupId = $this->getUserGroupId();
		$userId = (int) $entry->newRowId;

		// Create a new stage assignment.
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO');
		$stageAssignmentDao->build($monographId, $this->getStageId(), $userGroupId, $userId);
		return true;
	}

	/**
	 * Delete an entry.
	 * @param $rowId mixed ID of row to modify
	 * @return boolean
	 */
	function deleteEntry($rowId) {
		$monograph =& $this->getMonograph();
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageAssignmentDao->deleteByAll($monograph->getId(), $this->getStageId(), $this->getUserGroupId(), $rowId);

		return true;
	}
}

?>
