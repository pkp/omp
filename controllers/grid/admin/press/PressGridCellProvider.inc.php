<?php

/**
 * @file controllers/grid/admin/press/PressGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressGridCellProvider
 * @ingroup controllers_grid_admin_press
 *
 * @brief Subclass for a press grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class PressGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function PressGridCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'name':
				$label = $element->getLocalizedName() != '' ? $element->getLocalizedName() : Locale::translate('common.untitled');
				return array('label' => $label);
				break;
			case 'path':
				$label = $element->getPath();
				return array('label' => $label);
				break;
			default:
				break;
		}
	}
}

?>
