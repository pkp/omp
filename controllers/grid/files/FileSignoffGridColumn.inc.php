<?php

/**
 * @file controllers/grid/files/FileSignoffGridColumn.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileSignoffGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a grid column that displays file signoffs.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class FileSignoffGridColumn extends GridColumn {

	/** @var UserGroup */
	var $_signoffUserGroup;


	/**
	 * Constructor
	 * @param $signoffUserGroup UserGroup The user
	 *  group to be represented in this column.
	 */
	function FileSignoffGridColumn(&$signoffUserGroup) {
		// Configure the column.
		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn(
			'signoff-'.$signoffUserGroup->getId(),
			null,
			$signoffUserGroup->getLocalizedAbbrev(),
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
	function &getsignoffUserGroup() {
		return $this->_signoffUserGroup;
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
		$signoffUserGroup =& $this->getsignoffUserGroup();
		if ($signoffUserGroup->getId() == $monographFile->getUserGroupId()) {
			// Show that this column's user group is the uploading
			// user group.
			$templateVars = array('status' => 'uploaded');
		}

		return $templateVars;
	}
}

?>
