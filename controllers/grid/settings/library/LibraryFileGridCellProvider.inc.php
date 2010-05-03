<?php

/**
 * @file classes/controllers/grid/column/GridCellProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid_libraryFile
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
	 * @param $element mixed
	 * @param $columnId string
	 * @return array
	 */
	function getTemplateVarsFromElement(&$element, $columnId) {
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'files':
				$label = $element->getLocalizedName() != '' ? $element->getLocalizedName() : Locale::translate('common.untitled');
				return array('label' => $label);
		}
	}
}