<?php

/**
 * @defgroup submission_designer_DesignerAction
 */

/**
 * @file classes/submission/designer/DesignerAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DesignerAction
 * @ingroup submission_designer_DesignerAction
 *
 * @brief DesignerAction class.
 */



import('classes.submission.common.Action');

class DesignerAction extends Action {

	/**
	 * Assign a designer to a submission.
	 * @param $userId int
	 * @param $assignmentId int
	 * @param $submission object
	 */
	function selectDesigner($userId, $assignmentId, $submission) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		if (HookRegistry::call('DesignerAction::selectDesigner', array(&$submission, &$userId))) return;

		import('classes.monograph.log.MonographLog');
		import('classes.monograph.log.MonographEventLogEntry');

		$designSignoff = $signoffDao->build('PRODUCTION_DESIGN', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);
		$designer =& $userDao->getUser($userId);
		if ($designSignoff->getUserId()) {
			$designer =& $userDao->getUser($designSignoff->getUserId());
			MonographLog::logEvent($submission->getId(), MONOGRAPH_LOG_LAYOUT_UNASSIGN, MONOGRAPH_LOG_TYPE_LAYOUT, $designSignoff->getId(), 'log.layout.layoutEditorUnassigned', array('editorName' => $designer->getFullName(), 'monographId' => $submission->getId()));
		}

		$designSignoff->setUserId($userId);
		$signoffDao->updateObject($designSignoff);

