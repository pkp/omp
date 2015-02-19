<?php

/**
 * @file controllers/grid/files/fairCopy/FairCopyFilesGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FairCopyFilesGridHandler
 * @ingroup controllers_grid_files_fairCopy
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

import('lib.pkp.controllers.grid.files.fileSignoff.FileSignoffGridHandler');

class FairCopyFilesGridHandler extends FileSignoffGridHandler {
	/**
	 * Constructor
	 */
	function FairCopyFilesGridHandler() {
		import('lib.pkp.controllers.grid.files.SubmissionFilesGridDataProvider');
		parent::FileSignoffGridHandler(
			new SubmissionFilesGridDataProvider(SUBMISSION_FILE_FAIR_COPY),
			WORKFLOW_STAGE_ID_EDITING,
			'SIGNOFF_FAIR_COPY',
			FILE_GRID_ADD|FILE_GRID_DELETE|FILE_GRID_VIEW_NOTES
		);

		$this->addRoleAssignment(
			array(
				ROLE_ID_SUB_EDITOR,
				ROLE_ID_MANAGER,
				ROLE_ID_ASSISTANT
			),
			array(
				'fetchGrid', 'fetchRow',
				'addFile',
				'downloadFile',
				'deleteFile',
				'signOffFile'
			)
		);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request) {
		parent::initialize($request);

		$this->setTitle('editor.monograph.editorial.fairCopy');
		$this->setInstructions('editor.monograph.editorial.fairCopyDescription');

		// Rename the Press Assistant column to copyeditor
		$columnId = 'role-' . ROLE_ID_ASSISTANT;
		if ($this->hasColumn($columnId)) {
			$pressAssistantColumn =& $this->getColumn($columnId);
			$pressAssistantColumn->setTitle('user.role.copyeditor');
		}

		// Rename the Press manager column to press signoff
		$columnId = 'role-' . ROLE_ID_MANAGER;
		if ($this->hasColumn($columnId)) {
			$pressAssistantColumn =& $this->getColumn($columnId);
			$pressAssistantColumn->setTitle('editor.pressSignoff');
		}
	}
}

?>
