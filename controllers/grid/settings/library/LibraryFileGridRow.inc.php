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

class LibraryFileGridRow extends GridRow {
	/** the FileType for this grid */
	var $fileType;

	/**
	 * Constructor
	 */
	function LibraryFileGridRow() {
		parent::GridRow();
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
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);
		$this->setFileType($request->getUserVar('fileType'));

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);
			$this->addAction(
				new LegacyLinkAction(
					'editFile',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editFile', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				));
			$this->addAction(
				new LegacyLinkAction(
					'deleteFile',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteFile', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					Locale::translate('common.confirmDelete')
				));
		}
	}
}