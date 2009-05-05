<?php

/**
 * @file classes/submission/copyeditor/CopyeditorAction.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditorAction
 * @ingroup submission
 * @see CopyeditorSubmissionDAO
 *
 * @brief CopyeditorAction class.
 */

// $Id$


import('submission.common.Action');

class CopyeditorAction extends Action {

	/**
	 * Actions.
	 */

	/**
	 * Copyeditor completes initial copyedit.
	 * @param $copyeditorSubmission object
	 */
	function completeCopyedit($copyeditorSubmission, $send = false) {
		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		if ($copyeditorSubmission->getDateCompleted() != null) {
			return true;
		}

		$user =& Request::getUser();
		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($copyeditorSubmission, 'COPYEDIT_COMPLETE');

		$editAssignments = $copyeditorSubmission->getEditAssignments();

		$author = $copyeditorSubmission->getUser();

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('CopyeditorAction::completeCopyedit', array(&$copyeditorSubmission, &$editAssignments, &$author, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(ARTICLE_EMAIL_COPYEDIT_NOTIFY_COMPLETE, ARTICLE_EMAIL_TYPE_COPYEDIT, $copyeditorSubmission->getMonographId());
				$email->send();
			}

			$copyeditorSubmission->setDateCompleted(Core::getCurrentDate());
			$copyeditorSubmission->setDateAuthorNotified(Core::getCurrentDate());
			$copyeditorSubmissionDao->updateCopyeditorSubmission($copyeditorSubmission);

			// Add log entry
			import('monograph.log.MonographLog');
			import('monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($copyeditorSubmission->getMonographId(), ARTICLE_LOG_COPYEDIT_INITIAL, ARTICLE_LOG_TYPE_COPYEDIT, $user->getUserId(), 'log.copyedit.initialEditComplete', Array('copyeditorName' => $user->getFullName(), 'monographId' => $copyeditorSubmission->getMonographId()));

			return true;

		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($author->getEmail(), $author->getFullName());
				$email->ccAssignedEditingSectionEditors($copyeditorSubmission->getMonographId());
				$email->ccAssignedEditors($copyeditorSubmission->getMonographId());

				$paramArray = array(
					'editorialContactName' => $author->getFullName(),
					'copyeditorName' => $user->getFullName(),
					'authorUsername' => $author->getUsername(),
					'submissionEditingUrl' => Request::url(null, 'author', 'submissionEditing', array($copyeditorSubmission->getMonographId()))
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, 'copyeditor', 'completeCopyedit', 'send'), array('monographId' => $copyeditorSubmission->getMonographId()));

			return false;
		}
	}

	/**
	 * Copyeditor completes final copyedit.
	 * @param $copyeditorSubmission object
	 */
	function completeFinalCopyedit($copyeditorSubmission, $send = false) {
		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		if ($copyeditorSubmission->getDateFinalCompleted() != null) {
			return true;
		}

		$user =& Request::getUser();
		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($copyeditorSubmission, 'COPYEDIT_FINAL_COMPLETE');

		$editAssignments = $copyeditorSubmission->getEditAssignments();

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('CopyeditorAction::completeFinalCopyedit', array(&$copyeditorSubmission, &$editAssignments, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(ARTICLE_EMAIL_COPYEDIT_NOTIFY_FINAL_COMPLETE, ARTICLE_EMAIL_TYPE_COPYEDIT, $copyeditorSubmission->getMonographId());
				$email->send();
			}

			$copyeditorSubmission->setDateFinalCompleted(Core::getCurrentDate());
			$copyeditorSubmissionDao->updateCopyeditorSubmission($copyeditorSubmission);

			if ($copyEdFile =& $copyeditorSubmission->getFinalCopyeditFile()) {
				// Set initial layout version to final copyedit version
				$layoutDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
				$layoutAssignment =& $layoutDao->getLayoutAssignmentByMonographId($copyeditorSubmission->getMonographId());

				if (isset($layoutAssignment) && !$layoutAssignment->getLayoutFileId()) {
					import('file.MonographFileManager');
					$monographFileManager = new MonographFileManager($copyeditorSubmission->getMonographId());
					if ($layoutFileId = $monographFileManager->copyToLayoutFile($copyEdFile->getFileId(), $copyEdFile->getRevision())) {
						$layoutAssignment->setLayoutFileId($layoutFileId);
						$layoutDao->updateLayoutAssignment($layoutAssignment);
					}
				}
			}

			// Add log entry
			import('monograph.log.MonographLog');
			import('monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($copyeditorSubmission->getMonographId(), ARTICLE_LOG_COPYEDIT_FINAL, ARTICLE_LOG_TYPE_COPYEDIT, $user->getUserId(), 'log.copyedit.finalEditComplete', Array('copyeditorName' => $user->getFullName(), 'monographId' => $copyeditorSubmission->getMonographId()));

			return true;

		} else {
			if (!Request::getUserVar('continued')) {
				$assignedSectionEditors = $email->toAssignedEditingSectionEditors($copyeditorSubmission->getMonographId());
				$assignedEditors = $email->ccAssignedEditors($copyeditorSubmission->getMonographId());
				if (empty($assignedSectionEditors) && empty($assignedEditors)) {
					$email->addRecipient($press->getSetting('contactEmail'), $press->getSetting('contactName'));
					$paramArray = array(
						'editorialContactName' => $press->getSetting('contactName'),
						'copyeditorName' => $user->getFullName()
					);
				} else {
					$editorialContact = array_shift($assignedSectionEditors);
					if (!$editorialContact) $editorialContact = array_shift($assignedEditors);

					$paramArray = array(
						'editorialContactName' => $editorialContact->getEditorFullName(),
						'copyeditorName' => $user->getFullName()
					);
				}
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, 'copyeditor', 'completeFinalCopyedit', 'send'), array('monographId' => $copyeditorSubmission->getMonographId()));

			return false;
		}
	}

