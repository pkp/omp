<?php

/**
 * @file controllers/grid/files/submissionDocuments/SubmissionDocumentsFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileGridHandler
 * @ingroup controllers_grid_files_submissionDocuments
 *
 * @brief Handle submission documents file grid requests.
 */

import('controllers.grid.files.LibraryFileGridHandler');
import('classes.monograph.MonographFile');
import('controllers.grid.files.submissionDocuments.SubmissionDocumentsFilesGridDataProvider');

class SubmissionDocumentsFilesGridHandler extends LibraryFileGridHandler {
	/**
	 * Constructor
	 */
	function SubmissionDocumentsFilesGridHandler() {

		parent::LibraryFileGridHandler(new SubmissionDocumentsFilesGridDataProvider());
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_AUTHOR),
			array(
				'addFile', 'uploadFile', 'saveFile', // Adding new library files
				'editFile', 'updateFile', // Editing existing library files
				'deleteFile', 'viewLibrary'
			)
		);
	}


	//
	// Overridden template methods
	//

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		$this->setCanEdit(true); // this grid can always be edited.
		parent::initialize($request);

		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_EDITOR);

		// Set instructions
		$this->setInstructions('editor.submissionLibrary.description');

		$router =& $request->getRouter();

		// Add grid-level actions

		if ($this->canEdit()) {
			$this->addAction(
				new LinkAction(
					'addFile',
					new AjaxModal(
						$router->url($request, null, null, 'addFile', null, $this->getActionArgs()),
						__('grid.action.addFile'),
						'modal_add_file'
					),
					__('grid.action.addFile'),
					'add'
				)
			);
		}

		$this->addAction(
			new LinkAction(
				'viewLibrary',
				new AjaxModal(
					$router->url($request, null, null, 'viewLibrary', null, $this->getActionArgs()),
					__('grid.action.viewLibrary'),
					'modal_information'
				),
				__('grid.action.viewLibrary'),
				'more_info'
			)
		);

	}

	/**
	 * Retrieve the arguments for the 'add file' action.
	 * @return array
	 */
	function getActionArgs() {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$actionArgs = array(
			'monographId' => $monograph->getId(),
		);

		return $actionArgs;
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return LibraryFileGridRow
	 */
	function &getRowInstance() {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$row = new LibraryFileGridRow($this->canEdit(), $monograph);
		return $row;
	}

	//
	// Public File Grid Actions
	//

	/**
	 * Load the (read only) press file library.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function viewLibrary($args, &$request) {

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('canEdit', false);
		return $templateMgr->fetchJson('controllers/tab/settings/library.tpl');
	}

	/**
	 * Returns a specific instance of the new form for this grid.
	 * @param $context Press
	 * @return NewLibraryFileForm
	 */
	function &_getNewFileForm($context) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		import('controllers.grid.files.submissionDocuments.form.NewLibraryFileForm');
		$fileForm = new NewLibraryFileForm($context->getId(), $monograph->getId());
		return $fileForm;
	}

	/**
	 * Returns a specific instance of the edit form for this grid.
	 * @param $context Press
	 * @param $fileId int
	 * @return EditLibraryFileForm
	 */
	function &_getEditFileForm($context, $fileId) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		import('controllers.grid.files.submissionDocuments.form.EditLibraryFileForm');
		$fileForm = new EditLibraryFileForm($context->getId(), $fileId, $monograph->getId());
		return $fileForm;
	}
}

?>
