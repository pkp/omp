<?php

/**
 * @file controllers/grid/files/attachment/AuthorReviewAttachmentsGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorReviewAttachmentsGridHandler
 * @ingroup controllers_grid_files_attachment
 *
 * @brief Handle review attachment grid requests (author's perspective)
 */

import('lib.pkp.classes.controllers.grid.GridRow');
import('controllers.grid.files.attachment.ReviewAttachmentsGridHandler');

class AuthorReviewAttachmentsGridHandler extends ReviewAttachmentsGridHandler {
	/**
	 * Constructor
	 */
	function AuthorReviewAttachmentsGridHandler() {
		parent::ReviewAttachmentsGridHandler(false);
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_AUTHOR),
				array('fetchGrid', 'downloadFile'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Select the files to load in the grid
	 * @see SubmissionFilesGridHandler::loadMonographFiles()
	 */
	function loadMonographFiles() {
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), MONOGRAPH_FILE_ATTACHMENT);

		$rowData = array();
		foreach ($monographFiles as $monographFile) {
			// Only include files where the 'viewable' flag has been set, i.e. which the editor has approved for the author to see
			if($monographFile->getViewable()) $rowData[$monographFile->getFileId()] = $monographFile;
		}
		$this->setData($rowData);
	}
}