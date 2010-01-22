<?php

/**
 * @file classes/controllers/grid/column/GridCellProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid_masthead
 *
 * @brief Subclass class for a Masthead grid column's cell provider
 */

import('controllers.grid.GridCellProvider');

class MastheadGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function MastheadGridCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * To be used by a GridRowHandler to generate a rendered representation of
	 * the element for the given column.
	 * @param $row GridRowHandler
	 * @param $column GridColumn
	 * @return string the rendered representation of the element for the given column
	 */
	function render(&$row, &$column) {
		$columnId = $column->getId();
		assert(!empty($columnId));

		// Assume an array element by default
		$group =& $row->getData();
		$label = $group->getLocalizedTitle();

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