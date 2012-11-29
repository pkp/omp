<?php

/**
 * @file controllers/grid/admin/press/PressGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressGridRow
 * @ingroup controllers_grid_admin_press
 *
 * @brief Press grid row definition
 */

import('lib.pkp.controllers.grid.admin.context.ContextGridRow');

class PressGridRow extends ContextGridRow {
	/**
	 * Constructor
	 */
	function PressGridRow() {
		parent::ContextGridRow();
	}


	//
	// Overridden methods from ContextGridRow
	//
	/**
	 * Get the delete context row locale key.
	 * @return string
	 */
	function getConfirmDeleteKey() {
		return 'admin.presses.confirmDelete';
	}
}

?>
