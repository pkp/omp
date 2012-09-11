<?php

/**
 * @file controllers/grid/settings/library/LibraryFileAdminGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileAdminGridHandler
 * @ingroup controllers_grid_settings_library
 *
 * @brief Handle library file grid requests.
 */

import('controllers.grid.files.LibraryFileGridHandler');
import('controllers.grid.settings.library.LibraryFileAdminGridDataProvider');


class LibraryFileAdminGridHandler extends LibraryFileGridHandler {
	/**
	 * Constructor
	 */
	function LibraryFileAdminGridHandler() {

		parent::LibraryFileGridHandler(new LibraryFileAdminGridDataProvider(true));
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array(
				'addFile', 'uploadFile', 'saveFile', // Adding new library files
				'editFile', 'updateFile', // Editing existing library files
				'deleteFile'
			)
		);
	}


	//
	// Getters/Setters
	//


	//
	// Overridden template methods
	//

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {

		parent::initialize($request);
		// determine if this grid is read only.
		$this->setCanEdit((boolean) $request->getUserVar('canEdit'));

		// Set instructions
		$this->setInstructions('manager.setup.libraryDescription');

		$router =& $request->getRouter();

		// Add grid-level actions
		if ($this->canEdit()) {
			$this->addAction(
				new LinkAction(
					'addFile',
					new AjaxModal(
							$router->url($request, null, null, 'addFile'),
							__('grid.action.addFile'),
							'modal_add_file'
					),
					__('grid.action.addFile'),
					'add'
				)
			);
		}
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
		$fileForm = new NewLibraryFileForm($context->getId());
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
		$fileForm = new NewLibraryFileForm($context->getId());
		$fileForm->readInputData();

		if ($fileForm->validate()) {
			$fileId = $fileForm->execute($user->getId());

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent();
		}

		$json = new JSONMessage(false);
		return $json->getString();
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
		$fileForm = new EditLibraryFileForm($context->getId(), $fileId);
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
		$fileForm = new EditLibraryFileForm($context->getId(), $fileId);
		$fileForm->readInputData();

		if ($fileForm->validate()) {
			$fileForm->execute();

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent();
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

			return DAO::getDataChangedEvent();
		}

		$json = new JSONMessage(false);
		return $json->getString();
	}
}

?>
