<?php

/**
 * @file controllers/grid/files/revisions/RevisionsGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RevisionsGridHandler
 * @ingroup controllers_grid_files_revisions
 *
 * @brief Display the file revisions authors have uploaded
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

class RevisionsGridHandler extends GridHandler {
	/** the FileType for this grid */
	var $fileType;

	/** Boolean flag if grid is selectable **/
	var $_isSelectable;

	/** Boolean flag if user can upload file to grid **/
	var $_canUpload;

	/**
	 * Constructor
	 */
	function RevisionsGridHandler() {
		parent::GridHandler();
		// FIXME: Please correctly distribute the operations among roles.
		$this->addRoleAssignment(ROLE_ID_AUTHOR,
				$authorOperations = array());
		$this->addRoleAssignment(ROLE_ID_PRESS_ASSISTANT,
				$pressAssistantOperations = array_merge($authorOperations, array()));
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array_merge($pressAssistantOperations,
				array('fetchGrid', 'downloadFile', 'downloadAllFiles')));
	}

	//
	// Getters/Setters
	//
	/**
	 * Set the selectable flag
	 * @param $isSelectable bool
	 */
	function setIsSelectable($isSelectable) {
		$this->_isSelectable = $isSelectable;
	}

	/**
	 * Get the selectable flag
	 * @return bool
	 */
	function getIsSelectable() {
		return $this->_isSelectable;
	}

	/**
	 * Set the canUpload flag
	 * @param $canUpload bool
	 */
	function setCanUpload($canUpload) {
		$this->_canUpload = $canUpload;
	}

	/**
	 * Get the canUpload flag
	 * @return bool
	 */
	function getCanUpload() {
		return $this->_canUpload;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_INTERNAL_REVIEW));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$monographId = $request->getUserVar('monographId');
		$this->setId('revisions');
		$this->setTitle('editor.monograph.revisions');

		// Set the Is Selectable boolean flag
		$isSelectable = $request->getUserVar('isSelectable');
		$this->setIsSelectable($isSelectable);

		// Set the Can upload boolean flag
		$canUpload = $request->getUserVar('canUpload');
		$this->setCanUpload($canUpload);

		$reviewType = (int) $request->getUserVar('reviewType');
		$round = (int) $request->getUserVar('round');

		// Grab the files that are the same as in the current review, but with later revisions
		$reviewRoundDAO =& DAORegistry::getDAO('ReviewRoundDAO');
		$selectedFiles =& $reviewRoundDAO->getRevisionsOfCurrentReviewFiles($monographId, $round);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		// Do different initialization if this is a selectable grid or if its a display only version of the grid.
		// The selectable grid will contain all submission files, but non-revised files will have a 'hide' flag
		if ($isSelectable) {
			// Load a different grid template
			$this->setTemplate('controllers/grid/files/revisions/grid.tpl');

			// Set the files to all the available files to allow selection.
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$monographFiles =& $submissionFileDao->getLatestRevisions($monographId);
			$rowData = array();
			foreach ($monographFiles as $monographFile) {
				$rowData[$monographFile->getFileId()] = $monographFile;
			}
			$this->setData($rowData);
			$this->setId('revisionsSelect'); // Need a unique ID since the 'manage review files' modal is in the same namespace as the 'view review files' modal

			$this->addAction(
				new LinkAction(
					'filter',
					LINK_ACTION_MODE_LINK,
					LINK_ACTION_TYPE_NOTHING,
					'#',
					'editor.monograph.filter'
				)
			);

			// Set the already selected elements of the grid
			$templateMgr =& TemplateManager::getManager();
			//if(!empty($selectedFiles)) $templateMgr->assign('selectedFileIds', array_keys($selectedFiles[$reviewType][$round]));
			// Get IDs of selected files
			$selectedFileIds = array();
			foreach($selectedFiles as $selectedFile) {
				$selectedFileIds[] = $selectedFile->getFileId() . "-" . $selectedFile->getRevision();
			}
			$templateMgr->assign('selectedFileIds', $selectedFileIds);
		} else {
			// Otherwise, only display revisions
			$data = isset($selectedFiles) ? $selectedFiles : array();
			$this->setData($data);
		}

		// Test whether the tar binary is available for the export to work, if so, add grid action
		$tarBinary = Config::getVar('cli', 'tar');
		if (isset($this->_data) && !empty($tarBinary) && file_exists($tarBinary)) {
			$this->addAction(
				new LinkAction(
					'downloadAll',
					LINK_ACTION_MODE_LINK,
					LINK_ACTION_TYPE_NOTHING,
					$router->url($request, null, null, 'downloadAllFiles', null, array('monographId' => $monographId)),
					'submission.files.downloadAll',
					null,
					'getPackage'
				)
			);
		}

		import('controllers.grid.files.revisions.RevisionsGridCellProvider');
		$cellProvider =& new RevisionsGridCellProvider();
		// Columns
		if ($this->getIsSelectable()) {
			$this->addColumn(new GridColumn('select',
				'common.select',
				null,
				'controllers/grid/files/revisions/gridRowSelectInput.tpl',
				$cellProvider)
			);
		}

		$this->addColumn(new GridColumn('name',
			'common.file',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider)
		);

		// Show the file type.
		$this->addColumn(new GridColumn('type',
			'common.type',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider)
		);
	}

	//
	// Public methods
	//
	/**
	 * Download the monograph file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function downloadFile($args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId');

		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFile($monographId, $fileId);
	}

	/**
	 * Download all of the monograph files as one compressed file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function downloadAllFiles($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFilesArchive($monographId, $this->_data);
	}
}