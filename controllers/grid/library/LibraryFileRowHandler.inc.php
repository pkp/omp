<?php

/**
 * @file controllers/grid/file/FileRowHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileRowHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle file grid row requests.
 */

import('controllers.grid.GridRowHandler');

class LibraryFileRowHandler extends GridRowHandler {
	/** the FileType for this grid */
	var $fileType;
	
	/**
	 * Constructor
	 */
	function LibraryFileRowHandler() {
		parent::GridRowHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(),
				array('editFile', 'uploadFile', 'deleteFile'));
	}

	/**
	 * get the FileType 
	 */
	function getFileType() {
		return $this->fileType;
	}
	
	/**
	 * set the fileType
	 */
	function setFileType($fileType)	{
		$this->fileType = $fileType;
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		$this->setFileType($request->getUserVar('fileType'));
		
		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		$emptyActions = array();
		// Basic grid row configuration
		import('controllers.grid.library.LibraryFileGridCellProvider');
		$cellProvider =& new LibraryFileGridCellProvider();
		$this->addColumn(new GridColumn('groups', 'grid.libraryFiles.column.files', $emptyActions, 'controllers/grid/gridCellInSpan.tpl', $cellProvider));

		parent::initialize($request);
	}

	function _configureRow(&$request, $args = null) {
		// assumes row has already been initialized
		// do the default configuration
		parent::_configureRow($request, $args);
		
		// Actions
		$router =& $request->getRouter();
		$actionArgs = array(
			'gridId' => $this->getGridId(),
			'rowId' => $this->getId()
		);
		
		$this->addAction(
			new GridAction(
				'deleteFile',
				GRID_ACTION_MODE_CONFIRM,
				GRID_ACTION_TYPE_REMOVE,
				$router->url($request, null, 'grid.library.LibraryFileRowHandler', 'deleteFile', null, $actionArgs),
				'grid.action.delete',
				'delete'
			));		
	}
	
	//
	// Public File Row Actions
	//
	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editFile(&$args, &$request) {
		//FIXME: add validation here?
		$this->initialize($request);
		$this->_configureRow($request, $args);

		import('controllers.grid.library.form.FileForm');
		$fileForm = new FileForm($this->getFileType(), $this->getId());

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
		$this->_configureRow($request, $args);
		
		import('controllers.grid.library.form.FileForm');
		$fileForm = new FileForm($this->getFileType(), $this->getId());
		$fileForm->readInputData();

		// newUpload parameter appears only once the file has been uploaded
		if ( $request->getUserVar('newUpload') ) {
			$fileId = $request->getUserVar('fileId');
			$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
			$libraryFile =& $libraryFileDao->getById($fileId);
			
			import('controllers.grid.library.LibraryFileRowHandler');
			$fileRow =& new LibraryFileRowHandler();
			$fileRow->setId($fileId);
			$fileRow->setData($libraryFile);			

			$json = new JSON('true', $fileRow->renderRowInternally($request));
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
		$this->_configureRow($request, $args);

		$router =& $request->getRouter();
		$press =& $router->getContext();
 
		import('file.LibraryFileManager');
		$libraryFileManager = new LibraryFileManager($press->getId());
		$libraryFileManager->deleteFile($this->getId());
		$json = new JSON('true');
		echo $json->getString();
	}
}