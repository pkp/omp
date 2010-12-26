<?php

/**
 * @file controllers/grid/files/fairCopyFiles/FairCopyFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FairCopyFilesGridHandler
 * @ingroup controllers_grid_files_fairCopyFiles
 *
 * @brief Handle the fair copy files grid (displays copyedited files ready to move to proofreading)
 */

import('controllers.grid.files.submissionFiles.SubmissionFilesGridHandler');
import('controllers.grid.files.fairCopyFiles.FairCopyFilesGridRow');

class FairCopyFilesGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 */
	function FairCopyFilesGridHandler() {
		// Configure the submission file grid.
		parent::SubmissionFilesGridHandler(MONOGRAPH_FILE_FAIR_COPY, true);

		// Configure role based authorization.
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('fetchGrid', 'addFile', 'displayFileUploadForm', 'uploadFile', 'confirmRevision',
						'editMetadata', 'saveMetadata', 'downloadFile', 'downloadAllFiles', 'deleteFile'));
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

		// Test whether the tar binary is available for the export to work, if so, add grid action
		$tarBinary = Config::getVar('cli', 'tar');
		if ($this->hasData() && !empty($tarBinary) && file_exists($tarBinary)) {
			$monograph =& $this->getMonograph();
			$router =& $request->getRouter();
			$this->addAction(
				new LegacyLinkAction(
					'downloadAll',
					LINK_ACTION_MODE_LINK,
					LINK_ACTION_TYPE_NOTHING,
					$router->url($request, null, null, 'downloadAllFiles', null, array('monographId' => $monograph->getId())),
					'submission.files.downloadAll',
					null,
					'getPackage'
				)
			);
		}

		// Load additional translation components.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR));

		// Columns
		import('controllers.grid.files.fairCopyFiles.FairCopyFilesGridCellProvider');
		$cellProvider =& new FairCopyFilesGridCellProvider();
		parent::initialize($request, $cellProvider);

		// Add a column for the uploader.
		// FIXME: We're just adding some placeholder text here until this
		// is correctly implemented, see #6233.
		$this->addColumn(
			new GridColumn(
				'select',
				null,
				'FIXME',
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);

		// Add another column for the uploader's role
		// FIXME: We're just adding some placeholder text here until this
		// is correctly implemented, see #6233.
		$this->addColumn(
			new GridColumn(
				'name',
				null,
				'FIXME',
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance
	 */
	function &getRowInstance() {
		$row = new FairCopyFilesGridRow();
		return $row;
	}
}