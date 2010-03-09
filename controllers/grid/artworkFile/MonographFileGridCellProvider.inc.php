<?php

/**
 * @file classes/controllers/grid/artworkFile/MonographFileGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileGridCellProvider
 * @ingroup controllers_grid_artworkFile
 *
 * @brief Subclass class for ArtworkFile grid column's cell provider
 */

import('controllers.grid.GridCellProvider');

class MonographFileGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function MonographFileGridCellProvider() {
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

		$monographFile =& $file->getFile();

		$label = $monographFile->getFilename();

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