<?php
/**
 * @defgroup controllers_grid_files_fileList
 */

/**
 * @file controllers/grid/files/fileList/FileListGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileListGridHandler
 * @ingroup controllers_grid_files_fileList
 *
 * @brief Base grid for simple file lists. This grid shows the file type in
 *  addition to the file name.
 */

import('controllers.grid.files.SubmissionFilesGridHandler');

// Import class that implements some of the behaviours and data of this handler.
import('classes.controllers.grid.files.fileList.FileListGridHandlerImplementation');

class FileListGridHandler extends SubmissionFilesGridHandler {

	/**
	 * Constructor
	 * @param $dataProvider GridDataProvider
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function FileListGridHandler($dataProvider, $stageId, $capabilities, $gridHandlerImplementationClass = null) {
		if (is_null($gridHandlerImplementationClass)) {
			$gridHandlerImplementationClass = 'FileListGridHandlerImplementation';
		}

		parent::SubmissionFilesGridHandler($dataProvider, $stageId, $capabilities, $gridHandlerImplementationClass);
	}


	//
	// Getters/Setters
	//
	/**
	 * Whether the grid allows file management (select existing files to add to grid)
	 * @return boolean
	 */
	function canManage() {
		$handlerImplementation =& $this->getHandlerImplementation();
		return $handlerImplementation->_canManage;
	}
}

?>
