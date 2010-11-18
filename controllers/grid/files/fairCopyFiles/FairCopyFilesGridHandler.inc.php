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

import('lib.pkp.classes.controllers.grid.GridHandler');
import('controllers.grid.files.fairCopyFiles.FairCopyFilesGridRow');

class FairCopyFilesGridHandler extends GridHandler {
	/** the FileType for this grid */
	var $fileType;

	/** Boolean flag if grid is selectable **/
	var $_isSelectable;

	/**
	 * Constructor
	 */
	function FairCopyFilesGridHandler() {
		parent::GridHandler();

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_REVIEWER), array());
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('fetchGrid', 'downloadFile', 'deleteFile'));
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
		$this->setId('fairCopyFiles');
		$this->setTitle('editor.monograph.fairCopy');

		// Set the canUpload boolean flag
		$canUpload = (boolean)$request->getUserVar('canUpload');
		$this->setCanUpload($canUpload);

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		// Grab the fair copy files
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($monographId, MONOGRAPH_FILE_FAIR_COPY);
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);

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

		if ($canUpload) {
			$this->addAction(
				new LinkAction(
					'uploadFile',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_APPEND,
					$router->url($request, null, 'grid.files.submissionFiles.CopyeditingSubmissionFilesGridHandler', 'addFile', null, array('monographId' => $monographId, 'fileStage' => MONOGRAPH_FILE_FAIR_COPY)),
					'editor.monograph.fairCopy.addFile',
					null,
					'add'
				)
			);
		}

		// Columns
		import('controllers.grid.files.fairCopyFiles.FairCopyFilesGridCellProvider');
		$cellProvider =& new FairCopyFilesGridCellProvider();
		$this->addColumn(new GridColumn('name',
			'common.file',
			null,
			'controllers/grid/gridCell.tpl',
			$cellProvider)
		);
		$session =& $request->getSession();
		$actingAsUserGroupId = $session->getActingAsUserGroupId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$actingAsUserGroup =& $userGroupDao->getById($actingAsUserGroupId);

		// add a column for the role the user is acting as
		$this->addColumn(
			new GridColumn(
				$actingAsUserGroupId,
				null,
				$actingAsUserGroup->getLocalizedAbbrev(),
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);

		// Add another column for the submitter's role
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);
		$uploaderUserGroup =& $userGroupDao->getById($monograph->getUserGroupId());
		$this->addColumn(
			new GridColumn(
				$uploaderUserGroup->getId(),
				null,
				$uploaderUserGroup->getLocalizedAbbrev(),
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	* Get the row handler - override the default row handler
	* @return FairCopyFilesGridRow
	*/
	function &getRowInstance() {
		$row = new FairCopyFilesGridRow();
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