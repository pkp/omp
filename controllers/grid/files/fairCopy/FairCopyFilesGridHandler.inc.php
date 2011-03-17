<?php

/**
 * @file controllers/grid/files/fairCopy/FairCopyFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FairCopyFilesGridHandler
 * @ingroup controllers_grid_files_fairCopy
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

// Import UI base classes.
import('lib.pkp.classes.linkAction.request.RedirectAction');

import('controllers.grid.files.SubmissionFilesGridHandler');

class FairCopyFilesGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 */
	function FairCopyFilesGridHandler() {
		// Configure the submission file grid.
		parent::SubmissionFilesGridHandler(
			MONOGRAPH_FILE_FAIR_COPY,
			WORKFLOW_STAGE_ID_EDITING,
			FILE_GRID_ADD|FILE_GRID_DELETE);

		// Configure role based authorization.
		$this->addRoleAssignment(
			array(
				ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT
			),
			array(
				'fetchGrid', 'fetchRow', 'addFile', 'displayFileUploadForm', 'uploadFile', 'confirmRevision',
				'editMetadata', 'saveMetadata', 'finishFileSubmission', 'downloadFile', 'downloadAllFiles',
				'deleteFile'
			)
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		// Basic grid configuration
		$this->setId('fairCopyFiles');
		$this->setTitle('editor.monograph.fairCopy');

		// Load grid data.
		$this->loadMonographFiles();

		// Load additional translation components.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR));

		// Columns
		import('controllers.grid.files.fairCopy.FairCopyFilesGridCellProvider');
		$cellProvider = new FairCopyFilesGridCellProvider();
		parent::initialize($request, $cellProvider);

		// Add a column for the uploader.
		// FIXME: We're just adding some placeholder text here until this
		// is correctly implemented, see #6233.
		$this->addColumn(
			new GridColumn(
				'select',
				null,
				'FIXME',
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);

		// Add another column for the uploader's role
		// FIXME: We're just adding some placeholder text here until this
		// is correctly implemented, see #6233.
		$this->addColumn(
			new GridColumn(
				'uploader-name',
				null,
				'FIXME',
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
	}
}

?>
