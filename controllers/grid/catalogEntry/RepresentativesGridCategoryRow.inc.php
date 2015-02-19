<?php

/**
 * @file controllers/grid/catalogEntry/RepresentativesGridCategoryRow.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RepresentativesGridCategoryRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Representatives grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');

class RepresentativesGridCategoryRow extends GridCategoryRow {

	/**
	 * Constructor
	 */
	function RepresentativesGridCategoryRow() {
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
		$data = $this->getData();
		return __($data['name']);
	}
}
?>
