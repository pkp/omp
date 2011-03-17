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

	/* @var integer */
	var $_uploaderRoles;


	/**
	 * Constructor
	 */
	function FilesGridDataProvider() {
		parent::GridDataProvider();
	}


	//
	// Getters and Setters
	//
	/**
	 * Set the uploder roles.
	 * @param $roleAssignments array The grid's
	 *  role assignment from which the uploader roles
	 *  will be extracted.
	 */
	function setUploaderRoles($roleAssignments) {
		$this->_uploaderRoles = array_keys($roleAssignments);
	}

	/**
	 * Get the uploader roles.
	 * @return array
	 */
	function getUploaderRoles() {
		assert(is_array($this->_uploaderRoles) && !empty($this->_uploaderRoles));
		return $this->_uploaderRoles;
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

	/**
	 * Rearrange file revisions by file id.
	 * @param $revisions array
	 * @return array
	 */
	function &getRevisionsByFileId(&$revisions) {
		// Rearrange the files by file id as required by the grid.
		$files = array();
		foreach ($revisions as $revision) {
			$files[$revision->getFileId()] = $revision;
		}
		return $files;
	}
}

?>