	/**
	 * Set that the copyedit is underway.
	 */
	function copyeditUnderway(&$copyeditorSubmission) {
		if (!HookRegistry::call('CopyeditorAction::copyeditUnderway', array(&$copyeditorSubmission))) {
			$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');		

			if ($copyeditorSubmission->getDateNotified() != null && $copyeditorSubmission->getDateUnderway() == null) {
				$copyeditorSubmission->setDateUnderway(Core::getCurrentDate());
				$update = true;

			} elseif ($copyeditorSubmission->getDateFinalNotified() != null && $copyeditorSubmission->getDateFinalUnderway() == null) {
				$copyeditorSubmission->setDateFinalUnderway(Core::getCurrentDate());
				$update = true;
			}

			if (isset($update)) {
				$copyeditorSubmissionDao->updateCopyeditorSubmission($copyeditorSubmission);

				// Add log entry
				$user =& Request::getUser();
				import('monograph.log.MonographLog');
				import('monograph.log.MonographEventLogEntry');
				MonographLog::logEvent($copyeditorSubmission->getMonographId(), ARTICLE_LOG_COPYEDIT_INITIATE, ARTICLE_LOG_TYPE_COPYEDIT, $user->getUserId(), 'log.copyedit.initiate', Array('copyeditorName' => $user->getFullName(), 'monographId' => $copyeditorSubmission->getMonographId()));
			}
		}
	}	

	/**
	 * Upload the copyedited version of an monograph.
	 * @param $copyeditorSubmission object
	 */
	function uploadCopyeditVersion($copyeditorSubmission, $copyeditStage) {
		import("file.MonographFileManager");
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');		

		// Only allow an upload if they're in the initial or final copyediting
		// stages.
		if ($copyeditStage == 'initial' && ($copyeditorSubmission->getDateNotified() == null || $copyeditorSubmission->getDateCompleted() != null)) return;
		else if ($copyeditStage == 'final' && ($copyeditorSubmission->getDateFinalNotified() == null || $copyeditorSubmission->getDateFinalCompleted() != null)) return;
		else if ($copyeditStage != 'initial' && $copyeditStage != 'final') return;

		$monographFileManager = new MonographFileManager($copyeditorSubmission->getMonographId());
		$user =& Request::getUser();

		$fileName = 'upload';
		if ($monographFileManager->uploadedFileExists($fileName)) {
			HookRegistry::call('CopyeditorAction::uploadCopyeditVersion', array(&$copyeditorSubmission));
			if ($copyeditorSubmission->getCopyeditFileId() != null) {
				$fileId = $monographFileManager->uploadCopyeditFile($fileName, $copyeditorSubmission->getCopyeditFileId());
			} else {
				$fileId = $monographFileManager->uploadCopyeditFile($fileName);
			}
		}

		if (isset($fileId) && $fileId != 0) {
			$copyeditorSubmission->setCopyeditFileId($fileId);

			if ($copyeditStage == 'initial') {
				$copyeditorSubmission->setInitialRevision($monographFileDao->getRevisionNumber($fileId));
			} elseif ($copyeditStage == 'final') {
				$copyeditorSubmission->setFinalRevision($monographFileDao->getRevisionNumber($fileId));
			}

			$copyeditorSubmissionDao->updateCopyeditorSubmission($copyeditorSubmission);

			// Add log
			import('monograph.log.MonographLog');
			import('monograph.log.MonographEventLogEntry');

			$entry = new MonographEventLogEntry();
			$entry->setMonographId($copyeditorSubmission->getMonographId());
			$entry->setUserId($user->getUserId());
			$entry->setDateLogged(Core::getCurrentDate());
			$entry->setEventType(ARTICLE_LOG_COPYEDIT_COPYEDITOR_FILE);
			$entry->setLogMessage('log.copyedit.copyeditorFile');
			$entry->setAssocType(ARTICLE_LOG_TYPE_COPYEDIT);
			$entry->setAssocId($fileId);

			MonographLog::logEventEntry($copyeditorSubmission->getMonographId(), $entry);
		}
	}

