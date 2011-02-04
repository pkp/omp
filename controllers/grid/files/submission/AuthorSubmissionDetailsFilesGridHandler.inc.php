<?php

/**
 * @file controllers/grid/files/submission/AuthorSubmissionDetailsFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmissionDetailsFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Handle submission file grid requests on the author's submission details pages.
 *  Differs from the submission wizard file grid in that the 'add file' grid action is hidden;
 *  Files are added to the grid through a LinkAction at the top of the submission details page.
 *  If the 'Add revision' LinkAction is used, we set a request parameter to enforce that this
 *  grid only allows revisions to be uploaded.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import submission files grid specific classes
import('controllers.grid.files.submission.SubmissionDetailsFilesGridHandler');

class AuthorSubmissionDetailsFilesGridHandler extends SubmissionDetailsFilesGridHandler {
	/**
	 * Constructor
	 */
	function AuthorSubmissionDetailsFilesGridHandler() {
		parent::SubmissionDetailsFilesGridHandler(false, false, false, true);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, &$cellProvider) {
		$revisionOnly = (boolean)$request->getUserVar('revisionOnly');
		if($revisionOnly) $this->_revisionOnly = true;

		$additionalActionArgs = array('revisionOnly' => $revisionOnly);

		parent::initialize($request, $additionalActionArgs);
	}

}