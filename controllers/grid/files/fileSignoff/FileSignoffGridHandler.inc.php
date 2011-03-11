<?php
/**
 * @defgroup controllers_grid_files_fileSignoff
 */

/**
 * @file controllers/grid/files/fileSignoff/FileSignoffGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileSignoffGridHandler
 * @ingroup controllers_grid_files_fileSignoff
 *
 * @brief Base grid for file lists that allow for file signoff. This grid shows
 *  signoff columns in addition to the file name.
 */

import('controllers.grid.files.SubmissionFilesGridHandler');

class FileSignoffGridHandler extends SubmissionFilesGridHandler {

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function FileSignoffGridHandler($dataProvider, $stageId, $capabilities) {
		parent::SubmissionFilesGridHandler($dataProvider, $stageId, $capabilities);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);
	}
}