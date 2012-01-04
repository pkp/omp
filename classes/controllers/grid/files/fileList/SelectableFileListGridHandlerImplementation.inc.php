<?php
/**
 * @file classes/controllers/grid/files/fileList/SelectableFileListGridHandlerImplementation.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableFileListGridHandlerImplementation
 * @ingroup classes_controllers_grid_files_fileList
 *
 * @brief This class implements some of the common behaviours and data that a grid handler
 * can use to handle with a selectable submission files list.
 */

import('classes.controllers.grid.files.fileList.FileListGridHandlerImplementation');

class SelectableFileListGridHandlerImplementation extends FileListGridHandlerImplementation {

	/**
	 * Constructor
	 * @param $gridHandler Handler The grid handler that instatiated this class.
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SelectableFileListGridHandlerImplementation(&$gridHandler, $stageId, $capabilities) {
		parent::FileListGridHandlerImplementation($gridHandler, $stageId, $capabilities);
	}


	//
	// Extend methods from SubmissionFilesGridHandlerImplementation
	//
	/**
	 * @see SubmissionFilesGridHandlerImplementation::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$gridHandler =& $this->getGridHandler();

		$selectionPolicy =& $gridHandler->getSelectionPolicy($request, $args, $roleAssignments);
		if (!is_null($selectionPolicy)) {
			$gridHandler->addPolicy($selectionPolicy);
		}

		parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Overriden methods from FileListGridHandlerImplementation
	//
	/**
	 * @see FileListGridHandlerImplementation::initialize()
	 */
	function initialize(&$request) {
		$gridHandler =& $this->getGridHandler();

		// Add checkbox column to the grid.
		import('controllers.grid.files.fileList.FileSelectionGridColumn');
		$gridHandler->addColumn(new FileSelectionGridColumn($gridHandler->getSelectName()));

		parent::initialize($request);
	}


	//
	// Public methods
	//
	/**
	 * Implementation of the GridHandler::getRequestArgs() method.
	 */
	function getRequestArgs($requestArgs) {
		$gridHandler =& $this->getGridHandler();

		$return = array_merge($requestArgs, $gridHandler->getSelectionArgs());
		return $return;
	}

	/**
	 * Implementation of the GridHanler::loadData() method.
	 */
	function &loadData(&$submissionFiles) {
		$gridHandler =& $this->getGridHandler();

		$selectedFiles =& $gridHandler->getSelectedFileIds($submissionFiles);
		$submissionFiles =& $this->setSelectedFlag($submissionFiles, $selectedFiles);

		return $submissionFiles;
	}

	/**
	 * Go through the submission files, set their
	 * "selected" flag and return them.
	 * @param $submissionFiles array
	 * @return array
	 */
	function &setSelectedFlag(&$submissionFiles, &$selectedFiles) {
		foreach($submissionFiles as $fileId => $submissionFileData) {
			assert(isset($submissionFileData['submissionFile']));
			$monographFile =& $submissionFileData['submissionFile']; /* @var $monographFile MonographFile */
			$submissionFiles[$fileId]['selected'] = in_array(
				$monographFile->getFileIdAndRevision(),
				$selectedFiles
			);
			unset($monographFile);
		}
		return $submissionFiles;
	}

}

?>
