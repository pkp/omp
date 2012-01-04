<?php

/**
 * @file controllers/grid/files/SelectableSubmissionFileListCategoryGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableSubmissionFileListCategoryGridHandler
 * @ingroup controllers_grid_files
 *
 * @brief Handle selectable submission file list category grid requests. This handler
 * delegate some of its methods to another object, that contains the implementation required
 * to handle with selectable file lists.
 */

// Import UI base classes.
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');

// Import grid row class.
import('controllers.grid.files.SelectableSubmissionFileListCategoryGridRow');

// Import the class that implements the selectable file list functionality.
import('classes.controllers.grid.files.fileList.SelectableFileListGridHandlerImplementation');

class SelectableSubmissionFileListCategoryGridHandler extends CategoryGridHandler {

	/** @var SelectableFileListGridHandlerImplementation */
	var $_handlerImplementation;

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SelectableSubmissionFileListCategoryGridHandler(&$dataProvider, $stageId, $capabilities) {
		$handlerImplementation =& new SelectableFileListGridHandlerImplementation($this, $stageId, $capabilities);
		$handlerImplementation->_canManage = (boolean)($capabilities & FILE_GRID_MANAGE);

		$this->_handlerImplementation = $handlerImplementation;

		parent::GridHandler($dataProvider);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get handler implementation.
	 * @return SubmissionFilesGridHandlerImplementation
	 */
	function &getHandlerImplementation() {
		return $this->_handlerImplementation;
	}

	/**
	 * Get the workflow stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_handlerImplementation->getStageId();
	}

	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_handlerImplementation->getMonograph();
	}

	/**
	 * Does this grid allow the addition of files or revisions?
	 * @return boolean
	 */
	function canAdd() {
		return $this->_handlerImplementation->canAdd();
	}

	/**
	 * Set whether or not the grid allows the addition of files or revisions.
	 * @param $canAdd boolean
	 */
	function setCanAdd($canAdd) {
		$this->_handlerImplementation->setCanAdd((boolean) $canAdd);
	}

	/**
	 * Does this grid allow viewing of notes?
	 * @return boolean
	 */
	function canViewNotes() {
		return $this->_handlerImplementation->canViewNotes();
	}

	/**
	 * Can the user download all files as an archive?
	 * @return boolean
	 */
	function canDownloadAll() {
		return $this->_handlerImplementation->canDownloadAll();
	}

	/**
	 * Can the user delete files from this grid?
	 * @return boolean
	 */
	function canDelete() {
		return $this->_handlerImplementation->canDelete();
	}

	/**
	* Whether the grid allows file management (select existing files to add to grid)
	* @return boolean
	*/
	function canManage() {
		$handlerImplementation =& $this->getHandlerImplementation();
		return $handlerImplementation->_canManage;
	}

	/**
	 * Set whether or not the user can delete files from this grid.
	 * @param $canDelete boolean
	 */
	function setCanDelete($canDelete) {
		$this->_handlerImplementation->setCanDelete((boolean) $canDelete);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	* @see GridHandler::getRequestArgs()
	*/
	function getRequestArgs() {
		$requestArgs = parent::getRequestArgs();
		$handlerImplementation =& $this->getHandlerImplementation();

		return $handlerImplementation->getRequestArgs($requestArgs);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		// Let parent class get data from data provider.
		$workflowStages = parent::loadData($request, $filter);

		// Filter the data.
		if ($filter['allStages']) {
			return $workflowStages;
		} else {
			return array($this->getStageId());
		}
	}

	/**
	 * @see GridHandler::getFilterForm()
	 */
	function getFilterForm() {
		return 'controllers/grid/files/selectableSubmissionFileListCategoryGridFilter.tpl';
	}

	/**
	 * @see GridHandler::getFilterSelectionData()
	 */
	function getFilterSelectionData(&$request) {
		return array('allStages' => $request->getUserVar('allStages') ? true : false);
	}


	//
	// Overridden methods from CategoryGridHandler
	//
	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function &getCategoryData(&$categoryDataElement) {
		$stageId = $categoryDataElement;
		$stageSubmissionFiles =& parent::getCategoryData($stageId);
		$selectedFiles =& $this->getSelectedFileIds($stageSubmissionFiles);

		$handlerImplementation =& $this->getHandlerImplementation();
		$stageSubmissionFilesWithSelectedFlag =& $handlerImplementation->setSelectedFlag($stageSubmissionFiles, $selectedFiles);

		// Files that don't belongs to this actual workflow stage are always invisible.
		foreach ($stageSubmissionFilesWithSelectedFlag as $key => $submissionFileData) {
			$submissionFile =& $submissionFileData['submissionFile'];
			$dataProvider =& $this->getDataProvider();
			$setInvisible = false;
			if ($dataProvider->getFileStage() != $submissionFile->getFileStage()) {
				$setInvisible = true;
			} elseif ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
				$reviewRound =& $dataProvider->getReviewRound();
				if ($reviewRound->getStageId() != $stageId) {
					$setInvisible = true;
				}
			}
			if ($setInvisible) $stageSubmissionFilesWithSelectedFlag[$key]['selected'] = false;
		}
		return $stageSubmissionFilesWithSelectedFlag;
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 */
	function &getCategoryRowInstance() {
		$row = new SelectableSubmissionFileListCategoryGridRow();
		return $row;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$this->_handlerImplementation->authorize($request, $args, $roleAssignments);

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$this->_handlerImplementation->initialize($request);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		return $this->_handlerImplementation->getRowInstance();
	}


	//
	// Public handler methods
	//
	/**
	 * Download all of the monograph files as one compressed file.
	 * @param $args array
	 * @param $request Request
	 */
	function downloadAllFiles($args, &$request) {
		$dataProvider =& $this->getDataProvider();

		// Check for an workflow stage filter data.
		$filter = $request->getUserVar('allStages');
		if ($filter) {
			$workflowStages = $this->loadData($request, $filter);
		} else {
			$workflowStages = $this->getGridDataElements($request);
		}

		// Get the monograph files to be downloaded.
		$monographFiles = array();
		foreach ($workflowStages as $stageId) {
			$monographFiles = array_merge($monographFiles, $dataProvider->getCategoryData($stageId));
		}

		$this->_handlerImplementation->downloadAllFiles($args, $request, $monographFiles);
	}


	//
	// Protected methods
	//
	/**
	 * Return an (optional) additional authorization policy
	 * to authorize the file selection.
	 * @param $request Request
	 * @param $args array
	 * @param $roleAssignments array
	 * @return PolicySet
	 */
	function getSelectionPolicy(&$request, $args, $roleAssignments) {
		// By default we do not require an additional policy.
		return null;
	}

	/**
	 * Request parameters that describe the selected
	 * files.
	 * @param $request Request
	 * @return array
	 */
	function getSelectionArgs() {
		// By default we do not add any additional
		// request parameters for the selection.
		return array();
	}

	/**
	 * Return the selected file ids.
	 * @param $submissionFiles array
	 * @return array
	 */
	function getSelectedFileIds($submissionFiles) {
		// Set the already selected elements of the grid (the current review files).
		$selectedRevisions = array();

		// Include only the files marked viewable
		foreach ($submissionFiles as $id => $submissionFileData) {
			$submissionFile =& $submissionFileData['submissionFile'];
			if ($submissionFile->getViewable()) {
				$selectedRevisions[$id] =& $submissionFile;
			}
		}

		// Return the IDs
		return array_keys($selectedRevisions);
	}

	/**
	 * Get the selection name.
	 * @return string
	 */
	function getSelectName() {
		return 'selectedFiles';
	}
}

?>
