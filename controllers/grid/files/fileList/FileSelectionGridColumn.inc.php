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

	/** @var string */
	var $_selectName;


	/**
	 * Constructor
	 * @param $selectName string The name of the form parameter
	 *  to which the selected files will be posted.
	 */
	function FileSelectionGridColumn($selectName) {
		assert(is_string($selectName) && !empty($selectName));
		$this->_selectName = $selectName;

		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn('select', 'common.select', null, 'controllers/grid/gridRowSelectInput.tpl', $cellProvider,
				array('width' => 5));
	}


	//
	// Getters and Setters
	//
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
		// Retrieve the file data.
		$submissionFileData =& $row->getData();

		// Retrieve the monograph file.
		assert(isset($submissionFileData['submissionFile']));
		$monographFile =& $submissionFileData['submissionFile']; /* @var $monographFile MonographFile */
		assert(is_a($monographFile, 'MonographFile'));

		// Return the data expected by the column's cell template.
		assert(isset($submissionFileData['selected']));
		return array(
			'elementId' => $monographFile->getFileIdAndRevision(),
			'selectName' => $this->getSelectName(),
			'selected' => $submissionFileData['selected']);
	}
}

?>
