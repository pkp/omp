<?php

/**
 * @file controllers/listbuilder/settings/CategoriesListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoriesListbuilderGridCellProvider
 * @ingroup controllers_listbuilder_settings
 *
 * @brief Base class for a cell provider that can retrieve labels from arrays
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class CategoriesListbuilderGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function CategoriesListbuilderGridCellProvider() {
		parent::GridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * This implementation assumes a simple data element array that
	 * has column ids as keys.
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$category =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($category, 'Category') && !empty($columnId));
		switch ($columnId) {
			case 'title':
				return array('labelKey' => $category->getId(), 'label' => $category->getTitle(null));
		}
		// we got an unexpected column
		assert(false);
	}
}

?>
