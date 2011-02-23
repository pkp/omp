<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridRow
 * @ingroup controllers_grid_files
 *
 * @brief Handle submission file grid row requests.
 */

// Import grid base classes.
import('lib.pkp.classes.controllers.grid.GridRow');

class SubmissionFilesGridRow extends GridRow {

	/**
	 * Constructor
	 */
	function SubmissionFilesGridRow() {
		parent::GridRow();
	}


	//
	// Overridden template methods from GridRow
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $template = 'controllers/grid/gridRowWithActions.tpl') {
		parent::initialize($request, $template);

		// Retrieve the monograph file.
		$monographFile =& $this->getData(); /* @var $monographFile MonographFile */
		assert(is_a($monographFile, 'MonographFile'));

		// File grid row actions:
		// 1) Delete file action.
		import('controllers.api.file.linkAction.DeleteFileLinkAction');
		$this->addAction(new DeleteFileLinkAction($request, $monographFile));

		// 2) Information center action.
		import('controllers.informationCenter.linkAction.FileInfoCenterLinkAction');
		$this->addAction(new FileInfoCenterLinkAction($request, $monographFile));
	}
}
