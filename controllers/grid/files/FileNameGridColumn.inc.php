<?php

/**
 * @file controllers/grid/files/FileNameGridColumn.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileNameGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a file name column.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class FileNameGridColumn extends GridColumn {

	/** @var integer */
	var $_stageId;


	/**
	 * Constructor
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function FileNameGridColumn($stageId) {
		$this->_stageId = (int)$stageId;
		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn('name',	'common.name', null, 'controllers/grid/gridCell.tpl', $cellProvider);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the workflow stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
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
		// We do not need any template variables because
		// the only content of this column's cell will be
		// an action. See FileNameGridColumn::getCellActions().
		return array();
	}


	//
	// Override methods from GridColumn
	//
	/**
	 * @see GridColumn::getCellActions()
	 */
	function getCellActions(&$request, &$row, $position = GRID_ACTION_POSITION_DEFAULT) {
		// Create the cell action to download a file.
		import('controllers.api.file.linkAction.DownloadFileLinkAction');
		$cellActions = parent::getCellActions($request, $row, $position);
		$cellActions[] = new DownloadFileLinkAction($request, $row->getData(), $this->getStageId());
		return $cellActions;
	}
}

?>
