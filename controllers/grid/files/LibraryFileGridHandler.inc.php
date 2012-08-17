<?php

/**
 * @file controllers/grid/files/LibraryFileGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileGridHandler
 * @ingroup controllers_grid_files
 *
 * @brief Base class for handling library file grid requests.
 */

import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('controllers.grid.files.LibraryFileGridRow');
import('controllers.grid.files.LibraryFileGridCategoryRow');
import('classes.file.LibraryFileManager');


import('classes.press.LibraryFile');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class LibraryFileGridHandler extends CategoryGridHandler {
	/** the context for this grid */
	var $_context;

	/** whether or not the grid is editable **/
	var $_canEdit;

	/**
	 * Constructor
	 */
	function LibraryFileGridHandler($dataProvider) {
		parent::CategoryGridHandler($dataProvider);
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array(
				'fetchGrid', 'fetchCategory', 'fetchRow', // Parent grid-level actions
			)
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the context
	 * @return object context
	 */
	function getContext() {
		return $this->_context;
	}

	/**
	 * Can the user edit/add files in this grid?
	 * @return boolean
	 */
	function canEdit() {
		return $this->_canEdit;
	}

	/**
	 * Set whether or not the user can edit or add files.
	 * @param $canEdit boolean
	 */
	function setCanEdit($canEdit) {
		$this->_canEdit = $canEdit;
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
		$this->_context =& $router->getContext($request);

		// Set name and description
		$this->setTitle('manager.setup.library');
		$this->setInstructions('manager.setup.libraryDescription');

		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_APPLICATION_COMMON,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_OMP_MANAGER,
			LOCALE_COMPONENT_OMP_SUBMISSION
		);

		// Columns
		// Basic grid row configuration
		$this->addColumn($this->getFileNameColumn());
	}

	//
	// Implement template methods from CategoryGridHandler
	//
	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 */
	function &getCategoryRowInstance() {
		$row = new LibraryFileGridCategoryRow($this->getContext());
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {

		$context = $this->getContext();
		$libraryFileManager = new LibraryFileManager($context->getId());
		$fileTypeKeys = $libraryFileManager->getTypeSuffixMap();
		foreach (array_keys($fileTypeKeys) as $key) {
			$data[$key] = $key;
		}
		return $data;
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return LibraryFileGridRow
	 */
	function &getRowInstance() {
		$row = new LibraryFileGridRow($this->canEdit());
		return $row;
	}

	/**
	 * Get an instance of the cell provider for this grid.
	 * @return LibraryFileGridCellProvider
	 */
	function &getFileNameColumn() {
		import('controllers.grid.files.LibraryFileGridCellProvider');
		$column = new GridColumn(
			'files',
			'grid.libraryFiles.column.files',
			null,
			'controllers/grid/gridCell.tpl',
			new LibraryFileGridCellProvider()
		);

		return $column;
	}
}

?>