	//
	// Comments
	//

	/**
	 * View layout comments.
	 * @param $monograph object
	 */
	function viewLayoutComments($monograph) {
		if (!HookRegistry::call('CopyeditorAction::viewLayoutComments', array(&$monograph))) {
			import("submission.form.comment.LayoutCommentForm");

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$commentForm =& new LayoutCommentForm($monograph, ROLE_ID_COPYEDITOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post layout comment.
	 * @param $monograph object
	 */
	function postLayoutComment($monograph, $emailComment) {
		if (!HookRegistry::call('CopyeditorAction::postLayoutComment', array(&$monograph, &$emailComment))) {
			import("submission.form.comment.LayoutCommentForm");

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$commentForm =& new LayoutCommentForm($monograph, ROLE_ID_COPYEDITOR);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();

				// Send a notification to associated users
				import('notification.Notification');
				$notificationUsers = $monograph->getAssociatedUserIds(true, false);
				foreach ($notificationUsers as $user) {
					$url = Request::url(null, $user['role'], 'submissionEditing', $monograph->getMonographId(), null, 'layout');
					Notification::createNotification($user['id'], "notification.type.layoutComment",
						$monograph->getMonographTitle(), $url, 1, NOTIFICATION_TYPE_LAYOUT_COMMENT);
				}
				
				if ($emailComment) {
					$commentForm->email();
				}

			} else {
				$commentForm->display();
				return false;
			}
			return true;
		}
	}

	/**
	 * View copyedit comments.
	 * @param $monograph object
	 */
	function viewCopyeditComments($monograph) {
		if (!HookRegistry::call('CopyeditorAction::viewCopyeditComments', array(&$monograph))) {
			import("submission.form.comment.CopyeditCommentForm");

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$commentForm =& new CopyeditCommentForm($monograph, ROLE_ID_COPYEDITOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post copyedit comment. 
	 * @param $monograph object
	 */
	function postCopyeditComment($monograph, $emailComment) {
		if (!HookRegistry::call('CopyeditorAction::postCopyeditComment', array(&$monograph, &$emailComment))) {
			import("submission.form.comment.CopyeditCommentForm");

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$commentForm =& new CopyeditCommentForm($monograph, ROLE_ID_COPYEDITOR);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();

				// Send a notification to associated users
				import('notification.Notification');
				$notificationUsers = $monograph->getAssociatedUserIds(true, false);
				foreach ($notificationUsers as $user) {
					$url = Request::url(null, $user['role'], 'submissionEditing', $monograph->getMonographId(), null, 'coypedit');
					Notification::createNotification($user['id'], "notification.type.copyeditComment",
						$monograph->getMonographTitle(), $url, 1, NOTIFICATION_TYPE_COPYEDIT_COMMENT);
				}
				
				if ($emailComment) {
					$commentForm->email();
				}

			} else {
				$commentForm->display();
				return false;
			}
			return true;
		}
	}

	//
	// Misc
	//

	/**
	 * Download a file a copyeditor has access to.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int
	 */
	function downloadCopyeditorFile($submission, $fileId, $revision = null) {
		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');		

		$canDownload = false;

		// Copyeditors have access to:
		// 1) The first revision of the copyedit file
		// 2) The initial copyedit revision
		// 3) The author copyedit revision, after the author copyedit has been completed
		// 4) The final copyedit revision
		// 5) Layout galleys
		if ($submission->getCopyeditFileId() == $fileId) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');		
			$currentRevision =& $monographFileDao->getRevisionNumber($fileId);

			if ($revision == null) {
				$revision = $currentRevision;
			}

			if ($revision == 1) {
				$canDownload = true;
			} else if ($submission->getInitialRevision() == $revision) {
				$canDownload = true;
			} else if ($submission->getEditorAuthorRevision() == $revision && $submission->getDateAuthorCompleted() != null) {
				$canDownload = true;
			} else if ($submission->getFinalRevision() == $revision) {
				$canDownload = true;
			}
		}
		else {
			// Check galley files
			foreach ($submission->getGalleys() as $galleyFile) {
				if ($galleyFile->getFileId() == $fileId) {
					$canDownload = true;
				}
			}
			// Check supp files
			foreach ($submission->getSuppFiles() as $suppFile) {
				if ($suppFile->getFileId() == $fileId) {
					$canDownload = true;
				}
			}
		}

		$result = false;
		if (!HookRegistry::call('CopyeditorAction::downloadCopyeditorFile', array(&$submission, &$fileId, &$revision, &$result))) {
			if ($canDownload) {
				return Action::downloadFile($submission->getMonographId(), $fileId, $revision);
			} else {
				return false;
			}
		}

		return $result;
	}
}

?>
