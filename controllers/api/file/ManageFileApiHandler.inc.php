<?php

/**
 * @file controllers/api/file/ManageFileApiHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
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
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR),
			array('identifiers', 'updateIdentifiers', 'clearPubId',)
		);
	}

	/**
	 * Edit proof submission file pub ids.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function identifiers($args, $request) {
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$stageId = $request->getUserVar('stageId');
		import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');
		$form = new PKPPublicIdentifiersForm($submissionFile, $stageId);
		$form->initData();
		return new JSONMessage(true, $form->fetch($request));
	}

	/**
	 * Update proof submission file pub ids.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateIdentifiers($args, $request) {
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$stageId = $request->getUserVar('stageId');
		import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');
		$form = new PKPPublicIdentifiersForm($submissionFile, $stageId);
		$form->readInputData();
		if ($form->validate($request)) {
			$form->execute($request);
			return DAO::getDataChangedEvent($submissionFile->getId());
		} else {
			return new JSONMessage(true, $form->fetch($request));
		}
	}

	/**
	 * Clear proof submission file pub id.
	 * @param $args array
	 * @param $request Request
	 * @return JSONMessage JSON object
	 */
	function clearPubId($args, $request) {
		$submissionFile = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$stageId = $request->getUserVar('stageId');
		import('lib.pkp.controllers.tab.pubIds.form.PKPPublicIdentifiersForm');
		$form = new PKPPublicIdentifiersForm($submissionFile, $stageId);
		$form->clearPubId($request->getUserVar('pubIdPlugIn'));
		return new JSONMessage(true);
	}


	//
	// Subclassed methods
	//

	/**
	 * @copydoc PKPManageFileApiHandler::removeFileIndex()
	 */
	function removeFileIndex($submission, $submissionFile) {
		// update the submission's search index if this was a proof file
		if ($submissionFile->getFileStage() == SUBMISSION_FILE_PROOF) {
			import('lib.pkp.classes.search.SubmissionSearch');
			import('classes.search.MonographSearchIndex');
			MonographSearchIndex::deleteTextIndex($submission->getId(), SUBMISSION_SEARCH_GALLEY_FILE, $submissionFile->getFileId());
		}
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
		import('lib.pkp.classes.log.SubmissionFileLog');
		import('lib.pkp.classes.log.SubmissionFileEventLogEntry'); // constants

		if ($submissionFile->getRevision() > 1) {
			SubmissionFileLog::logEvent($request, $submissionFile, SUBMISSION_LOG_FILE_REVISION_DELETE, 'submission.event.revisionDeleted', array('fileStage' => $submissionFile->getFileStage(), 'sourceFileId' => $submissionFile->getSourceFileId(), 'fileId' => $submissionFile->getFileId(), 'fileRevision' => $submissionFile->getRevision(), 'originalFileName' => $submissionFile->getOriginalFileName(), 'submissionId' => $submissionFile->getSubmissionId(), 'username' => $user->getUsername()));
		} else {
			SubmissionFileLog::logEvent($request, $submissionFile, SUBMISSION_LOG_FILE_DELETE, 'submission.event.fileDeleted', array('fileStage' => $submissionFile->getFileStage(), 'sourceFileId' => $submissionFile->getSourceFileId(), 'fileId' => $submissionFile->getFileId(), 'fileRevision' => $submissionFile->getRevision(), 'originalFileName' => $submissionFile->getOriginalFileName(), 'submissionId' => $submissionFile->getSubmissionId(), 'username' => $user->getUsername()));
		}

		if ($submissionFile->getRevision() == 1 && $submissionFile->getSourceFileId() == null) {
			import('lib.pkp.classes.log.SubmissionLog');
			import('classes.log.SubmissionEventLogEntry'); // constants
			SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_LAST_REVISION_DELETED, 'submission.event.lastRevisionDeleted', array('title' => $submissionFile->getOriginalFileName(), 'submissionId' => $submissionFile->getSubmissionId(), 'username' => $user->getUsername()));
		}

	}

	/**
	 * Get the list of notifications to be updated on metadata form submission.
	 * @return array
	 */
	protected function getUpdateNotifications() {
		$updateNotifications = parent::getUpdateNotifications();
		$updateNotifications[] = NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS;
		return $updateNotifications;
	}
}

?>
