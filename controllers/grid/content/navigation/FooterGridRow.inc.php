<?php

/**
 * @file controllers/grid/content/navigation/FooterGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterGridRow
 * @ingroup controllers_grid_content_navigation
 *
 * @brief Footer grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class FooterGridRow extends GridRow {
	/** @var Press **/
	var $_press;

	/**
	 * Constructor
	 */
	function FooterGridRow(&$press) {
		$this->setPress($press);
		parent::GridRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization.  No actions, since link modifications occur in the listbuilder.
		parent::initialize($request);
	}

	/**
	 * Get the press for this row (already authorized)
	 * @return Press
	 */
	function getPress() {
		return $this->_press;
	}

	/**
	 * Set the press for this row (already authorized)
	 * @return Press
	 */
	function setPress($press) {
		$this->_press =& $press;
	}
}
?>
