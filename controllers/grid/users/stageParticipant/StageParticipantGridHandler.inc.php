<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantGridHandler
 * @ingroup controllers_grid_users_stageParticipant
 *
 * @brief Handle stageParticipant grid requests.
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
		// Press Assistants get read-only access
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_ASSISTANT),
			$peOps = array('fetchGrid', 'fetchRow')
		);
		// Managers and Editors additionally get administrative access
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR),
			array_merge($peOps, array('addParticipant', 'deleteParticipant', 'saveParticipant', 'userAutocomplete'))
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
	 * Determine whether the current user has admin priveleges for this
	 * grid.
	 * @return boolean
	 */
	function _canAdminister() {
		// If the current role set includes Manager or Editor, grant.
		return (boolean) array_intersect(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR),
			$this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES)
		);
	}


	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_EDITOR,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		// Basic grid configuration
		$this->setTitle('editor.monograph.stageParticipants');

		// Columns
		import('controllers.grid.users.stageParticipant.StageParticipantGridCellProvider');
		$cellProvider = new StageParticipantGridCellProvider();
		$this->addColumn(new GridColumn(
			'participants',
			'editor.monograph.participant',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider
		));

		// The "Add stage participant" grid action is available to
		// Editors and Managers only
		if ($this->_canAdminister()) {
			$router =& $request->getRouter();
			$this->addAction(
				new LinkAction(
					'requestAccount',
					new AjaxModal(
						$router->url($request, null, null, 'addParticipant', null, $this->getRequestArgs()),
						__('editor.monograph.addStageParticipant'),
						'addUser'
					),
					__('editor.monograph.addStageParticipant'),
					'add_user'
				)
			);
		}

		$this->setEmptyRowText('editor.monograph.noneAssigned');
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
		$stageId = $this->getStageId();

		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageAssignments =& $stageAssignmentDao->getBySubmissionAndStageId(
			$monograph->getId(),
			$stageId,
			$userGroup->getId()
		);

		return $stageAssignments->toAssociativeArray();
	}

	/**
	 * @see GridHandler::isSubComponent()
	 */
	function getIsSubcomponent() {
		return true;
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
	 * Add a participant to the stages
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addParticipant($args, &$request) {
		$monograph =& $this->getMonograph();
		$stageId = $this->getStageId();
		$userGroups =& $this->getGridDataElements($request);

		import('controllers.grid.users.stageParticipant.form.AddParticipantForm');
		$form = new AddParticipantForm($monograph, $stageId, $userGroups);
		$form->initData();

		$json = new JSONMessage(true, $form->fetch($request));
		return $json->getString();
	}


	/**
	 * Update the row for the current userGroup's stage participant list.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveParticipant($args, &$request) {
		$monograph =& $this->getMonograph();
		$stageId = $this->getStageId();
		$userGroups =& $this->getGridDataElements($request);

		import('controllers.grid.users.stageParticipant.form.AddParticipantForm');
		$form = new AddParticipantForm($monograph, $stageId, $userGroups);
		$form->readInputData();
		if ($form->validate()) {
			$userGroupId = $form->execute();

			$notificationMgr = new NotificationManager();

			// Check user group role id.
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroup = $userGroupDao->getById($userGroupId);
			if ($userGroup->getRoleId() == ROLE_ID_PRESS_MANAGER) {
				// Update NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_... for each stage.
				$stages = $this->_getStages();
				foreach ($stages as $workingStageId) {
					$notificationMgr->updateEditorAssignmentNotification($monograph, $workingStageId, $request);
				}
			}

			// Create trivial notification.
			$user =& $request->getUser();
			$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.addedStageParticipant')));

			return DAO::getDataChangedEvent($userGroupId);
		} else {
			$json = new JSONMessage(true, $form->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete the participant from the user groups
	 * @param $args
	 * @param $request
	 * @return void
	 */
	function deleteParticipant($args, &$request) {
		$monograph =& $this->getMonograph();
		$stageId = $this->getStageId();
		$assignmentId = (int) $request->getUserVar('assignmentId');

		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$stageAssignment =& $stageAssignmentDao->getById($assignmentId);
		if (!$stageAssignment || $stageAssignment->getSubmissionId() != $monograph->getId()) {
			fatalError('Invalid Assignment');
		}

		// Delete all user monograph file signoffs not completed, if any.
		$userId = $stageAssignment->getUserId();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');

		$signoffsFactory =& $signoffDao->getByUserId($userId);
		while($signoff =& $signoffsFactory->next()) {
			if (($signoff->getSymbolic() != 'SIGNOFF_COPYEDITING' &&
				$signoff->getSymbolic() != 'SIGNOFF_PROOFING') ||
				$signoff->getAssocType() != ASSOC_TYPE_MONOGRAPH_FILE ||
				$signoff->getDateCompleted()) continue;
			$monographFileId = $signoff->getAssocId();
			$monographFile =& $submissionFileDao->getLatestRevision($monographFileId, null, $stageAssignment->getSubmissionId());
			if (is_a($monographFile, 'MonographFile')) {
				$signoffDao->deleteObject($signoff);
			}
		}

		// Delete the assignment
		$stageAssignmentDao->deleteObject($stageAssignment);

		// FIXME: perhaps we can just insert the notification on page load
		// instead of having it there all the time?
		$notificationMgr = new NotificationManager();
		$stages = $this->_getStages();
		foreach ($stages as $workingStageId) {
			// remove user's assignment from this user group from all the stages
			// (no need to check if user group is assigned, since nothing will be deleted if there isn't)
			$stageAssignmentDao->deleteByAll($monograph->getId(), $workingStageId, $stageAssignment->getUserGroupId(), $stageAssignment->getUserId());

			// Update NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_...
			$notificationMgr->updateEditorAssignmentNotification($monograph, $workingStageId, $request);
		}

		// Redraw the category
		return DAO::getDataChangedEvent($stageAssignment->getUserGroupId());
	}

	/**
	 * Get the list of users for the specified user group
	 * @param $args array
	 * @param $request Request
	 * @return JSON string
	 */
	function userAutocomplete($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		$userGroupId = (int) $request->getUserVar('userGroupId');

		$userStageAssignmentDao =& DAORegistry::getDAO('UserStageAssignmentDAO'); /* @var $userStageAssignmentDao UserStageAssignmentDAO */
		$users =& $userStageAssignmentDao->getUsersNotAssignedToStageInUserGroup($monograph->getId(), $stageId, $userGroupId);

		$userList = array();
		while($user =& $users->next()) {
			$userList[] = array('label' => $user->getFullName(), 'value' => $user->getId());
			unset($reviewer);
		}

		$json = new JSONMessage(true, $userList);
		return $json->getString();
	}


	//
	// Private helper methods.
	//
	/**
	 * Return workflow stages.
	 * @return array
	 */
	function _getStages() {
		return array(WORKFLOW_STAGE_ID_SUBMISSION,
				WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
				WORKFLOW_STAGE_ID_EXTERNAL_REVIEW,
				WORKFLOW_STAGE_ID_EDITING,
				WORKFLOW_STAGE_ID_PRODUCTION);
	}
}

?>
