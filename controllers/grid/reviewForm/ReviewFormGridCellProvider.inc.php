<?php

/**
 * @file contorllers/grid/reviewForm/ReviewFormGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormGridCellProvider
 * @ingroup controllers_grid_reviewForm
 *
 * @brief Render the first column of the Review Form grid.
 */

import('controllers.grid.GridCellProvider');

class ReviewFormGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewFormGridCellProvider() {
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

		$reviewForm =& $row->getData();
		$label = $reviewForm->getLocalizedTitle();

		// Construct a default cell id
		$rowId = $reviewForm->getReviewFormId();
		assert(!empty($rowId));

		$row->setId($rowId);
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