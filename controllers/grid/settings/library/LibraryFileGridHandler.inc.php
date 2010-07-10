<?php

/**
 * @file controllers/grid/settings/library/LibraryFileGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.library.LibraryFileGridRow');

class LibraryFileGridHandler extends SetupGridHandler {
	/** the FileType for this grid */
	var $fileType;

	/**
	 * Constructor
	 */
	function LibraryFileGridHandler() {
		parent::SetupGridHandler();
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'addFile', 'editFile', 'uploadFile', 'saveMetadata', 'deleteFile'));
	}


	//
	// Getters/Setters
	//
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
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$this->setFileType($request->getUserVar('fileType'));
		$this->setId('libraryFile' . ucwords(strtolower($this->getFileType())));
		$this->setTitle('grid.libraryFiles.' . $this->getFileType() . '.title');

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$libraryFiles =& $libraryFileDao->getByPressId($context->getId(), $this->getFileType());
		$this->setData($libraryFiles);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'addFile',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addFile', null, array('gridId' => $this->getId(), 'fileType' => $this->getFileType())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		// Columns
		// Basic grid row configuration
		import('controllers.grid.settings.library.LibraryFileGridCellProvider');
		$cellProvider =& new LibraryFileGridCellProvider();
		$this->addColumn(new GridColumn('files',
										'grid.libraryFiles.column.files',
										null,
										'controllers/grid/gridCell.tpl',
										$cellProvider));
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
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('newFile', 'true');
		return $this->editFile($args, $request);
	}

	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editFile(&$args, &$request) {
		$this->initialize($request);
		$fileId = isset($args['rowId']) ? $args['rowId'] : null;

		import('controllers.grid.settings.library.form.FileForm');
		$fileForm = new FileForm($this->getFileType(), $fileId);

		if ($fileForm->isLocaleResubmit()) {
			$fileForm->readInputData();
		} else {
			$fileForm->initData($args, $request);
		}

		$json = new JSON('true', $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * upload a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadFile(&$args, &$request) {
		$fileId = isset($args['rowId']) ? $args['rowId'] : null;
		$fileType = isset($args['fileType']) ? $args['fileType'] : null;
		$router =& $request->getRouter();
		import('controllers.grid.settings.library.form.FileForm');
		$fileForm = new FileForm($fileType, $fileId, true);
		$fileForm->readInputData();

		if ($fileForm->validate()) {
			$fileId = $fileForm->uploadFile($args, $request);
			// form validated and file uploaded successfully
			$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
			$libraryFile =& $libraryFileDao->getById($fileId);

			//$templateMgr =& TemplateManager::getManager();
			// FIXME: Display FileInfo in output?
			//$templateMgr->assign_by_ref('libraryFile', $libraryFile);
			//$templateMgr->display('controllers/grid/settings/library/form/fileInfo.tpl');
			$additionalAttributes = array(
				'deleteUrl' => $router->url($request, null, null, 'deleteFile', null, array('gridId' => $this->getId(), 'rowId' => $fileId))
			);
			$json = new JSON('true', Locale::translate('submission.uploadSuccessful'), 'false', $fileId, $additionalAttributes);
		} else {
			$json = new JSON('false', Locale::translate('common.uploadFailed'));
		}

		echo '<textarea>' . $json->getString() . '</textarea>';
	}

	/**
	 * Save the name attribute for a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function saveMetadata(&$args, &$request) {
		$fileId = $request->getUserVar('rowId');
		$name = $request->getUserVar('name');
		$fileType = isset($args['fileType']) ? $args['fileType'] : null;

		import('controllers.grid.settings.library.form.FileForm');
		$fileForm = new FileForm($fileType, $fileId);
		$fileForm->readInputData();

		if ($fileForm->validate()) {
			$libraryFile = $fileForm->execute($args, $request);

			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($fileId);
			$row->setData($libraryFile);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
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
	function deleteFile(&$args, &$request) {
		$fileId = isset($args['rowId']) ? $args['rowId'] : null;
		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		if($fileId) {
			import('classes.file.LibraryFileManager');
			$libraryFileManager = new LibraryFileManager($press->getId());
			$libraryFileManager->deleteFile($fileId);
			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}
		return $json->getString();
	}
}