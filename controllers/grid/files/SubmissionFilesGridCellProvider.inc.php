<?php

/**
 * @file classes/controllers/grid/files/SubmissionFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridCellProvider
 * @ingroup controllers_grid_files
 *
 * @brief Class for a cell provider that can retrieve labels from submission files
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class SubmissionFilesGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function SubmissionFilesGridCellProvider() {
		parent::DataObjectGridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$monographFile =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($monographFile, 'MonographFile') && !empty($columnId));
		switch ($columnId) {
			case 'select':
				$flags = $column->getFlags();
				$selectedFileIds = isset($flags['selectedFileIds']) ? $flags['selectedFileIds'] : array();
				$selectName = isset($flags['selectName']) ? $flags['selectName'] : null;
				return array('rowId' => $monographFile->getFileId() . "-" . $monographFile->getRevision(), 'selectedFileIds' => $selectedFileIds, 'selectName' => $selectName);
		}
	}
}