<?php
/**
 * @file controllers/grid/files/fileList/SelectableFileListGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectableFileListGridHandler
 * @ingroup controllers_grid_files_fileList
 *
 * @brief Base grid for selectable file lists. The grid shows a check box for
 *  each row so that the user can make a selection among grid entries.
 */

import('controllers.grid.files.fileList.FileListGridHandler');

class SelectableFileListGridHandler extends FileListGridHandler {

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function SelectableFileListGridHandler($dataProvider, $stageId, $capabilities) {
		parent::FileListGridHandler($dataProvider, $stageId, $capabilities);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$selectionPolicy =& $this->getSelectionPolicy($request, $args, $roleAssignments);
		if (!is_null($selectionPolicy)) {
			$this->addPolicy($selectionPolicy);
		}
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		// Add checkbox column to the grid.
		import('controllers.grid.files.fileList.FileSelectionGridColumn');
		$this->addColumn(
			new FileSelectionGridColumn(
				$this->getSelectedFileIds(), $this->getSelectName()
			)
		);

		parent::initialize($request);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::fetchGrid()
	 */
	function fetchGrid($args, &$request, $fetchParams = array()) {
		// Retrieve and add the the request parameters required to
		// specify the contents of this grid.
		$fetchParams = array_merge($fetchParams, $this->getSelectionArgs());
		return parent::fetchGrid($args, $request, $fetchParams);
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
	 * Get a selection request parameter.
	 * @param $key string The name of the parameter to retrieve.
	 * @return mixed
	 */
	function getSelectionArg($key) {
		$selectionArgs =& $this->getSelectionArgs();
		assert(isset($selectionArgs[$key]));
		return $selectionArgs[$key];
	}

	/**
	 * Get the selected file IDs.
	 * @return array
	 */
	function getSelectedFileIds() {
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
