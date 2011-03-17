<?php

/**
 * @file controllers/grid/settings/masthead/MastheadGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid_settings_masthead
 *
 * @brief Subclass class for a Masthead grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class MastheadGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function MastheadGridCellProvider() {
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
			case 'groups':
				$label = $element->getLocalizedTitle();
				return array('label' => $label);
		}
	}
}

?>
