<?php

/**
 * @file controllers/listbuilder/users/NotifyUsersListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotifyUsersListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding participants to a stage.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class NotifyUsersListbuilderHandler extends ListbuilderHandler {
	/**
	 * Constructor
	 */
	function NotifyUsersListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT),
			array('fetch', 'fetchRow', 'fetchOptions')
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

	//
	// Overridden parent class functions
	//
	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId()
		);
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

		// Otherwise return from the newRowId
		// FIXME: Bug #6199; user ID not validated
		$userId = (int) $this->getNewRowId($request);
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

		$userStageAssignmentDao =& DAORegistry::getDAO('UserStageAssignmentDAO');
		$monograph =& $this->getMonograph();

		$users =& $userStageAssignmentDao->getUsersBySubmissionAndStageId($monograph->getId());

		while (!$users->eof()) {
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

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');
		import('controllers.listbuilder.users.UserListbuilderGridCellProvider');
		$cellProvider =& new UserListbuilderGridCellProvider();
		$nameColumn->setCellProvider($cellProvider);
		$this->addColumn($nameColumn);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		$users = array();
		return $users;
	}

	/**
	 * Persist a new entry insert.
	 * @see Listbuilder::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();
		$userGroupId = $this->getUserGroupId();
		$userId = (int) $newRowId['name'];

		// Create a new stage assignment.
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO');
		$stageAssignmentDao->build($monographId, $this->getStageId(), $userGroupId, $userId);
		return true;
	}

	/**
	 * Delete an entry.
	 * @see Listbuilder::deleteEntry
	 */
	function deleteEntry(&$request, $rowId) {
		$userId = (int) $rowId['name']; // No validation b/c delete is specific
		$monograph =& $this->getMonograph();
		$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageAssignmentDao->deleteByAll($monograph->getId(), $this->getStageId(), $this->getUserGroupId(), $userId);

		return true;
	}
}

?>
