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


import('lib.pkp.controllers.grid.files.submissionDocuments.PKPSubmissionDocumentsFilesGridDataProvider');

class SubmissionDocumentsFilesGridDataProvider extends PKPSubmissionDocumentsFilesGridDataProvider {

	/**
	 * Constructor
	 */
	function SubmissionDocumentsFilesGridDataProvider() {
		parent::PKPSubmissionDocumentsFilesGridDataProvider();
	}

	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.SubmissionAccessPolicy');
		$policy = new SubmissionAccessPolicy($request, $args, $roleAssignments, 'submissionId');
		return $policy;
	}
}

?>
