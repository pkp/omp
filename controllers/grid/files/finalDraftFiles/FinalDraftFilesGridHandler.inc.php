<?php

/**
 * @file controllers/grid/files/finalDraftFiles/FinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FinalDraftFilesGridHandler
 * @ingroup controllers_grid_files_finalDraftFiles
 *
 * @brief Handle the final draft files grid (displays files sent to copyediting from the review stage)
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('controllers.grid.files.finalDraftFiles.FinalDraftFilesGridRow');

class FinalDraftFilesGridHandler extends GridHandler {
	/** @var boolean flag if grid is selectable */
	var $_isSelectable;

	/** @var boolean flag if grid allows upload */
	var $_canUpload;

	/**
	 * Constructor
	 */
	function FinalDraftFilesGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('fetchGrid', 'downloadFile', 'downloadAllFiles', 'manageFinalDraftFiles', 'updateFinalDraftFiles', 'deleteFile'));
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


	//
	// Getters and Setters
	//
	/**
	 * Set the selectable flag
	 * @param $isSelectable boolean
	 */
	function setIsSelectable($isSelectable) {
		$this->_isSelectable = $isSelectable;
	}

	/**
	 * Get the selectable flag
	 * @return boolean
	 */
	function getIsSelectable() {
		return $this->_isSelectable;
	}

	/**
	 * Set the canUpload flag
	 * @param $canUpload boolean
	 */
	function setCanUpload($canUpload) {
		$this->_canUpload = $canUpload;
	}

	/**
	 * Get the canUpload flag
	 * @return boolean
	 */
	function getCanUpload() {
		return $this->_canUpload;
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration.
		$this->setTitle('submission.finalDraft');

		// Set the isSelectable boolean flag.
		if(!$this->getIsSelectable()) {
			// Only set this flag if it isn't already set -- The Final Draft Files grid will need to set this before initialize()
			$isSelectable = (boolean)$request->getUserVar('isSelectable');
			$this->setIsSelectable($isSelectable);
		}

		// Set the canUpload boolean flag.
		$canUpload = (boolean)$request->getUserVar('canUpload');
		$this->setCanUpload($canUpload);

		// Add required locale files.
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Elements to be displayed in the grid (different initialization
		// if this is a selectable grid or if its a display only version
		// of the grid).
		$router =& $request->getRouter();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$monographId = $monograph->getId();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		if ($this->getIsSelectable()) {
			// Set a special grid ID since the 'manage review files' modal is in the same namespace as the 'view review files' modal.
			$this->setId('finalDraftFilesSelect');

			// Load a different grid template.
			$this->setTemplate('controllers/grid/files/finalDraftFiles/grid.tpl');

			// Set the files to all the available files (submission and final draft file types).
			$monographFiles =& $submissionFileDao->getLatestRevisions($monographId);
			$rowData = array();
			foreach ($monographFiles as $monographFile) {
				$rowData[$monographFile->getFileId()] =& $monographFile;
				unset($monographFile);
			}
			$this->setData($rowData);

			// Set the already selected elements of the grid (the final draft files).
			$templateMgr =& TemplateManager::getManager();
			$selectedFileIds = array();
			foreach ($monographFiles as $monographFile) {
				if($monographFile->getFileStage() == MONOGRAPH_FILE_FINAL) {
					$selectedFileIds[] = $monographFile->getFileId() . "-" . $monographFile->getRevision();
				}
			}
			$templateMgr->assign('selectedFileIds', $selectedFileIds);

			// Add the upload action if required.
			if ($canUpload) {
				$this->addAction(
					new LegacyLinkAction(
						'uploadFile',
						LINK_ACTION_MODE_MODAL,
						LINK_ACTION_TYPE_APPEND,
						$router->url($request, null, 'grid.files.submissionFiles.CopyeditingSubmissionFilesGridHandler', 'addFile', null, array('monographId' => $monographId, 'fileStage' => MONOGRAPH_FILE_FINAL)),
						'editor.submissionArchive.uploadFile',
						null,
						'add'
					)
				);
			}
		} else {
			// Set the normal grid id.
			$this->setId('finalDraftFiles');

			// Grab only the final draft files.
			$monographFiles =& $submissionFileDao->getLatestRevisions($monographId, MONOGRAPH_FILE_FINAL);
			$rowData = array();
			foreach ($monographFiles as $monographFile) {
				$rowData[$monographFile->getFileId()] = $monographFile;
			}
			$this->setData($rowData);

			// Allow the user to manage the files (add existing, non-final files).
			$this->addAction(
				new LegacyLinkAction(
					'manageFinalDraftFiles',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'manageFinalDraftFiles', null, array('monographId' => $monographId)),
					'editor.monograph.manageFinalDraftFiles',
					null,
					'add'
				)
			);
		}

		// Test whether the tar binary is available for the export to work,
		// if so, add grid action to download all files.
		$tarBinary = Config::getVar('cli', 'tar');
		if ($this->hasData() && !empty($tarBinary) && is_executable($tarBinary)) {
			$this->addAction(
				new LegacyLinkAction(
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

		// Configure a special cell provider.
		import('controllers.grid.files.finalDraftFiles.FinalDraftFilesGridCellProvider');
		$cellProvider =& new FinalDraftFilesGridCellProvider();

		// Configure columns.
		if ($this->getIsSelectable()) {
			$this->addColumn(new GridColumn('select',
				'common.select',
				null,
				'controllers/grid/gridRowSelectInput.tpl',
				$cellProvider)
			);
		}

		$this->addColumn(new GridColumn('name',
			'common.file',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider)
		);

		$this->addColumn(new GridColumn('type',
			'common.type',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	* Get the row handler - override the default row handler
	* @return FinalDraftFilesGridRow
	*/
	function &getRowInstance() {
		$row = new FinalDraftFilesGridRow();
		return $row;
	}


	//
	// Public handler actions
	//
	/**
	 * Download the monograph file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function downloadFile($args, &$request) {
		// Download the file.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH); /* @var $monograph Monograph */
		$fileId = $request->getUserVar('fileId');
		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFile($monograph->getId(), $fileId); // NB: This will check the validity of the file id.
	}

	/**
	 * Download all of the monograph files as one compressed file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function downloadAllFiles($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH); /* @var $monograph Monograph */
		import('classes.file.MonographFileManager');
		MonographFileManager::downloadFilesArchive($monograph->getId(), $this->getData());
	}

	/**
	 * Add a file that the Press Editor did not initally add to the final draft
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function manageFinalDraftFiles($args, &$request) {
		// Instantiate the files form.
		import('controllers.grid.files.finalDraftFiles.form.ManageFinalDraftFilesForm');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH); /* @var $monograph Monograph */
		$manageFinalDraftFilesForm = new ManageFinalDraftFilesForm($monograph);

		// Initialize and render the files form.
		$manageFinalDraftFilesForm->initData($args, $request);
		$json = new JSON(true, $manageFinalDraftFilesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save 'manage final draft files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateFinalDraftFiles($args, &$request) {
		// Instantiate the files form.
		import('controllers.grid.files.finalDraftFiles.form.ManageFinalDraftFilesForm');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH); /* @var $monograph Monograph */
		$manageFinalDraftFilesForm = new ManageFinalDraftFilesForm($monograph);

		// Initialize form and read in data.
		$manageFinalDraftFilesForm->initData($args, $request);
		$manageFinalDraftFilesForm->readInputData();

		// Validate and execute form.
		if ($manageFinalDraftFilesForm->validate()) {
			$selectedFiles =& $manageFinalDraftFilesForm->execute($args, $request);

			// Re-render the grid with the updated files.
			$this->setData($selectedFiles);
			$this->initialize($request);
			// FIXME: Calls to private methods of superclasses are not allowed!
			$gridBodyParts = $this->_renderGridBodyPartsInternally($request);
			if (count($gridBodyParts) == 0) {
				// The following should usually be returned from a
				// template also so we remain view agnostic. But as this
				// is easy to migrate and we want to avoid the additional
				// processing overhead, let's just return plain HTML.
				$renderedGridRows = '<tbody> </tbody>';
			} else {
				assert(count($gridBodyParts) == 1);
				$renderedGridRows = $gridBodyParts[0];
			}
			$json = new JSON(true, $renderedGridRows);
		} else {
			$json = new JSON(false);
		}

		// Serialize JSON.
		return $json->getString();
	}

	/**
	 * Delete a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteFile($args, &$request) {
		$fileId = $request->getUserVar('fileId');

		if($fileId) {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$submissionFileDao->deleteAllRevisionsById($fileId);

			$json = new JSON(true);
		} else {
			$json = new JSON(false);
		}
		return $json->getString();
	}
}