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
import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class ManageFileApiHandler extends Handler {

	/**
	 * Constructor.
	 */
	function ManageFileApiHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR),
			array('deleteFile')
		);
	}


	//
	// Implement methods from PKPHandler
	//
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpMonographFileAccessPolicy');
		$this->addPolicy(new OmpMonographFileAccessPolicy($request, $args, $roleAssignments, SUBMISSION_FILE_ACCESS_MODIFY));

		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Public handler methods
	//
	/**
	 * Delete a file or revision
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function deleteFile($args, &$request) {
		$monographFile =& $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION_FILE);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId =& $request->getUserVar('stageId');
		if ($stageId) {
			// validate the stage id.
			$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
			$user =& $request->getUser();
			$stageAssignments =& $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), $stageId, null, $user->getId());
		}

		assert($monographFile && $monograph); // Should have been validated already

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getByAssoc(ASSOC_TYPE_SUBMISSION_FILE, $monographFile->getFileId());
		while ($note =& $notes->next()) {
			$noteDao->deleteById($note->getId());
			unset($note);
		}

		// Delete all signoffs related with this file.
		$signoffDao = DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoffFactory =& $signoffDao->getAllByAssocType(ASSOC_TYPE_SUBMISSION_FILE, $monographFile->getFileId());
		$signoffs = $signoffFactory->toArray();
		$notificationMgr = new NotificationManager();

		foreach ($signoffs as $signoff) {
			$signoffDao->deleteObject($signoff);

			// Delete for all users.
			$notificationMgr->updateNotification(
				$request,
				array(NOTIFICATION_TYPE_AUDITOR_REQUEST, NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT),
				null,
				ASSOC_TYPE_SIGNOFF,
				$signoff->getId()
			);

			$notificationMgr->updateNotification(
				$request,
				array(NOTIFICATION_TYPE_SIGNOFF_COPYEDIT, NOTIFICATION_TYPE_SIGNOFF_PROOF),
				array($signoff->getUserId()),
				ASSOC_TYPE_MONOGRAPH,
				$monograph->getId()
			);
		}

		// Delete the monograph file.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */


		// check to see if we need to remove review_round_file associations
		if (!$stageAssignments->wasEmpty()) {
			$submissionFileDao->deleteReviewRoundAssignment($monograph->getId(), $stageId, $monographFile->getFileId(), $monographFile->getRevision());
		}
		$success = (boolean)$submissionFileDao->deleteRevisionById($monographFile->getFileId(), $monographFile->getRevision(), $monographFile->getFileStage(), $monograph->getId());

		if ($success) {
			if ($monographFile->getFileStage() == SUBMISSION_FILE_REVIEW_REVISION) {
				$notificationMgr->updateNotification(
					$request,
					array(NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS, NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS),
					array($monograph->getUserId()),
					ASSOC_TYPE_MONOGRAPH,
					$monograph->getId()
				);

				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				$lastReviewRound =& $reviewRoundDao->getLastReviewRoundByMonographId($monograph->getId(), $stageId);
				$notificationMgr->updateNotification(
					$request,
					array(NOTIFICATION_TYPE_ALL_REVISIONS_IN),
					null,
					ASSOC_TYPE_REVIEW_ROUND,
					$lastReviewRound->getId()
				);
			}

			// update the monograph's search index if this was a proof file
			if ($monographFile->getFileStage() == SUBMISSION_FILE_PROOF) {
				if ($monograph->getDatePublished()) {
					import('classes.search.MonographSearchIndex');
					MonographSearchIndex::indexMonographFiles($monograph);
				}
			}
			import('classes.file.MonographFileManager');
			$monographFileManager = new MonographFileManager($monograph->getPressId(), $monograph->getId());
			$monographFileManager->deleteFile($monographFile->getFileId(), $monographFile->getRevision());
			$this->setupTemplate($request);
			$user =& $request->getUser();
			NotificationManager::createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedFile')));

			// log the deletion event.
			import('classes.log.MonographFileLog');
			import('classes.log.MonographFileEventLogEntry'); // constants

			if ($monographFile->getRevision() > 1) {
				MonographFileLog::logEvent($request, $monographFile, MONOGRAPH_LOG_FILE_REVISION_DELETE, 'submission.event.revisionDeleted', array('fileStage' => $monographFile->getFileStage(), 'sourceFileId' => $monographFile->getSourceFileId(), 'fileId' => $monographFile->getFileId(), 'fileRevision' => $monographFile->getRevision(), 'originalFileName' => $monographFile->getOriginalFileName(), 'submissionId' => $monograph->getId(), 'username' => $user->getUsername()));
			} else {
				MonographFileLog::logEvent($request, $monographFile, MONOGRAPH_LOG_FILE_DELETE, 'submission.event.fileDeleted', array('fileStage' => $monographFile->getFileStage(), 'sourceFileId' => $monographFile->getSourceFileId(), 'fileId' => $monographFile->getFileId(), 'fileRevision' => $monographFile->getRevision(), 'originalFileName' => $monographFile->getOriginalFileName(), 'submissionId' => $monograph->getId(), 'username' => $user->getUsername()));
			}

			if ($monographFile->getRevision() == 1 && $monographFile->getSourceFileId() == null) {
				import('classes.log.MonographLog');
				import('classes.log.MonographEventLogEntry'); // constants
				MonographLog::logEvent($request, $monograph, MONOGRAPH_LOG_LAST_REVISION_DELETED, 'submission.event.lastRevisionDeleted', array('title' => $monographFile->getOriginalFileName(), 'submissionId' => $monograph->getId(), 'username' => $user->getUsername()));
			}

			return DAO::getDataChangedEvent();
			} else {
				$json = new JSONMessage(false);
				return $json->getString();
			}
	}
}

?>
