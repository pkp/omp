<?php

/**
 * @file controllers/grid/files/UserGroupColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a grid column for a user group.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class UserGroupColumn extends GridColumn {

	/** @var UserGroup */
	var $_userGroup;


	/**
	 * Constructor
	 * @param $userGroup UserGroup The user
	 *  group to be represented in this column.
	 * @param $userGroupsPrefix string A prefix
	 *  that uniquely identifies the set of
	 *  user group columns.
	 */
	function UserGroupColumn(&$userGroup, $userGroupsPrefix) {
		$this->_userGroup =& $userGroup;

		// Configure the column.
		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn(
			$userGroupsPrefix.'Group-'.$userGroup->getId(),
			null,
			$userGroup->getLocalizedName(),
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
	function &getUserGroup() {
		return $this->_userGroup;
	}


	//
	// Public methods
	//
	/**
	 * Method expected by ColumnBasedGridCellProvider
	 * to render a cell in this column.
	 * @see ColumnBasedGridCellProvider::getTemplateVarsFromRowColumn()
	 * @param $row GridRow
	 */
	function getTemplateVarsFromRow($row) {
		// By default the cell is empty.
		return array('status' => '');
	}


	//
	// Protected helper methods
	//
	/**
	 * Get the monograph file from the row.
	 * @param $row GridRow
	 * @return MonographFile
	 */
	function &getMonographFile($row) {
		$submissionFileData =& $row->getData();
		assert(isset($submissionFileData['submissionFile']));
		$monographFile =& $submissionFileData['submissionFile']; /* @var $monographFile MonographFile */
		assert(is_a($monographFile, 'MonographFile'));
		return $monographFile;
	}
}