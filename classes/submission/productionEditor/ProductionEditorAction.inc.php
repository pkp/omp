<?php

/**
 * @file classes/submission/productionEditor/ProductionEditorAction.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionEditorAction
 * @ingroup submission
 *
 * @brief ProductionEditorAction class.
 */

// $Id$


import('submission.common.Action');

class ProductionEditorAction extends Action {

	/**
	 * Actions.
	 * Ideas: unsuitable art
	 */


	//
	// Layout Editing
	//

	/**
	 * Upload the layout version of an monograph.
	 * @param $submission object
	 */
	function uploadLayoutVersion($submission) {
		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($submission->getMonographId());
		$submissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');

		$fileName = 'layoutFile';
		if ($monographFileManager->uploadedFileExists($fileName) && !HookRegistry::call('AcquisitionsEditorAction::uploadLayoutVersion', array(&$submission, &$layoutAssignment))) {
			$layoutFileId = $monographFileManager->uploadLayoutFile($fileName);
			$submission->setLayoutFileId($layoutFileId);

			$submissionDao->updateAcquisitionsEditorSubmission($submission);
		}
	}

	/**
	 * Assign a layout editor to a submission.
	 * @param $submission object
	 * @param $editorId int user ID of the new layout editor
	 */
	function assignLayoutEditor($submission, $designerId) {
		if (HookRegistry::call('AcquisitionsEditorAction::assignLayoutEditor', array(&$submission, &$designerId))) return;

	//	$layoutAssignments =& $submission->getLayoutAssignments();

/*		import('monograph.log.MonographLog');
		import('monograph.log.MonographEventLogEntry');

		if ($layoutAssignment->getEditorId()) {
			MonographLog::logEvent($submission->getMonographId(), MONOGRAPH_LOG_LAYOUT_UNASSIGN, MONOGRAPH_LOG_TYPE_LAYOUT, $layoutAssignment->getLayoutId(), 'log.layout.layoutEditorUnassigned', array('editorName' => $layoutAssignment->getEditorFullName(), 'monographId' => $submission->getMonographId()));
		}
*/
		$layoutAssignment = new LayoutAssignment;
		$layoutAssignment->setDesignerId($designerId);
		$layoutAssignment->setDateNotified(null);
		$layoutAssignment->setDateUnderway(null);
		$layoutAssignment->setDateCompleted(null);
		$layoutAssignment->setDateAcknowledged(null);
		$layoutAssignment->setMonographId($submission->getMonographId());
		$layoutAssignment->setLayoutFileId($submission->getLayoutFileId());

		$layoutDao =& DAORegistry::getDAO('LayoutAssignmentDAO');

		$layoutDao->insertLayoutAssignment($layoutAssignment);

//		$layoutAssignment =& $layoutDao->getLayoutAssignmentById($layoutAssignment->getLayoutId());
/*
		MonographLog::logEvent($submission->getMonographId(), MONOGRAPH_LOG_LAYOUT_ASSIGN, MONOGRAPH_LOG_TYPE_LAYOUT, $layoutAssignment->getLayoutId(), 'log.layout.layoutEditorAssigned', array('editorName' => $layoutAssignment->getEditorFullName(), 'monographId' => $submission->getMonographId()));
*/	}

	/**
	 * Notifies the current layout editor about an assignment.
	 * @param $submission object
	 * @param $layoutAssignmentId int
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function notifyLayoutDesigner($submission, $layoutAssignmentId, $send = false) {
		$layoutAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_REQUEST');

		$layoutAssignments =& $submission->getLayoutAssignments();

		foreach ($layoutAssignments as $layoutAssignmentItem) {
			if ($layoutAssignmentItem->getId() == $layoutAssignmentId) {
				$layoutAssignment =& $layoutAssignmentItem;
				$layoutDesigner =& $userDao->getUser($layoutAssignmentItem->getDesignerId());
				break;
			}
		}

		if (!isset($layoutDesigner)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('AcquisitionsEditorAction::notifyLayoutEditor', array(&$submission, &$layoutDesigner, &$layoutAssignment, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_NOTIFY_EDITOR, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutAssignment->getId());
				$email->send();
			}
			$layoutAssignment->setDateNotified(Core::getCurrentDate());
			$layoutAssignment->setDateUnderway(null);
			$layoutAssignment->setDateCompleted(null);
			$layoutAssignment->setDateAcknowledged(null);
			$layoutAssignmentDao->updateLayoutAssignment($layoutAssignment);

		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($layoutDesigner->getEmail(), $layoutDesigner->getFullName());
				$paramArray = array(
					'layoutEditorName' => $layoutDesigner->getFullName(),
					'layoutEditorUsername' => $layoutDesigner->getUsername(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionLayoutUrl' => Request::url(null, 'layoutDesigner', 'submission', $submission->getMonographId())
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'notifyLayoutDesigner', 'send'), array('monographId' => $submission->getMonographId(), 'layoutAssignmentId' => $layoutAssignmentId));
			return false;
		}
		return true;
	}

	/**
	 * Sends acknowledgement email to the current layout editor.
	 * @param $submission object
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function thankLayoutEditor($submission, $send = false) {
		$submissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_ACK');

		$layoutAssignment =& $submission->getLayoutAssignment();
		$layoutEditor =& $userDao->getUser($layoutAssignment->getEditorId());
		if (!isset($layoutEditor)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('AcquisitionsEditorAction::thankLayoutEditor', array(&$submission, &$layoutEditor, &$layoutAssignment, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_THANK_EDITOR, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutAssignment->getLayoutId());
				$email->send();
			}

			$layoutAssignment->setDateAcknowledged(Core::getCurrentDate());
			$submissionDao->updateAcquisitionsEditorSubmission($submission);

		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($layoutEditor->getEmail(), $layoutEditor->getFullName());
				$paramArray = array(
					'layoutEditorName' => $layoutEditor->getFullName(),
					'editorialContactSignature' => $user->getContactSignature()
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'thankLayoutEditor', 'send'), array('monographId' => $submission->getMonographId()));
			return false;
		}
		return true;
	}

	/**
	 * Change the sequence order of a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 * @param $direction char u = up, d = down
	 */
	function orderGalley($monograph, $galleyId, $direction) {
		import('submission.layoutEditor.LayoutEditorAction');
		LayoutEditorAction::orderGalley($monograph, $galleyId, $direction);
	}

