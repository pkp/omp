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
import('lib.pkp.classes.controllers.grid.GridHandler');


// import stageParticipant grid specific classes
import('controllers.grid.users.stageParticipant.StageParticipantGridRow');

class StageParticipantGridHandler extends GridHandler {

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


	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = $request->getUserVar('stageId');
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
		$this->addColumn(
			new GridColumn(
				'group',
				'author.users.contributor.role',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'participants',
				'submission.participants',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
        $monograph =& $this->getMonograph();
		$row = new StageParticipantGridRow($monograph, $this->getStageId());
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
		// Retrieve the signoffs.
		$monograph =& $this->getMonograph();
		$press =& $request->getPress();

		// Get each default user group ID, then load users by that user group ID
		$userGroupDao = & DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$userGroups =& $userGroupDao->getUserGroupsByStage($press->getId(), $this->getStageId(), true, true);

		$stageAssignments = array();
		$userStageAssignmentDao = & DAORegistry::getDAO('UserStageAssignmentDAO');
		while($userGroup =& $userGroups->next()) {
            // Skip both Author and Reviewer User Groups (they are not shown by design)
            // If this changes, special handling will be required, as they are not stored with the stage_assignments
            if ( !($userGroup->getRoleId() == ROLE_ID_AUTHOR && $userGroup->getRoleId() == ROLE_ID_REVIEWER) ) {
			    $stageAssignments[$userGroup->getId()] = $userStageAssignmentDao->getUsersBySubmissionAndStageId(
                                                        $monograph->getId(), $this->getStageId(), $userGroup->getId());
            }
			unset($userGroup);
		}

		return $stageAssignments;
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

        // assign the userGroupId to the template
        // FIXME: #6199 THis is not authorized anywhere.
        $userGroupId = (int) $request->getUserVar('userGroupId');
        $templateMgr->assign('userGroupId', $userGroupId);

        return $templateMgr->fetchJson('controllers/grid/users/stageParticipant/editStageParticipantList.tpl');
	}

	/**
	 * Update the row for the current userGroup's stage participant list.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveStageParticipantList($args, &$request) {
        // assign the userGroupId to the template
        // FIXME: #6199 THis is not authorized anywhere.
        $userGroupId = (int) $request->getUserVar('userGroupId');

        return DAO::getDataChangedEvent($userGroupId);
	}
}

?>
