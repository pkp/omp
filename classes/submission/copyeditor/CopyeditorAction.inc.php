<?php

/**
 * @file classes/submission/copyeditor/CopyeditorAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditorAction
 * @ingroup submission
 * @see CopyeditorSubmissionDAO
 *
 * @brief CopyeditorAction class.
 */

// $Id$

import('classes.submission.common.Action');

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
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		$initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());
		if ($initialSignoff->getDateCompleted() != null) {
			return true;
		}

		$user =& Request::getUser();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($copyeditorSubmission, 'COPYEDIT_COMPLETE');

		$editAssignments = $copyeditorSubmission->getEditAssignments();

		$author = $copyeditorSubmission->getUser();

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('CopyeditorAction::completeCopyedit', array(&$copyeditorSubmission, &$editAssignments, &$author, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_COMPLETE, MONOGRAPH_EMAIL_TYPE_COPYEDIT, $copyeditorSubmission->getId());
				$email->send();
			}

			$initialSignoff->setDateCompleted(Core::getCurrentDate());

			$authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());
			$authorSignoff->setUserId($author->getId());
			$authorSignoff->setDateNotified(Core::getCurrentDate());
			$signoffDao->updateObject($initialSignoff);
			$signoffDao->updateObject($authorSignoff);


			// Add log entry
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');

			MonographLog::logEvent(
					$copyeditorSubmission->getId(),
					MONOGRAPH_LOG_COPYEDIT_INITIAL,
					MONOGRAPH_LOG_TYPE_COPYEDIT, $user->getId(),
					'log.copyedit.initialEditComplete',
					Array(
						'copyeditorName' => $user->getFullName(),
						'monographId' => $copyeditorSubmission->getId()
					)
				);

			return true;

		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($author->getEmail(), $author->getFullName());
				$email->ccAssignedEditingSeriesEditors($copyeditorSubmission->getId());
				$email->ccAssignedEditors($copyeditorSubmission->getId());

				$paramArray = array(
					'editorialContactName' => $author->getFullName(),
					'copyeditorName' => $user->getFullName(),
					'authorUsername' => $author->getUsername(),
					'submissionEditingUrl' => Request::url(null, 'author', 'submissionEditing', array($copyeditorSubmission->getId()))
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, 'copyeditor', 'completeCopyedit', 'send'), array('monographId' => $copyeditorSubmission->getId()));

			return false;
		}
	}

	/**
	 * Copyeditor completes final copyedit.
	 * @param $copyeditorSubmission object
	 */
	function completeFinalCopyedit($copyeditorSubmission, $send = false) {
		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		$finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());
		if ($finalSignoff->getDateCompleted() != null) {
			return true;
		}

		$user =& Request::getUser();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($copyeditorSubmission, 'COPYEDIT_FINAL_COMPLETE');

		$editAssignments = $copyeditorSubmission->getEditAssignments();

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('CopyeditorAction::completeFinalCopyedit', array(&$copyeditorSubmission, &$editAssignments, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_FINAL_COMPLETE, MONOGRAPH_EMAIL_TYPE_COPYEDIT, $copyeditorSubmission->getId());
				$email->send();
			}

			$finalSignoff->setDateCompleted(Core::getCurrentDate());
			$signoffDao->updateObject($finalSignoff);

			if ($copyEdFile = $copyeditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL')) {
				// Set initial layout version to final copyedit version
				$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());

				if (!$layoutSignoff->getFileId()) {
					import('classes.file.MonographFileManager');
					$monographFileManager = new MonographFileManager($copyeditorSubmission->getId());
					if ($layoutFileId = $monographFileManager->copyToLayoutFile($copyEdFile->getFileId(), $copyEdFile->getRevision())) {
						$layoutSignoff->setFileId($layoutFileId);
						$signoffDao->updateObject($layoutSignoff);
					}
				}
			}

			// Add log entry
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');

			MonographLog::logEvent($copyeditorSubmission->getId(), MONOGRAPH_LOG_COPYEDIT_FINAL, MONOGRAPH_LOG_TYPE_COPYEDIT, $user->getId(), 'log.copyedit.finalEditComplete', Array('copyeditorName' => $user->getFullName(), 'monographId' => $copyeditorSubmission->getId()));

			return true;

		} else {
			if (!Request::getUserVar('continued')) {
				$assignedSeriesEditors = $email->toAssignedEditingSeriesEditors($copyeditorSubmission->getId());
				$assignedEditors = $email->ccAssignedEditors($copyeditorSubmission->getId());
				if (empty($assignedSeriesEditors) && empty($assignedEditors)) {
					$email->addRecipient($press->getSetting('contactEmail'), $press->getSetting('contactName'));
					$paramArray = array(
						'editorialContactName' => $press->getSetting('contactName'),
						'copyeditorName' => $user->getFullName()
					);
				} else {
					$editorialContact = array_shift($assignedSeriesEditors);
					if (!$editorialContact) $editorialContact = array_shift($assignedEditors);

					$paramArray = array(
						'editorialContactName' => $editorialContact->getEditorFullName(),
						'copyeditorName' => $user->getFullName()
					);
				}
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, 'copyeditor', 'completeFinalCopyedit', 'send'), array('monographId' => $copyeditorSubmission->getId()));

			return false;
		}
	}

	/**
	 * Set that the copyedit is underway.
	 */
	function copyeditUnderway(&$copyeditorSubmission) {
		if (!HookRegistry::call('CopyeditorAction::copyeditUnderway', array(&$copyeditorSubmission))) {
			$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
			$signoffDao =& DAORegistry::getDAO('SignoffDAO');

			$initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());
			$finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());

			if ($initialSignoff->getDateNotified() != null && $initialSignoff->getDateUnderway() == null) {
				$initialSignoff->setDateUnderway(Core::getCurrentDate());
				$signoffDao->updateObject($initialSignoff);
				$update = true;

			} elseif ($finalSignoff->getDateNotified() != null && $finalSignoff->getDateUnderway() == null) {
				$finalSignoff->setDateUnderway(Core::getCurrentDate());
				$signoffDao->updateObject($finalSignoff);
				$update = true;
			}

			if (isset($update)) {
				// Add log entry
				$user =& Request::getUser();
				import('classes.monograph.log.MonographLog');
				import('classes.monograph.log.MonographEventLogEntry');

				MonographLog::logEvent(
						$copyeditorSubmission->getId(),
						MONOGRAPH_LOG_COPYEDIT_INITIATE,
						MONOGRAPH_LOG_TYPE_COPYEDIT,
						$user->getId(), 'log.copyedit.initiate',
						Array(
							'copyeditorName' => $user->getFullName(),
							'monographId' => $copyeditorSubmission->getId()
						)
					);
			}
		}
	}

	/**
	 * Upload the copyedited version of a monograph.
	 * @param $copyeditorSubmission object
	 */
	function uploadCopyeditVersion($copyeditorSubmission, $copyeditStage) {
		import('classes.file.MonographFileManager');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		if($copyeditStage == 'initial') {
			$signoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());
		} else if($copyeditStage == 'final') {
			$signoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());
		}

		// Only allow an upload if they're in the initial or final copyediting
		// stages.
		if ($copyeditStage == 'initial' && ($signoff->getDateNotified() == null || $signoff->getDateCompleted() != null)) return;
		else if ($copyeditStage == 'final' && ($signoff->getDateNotified() == null || $signoff->getDateCompleted() != null)) return;
		else if ($copyeditStage != 'initial' && $copyeditStage != 'final') return;

		$monographFileManager = new MonographFileManager($copyeditorSubmission->getId());
		$user =& Request::getUser();

		$fileName = 'upload';
		if ($monographFileManager->uploadedFileExists($fileName)) {
			HookRegistry::call('CopyeditorAction::uploadCopyeditVersion', array(&$copyeditorSubmission));
			if ($signoff->getFileId() != null) {
				$fileId = $monographFileManager->uploadCopyeditFile($fileName, $copyeditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true));
			} else {
				$fileId = $monographFileManager->uploadCopyeditFile($fileName);
			}
		}

		if (isset($fileId) && $fileId != 0) {
			$signoff->setFileId($fileId);
			$signoff->setFileRevision($monographFileDao->getRevisionNumber($fileId));
			$signoffDao->updateObject($signoff);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');

			$entry = new MonographEventLogEntry();
			$entry->setMonographId($copyeditorSubmission->getId());
			$entry->setUserId($user->getId());
			$entry->setDateLogged(Core::getCurrentDate());
			$entry->setEventType(MONOGRAPH_LOG_COPYEDIT_COPYEDITOR_FILE);
			$entry->setLogMessage('log.copyedit.copyeditorFile');
			$entry->setAssocType(MONOGRAPH_LOG_TYPE_COPYEDIT);
			$entry->setAssocId($fileId);

			MonographLog::logEventEntry($copyeditorSubmission->getId(), $entry);
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
			import('classes.submission.form.comment.LayoutCommentForm');

			$commentForm = new LayoutCommentForm($monograph, ROLE_ID_COPYEDITOR);
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
			import('classes.submission.form.comment.LayoutCommentForm');

			$commentForm = new LayoutCommentForm($monograph, ROLE_ID_COPYEDITOR);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();

				// Send a notification to associated users
				import('lib.pkp.classes.notification.NotificationManager');
				$notificationUsers = $monograph->getAssociatedUserIds();
				$notificationManager = new NotificationManager();
				foreach ($notificationUsers as $userRole) {
					$url = Request::url(null, $userRole['role'], 'submissionEditing', $monograph->getId(), null, 'layout');
					$notificationManager->createNotification(
						$userRole['id'], "notification.type.layoutComment",
						$monograph->getLocalizedTitle(), $url, 1,
						NOTIFICATION_TYPE_LAYOUT_COMMENT
					);
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
			import('classes.submission.form.comment.CopyeditCommentForm');

			$commentForm = new CopyeditCommentForm($monograph, ROLE_ID_COPYEDITOR);
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
			import('classes.submission.form.comment.CopyeditCommentForm');

			$commentForm = new CopyeditCommentForm($monograph, ROLE_ID_COPYEDITOR);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();

				// Send a notification to associated users
				import('lib.pkp.classes.notification.NotificationManager');
				$notificationUsers = $monograph->getAssociatedUserIds();
				$notificationManager = new NotificationManager();
				foreach ($notificationUsers as $userRole) {
					$url = Request::url(null, $userRole['role'], 'submissionEditing', $monograph->getId(), null, 'coypedit');
					$notificationManager->createNotification(
						$userRole['id'],
						'notification.type.copyeditComment',
						$monograph->getLocalizedTitle(),
						$url, 1, NOTIFICATION_TYPE_COPYEDIT_COMMENT
					);
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
		if ($submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true) == $fileId) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$currentRevision =& $monographFileDao->getRevisionNumber($fileId);

			if ($revision == null) {
				$revision = $currentRevision;
			}

			$initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $authorSubmission->getMonographId());
			$authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $authorSubmission->getMonographId());
			$finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $authorSubmission->getMonographId());

			if ($revision == 1) {
				$canDownload = true;
			} else if ($initialSignoff->getFileRevision() == $revision) {
				$canDownload = true;
			} else if ($authorSignoff->getFileRevision() == $revision && $submission->getDateAuthorCompleted() != null) {
				$canDownload = true;
			} else if ($finalSignoff->getFileRevision() == $revision) {
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
		}

		$result = false;
		if (!HookRegistry::call('CopyeditorAction::downloadCopyeditorFile', array(&$submission, &$fileId, &$revision, &$result))) {
			if ($canDownload) {
				return Action::downloadFile($submission->getId(), $fileId, $revision);
			} else {
				return false;
			}
		}

		return $result;
	}
}

?>
