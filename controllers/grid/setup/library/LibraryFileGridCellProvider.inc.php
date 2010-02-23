<?php

/**
 * @file classes/controllers/grid/column/GridCellProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid_libraryFile
 *
 * @brief Subclass class for a LibraryFile grid column's cell provider
 */

import('controllers.grid.GridCellProvider');

class LibraryFileGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function LibraryFileGridCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * To be used by a GridRow to generate a rendered representation of
	 * the element for the given column.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return string the rendered representation of the element for the given column
	 */
	function render(&$row, &$column) {
		$columnId = $column->getId();
		assert(!empty($columnId));

		// Assume an array element by default
		$file =& $row->getData();
		$label = $file->getFileName();

		// Construct a default cell id
		$rowId = $row->getId();
		assert(!empty($rowId));
		$cellId = $rowId.'-'.$columnId;

		// Pass control to the view to render the cell
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('id', $cellId);
		$templateMgr->assign('label', $label);
		$templateMgr->assign_by_ref('column', $column);
		$templateMgr->assign_by_ref('actions', $column->getActions());

		$template = $column->getTemplate();
		assert(!empty($template));
		return $templateMgr->fetch($template);
	}
}