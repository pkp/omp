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
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import stageParticipant grid specific classes
import('controllers.grid.users.stageParticipant.StageParticipantGridCellProvider');
import('controllers.grid.users.stageParticipant.StageParticipantGridRow');

class StageParticipantGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function StageParticipantGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'addStageParticipant', 'editStageParticipant',
				'saveStageParticipant', 'deleteStageParticipant'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this stageParticipant grid.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the monograph associated with this stageParticipant grid.
	 * @param $monograph Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the authorized monograph.
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));

		// Basic grid configuration
		$this->setTitle('submission.submit.stageParticipants');

		// Get the monograph id
		$monograph =& $this->getMonograph();
		assert(is_a($monograph, 'Monograph'));
		$monographId = $monograph->getId();

		// Retrieve the stageParticipants associated with this monograph to be displayed in the grid
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$data =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, null, $monograph->getCurrentStageId());
		$this->setData($data);

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = array('monographId' => $monographId, 'stageId' => $monograph->getCurrentStageId());
		$this->addAction(
			new LegacyLinkAction(
				'addStageParticipant',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_REPLACE,
				$router->url($request, null, null, 'addStageParticipant', null, $actionArgs),
				'submission.submit.addStageParticipant'
			)
		);

		// Columns
		$cellProvider = new StageParticipantGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
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
	 * @return StageParticipantGridRow
	 */
	function &getRowInstance() {
		$row = new StageParticipantGridRow();
		return $row;
	}

	//
	// Public StageParticipant Grid Actions
	//
	/**
	 * An action to manually add a new stage participant
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addStageParticipant($args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');

		// Form handling
		import('controllers.grid.users.stageParticipant.form.StageParticipantForm');
		$stageParticipantForm = new StageParticipantForm($monographId);
		$stageParticipantForm->initData();

		$json = new JSON('true', $stageParticipantForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the 'add stage participant' form.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveStageParticipant($args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Form handling
		import('controllers.grid.users.stageParticipant.form.StageParticipantForm');
		$stageParticipantForm = new StageParticipantForm($monographId);
		$stageParticipantForm->readInputData();
		if ($stageParticipantForm->validate()) {
			$signoffDao =& DAORegistry::getDAO('SignoffDAO');
			$data =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, null, $monograph->getCurrentStageId());

			$this->setData($data);
			$this->initialize($request);

			// Pass to modal.js to reload the grid with the new content
			$gridBodyParts = $this->_renderGridBodyPartsInternally($request);
			if (count($gridBodyParts) == 0) {
				// The following should usually be returned from a
				// template also so we remain view agnostic. But as this
				// is easy to migrate and we want to avoid the additional
				// processing overhead, let's just return plain HTML.
				$renderedGridRows = '<tbody> </tbody>';
			} else {
				assert(count($gridBodyParts) == 1);
				$renderedGridRows = $gridBodyParts[0];
			}
			$json = new JSON('true', $renderedGridRows);
		} else {
			$json = new JSON('false', Locale::translate('editor.monograph.addUserError'));
		}
		return $json->getString();
	}

	/**
	 * Delete a stage participant
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteStageParticipant($args, &$request) {
		// Identify the submission Id
		$signoffId = $request->getUserVar('signoffId');

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		if($signoffDao->deleteObjectById($signoffId)) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}
}