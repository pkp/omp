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

// Import the class that implements the file list functionality.
import('classes.controllers.grid.files.fileList.FileListGridHandlerImplementation');

class SelectableSubmissionFileListCategoryGridHandler extends CategoryGridHandler {

	/** @var FileListGridHandlerImplementation */
	var $_handlerImplementation;

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SelectableSubmissionFileListCategoryGridHandler(&$dataProvider, $stageId, $capabilities) {
		$handlerImplementation = new FileListGridHandlerImplementation($this, $stageId, $capabilities);
		$handlerImplementation->_canManage = (boolean)($capabilities & FILE_GRID_MANAGE);

		$this->_handlerImplementation = $handlerImplementation;

		parent::CategoryGridHandler($dataProvider);
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
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		// Let parent class get data from data provider.
		$workflowStages = parent::loadData($request, $filter);

		// Filter the data.
		if ($filter['allStages']) {
			return array_combine($workflowStages, $workflowStages);
		} else {
			return array($this->getStageId() => $this->getStageId());
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

	/**
	 * @see GridHandler::initFeatures()
	 */
	function initFeatures($request, $args) {
		import('lib.pkp.classes.controllers.grid.feature.selectableItems.SelectableItemsFeature');
		return array(new SelectableItemsFeature());
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
	// Protected methods
	//
	/**
	 * Get all files of this grid to download.
	 * @param $request Request
	 * @return array
	 */
	function getFilesToDownload(&$request) {
		$dataProvider =& $this->getDataProvider();
		$workflowStages = $this->getGridDataElements($request);

		// Get the monograph files to be downloaded.
		$monographFiles = array();
		foreach ($workflowStages as $stageId) {
			$monographFiles = array_merge($monographFiles, $dataProvider->getCategoryData($stageId));
		}
		return $monographFiles;
	}

	/**
	 * @see GridHandler::isDataElementInCategorySelected()
	 */
	function isDataElementInCategorySelected($categoryDataId, &$gridDataElement) {
		$currentStageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$submissionFile =& $gridDataElement['submissionFile'];

		// Check for special cases when the file needs to be unselected.
		$dataProvider =& $this->getDataProvider();
		if ($dataProvider->getFileStage() != $submissionFile->getFileStage()) {
			return false;
		} elseif ($currentStageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $currentStageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			if ($currentStageId != $categoryDataId) {
				return false;
			}
		}

		// Passed the checks above. If viewable then select it.
		return $submissionFile->getViewable();
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
