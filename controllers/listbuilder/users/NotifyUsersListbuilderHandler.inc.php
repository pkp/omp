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
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_AUTHOR),
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

	function getStageId() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
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
			'stageId' => $this->getStageId()
		);
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
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_USER);

		// Basic configuration.
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('users');

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

	//
	// Implement methods from ListbuilderHandler
	//
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
		// FIXME: Validate user ID?
		$newRowId = $this->getNewRowId($request);
		$userId = (int) $newRowId['name'];
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

		// FIXME: add stage id?
		$users =& $userStageAssignmentDao->getUsersBySubmissionAndStageId($monograph->getId());

		while ($user =& $users->next()) {
			$items[0][$user->getId()] = $user->getFullName();
			unset($user);
		}
		unset($users);

		return $items;
	}
}

?>