		MonographLog::logEvent($submission->getId(), MONOGRAPH_LOG_LAYOUT_ASSIGN, MONOGRAPH_LOG_TYPE_LAYOUT, $designSignoff->getId(), 'log.layout.layoutEditorAssigned', array('designerName' => $designer->getFullName(), 'monographId' => $submission->getId()));
	}

	/**
	 * Change the sequence order of a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 * @param $direction char u = up, d = down
	 */
	function orderGalley($monograph, $galleyId, $direction) {
		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$galley =& $galleyDao->getGalley($galleyId, $monograph->getId());

		if (isset($galley)) {
			$galley->setSequence($galley->getSequence() + ($direction == 'u' ? -1.5 : 1.5));
			$galleyDao->updateObject($galley);
			$galleyDao->resequenceGalleys($monograph->getId(), $galley->getAssignmentId());
		}
	}

	/**
	 * Delete a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 */
	function deleteGalley($monograph, $galleyId) {
		import('classes.file.MonographFileManager');

		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$galley =& $galleyDao->getGalley($galleyId, $monograph->getId());

		if (isset($galley) && !HookRegistry::call('DesignerAction::deleteGalley', array(&$monograph, &$galley))) {
			$monographFileManager = new MonographFileManager($monograph->getId());

			if ($galley->getFileId()) {
				$monographFileManager->deleteFile($galley->getFileId());
				import('classes.search.MonographSearchIndex');
				MonographSearchIndex::deleteTextIndex($monograph->getId(), MONOGRAPH_SEARCH_GALLEY_FILE, $galley->getFileId());
			}
			if ($galley->isHTMLGalley()) {
				if ($galley->getStyleFileId()) {
					$monographFileManager->deleteFile($galley->getStyleFileId());
				}
				foreach ($galley->getImageFiles() as $image) {
					$monographFileManager->deleteFile($image->getFileId());
				}
			}
			$galleyDao->deleteObject($galley);
		}
	}

	/**
	 * Delete an image from a monograph galley.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int (optional)
	 */
	function deleteMonographImage($submission, $fileId, $revision) {
		import('classes.file.MonographFileManager');
		$monographGalleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		if (HookRegistry::call('DesignerAction::deleteMonographImage', array(&$submission, &$fileId, &$revision))) return;
		foreach ($submission->getGalleys() as $galley) {
			$images =& $monographGalleyDao->getGalleyImages($galley->getGalleyId());
			foreach ($images as $imageFile) {
				if ($imageFile->getMonographId() == $submission->getId() && $fileId == $imageFile->getFileId() && $imageFile->getRevision() == $revision) {
					$monographFileManager = new MonographFileManager($submission->getId());
					$monographFileManager->deleteFile($imageFile->getFileId(), $imageFile->getRevision());
				}
			}
			unset($images);
		}
	}

	/**
	 * Marks design assignment as completed.
	 * @param $submission object
	 * @param $assignmentId int
	 * @param $send boolean
	 */
	function completeDesign($submission, $assignmentId, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		$layoutSignoff = $signoffDao->getBySymbolic('PRODUCTION_DESIGN', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);
		if ($layoutSignoff->getDateCompleted() != null) {
			return true;
		}

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_COMPLETE');

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('DesignerAction::completeDesign', array(&$submission, &$layoutSignoff, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_NOTIFY_COMPLETE, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutSignoff->getId());
				$email->send();
			}

			$layoutSignoff->setDateCompleted(Core::getCurrentDate());
			$signoffDao->updateObject($layoutSignoff);

			// Add log entry
			$user =& Request::getUser();
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($submission->getId(), MONOGRAPH_LOG_LAYOUT_COMPLETE, MONOGRAPH_LOG_TYPE_LAYOUT, $user->getId(), 'log.layout.layoutEditComplete', Array('editorName' => $user->getFullName(), 'monographId' => $submission->getId()));

			return true;
		} else {
			$user =& Request::getUser();
			if (!Request::getUserVar('continued')) {
				$assignedSeriesEditors = $email->toAssignedEditingSeriesEditors($submission->getId());
				$assignedEditors = $email->ccAssignedEditors($submission->getId());
				if (empty($assignedSeriesEditors) && empty($assignedEditors)) {
					$email->addRecipient($press->getSetting('contactEmail'), $press->getSetting('contactName'));
					$editorialContactName = $press->getSetting('contactName');
				} else {
					$editorialContact = array_shift($assignedSeriesEditors);
					if (!$editorialContact) $editorialContact = array_shift($assignedEditors);
					$editorialContactName = $editorialContact->getEditorFullName();
				}
				$paramArray = array(
					'editorialContactName' => $editorialContactName,
					'layoutEditorName' => $user->getFullName()
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, 'designer', 'completeDesign', 'send'), array('monographId' => $submission->getId(), 'assignmentId' => $assignmentId));

			return false;
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
		if (!HookRegistry::call('LayoutEditorAction::viewLayoutComments', array(&$monograph))) {
			import('classes.submission.form.comment.LayoutCommentForm');

			$commentForm = new LayoutCommentForm($monograph, ROLE_ID_LAYOUT_EDITOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post layout comment.
	 * @param $monograph object
	 */
	function postLayoutComment($monograph, $emailComment) {
		if (!HookRegistry::call('LayoutEditorAction::postLayoutComment', array(&$monograph, &$emailComment))) {
			import('classes.submission.form.comment.LayoutCommentForm');

			$commentForm = new LayoutCommentForm($monograph, ROLE_ID_LAYOUT_EDITOR);
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
						$userRole['id'], 'notification.type.layoutComment',
						$monograph->getMonographTitle(), $url, 1, NOTIFICATION_TYPE_LAYOUT_COMMENT
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
	 * View proofread comments.
	 * @param $monograph object
	 */
	function viewProofreadComments($monograph) {
		if (!HookRegistry::call('LayoutEditorAction::viewProofreadComments', array(&$monograph))) {
			import('classes.submission.form.comment.ProofreadCommentForm');

			$commentForm = new ProofreadCommentForm($monograph, ROLE_ID_LAYOUT_EDITOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post proofread comment.
	 * @param $monograph object
	 */
	function postProofreadComment($monograph, $emailComment) {
		if (!HookRegistry::call('LayoutEditorAction::postProofreadComment', array(&$monograph, &$emailComment))) {
			import('classes.submission.form.comment.ProofreadCommentForm');

			$commentForm = new ProofreadCommentForm($monograph, ROLE_ID_LAYOUT_EDITOR);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();

				// Send a notification to associated users
				import('lib.pkp.classes.notification.NotificationManager');
				$notificationUsers = $monograph->getAssociatedUserIds();
				$notificationManager = new NotificationManager();
				foreach ($notificationUsers as $userRole) {
					$url = Request::url(null, $userRole['role'], 'submissionEditing', $monograph->getId(), null, 'proofread');
					$notificationManager->createNotification(
						$userRole['id'], "notification.type.proofreadComment",
						$monograph->getMonographTitle(), $url, 1, NOTIFICATION_TYPE_PROOFREAD_COMMENT
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
	 * Download a file a layout editor has access to.
	 * This includes: The layout editor submission file and galley files.
	 * @param $monograph object
	 * @param $fileId int
	 * @param $revision int optional
	 * @return boolean
	 */
	function downloadFile($monograph, $fileId, $revision = null) {
		$canDownload = false;

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');

		$layoutAssignment =& $signoffDao->build(
						  'SIGNOFF_LAYOUT',
						  ASSOC_TYPE_MONOGRAPH,
						  $monograph->getId()
					);

		if ($layoutAssignment->getFileId() == $fileId) {
			$canDownload = true;

		} else if($galleyDao->galleyExistsByFileId($monograph->getId(), $fileId)) {
			$canDownload = true;

		}

		$result = false;
		if (!HookRegistry::call('LayoutEditorAction::downloadFile', array(&$monograph, &$fileId, &$revision, &$canDownload, &$result))) {
			if ($canDownload) {
				return parent::downloadFile($monograph->getId(), $fileId, $revision);
			} else {
				return false;
			}
		}
		return $result;
	}
}

?>
