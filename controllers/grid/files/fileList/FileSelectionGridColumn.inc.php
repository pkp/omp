<?php
/**
 * @file controllers/grid/files/fileList/FileSelectionGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileSelectionGridColumn
 * @ingroup controllers_grid_files_fileList
 *
 * @brief Implements a column with checkboxes to select files.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class FileSelectionGridColumn extends GridColumn {
	/** @var array */
	var $_selectedFileIds;

	/** @var string */
	var $_selectName;

	/**
	 * Constructor
	 * @param $selectedFileIds array The ids of pre-selected files.
	 * @param $selectName string The name of the form parameter
	 *  to which the selected files will be posted.
	 */
	function FileSelectionGridColumn($selectedFileIds, $selectName) {
		assert(is_array($selectedFileIds));
		$this->_selectedFileIds = $selectedFileIds;
		assert(is_string($selectName) && !empty($selectName));
		$this->_selectName = $selectName;

		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn('select', 'common.select', null, 'controllers/grid/gridRowSelectInput.tpl', $cellProvider);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the selected file ids.
	 * @return array
	 */
	function getSelectedFileIds() {
		return $this->_selectedFileIds;
	}

	/**
	 * Get the select name.
	 * @return string
	 */
	function getSelectName() {
		return $this->_selectName;
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
		// Return the selection information.
		$monographFile =& $row->getData(); /* @var $monographFile MonographFile */
		assert(is_a($monographFile, 'MonographFile'));
		$fileIdAndRevision = $monographFile->getFileId().'-'.$monographFile->getRevision();
		$selected = in_array($fileIdAndRevision, $this->getSelectedFileIds());
		return array(
			'elementId' => $fileIdAndRevision,
			'selectName' => $this->getSelectName(),
			'selected' => $selected);
	}
}