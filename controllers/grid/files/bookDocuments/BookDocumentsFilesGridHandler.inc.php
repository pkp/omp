<?php

/**
 * @file controllers/grid/files/bookDocuments/BookDocumentsFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileGridHandler
 * @ingroup controllers_grid_files_bookDocuments
 *
 * @brief Handle book documents file grid requests.
 */

import('controllers.grid.files.LibraryFileGridHandler');
import('classes.monograph.MonographFile');
import('controllers.grid.files.bookDocuments.BookDocumentsFilesGridDataProvider');
import('controllers.grid.files.SubmissionFilesGridRow');

class BookDocumentsFilesGridHandler extends LibraryFileGridHandler {
	/**
	 * Constructor
	 */
	function BookDocumentsFilesGridHandler() {

		parent::LibraryFileGridHandler(new BookDocumentsFilesGridDataProvider());
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_AUTHOR),
			array('viewLibrary')
		);
	}


	//
	// Getters/Setters
	//

	/**
	 * Return the file type for this grid.  It will always
	 *  be MONOGRAPH_FILE_BOOK_DOCUMENT.
	 * @return int
	 */
	function getFileStage() {
		return MONOGRAPH_FILE_BOOK_DOCUMENT;
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

		// The file list grid layout has an additional file genre column.
		import('controllers.grid.files.fileList.FileGenreGridColumn');
		$this->addColumn(new FileGenreGridColumn());

		$router =& $request->getRouter();

		// Add grid-level actions
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$actionArgs = array(
			'monographId' => $monograph->getId(),
			'fileStage' => $this->getFileStage(),
		);

		import('controllers.api.file.linkAction.AddFileLinkAction');
		$this->addAction(
			new AddFileLinkAction(
				$request, $monograph->getId(), WORKFLOW_STAGE_ID_SUBMISSION,
				array_keys($this->getRoleAssignments()), $this->getFileStage()
			)
		);

		$this->addAction(
			new LinkAction(
				'viewLibrary',
				new AjaxModal(
					$router->url($request, null, null, 'viewLibrary', null, $actionArgs),
					__('grid.action.viewLibrary'),
					'modal_information'
				),
				__('grid.action.viewLibrary'),
				'more_info'
			)
		);

	}

	/**
	 * Implementation of the GridHandler::getRowInstance() method.
	 */
	function &getRowInstance() {
		$row = new SubmissionFilesGridRow(true, true, $this->getFileStage());
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
	 * Get an instance of the cell provider for this grid.
	 * Since these are monograph files, use the grid column that is
	 *  standard for submission grids.
	 * @return FileNameGridColumn
	 */
	function &getFileNameColumn() {
		// The file name column is common to all file grid types.
		import('controllers.grid.files.FileNameGridColumn');
		$column = new FileNameGridColumn(true, WORKFLOW_STAGE_ID_SUBMISSION);
		return $column;
	}
}

?>
