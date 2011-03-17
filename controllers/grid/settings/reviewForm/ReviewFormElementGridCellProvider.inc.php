<?php

/**
 * @file controllers/grid/settings/reviewForm/ReviewFormElementGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElementGridCellProvider
 * @ingroup controllers_grid_reviewForm
 *
 * @brief Render the first column of the Review Form Element grid.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ReviewFormElementGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewFormElementGridCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $element mixed
	 * @param $columnId string
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'titles':
				$label = String::substr($element->getLocalizedQuestion(), 0, 20);
				return array('label' => $label);
		}
	}
}

?>
