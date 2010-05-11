<?php

/**
 * @file controllers/grid/files/editorReviewFileSelection/EditorReviewFileSelectionGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileRow
 * @ingroup controllers_grid_file
 *
 * @brief Handle file grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class EditorReviewFileSelectionGridRow extends GridRow {


	/**
	 * Constructor
	 */
	function EditorReviewFileSelectionGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRow.tpl');


	}
}