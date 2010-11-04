<?php

/**
 * @file controllers/grid/files/finalDraftFiles/FinalDraftFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
	/** the FileType for this grid */
	var $fileType;

	/** Boolean flag if grid is selectable **/
	var $_isSelectable;

	/**
	 * Constructor
	 */
	function FinalDraftFilesGridHandler() {
		parent::GridHandler();

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_REVIEWER), array());
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('fetchGrid', 'downloadFile', 'downloadAllFiles', 'manageFinalDraftFiles', 'updateFinalDraftFiles', 'deleteFile'));
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
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
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

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$monographId = (integer)$request->getUserVar('monographId');
		$this->setId('finalDraftFiles');
		$this->setTitle('submission.finalDraft');

		// Set the Is Selectable boolean flag
		$isSelectable = (boolean)$request->getUserVar('isSelectable');
		$this->setIsSelectable($isSelectable);

		// Set the canUpload boolean flag
		$canUpload = (boolean)$request->getUserVar('canUpload');
		$this->setCanUpload($canUpload);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		// Do different initialization if this is a selectable grid or if its a display only version of the grid.
		if ($isSelectable) {
			// Load a different grid template
			$this->setTemplate('controllers/grid/files/finalDraftFiles/grid.tpl');

			// Set the files to all the available files (submission and final draft file types)
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFiles =& $monographFileDao->getByMonographId($monographId);
			$rowData = array();
			foreach ($monographFiles as $monographFile) {
				$rowData[$monographFile->getFileId()] = $monographFile;
			}
			$this->setData($rowData);
			$this->setId('finalDraftFilesSelect'); // Need a unique ID since the 'manage review files' modal is in the same namespace as the 'view review files' modal

			// Set the already selected elements of the grid (the final draft files)
			$templateMgr =& TemplateManager::getManager();
			$selectedFileIds = array();
			foreach ($monographFiles as $monographFile) {
				if($monographFile->getType() == MONOGRAPH_FILE_FINAL) {
					$selectedFileIds[] = $monographFile->getFileId() . "-" . $monographFile->getRevision();
				}
			}
			$templateMgr->assign('selectedFileIds', $selectedFileIds);

			if ($canUpload) {
				$this->addAction(
					new LinkAction(
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
			// Grab only the final draft files
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFiles =& $monographFileDao->getByMonographId($monographId, MONOGRAPH_FILE_FINAL);
			$rowData = array();
			foreach ($monographFiles as $monographFile) {
				$rowData[$monographFile->getFileId()] = $monographFile;
			}
			$this->setData($rowData);

			// Allow the user to manage the files (add existing, non-final files)
			$this->addAction(
				new LinkAction(
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


		import('controllers.grid.files.finalDraftFiles.FinalDraftFilesGridCellProvider');
		$cellProvider =& new FinalDraftFilesGridCellProvider();
		// Columns
		if ($this->getIsSelectable()) {
			$this->addColumn(new GridColumn('select',
				'common.select',
				null,
				'controllers/grid/files/finalDraftFiles/gridRowSelectInput.tpl',
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
		$monographFileManager = new MonographFileManager($monographId);
		$monographFileManager->downloadFile($fileId);
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
		$monographFileManager = new MonographFileManager($monographId);
		$monographFileManager->downloadFilesArchive($this->_data);
	}

	/**
	 * Add a file that the Press Editor did not initally add to the final draft
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function manageFinalDraftFiles($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.files.finalDraftFiles.form.ManageFinalDraftFilesForm');
		$manageFinalDraftFilesForm = new ManageFinalDraftFilesForm($monographId);

		$manageFinalDraftFilesForm->initData($args, $request);
		$json = new JSON('true', $manageFinalDraftFilesForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save 'manage final draft files' form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateFinalDraftFiles($args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.files.finalDraftFiles.form.ManageFinalDraftFilesForm');
		$manageFinalDraftFilesForm = new ManageFinalDraftFilesForm($monographId);

		$manageFinalDraftFilesForm->readInputData();

		if ($manageFinalDraftFilesForm->validate()) {
			$selectedFiles =& $manageFinalDraftFilesForm->execute($args, $request);

			// Re-render the grid with the updated files
			$this->setData($selectedFiles);
			$this->initialize($request);

			// Pass to modal.js to reload the grid with the new content
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
			$json = new JSON('true', $renderedGridRows);
		} else {
			$json = new JSON('false');
		}
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
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFileDao->deleteMonographFileById($fileId);

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}
}