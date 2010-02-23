<?php

/**
 * @file controllers/grid/reviewForm/ReviewFormElementGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElementGridCellProvider
 * @ingroup controllers_grid_reviewForm
 *
 * @brief Render the first column of the Review Form Element grid.
 */

import('controllers.grid.GridCellProvider');

class ReviewFormElementGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewFormElementGridCellProvider() {
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

		$reviewFormElement =& $row->getData();
		$questionLabel = $reviewFormElement->getLocalizedQuestion();

		// Construct a default cell id
		$rowId = $reviewFormElement->getReviewFormId();
		assert(!empty($rowId));

		$cellId = $rowId.'-'.$columnId;

		// Pass control to the view to render the cell
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('id', $cellId);
		$templateMgr->assign('label', substr($questionLabel, 0, 20));
		$templateMgr->assign_by_ref('column', $column);
		$templateMgr->assign_by_ref('actions', $column->getActions());

		$template = $column->getTemplate();
		assert(!empty($template));
		return $templateMgr->fetch($template);
	}
}