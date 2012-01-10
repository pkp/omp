<?php

/**
 * @file controllers/grid/files/final/FinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FinalDraftFilesGridHandler
 * @ingroup controllers_grid_files_final
 *
 * @brief Handle the final draft files grid (displays files sent to copyediting from the review stage)
 */


import('controllers.grid.files.fileList.FileListGridHandler');

class FinalDraftFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 * @param $capabilities integer A bit map with zero or more
	 *  FILE_GRID_* capabilities set.
	 */
	function FinalDraftFilesGridHandler() {
		import('controllers.grid.files.final.FinalDraftFilesGridDataProvider');
		parent::FileListGridHandler(
			new FinalDraftFilesGridDataProvider(),
			null,
			FILE_GRID_DOWNLOAD_ALL|FILE_GRID_MANAGE|FILE_GRID_VIEW_NOTES
		);
		$this->addRoleAssignment(
			array(
				ROLE_ID_SERIES_EDITOR,
				ROLE_ID_PRESS_MANAGER,
				ROLE_ID_PRESS_ASSISTANT
			),
			array(
				'fetchGrid', 'fetchRow', 'selectFiles'
			)
		);

		// Set the grid title
		$this->setTitle('submission.finalDraft');
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

		import('controllers.grid.files.final.form.ManageFinalDraftFilesForm');
		$manageFinalDraftFilesForm = new ManageFinalDraftFilesForm($monograph->getId());

		$manageFinalDraftFilesForm->initData($args, $request);
		$json = new JSONMessage(true, $manageFinalDraftFilesForm->fetch($request));
		return $json->getString();
	}
}

?>
