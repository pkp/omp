<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridHandler
 * @ingroup controllers_grid_files
 *
 * @brief Handle submission file grid requests.
 */

// Import UI base classes.
import('lib.pkp.classes.controllers.grid.GridHandler');

// Import the class that implement some of this handler operations.
import('classes.controllers.grid.files.SubmissionFilesGridHandlerImplementation');

class SubmissionFilesGridHandler extends GridHandler {

	/** @var SubmissionFilesGridHandlerImplementation */
	var $_handlerImplementation;

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SubmissionFilesGridHandler(&$dataProvider, $stageId, $capabilities, $gridHandlerImplementationClass = null) {
		parent::GridHandler($dataProvider);

		if (is_null($gridHandlerImplementationClass)) {
			$gridHandlerImplementationClass = 'SubmissionFilesGridHandlerImplementation';
		}
		$handlerImplementation =& new $gridHandlerImplementationClass($this, $stageId, $capabilities);

		if (is_a($handlerImplementation, 'SubmissionFilesGridHandlerImplementation')) {
			$this->setHandlerImplementation($handlerImplementation);
		} else {
			assert(false);
		}
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
	 * Set the handler implementation.
	 * @param $handlerImplementation SubmissionFilesGridHandlerImplementation
	 */
	function setHandlerImplementation(&$handlerImplementation) {
		$this->_handlerImplementation =& $handlerImplementation;
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
	 * Set whether or not the user can delete files from this grid.
	 * @param $canDelete boolean
	 */
	function setCanDelete($canDelete) {
		$this->_handlerImplementation->setCanDelete((boolean) $canDelete);
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
	// Protected methods.
	//
	function getFilesToDownload(&$request) {
		return $this->getGridDataElements($request);
	}
}

?>
