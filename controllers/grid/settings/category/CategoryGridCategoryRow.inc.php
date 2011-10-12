<?php

/**
 * @file controllers/grid/settings/roles/CategoryGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryGridCategoryRow
 * @ingroup controllers_grid_settings_category
 *
 * @brief Category grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');

class CategoryGridCategoryRow extends GridCategoryRow {
	/**
	 * Constructor
	 */
	function CategoryGridCategoryRow() {
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridCategoryRow
	//

	/**
	 * Category rows only have one cell and one label.  This is it.
	 * return string
	 */
	function getCategoryLabel() {
		$data =& $this->getData();
		return $data->getLocalizedTitle();
	}
}

?>
