<?php

/**
 * @file controllers/grid/files/bookDocuments/BookDocumentsFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookDocumentsFilesGridDataProvider
 * @ingroup controllers_grid_files_bookDocuments
 *
 * @brief The data provider for the book documents library files grid.
 */


import('lib.pkp.classes.controllers.grid.CategoryGridDataProvider');

class BookDocumentsFilesGridDataProvider extends CategoryGridDataProvider {

	/**
	 * Constructor
	 */
	function BookDocumentsFilesGridDataProvider() {
		parent::CategoryGridDataProvider();
	}


	//
	// Getters and Setters
	//

	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$policy = new OmpSubmissionAccessPolicy($request, $args, $roleAssignments);
		return $policy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
		);
	}

	/**
	 * get the current press context
	 * @return $context Press
	 */
	function &getContext() {
		return $this->_context;
	}


	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData(&$fileType, $filter = null) {

		// Retrieve all monograph files for the given book document category.
		$monograph =& $this->getMonograph();
		import('classes.monograph.MonographFile');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), MONOGRAPH_FILE_BOOK_DOCUMENT);
		$fileData = array();
		foreach ($monographFiles as $file) {
			if ($file->getLibraryCategoryId() != $fileType) continue;

			$fileData[$file->getFileId()] = array(
				'submissionFile' => $file
			);
			unset($file);
		}
		return $fileData;
	}
}

?>
