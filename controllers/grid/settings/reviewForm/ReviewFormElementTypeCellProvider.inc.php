<?php

/**
 * @file controllers/grid/settings/reviewForm/ReviewFormElementTypeCellProvider.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElementTypeCellProvider
 * @ingroup controllers_grid_settings_reviewForm
 *
 * @brief Render the 'type' column of the Review Form Element grid.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ReviewFormElementTypeCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewFormElementTypeCellProvider() {
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
		$typeLabel = $reviewFormElement->getElementType();

		$typeOptions =& $reviewFormElement->getReviewFormElementTypeOptions();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		$typeLabel = Locale::translate($typeOptions[$typeLabel]);

		// Construct a default cell id
		$rowId = $row->getId();
		assert(!empty($rowId));

		$row->setId($rowId);
		$cellId = $rowId.'-'.$columnId;

		// Pass control to the view to render the cell
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('id', $cellId);
		$templateMgr->assign('label', $typeLabel);
		$templateMgr->assign_by_ref('column', $column);
		$templateMgr->assign_by_ref('actions', $column->getActions());

		$template = $column->getTemplate();
		assert(!empty($template));
		return $templateMgr->fetch($template);
	}
}

?>
