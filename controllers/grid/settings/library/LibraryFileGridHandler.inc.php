<?php

/**
 * @file controllers/grid/settings/library/LibraryFileGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileGridHandler
 * @ingroup controllers_grid_settings_library
 *
 * @brief Handle library file grid requests.
 */

import('controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.library.LibraryFileGridRow');
import('classes.press.LibraryFile');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class LibraryFileGridHandler extends SetupGridHandler {
	/** the FileType for this grid */
	var $fileType;

	/**
	 * Constructor
	 */
	function LibraryFileGridHandler() {
		parent::SetupGridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array(
				'fetchGrid', 'fetchRow', // Grid-level actions
				'addFile', 'uploadFile', 'saveFile', // Adding new library files
				'editFile', 'updateFile', // Editing existing library files
				'deleteFile'
			)
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the file type
	 * @return int LIBRARY_FILE_TYPE_...
	 */
	function getFileType() {
		return $this->fileType;
	}

	/**
	 * Set the file type
	 * @param $fileType int LIBRARY_FILE_TYPE_...
	 */
	function setFileType($fileType)	{
		$this->fileType = (int) $fileType;
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		import('classes.file.LibraryFileManager');
		$libraryFileManager = new LibraryFileManager($context->getId());

		// Fetch and validate fileType (validated in getNameFromType)
		$fileType = (int) $request->getUserVar('fileType');
		$this->setFileType($fileType);
		$name = $libraryFileManager->getNameFromType($this->getFileType());

		// Basic grid configuration
		$this->setId('libraryFile' . ucwords(strtolower($name)));
		$this->setTitle("grid.libraryFiles.$name.title");

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_SUBMISSION));

		// Elements to be displayed in the grid
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$libraryFiles =& $libraryFileDao->getByPressId($context->getId(), $this->getFileType());
		$this->setGridDataElements($libraryFiles);

		// Add grid-level actions
		$this->addAction(
			new LinkAction(
				'addFile',
				new AjaxModal(
					$router->url($request, null, null, 'addFile', null, array('fileType' => $this->getFileType())),
					__('grid.action.addItem'),
					'fileManagement'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		// Basic grid row configuration
		import('controllers.grid.settings.library.LibraryFileGridCellProvider');
		$this->addColumn(
			new GridColumn(
				'files',
				'grid.libraryFiles.column.files',
				null,
				'controllers/grid/gridCell.tpl',
				new LibraryFileGridCellProvider()
			)
		);
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

	/**
	 * @see GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		$requestArgs = array_merge(parent::getRequestArgs(), array('fileType' => $this->getFileType()));
		return $requestArgs;
	}


	//
	// Public File Grid Actions
	//
	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addFile($args, &$request) {
		$this->initialize($request);
		$router = $request->getRouter();
		$context = $request->getContext();

		import('controllers.grid.settings.library.form.NewLibraryFileForm');
		$fileForm = new NewLibraryFileForm($context->getId(), $this->getFileType());
		$fileForm->initData();

		$json = new JSONMessage(true, $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a new library file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadFile($args, &$request) {
		$router =& $request->getRouter();
		$context = $request->getContext();
		$user =& $request->getUser();

		import('classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
		if ($temporaryFile) {
			$json = new JSONMessage(true);
			$json->setAdditionalAttributes(array(
				'temporaryFileId' => $temporaryFile->getId()
			));
		} else {
			$json = new JSONMessage(false, __('common.uploadFailed'));
		}

		return $json->getString();
	}

	/**
	 * Save a new library file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function saveFile($args, &$request) {
		$router =& $request->getRouter();
		$context = $request->getContext();
		$user =& $request->getUser();

		import('controllers.grid.settings.library.form.NewLibraryFileForm');
		$fileForm = new NewLibraryFileForm($context->getId(), $this->getFileType());
		$fileForm->readInputData();

		if ($fileForm->validate()) {
			$fileId = $fileForm->execute($user->getId());

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent($fileId);
		}

		return new JSONMessage(false);
	}

	/**
	 * An action to add a new file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editFile($args, &$request) {
		$this->initialize($request);
		assert(isset($args['fileId']));
		$fileId = (int) $args['fileId'];

		$router = $request->getRouter();
		$context = $request->getContext();

		import('controllers.grid.settings.library.form.EditLibraryFileForm');
		$fileForm = new EditLibraryFileForm($context->getId(), $this->getFileType(), $fileId);
		$fileForm->initData();

		$json = new JSONMessage(true, $fileForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save changes to an existing library file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateFile($args, &$request) {
		assert(isset($args['fileId']));
		$fileId = (int) $args['fileId'];

		$router =& $request->getRouter();
		$context = $request->getContext();

		import('controllers.grid.settings.library.form.EditLibraryFileForm');
		$fileForm = new EditLibraryFileForm($context->getId(), $this->getFileType(), $fileId);
		$fileForm->readInputData();

		if ($fileForm->validate()) {
			$fileForm->execute();

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent($fileId);
		}

		$json = new JSONMessage(false);
		return $json->getString();
	}

	/**
	 * Delete a file
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteFile($args, &$request) {
		$fileId = isset($args['fileId']) ? $args['fileId'] : null;
		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		if ($fileId) {
			import('classes.file.LibraryFileManager');
			$libraryFileManager = new LibraryFileManager($press->getId());
			$libraryFileManager->deleteFile($fileId);

			return DAO::getDataChangedEvent($fileId);
		}

		$json = new JSONMessage(false);
		return $json->getString();
	}
}

?>
