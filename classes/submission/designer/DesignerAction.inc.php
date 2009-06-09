<?php

/**
 * @defgroup submission_designer_DesignerAction
 */
 
/**
 * @file classes/submission/designer/DesignerAction.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DesignerAction
 * @ingroup submission_designer_DesignerAction
 *
 * @brief DesignerAction class.
 */

// $Id$


import('submission.common.Action');

class DesignerAction extends Action {

	//
	// Actions
	//

	/**
	 * Change the sequence order of a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 * @param $direction char u = up, d = down
	 */
	function orderGalley($monograph, $galleyId, $direction) {
		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$galley =& $galleyDao->getGalley($galleyId, $monograph->getMonographId());

		if (isset($galley)) {
			$galley->setSequence($galley->getSequence() + ($direction == 'u' ? -1.5 : 1.5));
			$galleyDao->updateGalley($galley);
			$galleyDao->resequenceGalleys($monograph->getMonographId());
		}
	}

	/**
	 * Delete a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 */
	function deleteGalley($monograph, $galleyId) {
		import('file.MonographFileManager');

		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$galley =& $galleyDao->getGalley($galleyId, $monograph->getMonographId());

		if (isset($galley) && !HookRegistry::call('LayoutEditorAction::deleteGalley', array(&$monograph, &$galley))) {
			$monographFileManager = new MonographFileManager($monograph->getMonographId());

			if ($galley->getFileId()) {
				$monographFileManager->deleteFile($galley->getFileId());
				import('search.MonographSearchIndex');
				MonographSearchIndex::deleteTextIndex($monograph->getMonographId(), MONOGRAPH_SEARCH_GALLEY_FILE, $galley->getFileId());
			}
			if ($galley->isHTMLGalley()) {
				if ($galley->getStyleFileId()) {
					$monographFileManager->deleteFile($galley->getStyleFileId());
				}
				foreach ($galley->getImageFiles() as $image) {
					$monographFileManager->deleteFile($image->getFileId());
				}
			}
			$galleyDao->deleteGalley($galley);
		}
	}

	/**
	 * Delete an image from a monograph galley.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int (optional)
	 */
	function deleteMonographImage($submission, $fileId, $revision) {
		import('file.MonographFileManager');
		$monographGalleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		if (HookRegistry::call('LayoutEditorAction::deleteMonographImage', array(&$submission, &$fileId, &$revision))) return;
		foreach ($submission->getGalleys() as $galley) {
			$images =& $monographGalleyDao->getGalleyImages($galley->getGalleyId());
			foreach ($images as $imageFile) {
				if ($imageFile->getMonographId() == $submission->getMonographId() && $fileId == $imageFile->getFileId() && $imageFile->getRevision() == $revision) {
					$monographFileManager = new MonographFileManager($submission->getMonographId());
					$monographFileManager->deleteFile($imageFile->getFileId(), $imageFile->getRevision());
				}
			}
			unset($images);
		}
	}

