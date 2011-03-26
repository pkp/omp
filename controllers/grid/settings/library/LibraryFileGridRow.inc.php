<?php

/**
 * @file controllers/grid/settings/library/LibraryFileGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileGridRow
 * @ingroup controllers_grid_settings_library
 *
 * @brief Handle library file grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');
import('lib.pkp.classes.linkAction.request.ConfirmationModal');

class LibraryFileGridRow extends GridRow {
	/** @var $fileType int LIBRARY_FILE_TYPE_... */
	var $fileType;

	/**
	 * Constructor
	 */
	function LibraryFileGridRow() {
		parent::GridRow();
	}

	//
	// Getters / setters
	//
	/**
	 * Get the file type for this row
	 * @return fileType
	 */
	function getFileType() {
		return $this->fileType;
	}

	function setFileType($fileType) {
		$this->fileType = $fileType;
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$this->setFileType($request->getUserVar('fileType'));

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		// Is this a new row or an existing row?
		$fileId = $this->getId();
		if (!empty($fileId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'fileId' => $fileId,
				'fileType' => $this->getFileType()
			);
			$this->addAction(
				new LinkAction(
					'editFile',
					new AjaxModal(
						$router->url($request, null, null, 'editFile', null, $actionArgs),
						__('grid.action.edit'),
						'edit'
					),
					__('grid.action.edit'),
					'edit'
				)
			);
			$this->addAction(
				new LinkAction(
					'deleteFile',
					new ConfirmationModal(
						__('common.confirmDelete'), null,
						$router->url($request, null, null, 'deleteFile', null, $actionArgs)
					),
					__('grid.action.delete'),
					'delete'
				)
			);
		}
	}
}

?>
