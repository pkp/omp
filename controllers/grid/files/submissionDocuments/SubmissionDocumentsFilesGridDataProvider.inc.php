<?php

/**
 * @file controllers/grid/files/submissionDocuments/SubmissionDocumentsFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionDocumentsFilesGridDataProvider
 * @ingroup controllers_grid_files_submissionDocuments
 *
 * @brief The data provider for the submission documents library files grid.
 */


import('lib.pkp.classes.controllers.grid.CategoryGridDataProvider');

class SubmissionDocumentsFilesGridDataProvider extends CategoryGridDataProvider {

	/**
	 * Constructor
	 */
	function SubmissionDocumentsFilesGridDataProvider() {
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
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData(&$fileType, $filter = null) {

		// Retrieve all library files for the given submission document category.
		$monograph =& $this->getMonograph();
		import('classes.press.LibraryFile');
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO'); /* @var $libraryFileDao LibraryFileDAO */
		$libraryFiles =& $libraryFileDao->getByMonographId($monograph->getId(), $fileType);

		return $libraryFiles->toAssociativeArray();
	}
}

?>
