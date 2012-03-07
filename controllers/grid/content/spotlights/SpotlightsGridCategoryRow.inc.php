<?php

/**
 * @file controllers/grid/content/spotlights/SpotlightsGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridCategoryRow
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Spotlights grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');

class SpotlightsGridCategoryRow extends GridCategoryRow {

	/**
	 * Constructor
	 */
	function SpotlightsGridCategoryRow() {
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
		return __($data['name']);
	}
}
?>