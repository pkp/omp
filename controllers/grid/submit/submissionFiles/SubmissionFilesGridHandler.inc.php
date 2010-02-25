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

import('controllers.grid.GridHandler');
import('controllers.grid.submit.submissionFiles.SubmissionFilesGridRow');

class SubmissionFilesGridHandler extends GridHandler {
	/** the FileType for this grid */
	var $fileType;

	/**
	 * Constructor
	 */
	function SubmissionFilesGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
// FIXME: Do we need file types?  Are we going to reuse this grid?
//	/**
//	 * get the FileType
//	 */
//	function getFileType() {
//		return $this->fileType;
//	}
//
//	/**
//	 * set the fileType
//	 */
//	function setFileType($fileType)	{
//		$this->fileType = $fileType;
//	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addFile', 'editFile', 'uploadFile', 'deleteFile'));
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
		// Basic grid configuration
	//	$this->setFileType($request->getUserVar('fileType'));
		$this->setTitle('author.submit.submissionFiles');
		
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_PKP_SUBMISSION));
		
		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		
		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new GridAction(
				'addFile',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addFile', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		// Columns
		$emptyActions = array();
		// Basic grid row configuration
		$this->addColumn(new GridColumn('submissionFiles', 'submission.files', $emptyActions, 'controllers/grid/gridCellInSpan.tpl'));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return LibraryFileGridRow
	 */
	function &getRowInstance() {
		$row = new LibraryFileGridRow();
		return $row;
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
		// Calling editSponsor with an empty row id will add
		// a new sponsor.
		$this->editFile($args, $request);
	}

	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editFile(&$args, &$request) {
		$fileId = isset($args['rowId']) ? $args['rowId'] : null;
		//FIXME: add validation here?
		$this->initialize($request);
		
		import('controllers.grid.submit.submissionFiles.form.SubmissionFilesForm');
		$fileForm = new SubmissionFilesForm($fileId);

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
		//FIXME: add validation here?
		$this->initialize($request);

		import('controllers.grid.submit.submissionFiles.form.SubmissionFilesForm');
		$fileForm = new SubmissionFilesForm($this->getFileType(), $this->getId());
		$fileForm->readInputData();

		// newUpload parameter appears only once the file has been uploaded
		if ( $request->getUserVar('newUpload') ) {
			$fileId = $request->getUserVar('fileId');
			$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
			$libraryFile =& $libraryFileDao->getById($fileId);

			import('controllers.grid.setup.library.LibraryFileGridRow');
			$fileRow =& new LibraryFileGridRow();
			$fileRow->setId($fileId);
			$fileRow->setData($libraryFile);

			$json = new JSON('true', $fileRow->_renderRowInternally($request));
			echo $json->getString();
		} elseif ($fileForm->validate() && ($fileId = $fileForm->uploadFile($args, $request)) ) {
			// form validated and file uploaded successfully
			$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
			$libraryFile =& $libraryFileDao->getById($fileId);

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('libraryFile', $libraryFile);
			$templateMgr->display('controllers/grid/library/form/fileInfo.tpl');
			exit;
		} else {
			echo Locale::translate("problem uploading file");
		}

	}


	/**
	 * Delete a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteFile(&$args, &$request) {
		// FIXME: add validation here?
		$this->initialize($request);

		$router =& $request->getRouter();
		$press =& $router->getContext();

		import('file.LibraryFileManager');
		$libraryFileManager = new LibraryFileManager($press->getId());
		$libraryFileManager->deleteFile($this->getId());
		$json = new JSON('true');
		echo $json->getString();
	}
}