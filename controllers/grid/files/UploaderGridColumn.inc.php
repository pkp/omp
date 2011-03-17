<?php

/**
 * @file controllers/grid/files/UploaderGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UploaderGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a grid column that displays uploader user groups.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class UploaderGridColumn extends GridColumn {

	/** @var UserGroup */
	var $_uploaderUserGroup;


	/**
	 * Constructor
	 * @param $uploaderUserGroup UserGroup The user
	 *  group to be represented in this column.
	 */
	function UploaderGridColumn(&$uploaderUserGroup) {
		$this->_uploaderUserGroup =& $uploaderUserGroup;

		// Configure the column.
		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn(
			'uploaderGroup-'.$uploaderUserGroup->getId(),
			null,
			$uploaderUserGroup->getLocalizedAbbrev(),
			'controllers/grid/common/cell/statusCell.tpl',
			$cellProvider
		);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the uploader user group.
	 * @return UserGroup
	 */
	function &getUploaderUserGroup() {
		return $this->_uploaderUserGroup;
	}


	//
	// Public methods
	//
	/**
	 * Method expected by ColumnBasedGridCellProvider
	 * to render a cell in this column.
	 *
	 * @see ColumnBasedGridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRow($row) {
		// Retrieve the monograph file.
		$monographFile =& $row->getData(); /* @var $monographFile MonographFile */
		assert(is_a($monographFile, 'MonographFile'));

		// By default we return an empty cell.
		$templateVars = array('status' => '');

		// Find out whether the uploader of the current file
		// belongs to the user group displayed in this column.
		$uploaderUserGroup =& $this->getUploaderUserGroup();
		if ($uploaderUserGroup->getId() == $monographFile->getUserGroupId()) {
			// Show that this column's user group is the uploading
			// user group.
			$templateVars = array('status' => 'uploaded');
		}

		return $templateVars;
	}
}

?>
