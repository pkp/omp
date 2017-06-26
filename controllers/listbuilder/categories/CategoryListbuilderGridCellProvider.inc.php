<?php

/**
 * @file controllers/listbuilder/categories/CategoryListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryListbuilderGridCellProvider
 * @ingroup controllers_listbuilder
 *
 * @brief Class for a cell provider that provides category information
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class CategoryListbuilderGridCellProvider extends GridCellProvider {

	//
	// Template methods from GridCellProvider
	//
	/**
	 * This implementation assumes a simple data element array that
	 * has column ids as keys.
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$category = $row->getData();
		$columnId = $column->getId();
		assert((is_a($category, 'Category')) && !empty($columnId));

		return array('labelKey' => $category->getId(), 'label' => $category->getLocalizedTitle());
	}
}

?>
