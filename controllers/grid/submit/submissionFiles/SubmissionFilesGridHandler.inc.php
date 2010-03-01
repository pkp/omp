<?php

/**
 * @file controllers/grid/submit/submissionFiles/SubmissionFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle submission file grid requests.
 */

// import grid base classes
import('controllers.grid.GridHandler');

// import submission files grid specific classes
import('controllers.grid.submit.submissionFiles.SubmissionFilesGridRow');

// import validation classes
import('handler.validation.HandlerValidatorPress');
import('handler.validation.HandlerValidatorRoles');

class SubmissionFilesGridHandler extends GridHandler {
	var $_monographId;

	/**
	 * Constructor
	 */
	function SubmissionFilesGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addFile', 'editFile', 'displayFileForm', 'uploadFile', 'deleteFile', 'editMetadata', 'saveMetadata', 'finishFileSubmission', 'returnFileRow'));
	}

	//
	// Overridden template methods
	//
	/*
	* Configure the grid
	* @param PKPRequest $request
	*/
	function initialize(&$request) {
		parent::initialize($request);

		$this->_monographId = $request->getUserVar('monographId');
		// Basic grid configuration
		$this->setTitle('author.submit.submissionFiles');

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($this->_monographId);
		
		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			$fileType = $bookFileTypeDao->getById($monographFile->getAssocId());
			$monographFileId = $monographFile->getFileId();
			$rowData[$monographFileId] = array('name' => $monographFile->getLocalizedName(), 'type' => $fileType->getLocalizedName());
		}
		$this->setData($rowData);

		// Add grid-level actions
		$this->addAction(
			new GridAction(
				'addFile',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addFile', null, array('gridId' => $this->getId(), 'monographId' => $this->_monographId)),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);
				
		// Columns
		$emptyActions = array();
		// Basic grid row configuration
		$this->addColumn(new GridColumn('name', 'common.name', $emptyActions, 'controllers/grid/gridCellInSpan.tpl'));
		$this->addColumn(new GridColumn('type', 'common.type'));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	* Get the row handler - override the default row handler
	* @return LibraryFileGridRow
	*/
	function &getRowInstance() {
		$row = new SubmissionFilesGridRow();
		return $row;
	}

	/**
	 * Validate that the user is the assigned author for the monograph
	 * Raises a fatal error if validation fails.
	 * @param $requiredContexts array
	 * @param $request PKPRequest
	 * @return boolean
	 */
	function validate($requiredContexts, $request) {
		// Retrieve the request context
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();

		// 1) Ensure we're in a press
		$this->addCheck(new HandlerValidatorPress($this, false, 'No press in context!'));

		// 2) Only Authors may access
		$this->addCheck(new HandlerValidatorRoles($this, false, 'Insufficient privileges!', null, array(ROLE_ID_AUTHOR)));
		
		// 3) Only this monograph's author may access
		$monographId = $request->getUserVar('monographId');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getMonograph($monographId);
		
		if ( isset($user) && isset($monograph)) {
			$userId = $user->getId();
			$monographSubmiter = $monograph->getUserId();
			import('handler.validation.HandlerValidatorCustom');
			$this->addCheck(new HandlerValidatorCustom($this, false, 'Restricted site access!', null, create_function('$monographSubmitter, $userId', 'if ($monographSubmitter != $userId) return false; else return true;'), array($monographSubmiter, $userId)));
		}
		
		// Execute standard checks
		if (!parent::validate($requiredContexts, $request)) return false;

		return true;

	}

	//
	// Public File Grid Actions
	//
	/**
	* An action to add a new file
	* @param $args array
	* @param $request PKPRequest
	*/
	function addFile(&$args, &$request) {
		// Calling editSponsor with an empty file id will add a new file
		$this->editFile($args, $request);
	}

	/**
	 * Action to edit an existing file (or a new one where the file id is null)
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editFile(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->_monographId);
		$templateMgr->assign('fileId', $fileId);
		$templateMgr->assign('gridId', $this->getId());
		$templateMgr->display('controllers/grid/submissionFiles/form/submissionFiles.tpl');
	}
	
	/**
	 * Display the file upload form
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function displayFileForm(&$args, &$request) {
		$fileId = !empty($args['fileId']) ? ((int) $args['fileId']) : null;
		$monographId = isset($args['monographId']) ? $args['monographId'] : null;

		//FIXME: add validation here?
		$this->initialize($request);

		import('controllers.grid.submit.submissionFiles.form.SubmissionFilesUploadForm');
		$fileForm = new SubmissionFilesUploadForm($fileId, $monographId);

		if ($fileForm->isLocaleResubmit()) {
			$fileForm->readInputData();
		} else {
			$fileForm->initData($args, $request);
		}
		$fileForm->display();
	}

	/**
	 * upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadFile(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		$monographId = isset($args['monographId']) ? $args['monographId'] : null;

		import('controllers.grid.submit.submissionFiles.form.SubmissionFilesUploadForm');
		$fileForm = new SubmissionFilesUploadForm($fileId, $monographId);
		$fileForm->readInputData();

		if ($fileForm->validate() && ($fileId = $fileForm->uploadFile($args, $request)) ) {
			$router =& $request->getRouter();
			
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('fileId', $fileId);
			
			$additionalAttributes = array('metadataUrl' => $router->url($request, null, null, 'editMetadata', null, array('gridId' => $this->getId(), 'fileId' => $fileId)));
			$json = new JSON('true', Locale::translate('submission.uploadSuccessful'), 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		// The ajaxForm library requires the JSON to be wrapped in a textarea for it to be read by the client (See http://jquery.malsup.com/form/#file-upload)
		echo '<textarea>' . $json->getString() . '</textarea>';
	}

	/**
	 * Edit the metadata of a submission file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editMetadata(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submit.submissionFiles.form.SubmissionFilesMetadataForm');
		$metadataForm = new SubmissionFilesMetadataForm($fileId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('gridId', $this->getId());

		if ($metadataForm->isLocaleResubmit()) {
			$metadataForm->readInputData();
		} else {
			$metadataForm->initData($args, $request);
		}
		$metadataForm->display();
	}


	/**
	 * Save the metadata of a submission file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveMetadata(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		$monographId = $request->getUserVar('monographId');
		
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		if(isset($monographFile) && $monographFile->getLocalizedName() != '') { //Name exists, just updating it
			$isEditing = true;
		} else { 
			$isEditing = false;
		}

		import('controllers.grid.submit.submissionFiles.form.SubmissionFilesMetadataForm');
		$metadataForm = new SubmissionFilesMetadataForm($fileId);
		$metadataForm->readInputData();

		if ($metadataForm->validate()) {
			$metadataForm->execute($args, $request);
			$router =& $request->getRouter();
						
			$additionalAttributes = array('isEditing' => $isEditing, 'finishingUpUrl' => $router->url($request, null, null, 'finishFileSubmission', null, array('gridId' => $this->getId(), 'fileId' => $fileId)));
			$json = new JSON('true', '', 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('author.submit.fileNameRequired'));
		}

		echo $json->getString();
	}
	
	/**
	 * Display the final tab of the modal
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function finishFileSubmission(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('fileId', $fileId);
		$templateMgr->assign('gridId', $this->getId());
		$templateMgr->display('controllers/grid/submissionFiles/form/fileSubmissionComplete.tpl');
	}
	
	/**
	 * Return a grid row with for the submission grid
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function returnFileRow(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		
		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId);
		
		if($monographFile) {
			$fileType = $bookFileTypeDao->getById($monographFile->getAssocId());

			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($monographFile->getFileId());
			$rowData = array('name' => $monographFile->getLocalizedName(), 'type' => $fileType->getLocalizedName());
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false', Locale::translate("There was an error with trying to fetch the file"));
		}
		
		echo $json->getString();
	}

	/**
	 * Delete a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteFile(&$args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFileDao->deleteMonographFileById($fileId);

		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($press->getId());
		$monographFileManager->deleteFile($fileId);
		
		$json = new JSON('true');
		echo $json->getString();
	}
}