<?php

/**
 * @file controllers/grid/files/submissionFiles/CopyeditingSubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingSubmissionFilesGridHandler
 * @ingroup controllers_grid_files_submissionFiles
 *
 * @brief Handle uploading files to the final draft, copyediting, and fair copy files grid.
 */

// import submission files grid specific classes
import('controllers.grid.files.submissionFiles.SubmissionFilesGridHandler');

class CopyeditingSubmissionFilesGridHandler extends SubmissionFilesGridHandler {
	/**
	 * Constructor
	 */
	function CopyeditingSubmissionFilesGridHandler() {
		parent::SubmissionFilesGridHandler();

		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_REVIEWER), array());
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array('fetchGrid', 'addFile', 'addRevision', 'editFile', 'displayFileForm', 'uploadFile',
			'confirmRevision', 'deleteFile', 'editMetadata', 'saveMetadata', 'finishFileSubmission',
			'returnFileRow', 'downloadFile'));
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
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_EDITING));
		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Overridden public AJAX methods from SubmissionFilesGridHandler
	//

	/**
	 * Action to edit an existing file (or a new one where the file id is null)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editFile(&$args, &$request) {
		$fileId = $request->getUserVar('fileId') ? $request->getUserVar('fileId'): null;
		$fileStage = $request->getUserVar('fileStage') ? $request->getUserVar('fileStage'): null;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->_monographId);
		$templateMgr->assign('fileId', $fileId);
		$templateMgr->assign('gridId', $this->getId());
		$templateMgr->assign('fileStage', $fileStage);

		$json = new JSON('true', $templateMgr->fetch('controllers/grid/files/submissionFiles/form/submissionFiles.tpl'));
		return $json->getString();
	}

	/**
	 * Display the file upload form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function displayFileForm(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId') ? $request->getUserVar('fileId'): null;
		$fileStage = $request->getUserVar('fileStage') ? $request->getUserVar('fileStage'): null;

		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		$fileForm = new SubmissionFilesUploadForm($fileId, $monographId, $fileStage);

		if ($fileForm->isLocaleResubmit()) {
			$fileForm->readInputData();
		} else {
			$fileForm->initData($args, $request);
		}

		$json = new JSON('true', $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function uploadFile(&$args, &$request) {
		$monographId = (int) $request->getUserVar('monographId');
		$fileId = $request->getUserVar('fileId') ? (int) $request->getUserVar('fileId'): null;
		$fileStage = $request->getUserVar('fileStage') ? (int) $request->getUserVar('fileStage'): null;

		import('controllers.grid.files.submissionFiles.form.SubmissionFilesUploadForm');
		$fileForm = new SubmissionFilesUploadForm($fileId, $monographId, $fileStage);
		$fileForm->readInputData();

		// Check to see if the file uploaded might be a revision to an existing file
		if(!$fileId) {
			$possibleRevision = $fileForm->checkForRevision(&$args, &$request);
		} else $possibleRevision = false;

		if ($fileForm->validate() && ($fileId = $fileForm->uploadFile($args, $request)) ) {
			$router =& $request->getRouter();

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign_by_ref('fileId', $fileId);

			$additionalAttributes = array(
				'fileFormUrl' => $router->url($request, null, null, 'displayFileForm', null, array('gridId' => $this->getId(), 'monographId' => $monographId, 'fileId' => $fileId)),
				'metadataUrl' => $router->url($request, null, null, 'editMetadata', null, array('gridId' => $this->getId(), 'monographId' => $monographId, 'fileId' => $fileId)),
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('monographId' => $monographId, 'fileId' => $fileId))
			);

			if ($possibleRevision) {
				$additionalAttributes['possibleRevision'] = true;
				$additionalAttributes['revisionConfirmUrl'] = $router->url($request, null, null, 'confirmRevision', null, array('fileId' => $fileId, 'monographId' => $monographId, 'revisionId' => $possibleRevision));
			}


			$json = new JSON('true', Locale::translate('submission.uploadSuccessfulContinue'), 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		// The ajaxForm library requires the JSON to be wrapped in a textarea for it to be read by the client (See http://jquery.malsup.com/form/#file-upload)
		return $json->getString();
	}

	/**
	 * Display the final tab of the modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function finishFileSubmission(&$args, &$request) {
		$monographId = isset($args['monographId']) ? $args['monographId'] : null;
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('fileId', $fileId);

		// Get the grid ID from the file type, so fileSubmissionComplete.tpl knows which grid to update
		$fileTypeToGridId = array(MONOGRAPH_FILE_FINAL => 'finalDraftFiles',
									MONOGRAPH_FILE_COPYEDIT => 'copyeditingFiles',
									MONOGRAPH_FILE_FAIR_COPY => 'fairCopyFiles');
		$templateMgr->assign('gridId', $fileTypeToGridId[$monographFile->getType()]);

		$json = new JSON('true', $templateMgr->fetch('controllers/grid/files/submissionFiles/form/fileSubmissionComplete.tpl'));
		return $json->getString();
	}

	/**
	 * Return a grid row with for the submission grid
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function returnFileRow(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;

		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		$monographId = $monographFile->getMonographId();

		if($monographFile) {
			// Get the handler path and name from the file type, so fileSubmissionComplete.tpl knows which grid to update
			$fileTypeToHandlerPath = array(MONOGRAPH_FILE_FINAL => 'controllers.grid.files.finalDraftFiles.FinalDraftFilesGridHandler',
									MONOGRAPH_FILE_COPYEDIT => 'controllers.grid.files.copyeditingFiles.CopyeditingFilesGridHandler',
									MONOGRAPH_FILE_FAIR_COPY => 'controllers.grid.files.fairCopyFiles.FairCopyFilesGridHandler');
			$fileTypeToHandlerName = array(MONOGRAPH_FILE_FINAL => 'FinalDraftFilesGridHandler',
									MONOGRAPH_FILE_COPYEDIT => 'CopyeditingFilesGridHandler',
									MONOGRAPH_FILE_FAIR_COPY => 'FairCopyFilesGridHandler');
			import($fileTypeToHandlerPath[$monographFile->getType()]);
			$filesGridHandler =& new $fileTypeToHandlerName[$monographFile->getType()]();
			$filesGridHandler->initialize($request);

			if(is_a($filesGridHandler, 'CopyeditingFilesGridHandler')) {
				// If we are working with copyediting files, we need to return a category row
				$categoryRow =& $filesGridHandler->getCategoryRowInstance();
				$categoryRow->setGridId($this->getId());
				$categoryRow->setId($fileId);
				$categoryRow->setData($monographFile);
				$categoryRow->initialize($request);

				$json = new JSON('true', $filesGridHandler->_renderCategoryInternally($request, $categoryRow));
			} else {
				$row =& $filesGridHandler->getRowInstance();
				$row->setId($monographFile->getFileId());
				$row->setData($monographFile);
				$row->initialize($request);

				$json = new JSON('true', $filesGridHandler->_renderRowInternally($request, $row));
			}

		} else {
			$json = new JSON('false', Locale::translate("There was an error with trying to fetch the file"));
		}

		return $json->getString();
	}
}