<?php

/**
 * @file controllers/api/file/ManageFileApiHandler.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageFileApiHandler
 * @ingroup controllers_api_file
 *
 * @brief Class defining an AJAX API for file manipulation.
 */

// Import the base handler.
import('lib.pkp.controllers.api.file.PKPManageFileApiHandler');
import('lib.pkp.classes.core.JSONMessage');

class ManageFileApiHandler extends PKPManageFileApiHandler {

	/**
	 * Constructor.
	 */
	function ManageFileApiHandler() {
		parent::PKPManageFileApiHandler();
	}

	//
	// Implement methods from PKPHandler
	//
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.SubmissionFileAccessPolicy');
		$this->addPolicy(new SubmissionFileAccessPolicy($request, $args, $roleAssignments, SUBMISSION_FILE_ACCESS_MODIFY));

		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Subclassed methods
	//

	/**
	 * indexes the files associated with a submission.
	 * @param $submission Submission
	 * @param $submissionFile SubmissionFile
	 */
	function indexSubmissionFiles($submission, $submissionFile) {
		// update the submission's search index if this was a proof file
		if ($submissionFile->getFileStage() == SUBMISSION_FILE_PROOF) {
			if ($submission->getDatePublished()) {
				import('classes.search.MonographSearchIndex');
				MonographSearchIndex::indexMonographFiles($submission);
			}
		}
	}

	/**
	 * indexes the files associated with a submission.
	 * @param $contextId int the context id.
	 * @param $submissionId int the submission id.
	 * @return MonographFileManager
	 */
	function getFileManager($contextId, $submissionId) {
		import('classes.file.MonographFileManager');
		return new MonographFileManager($contextId, $submissionId);
	}

	/**
	 * logs the deletion event using app-specific logging classes.
	 * @param $request PKPRequest
	 * @param $submission Submission
	 * @param $submissionFile SubmissionFile
	 * @param $user PKPUser
	 */
	function logDeletionEvent($request, $submission, $submissionFile, $user) {
		// log the deletion event.
		import('classes.log.MonographFileLog');
		import('classes.log.MonographFileEventLogEntry'); // constants

		if ($submissionFile->getRevision() > 1) {
			MonographFileLog::logEvent($request, $submissionFile, MONOGRAPH_LOG_FILE_REVISION_DELETE, 'submission.event.revisionDeleted', array('fileStage' => $submissionFile->getFileStage(), 'sourceFileId' => $submissionFile->getSourceFileId(), 'fileId' => $submissionFile->getFileId(), 'fileRevision' => $submissionFile->getRevision(), 'originalFileName' => $submissionFile->getOriginalFileName(), 'submissionId' => $submissionFile->getSubmissionId(), 'username' => $user->getUsername()));
		} else {
			MonographFileLog::logEvent($request, $submissionFile, MONOGRAPH_LOG_FILE_DELETE, 'submission.event.fileDeleted', array('fileStage' => $submissionFile->getFileStage(), 'sourceFileId' => $submissionFile->getSourceFileId(), 'fileId' => $submissionFile->getFileId(), 'fileRevision' => $submissionFile->getRevision(), 'originalFileName' => $submissionFile->getOriginalFileName(), 'submissionId' => $submissionFile->getSubmissionId(), 'username' => $user->getUsername()));
		}

		if ($submissionFile->getRevision() == 1 && $submissionFile->getSourceFileId() == null) {
			import('classes.log.MonographLog');
			import('classes.log.MonographEventLogEntry'); // constants
			MonographLog::logEvent($request, $submission, MONOGRAPH_LOG_LAST_REVISION_DELETED, 'submission.event.lastRevisionDeleted', array('title' => $submissionFile->getOriginalFileName(), 'submissionId' => $submissionFile->getSubmissionId(), 'username' => $user->getUsername()));
		}

	}
}

?>
