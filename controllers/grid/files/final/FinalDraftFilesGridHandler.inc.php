<?php

/**
 * @file controllers/grid/files/final/FinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FinalDraftFilesGridHandler
 * @ingroup controllers_grid_files_final
 *
 * @brief Handle the final draft files grid (displays files sent to copyediting from the review stage)
 */


// Import submission files grid base class
import('controllers.grid.files.SubmissionFilesGridHandler');

class FinalDraftFilesGridHandler extends SubmissionFilesGridHandler {
	/** @var boolean */
	var $_canManage;

	/**
	 * Constructor
	 */
	function FinalDraftFilesGridHandler($canAdd = false, $isSelectable = false, $canDownloadAll = true, $canManage = true) {
		$this->_canManage = $canManage;
		parent::SubmissionFilesGridHandler(MONOGRAPH_FILE_FINAL, $canAdd, false, $isSelectable, $canDownloadAll);
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('fetchGrid', 'downloadFile', 'downloadAllFiles', 'manageFinalDraftFiles', 'updateFinalDraftFiles', 'deleteFile'));
	}

	/**
	 * Whether the grid allows file management (select existing files to add to grid)
	 * @return boolean
	 */
	function canManage() {
		return $this->_canManage;
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {

		// Basic grid configuration.
		$this->setTitle('submission.finalDraft');

		// Add required locale files.
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Load the monograph files to be displayed in the grid
		$this->loadMonographFiles();

		if($this->canManage()) {
			$monograph =& $this->getMonograph();
			$router =& $request->getRouter();
				$this->addAction(
					new LinkAction(
						'manageFinalDraftFiles',
						new AjaxModal(
							$router->url($request, null, null, 'manageFinalDraftFiles', null, array('monographId' => $monograph->getId())),
							'editor.monograph.manageFinalDraftFiles'
						),
						'editor.monograph.manageFinalDraftFiles',
						'add'
					)
				);
		}

		import('controllers.grid.files.SubmissionFilesGridCellProvider');
		$cellProvider =& new SubmissionFilesGridCellProvider();
		parent::initialize($request, $cellProvider);
	}

	//
	// Protected methods
	//

	/**
	 * Add a file that the Press Editor did not initally add to the final draft
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function manageFinalDraftFiles($args, &$request) {
		// Instantiate the files form.
		import('controllers.grid.files.final.form.ManageFinalDraftFilesForm');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH); /* @var $monograph Monograph */
		$manageFinalDraftFilesForm = new ManageFinalDraftFilesForm($monograph);

		// Initialize and render the files form.
		$manageFinalDraftFilesForm->initData($args, $request);
		$json = new JSON(true, $manageFinalDraftFilesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save 'manage final draft files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateFinalDraftFiles($args, &$request) {
		// Instantiate the files form.
		import('controllers.grid.files.final.form.ManageFinalDraftFilesForm');
		$monograph =& $this->getMonograph(); /* @var $monograph Monograph */
		$manageFinalDraftFilesForm = new ManageFinalDraftFilesForm($monograph);

		// Initialize form and read in data.
		$manageFinalDraftFilesForm->initData($args, $request);
		$manageFinalDraftFilesForm->readInputData();

		// Validate and execute form.
		if ($manageFinalDraftFilesForm->validate()) {
			$manageFinalDraftFilesForm->execute($args, $request);

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}

}