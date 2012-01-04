<?php
/**
 * @file controllers/grid/files/fileList/SelectableFileListGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableFileListGridHandler
 * @ingroup controllers_grid_files_fileList
 *
 * @brief Base grid for selectable file lists. The grid shows a check box for
 *  each row so that the user can make a selection among grid entries.
 */

import('controllers.grid.files.fileList.FileListGridHandler');

import('classes.controllers.grid.files.fileList.SelectableFileListGridHandlerImplementation');

class SelectableFileListGridHandler extends FileListGridHandler {

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SelectableFileListGridHandler($dataProvider, $stageId, $capabilities) {
		parent::FileListGridHandler($dataProvider, $stageId, $capabilities, 'SelectableFileListGridHandlerImplementation');
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
	function &loadData($request, $filter) {
		$submissionFiles =& parent::loadData($request, $filter);

		$handlerImplementation =& $this->getHandlerImplementation();
		return $handlerImplementation->loadData($submissionFiles);
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
	 * Get the selected file IDs.
	 * @param $submissionFiles array Set of submission files to filter
	 * @return array
	 */
	function getSelectedFileIds($submissionFiles) {
		// By default we select nothing.
		return array();
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
