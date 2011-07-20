<?php

/**
 * @file controllers/grid/files/copyedit/CopyeditingFilesGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesGridCategoryRow
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief CopyeditingFiles grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

class CopyeditingFilesGridCategoryRow extends GridCategoryRow {
	/** @var $_monograph Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function CopyeditingFilesGridCategoryRow(&$monograph) {
		parent::GridCategoryRow();
		$this->_monograph =& $monograph;
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

			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $this->_monograph->getId(),
				'fileId' => $fileId
			);

			// Add the row actions.
			// FIXME: Not all roles should see this action. Bug #5975.
			import('controllers.api.file.linkAction.DeleteFileLinkAction');
			$this->addAction(new DeleteFileLinkAction($request, $monographFile, ''));

			// The title should link to a download action.
			import('controllers.api.file.linkAction.DownloadFileLinkAction');
			$this->addAction(new DownloadFileLinkAction($request, $monographFile));
		}
	}
}

?>
