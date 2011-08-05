<?php

/**
 * @file controllers/grid/files/FilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
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
	 * Rearrange file revisions by file id and return the file
	 * data wrapped into an array so that grid implementations
	 * can add further data.
	 * @param $revisions array
	 * @return array
	 */
	function &prepareSubmissionFileData(&$revisions) {
		// Rearrange the files as required by submission file grids.
		$submissionFileData = array();
		foreach ($revisions as $revision) {
			$submissionFileData[$revision->getFileId()] = array(
				'submissionFile' => $revision
			);
		}
		return $submissionFileData;
	}
}

?>
