<?php

/**
 * @file controllers/grid/files/review/EditorReviewFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorReviewFilesGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Handle the editor review file grid (displays files that are to be reviewed in the current round)
 */

import('controllers.grid.files.fileList.FileListGridHandler');

class EditorReviewFilesGridHandler extends FileListGridHandler {

	/**
	 * Constructor
	 */
	function EditorReviewFilesGridHandler() {
		import('controllers.grid.files.review.ReviewFilesGridDataProvider');
		$dataProvider = new ReviewFilesGridDataProvider();
		// FIXME: #6244# HARDCODED INTERNAL_REVIEW
		parent::FileListGridHandler(
			$dataProvider,
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
			FILE_GRID_DOWNLOAD_ALL|FILE_GRID_MANAGE
		);

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetchGrid', 'fetchRow', 'downloadAllFiles', 'selectFiles')
		);

		// Set the grid title.
		$this->setTitle('reviewer.monograph.reviewFiles');
	}


	//
	// Public handler methods
	//
	/**
	 * Show the form to allow the user to select review files
	 * (bring in/take out files from submission stage to review stage)
	 *
	 * FIXME: Move to it's own handler so that it can be re-used among grids.
	 *
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function selectFiles($args, &$request) {
		$monograph =& $this->getMonograph();

		import('controllers.grid.files.review.form.ManageReviewFilesForm');
		$manageReviewFilesForm = new ManageReviewFilesForm($monograph->getId(), $this->getRequestArg('reviewType'), $this->getRequestArg('round'));

		$manageReviewFilesForm->initData($args, $request);
		$json = new JSONMessage(true, $manageReviewFilesForm->fetch($request));
		return $json->getString();
	}
}

?>
