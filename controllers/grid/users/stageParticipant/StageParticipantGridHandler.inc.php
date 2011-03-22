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
			array_merge($readAccess, array('addStageParticipant', 'deleteStageParticipant'))
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

		// Grid actions
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$router =& $request->getRouter();
		// FIXME: Not all roles should see this action. Bug #5975.
		$this->addAction(
			new LinkAction(
				'addStageParticipant',
				new AjaxModal(
					$router->url(
						$request, null, null, 'addStageParticipant', null,
						array('monographId' => $monographId, 'stageId' => $this->getStageId())
					),
					__('submission.submit.addStageParticipant'),
					'fileManagement'
				),
				__('submission.submit.addStageParticipant')
			)
		);

		// Columns
		import('lib.pkp.classes.controllers.grid.ArrayGridCellProvider');
		$cellProvider = new ArrayGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'userName',
				'author.users.contributor.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		$this->addColumn(
			new GridColumn(
				'userGroup',
				'author.users.contributor.role',
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
		$row = new StageParticipantGridRow($monograph->getId(), $this->getStageId());
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		// Retrieve the signoffs.
		$monograph =& $this->getMonograph();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoffFactory =& $signoffDao->getAllBySymbolic(
			'SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null, $this->getStageId()
		);

		// Prepare the element list as an array with the user name
		// and user group for each sign off.
		$elements = array();
		$userDao =& DAORegistry::getDAO('UserDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		while($signoff =& $signoffFactory->next()) { /* @var $signoff Signoff */
			$user =& $userDao->getUser($signoff->getUserId());
			$userGroup =& $userGroupDao->getById($signoff->getUserGroupId());
			$elements[(int)$signoff->getId()] = array(
				'userName' => $user->getFullName(),
				'userGroup' => $userGroup->getLocalizedAbbrev()
			);
		}
		return $elements;
	}

	/**
	 * @see GridHandler::fetchGrid()
	 */
	function fetchGrid($args, $request) {
		$monograph =& $this->getMonograph();
		$fetchParams = array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->getStageId()
		);
		return parent::fetchGrid($args, $request, $fetchParams);
	}


	//
	// Public actions
	//
	/**
	 * An action to manually add a new stage participant
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addStageParticipant($args, &$request) {
		// Render the stage participant form.
		// FIXME: We only need a form class here to gain access to the
		// form vocab. Make the form vocab globally available and implement this
		// form as a simple template, see #6505.
		import('controllers.grid.users.stageParticipant.form.StageParticipantForm');
		$stageParticipantForm = new StageParticipantForm($this->getMonograph(), $this->getStageId());
		$json = new JSON(true, $stageParticipantForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Delete a stage participant.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteStageParticipant($args, &$request) {
		// Identify the stage participant.
		$signoffId = (int)$request->getUserVar('signoffId');

		// Make sure that the stage participant is actually in this grid.
		$elements =& $this->getGridDataElements($request);
		if (!isset($elements[$signoffId])) fatalError('Invalid signoff id');

		// Delete the stage participant.
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		if($signoffDao->deleteObjectById($signoffId)) {
			return $json = DAO::getDataChangedEvent($signoffId);
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}
}

?>
