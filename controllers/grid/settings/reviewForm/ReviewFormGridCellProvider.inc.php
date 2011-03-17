<?php

/**
 * @file contorllers/grid/settings/reviewForm/ReviewFormGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormGridCellProvider
 * @ingroup controllers_grid_settings_reviewForm
 *
 * @brief Render the first column of the Review Form grid.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ReviewFormGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewFormGridCellProvider() {
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
				$label = $element->getLocalizedTitle();
				return array('label' => $label);
		}
	}
}

?>
