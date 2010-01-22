<?php

/**
 * @file controllers/grid/library/LibraryFileGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileGridHandler
 * @ingroup controllers_grid_file
 *
 * @brief Handle file grid requests.
 */

import('controllers.grid.GridMainHandler');

class LibraryFileGridHandler extends GridMainHandler {
	/** the FileType for this grid */
	var $fileType;
	
	/**
	 * Constructor
	 */
	function LibraryFileGridHandler() {
		parent::GridMainHandler();
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
	
	/**
	 * Get the row handler - override the default row handler
	 * @return FileRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandler) {
			import('controllers.grid.library.LibraryFileRowHandler');
			$rowHandler =& new LibraryFileRowHandler();
			$this->setRowHandler($rowHandler);
		}
		return parent::getRowHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addFile'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		// Basic grid configuration
		$this->setFileType($request->getUserVar('fileType'));
		$this->setId('libraryFile' . ucwords(strtolower($this->getFileType())));
		$this->setTitle('grid.libraryFiles.' . $this->getFileType() . '.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$libraryFiles =& $libraryFileDao->getByPressId($context->getId(), $this->getFileType());
		$this->setData($libraryFiles);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new GridAction(
				'addFile',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addFile', null, array('gridId' => $this->getId(), 'fileType' => $this->getFileType())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		parent::initialize($request);
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
		// Delegate to the row handler
		import('controllers.grid.library.LibraryFileRowHandler');
		$libraryFileRow =& new LibraryFileRowHandler();

		// Calling editSponsor with an empty row id will add
		// a new sponsor.
		$libraryFileRow->editFile($args, $request);
	}	
}