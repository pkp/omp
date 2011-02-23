<?php

/**
 * @file controllers/grid/files/FilesGridDataProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FilesGridDataProvider
 * @ingroup controllers_grid_files
 *
 * @brief Basic files grid data provider.
 */


import('lib.pkp.classes.controllers.grid.GridDataProvider');

class FilesGridDataProvider extends GridDataProvider {

	/**
	 * Constructor
	 */
	function FilesGridDataProvider() {
		parent::GridDataProvider();
	}


	//
	// Public helper methods
	//
	/**
	 * Configures and returns the action to add a file.
	 *
	 * NB: Must be overridden by subclasses (if implemented).
	 *
	 * @param $request Request
	 *
	 * @return AddFileLinkAction
	 */
	function &getAddFileAction($request) {
		assert(false);
	}

	/**
	 * Configures and returns the select files action.
	 *
	 * NB: Must be overridden by subclasses (if implemented).
	 *
	 * @param $request Request
	 *
	 * @return SelectFilesLinkAction
	 */
	function &getSelectAction($request) {
		assert(false);
	}

	/**
	 * Configures and returns the download all files action.
	 *
	 * @param $request Request
	 *
	 * @return DownoadAllLinkAction
	 */
	function &getDownloadAllAction($request) {
		import('controllers.grid.files.fileList.linkAction.DownloadAllLinkAction');
		$monograph =& $this->getMonograph();
		$downloadAllAction = new DownloadAllLinkAction($request, $monograph->getId());
		return $downloadAllAction;
	}


	//
	// Protected helper methods
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}
}