	/**
	 * Marks layout assignment as completed.
	 * @param $submission object
	 * @param $send boolean
	 */
	function completeLayoutEditing($submission, $send = false) {
		$submissionDao =& DAORegistry::getDAO('LayoutEditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		$layoutAssignment =& $submission->getLayoutAssignment();
		if ($layoutAssignment->getDateCompleted() != null) {
			return true;
		}

		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_COMPLETE');

		$editAssignments =& $submission->getByIds();
		if (empty($editAssignments)) return;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('LayoutEditorAction::completeLayoutEditing', array(&$submission, &$layoutAssignment, &$editAssignments, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_NOTIFY_COMPLETE, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutAssignment->getLayoutId());
				$email->send();
			}

			$layoutAssignment->setDateCompleted(Core::getCurrentDate());
			$submissionDao->updateSubmission($submission);

			// Add log entry
			$user =& Request::getUser();
			import('monograph.log.MonographLog');
			import('monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($submission->getMonographId(), MONOGRAPH_LOG_LAYOUT_COMPLETE, MONOGRAPH_LOG_TYPE_LAYOUT, $user->getId(), 'log.layout.layoutEditComplete', Array('editorName' => $user->getFullName(), 'monographId' => $submission->getMonographId()));

			return true;
		} else {
			$user =& Request::getUser();
			if (!Request::getUserVar('continued')) {
				$assignedAcquisitionsEditors = $email->toAssignedEditingAcquisitionsEditors($submission->getMonographId());
				$assignedEditors = $email->ccAssignedEditors($submission->getMonographId());
				if (empty($assignedAcquisitionsEditors) && empty($assignedEditors)) {
					$email->addRecipient($press->getSetting('contactEmail'), $press->getSetting('contactName'));
					$editorialContactName = $press->getSetting('contactName');
				} else {
					$editorialContact = array_shift($assignedAcquisitionsEditors);
					if (!$editorialContact) $editorialContact = array_shift($assignedEditors);
					$editorialContactName = $editorialContact->getEditorFullName();
				}
				$paramArray = array(
					'editorialContactName' => $editorialContactName,
					'layoutEditorName' => $user->getFullName()
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, 'layoutEditor', 'completeAssignment', 'send'), array('monographId' => $submission->getMonographId()));

			return false;
		}
	}

	/**
	 * Upload the layout version of a monograph.
	 * @param $submission object
	 */
	function uploadLayoutVersion($submission) {
		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($submission->getMonographId());
		$layoutEditorSubmissionDao =& DAORegistry::getDAO('LayoutEditorSubmissionDAO');

		$layoutDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
		$layoutAssignment =& $layoutDao->getLayoutAssignmentByMonographId($submission->getMonographId());

		$fileName = 'layoutFile';
		if ($monographFileManager->uploadedFileExists($fileName) && !HookRegistry::call('LayoutEditorAction::uploadLayoutVersion', array(&$submission, &$layoutAssignment))) {
			$layoutFileId = $monographFileManager->uploadLayoutFile($fileName, $layoutAssignment->getLayoutFileId());
			$layoutAssignment->setLayoutFileId($layoutFileId);
			$layoutDao->updateLayoutAssignment($layoutAssignment);
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
			import("submission.form.comment.LayoutCommentForm");

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$commentForm =& new LayoutCommentForm($monograph, ROLE_ID_LAYOUT_EDITOR);
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
			import("submission.form.comment.LayoutCommentForm");

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$commentForm =& new LayoutCommentForm($monograph, ROLE_ID_LAYOUT_EDITOR);
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
	 * View proofread comments.
	 * @param $monograph object
	 */
	function viewProofreadComments($monograph) {
		if (!HookRegistry::call('LayoutEditorAction::viewProofreadComments', array(&$monograph))) {
			import("submission.form.comment.ProofreadCommentForm");

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$commentForm =& new ProofreadCommentForm($monograph, ROLE_ID_LAYOUT_EDITOR);
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
			import('submission.form.comment.ProofreadCommentForm');

			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$commentForm =& new ProofreadCommentForm($monograph, ROLE_ID_LAYOUT_EDITOR);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();
				
				// Send a notification to associated users
				import('notification.Notification');
				$notificationUsers = $monograph->getAssociatedUserIds(true, false);
				foreach ($notificationUsers as $user) {
					$url = Request::url(null, $user['role'], 'submissionEditing', $monograph->getMonographId(), null, 'proofread');
					Notification::createNotification($user['id'], "notification.type.proofreadComment",
						$monograph->getMonographTitle(), $url, 1, NOTIFICATION_TYPE_PROOFREAD_COMMENT);
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
	 * This includes: The layout editor submission file, supplementary files, and galley files.
	 * @param $monograph object
	 * @param $fileId int
	 * @param $revision int optional
	 * @return boolean
	 */
	function downloadFile($monograph, $fileId, $revision = null) {
		$canDownload = false;

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$suppDao =& DAORegistry::getDAO('SuppFileDAO');

		$layoutAssignment =& $signoffDao->build(
						  'SIGNOFF_LAYOUT',
						  ASSOC_TYPE_MONOGRAPH,
						  $monograph->getMonographId()
					);

		if ($layoutAssignment->getFileId() == $fileId) {
			$canDownload = true;

		} else if($galleyDao->galleyExistsByFileId($monograph->getMonographId(), $fileId)) {
			$canDownload = true;

		} else if($suppDao->suppFileExistsByFileId($monograph->getMonographId(), $fileId)) {
			$canDownload = true;
		}

		$result = false;
		if (!HookRegistry::call('LayoutEditorAction::downloadFile', array(&$monograph, &$fileId, &$revision, &$canDownload, &$result))) {
			if ($canDownload) {
				return parent::downloadFile($monograph->getMonographId(), $fileId, $revision);
			} else {
				return false;
			}
		}
		return $result;
	}
}

?>
