<?php

/**
 * @file controllers/grid/files/copyedit/CopyeditingFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesGridHandler
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Subclass of file editor/auditor grid for copyediting files.
 */

// import grid signoff files grid base classes
import('controllers.grid.files.signoff.SignoffFilesGridHandler');

// Import monograph file class which contains the MONOGRAPH_FILE_* constants.
import('classes.monograph.MonographFile');

// Import MONOGRAPH_EMAIL_* constants.
import('classes.mail.MonographMailTemplate');

class CopyeditingFilesGridHandler extends SignoffFilesGridHandler {
	/**
	 * Constructor
	 */
	function CopyeditingFilesGridHandler() {
		parent::SignoffFilesGridHandler(
			WORKFLOW_STAGE_ID_EDITING,
			MONOGRAPH_FILE_COPYEDIT,
			'SIGNOFF_COPYEDITING',
			MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR
		);
	}


	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration
		$this->setId('copyeditingFiles');
		$this->setTitle('submission.copyediting');

		// Rename the 'editor' column to copyeditor
		$pressAssistantColumn =& $this->getColumn('editor');
		$pressAssistantColumn->setTitle('user.role.copyeditor');
	}
}

?>
