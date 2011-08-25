<?php

/**
 * @file controllers/grid/files/signoff/SignoffFilesGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffFilesGridCategoryRow
 * @ingroup controllers_grid_files_signoff
 *
 * @brief A category row containing the file that users are asked to signoff on.
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

class SignoffFilesGridCategoryRow extends GridCategoryRow {
	/**
	 * Constructor
	 */
	function SignoffFilesGridCategoryRow() {
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridCategoryRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Is this a new row or an existing row?
		$fileId = $this->getId();
		if (!empty($fileId) && is_numeric($fileId)) {
			$monographFile =& $this->getData();

			// Add the row actions.
			import('controllers.api.file.linkAction.DeleteFileLinkAction');
			$this->addAction(new DeleteFileLinkAction($request, $monographFile, ''));

			// The title should link to a download action.
			import('controllers.api.file.linkAction.DownloadFileLinkAction');
			$this->addAction(new DownloadFileLinkAction($request, $monographFile));
		}

		// Set the no-row locale key
		$this->setEmptyCategoryRowText('editor.monograph.noAuditRequested');
	}
}

?>
