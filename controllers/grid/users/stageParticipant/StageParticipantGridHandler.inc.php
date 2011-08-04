<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantGridHandler
 * @ingroup controllers_grid_users_stageParticipant
 *
 * @brief Handle stageParticipant grid requests.
 * FIXME: The add/delete actions should not be visible to press assistants, see #6298.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');

// import stageParticipant grid specific classes
import('controllers.grid.users.stageParticipant.StageParticipantGridRow');
import('controllers.grid.users.stageParticipant.StageParticipantGridCategoryRow');

class StageParticipantGridHandler extends CategoryGridHandler {
	/**
	 * Constructor
	 */
	function StageParticipantGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_ASSISTANT, $readAccess = array('fetchGrid', 'fetchRow'));
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array_merge($readAccess, array('editStageParticipantList', 'saveStageParticipantList'))
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * Get the authorized workflow stage.
	 * @return integer
	 */
	function getStageId() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));

		// Basic grid configuration
		$this->setTitle('submission.submit.stageParticipants');

		// Columns
		import('controllers.grid.users.stageParticipant.StageParticipantGridCellProvider');
		$cellProvider = new StageParticipantGridCellProvider();
		$this->addColumn(new GridColumn(
			'participants',
			'submission.participants',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider
		));

	}


	//
	// Overridden methods from [Category]GridHandler
	//
	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData(&$userGroup) {
		// Retrieve useful objects.
		$monograph =& $this->getMonograph();

		$userStageAssignmentDao = & DAORegistry::getDAO('UserStageAssignmentDAO');
		$returner =& $userStageAssignmentDao->getUsersBySubmissionAndStageId(
			$monograph->getId(),
			$this->getStageId(),
			$userGroup->getId()
		);
		$returner = $returner->toArray();

		return $returner;
	}

	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		$monograph =& $this->getMonograph();
		$row = new StageParticipantGridRow($monograph, $this->getStageId());
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 */
	function &getCategoryRowInstance() {
		$monograph =& $this->getMonograph();
		$row = new StageParticipantGridCategoryRow($monograph, $this->getStageId());
		return $row;
	}

	/**
	 * @see GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->getStageId()
		);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		$userGroupDao = & DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$press =& $request->getPress();
		$userGroups =& $userGroupDao->getUserGroupsByStage($press->getId(), $this->getStageId(), false, true);

		return $userGroups;
	}


	//
	// Public actions
	//
	/**
	 * An action to manually edit the stage participants
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editStageParticipantList($args, &$request) {
		$templateMgr =& TemplateManager::getManager();

		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		// Assign the stage id to the template.
		$templateMgr->assign('stageId', $this->getStageId());

		// Get and validate the user group ID.
		$userGroupId = (int) $request->getUserVar('userGroupId');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroup =& $userGroupDao->getById($userGroupId, $monograph->getPressId());
		if (!$userGroup) fatalError('Invalid userGroupId.');

		$templateMgr->assign('userGroupId', $userGroup->getId());
		return $templateMgr->fetchJson('controllers/grid/users/stageParticipant/editStageParticipantList.tpl');
	}


	/**
	 * Update the row for the current userGroup's stage participant list.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveStageParticipantList($args, &$request) {
		// Get and validate the user group ID.
		$userGroupId = (int) $request->getUserVar('userGroupId');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$monograph =& $this->getMonograph();
		$userGroup =& $userGroupDao->getById($userGroupId, $monograph->getPressId());
		if (!$userGroup) fatalError('Invalid userGroupId.');

		return DAO::getDataChangedEvent($userGroupId);
	}
}

?>