	/**
	 * Delete a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 */
	function deleteGalley($monograph, $galleyId) {
		import('submission.layoutEditor.LayoutEditorAction');
		LayoutEditorAction::deleteGalley($monograph, $galleyId);
	}

	/**
	 * Change the sequence order of a supplementary file.
	 * @param $monograph object
	 * @param $suppFileId int
	 * @param $direction char u = up, d = down
	 */
	function orderSuppFile($monograph, $suppFileId, $direction) {
		import('submission.layoutEditor.LayoutEditorAction');
		LayoutEditorAction::orderSuppFile($monograph, $suppFileId, $direction);
	}

	/**
	 * Delete a supplementary file.
	 * @param $monograph object
	 * @param $suppFileId int
	 */
	function deleteSuppFile($monograph, $suppFileId) {
		import('submission.layoutEditor.LayoutEditorAction');
		LayoutEditorAction::deleteSuppFile($monograph, $suppFileId);
	}

	/**
	 * Delete a file from an monograph.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int (optional)
	 */
	function deleteMonographFile($submission, $fileId, $revision) {
		import('file.MonographFileManager');
		$file =& $submission->getEditorFile();

		if (isset($file) && $file->getFileId() == $fileId && !HookRegistry::call('AcquisitionsEditorAction::deleteMonographFile', array(&$submission, &$fileId, &$revision))) {
			$monographFileManager = new MonographFileManager($submission->getMonographId());
			$monographFileManager->deleteFile($fileId, $revision);
		}
	}

	/**
	 * Delete an image from an monograph galley.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int (optional)
	 */
	function deleteMonographImage($submission, $fileId, $revision) {
		import('file.MonographFileManager');
		$monographGalleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		if (HookRegistry::call('AcquisitionsEditorAction::deleteMonographImage', array(&$submission, &$fileId, &$revision))) return;
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

	//
	// Comments
	//
	/**
	 * View layout comments.
	 * @param $monograph object
	 */
	function viewLayoutComments($monograph) {
		if (HookRegistry::call('AcquisitionsEditorAction::viewLayoutComments', array(&$monograph))) return;

		import('submission.form.comment.LayoutCommentForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$commentForm =& new LayoutCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SECTION_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post layout comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postLayoutComment($monograph, $emailComment) {
		if (HookRegistry::call('AcquisitionsEditorAction::postLayoutComment', array(&$monograph, &$emailComment))) return;

		import('submission.form.comment.LayoutCommentForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$commentForm =& new LayoutCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SECTION_EDITOR);
		$commentForm->readInputData();

		if ($commentForm->validate()) {
			$commentForm->execute();

			if ($emailComment) {
				$commentForm->email();
			}

		} else {
			$commentForm->display();
			return false;
		}
		return true;
	}

	/**
	 * View proofread comments.
	 * @param $monograph object
	 */
	function viewProofreadComments($monograph) {
		if (HookRegistry::call('AcquisitionsEditorAction::viewProofreadComments', array(&$monograph))) return;

		import('submission.form.comment.ProofreadCommentForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$commentForm =& new ProofreadCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SECTION_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post proofread comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postProofreadComment($monograph, $emailComment) {
		if (HookRegistry::call('AcquisitionsEditorAction::postProofreadComment', array(&$monograph, &$emailComment))) return;

		import('submission.form.comment.ProofreadCommentForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$commentForm =& new ProofreadCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SECTION_EDITOR);
		$commentForm->readInputData();

		if ($commentForm->validate()) {
			$commentForm->execute();

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

?>