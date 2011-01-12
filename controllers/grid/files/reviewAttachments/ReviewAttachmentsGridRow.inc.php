<?php

/**
 * @file controllers/grid/files/reviewAttachments/ReviewAttachmentsGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileRow
 * @ingroup controllers_grid_files_reviewAttachments
 *
 * @brief Handle file grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ReviewAttachmentsGridRow extends GridRow {
	/** the FileType for this grid */
	var $fileType;

	/** boolean flag to make grid read only **/
	var $_readOnly;

	/**
	 * Constructor
	 */
	function ReviewAttachmentsGridRow() {
		parent::GridRow();
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
	 * Set the boolean flag to make grid read only
	 * @param $readOnly bool
	 */
	function setReadOnly($readOnly) {
		$this->_readOnly = $readOnly;
	}

	/**
	 * Get the boolean flag to make grid read only
	 * @return bool
	 */
	function getReadOnly() {
		return $this->_readOnly;
	}

	/**
	 * Set the selectable flag
	 * @param $isSelectable bool
	 */
	function setIsSelectable($isSelectable) {
		$this->_isSelectable = $isSelectable;
	}

	/**
	 * Get the selectable flag
	 * @return bool
	 */
	function getIsSelectable() {
		return $this->_isSelectable;
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
		$this->setReadOnly($request->getUserVar('readOnly')?true:false);
		$this->setIsSelectable($request->getUserVar('isSelectable')?true:false);

		if ( !$this->getReadOnly() ) {
			// add Grid Row Actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}

		// Retrieve the monograph id from the request
		$monographId = $request->getUserVar('monographId');
		assert(is_numeric($monographId));

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId) && !$this->getReadOnly()) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId,
				'monographId' => $monographId
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
						'delete'
					));

		}
	}
}