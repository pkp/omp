<?php

/**
 * @file controllers/grid/settings/library/LibraryFileGridCellProvider.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileGridCellProvider
 * @ingroup controllers_grid_settings_library
 *
 * @brief Subclass class for a LibraryFile grid column's cell provider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class LibraryFileGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function LibraryFileGridCellProvider() {
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
			case 'files':
				$label = $element->getLocalizedName() != '' ? $element->getLocalizedName() : __('common.untitled');
				return array('label' => $label);
		}
	}
}

?>
