<?php

/**
 * @file controllers/grid/files/fairCopy/FairCopyFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FairCopyFilesGridHandler
 * @ingroup controllers_grid_files_fairCopy
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

import('controllers.grid.files.fileSignoff.FileSignoffGridHandler');

class FairCopyFilesGridHandler extends FileSignoffGridHandler {
	/**
	 * Constructor
	 */
	function FairCopyFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		parent::FileSignoffGridHandler(
			new SubmissionFilesGridDataProvider(MONOGRAPH_FILE_FAIR_COPY),
			WORKFLOW_STAGE_ID_EDITING,
			'SIGNOFF_FAIR_COPY',
			FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_VIEW_NOTES
		);

		$this->addRoleAssignment(
			array(
				ROLE_ID_SERIES_EDITOR,
				ROLE_ID_PRESS_MANAGER,
				ROLE_ID_PRESS_ASSISTANT
			),
			array(
				'fetchGrid', 'fetchRow',
				'addFile',
				'downloadFile', 'downloadAllFiles',
				'deleteFile',
				'signOffFile'
			)
		);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$this->setTitle('submission.fairCopy');

		// Rename the Press Assistant column to copyeditor
		$columnId = 'role-' . ROLE_ID_PRESS_ASSISTANT;
		if ($this->hasColumn($columnId)) {
			$pressAssistantColumn =& $this->getColumn($columnId);
			$pressAssistantColumn->setTitle('user.role.copyeditor');
		}
	}
}

?>
