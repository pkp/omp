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

	//
	// Layout Editing
	//

	/**
	 * Upload the layout version of a monograph.
	 * @param $submission object
	 */
	function uploadLayoutVersion($submission) {
		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($submission->getMonographId());
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getMonographId());

		$fileName = 'layoutFile';
		if ($monographFileManager->uploadedFileExists($fileName) && !HookRegistry::call('AcquisitionsEditorAction::uploadLayoutVersion', array(&$submission, &$layoutAssignment))) {
			if ($layoutSignoff->getFileId() != null) {
				$layoutFileId = $monographFileManager->uploadLayoutFile($fileName, $layoutSignoff->getFileId());
			} else {
				$layoutFileId = $monographFileManager->uploadLayoutFile($fileName);
			}			
			$layoutSignoff->setFileId($layoutFileId);
			$signoffDao->updateObject($layoutSignoff);
		}
	}

	/**
	 * Assign a layout editor to a submission.
	 * @param $submission object
	 * @param $editorId int user ID of the new layout editor
	 */
	function assignLayoutEditor($submission, $designerId) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		if (HookRegistry::call('AcquisitionsEditorAction::assignLayoutEditor', array(&$submission, &$designerId))) return;

		import('monograph.log.MonographLog');
		import('monograph.log.MonographEventLogEntry');

		$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getMonographId());
		$layoutProofSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getMonographId());
		if ($layoutSignoff->getUserId()) {
			$layoutEditor =& $userDao->getUser($layoutSignoff->getUserId());
			MonographLog::logEvent($submission->getMonographId(), MONOGRAPH_LOG_LAYOUT_UNASSIGN, MONOGRAPH_LOG_TYPE_LAYOUT, $layoutSignoff->getId(), 'log.layout.layoutEditorUnassigned', array('editorName' => $layoutEditor->getFullName(), 'monographId' => $submission->getMonographId()));
		}
		
		$layoutSignoff->setUserId($designerId);
		$layoutSignoff->setDateNotified(null);
		$layoutSignoff->setDateUnderway(null);
		$layoutSignoff->setDateCompleted(null);
		$layoutSignoff->setDateAcknowledged(null);
		$layoutProofSignoff->setUserId($designerId);
		$layoutProofSignoff->setDateNotified(null);
		$layoutProofSignoff->setDateUnderway(null);
		$layoutProofSignoff->setDateCompleted(null);
		$layoutProofSignoff->setDateAcknowledged(null);
		$signoffDao->updateObject($layoutSignoff);
		$signoffDao->updateObject($layoutProofSignoff);

		$layoutEditor =& $userDao->getUser($layoutSignoff->getUserId());
		MonographLog::logEvent($submission->getMonographId(), MONOGRAPH_LOG_LAYOUT_ASSIGN, MONOGRAPH_LOG_TYPE_LAYOUT, $layoutSignoff->getId(), 'log.layout.layoutEditorAssigned', array('editorName' => $layoutEditor->getFullName(), 'monographId' => $submission->getMonographId()));
	}

	/**
	 * Notifies the current layout editor about an assignment.
	 * @param $submission object
	 * @param $layoutAssignmentId int
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function notifyLayoutDesigner($submission, $layoutAssignmentId, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$submissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_REQUEST');
		$layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getMonographId());
		$layoutEditor =& $userDao->getUser($layoutSignoff->getUserId());
		if (!isset($layoutEditor)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('AcquisitionsEditorAction::notifyLayoutEditor', array(&$submission, &$layoutEditor, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_NOTIFY_EDITOR, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutSignoff->getId());
				$email->send();
			}

			$layoutSignoff->setDateNotified(Core::getCurrentDate());
			$layoutSignoff->setDateUnderway(null);
			$layoutSignoff->setDateCompleted(null);
			$layoutSignoff->setDateAcknowledged(null);
			$signoffDao->updateObject($layoutSignoff);
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($layoutEditor->getEmail(), $layoutEditor->getFullName());
				$paramArray = array(
					'layoutEditorName' => $layoutEditor->getFullName(),
					'layoutEditorUsername' => $layoutEditor->getUsername(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionLayoutUrl' => Request::url(null, 'layoutDesigner', 'submission', $submission->getMonographId())
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'notifyLayoutDesigner', 'send'), array('monographId' => $submission->getMonographId()));
			return false;
		}
		return true;
	}

	/**
	 * Sends acknowledgement email to the current layout designer.
	 * @param $submission object
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function thankLayoutDesigner($submission, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$submissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_ACK');

		$layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getMonographId());
		$layoutEditor =& $userDao->getUser($layoutSignoff->getUserId());
		if (!isset($layoutEditor)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('AcquisitionsEditorAction::thankLayoutEditor', array(&$submission, &$layoutEditor, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_THANK_EDITOR, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutSignoff->getId());
				$email->send();
			}

			$layoutSignoff->setDateAcknowledged(Core::getCurrentDate());
			$signoffDao->updateObject($layoutSignoff);

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
		import('submission.designer.DesignerAction');
		DesignerAction::orderGalley($monograph, $galleyId, $direction);
	}

	/**
	 * Delete a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 */
	function deleteGalley($monograph, $galleyId) {
		import('submission.designer.Designer');
		DesignerAction::deleteGalley($monograph, $galleyId);
	}

	/**
	 * Delete an image from a monograph galley.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int (optional)
	 */
	function deleteMonographImage($submission, $fileId, $revision) {
		import('submission.designer.DesignerAction');
		DesginerAction::deleteMonographImage($submission, $fileId, $revision);
	}

	/**
	 * Change the sequence order of a supplementary file.
	 * @param $monograph object
	 * @param $suppFileId int
	 * @param $direction char u = up, d = down
	 */
	function orderSuppFile($monograph, $suppFileId, $direction) {
		$suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
		$suppFile =& $suppFileDao->getSuppFile($suppFileId, $monograph->getMonographId());

		if (isset($suppFile)) {
			$suppFile->setSequence($suppFile->getSequence() + ($direction == 'u' ? -1.5 : 1.5));
			$suppFileDao->updateSuppFile($suppFile);
			$suppFileDao->resequenceSuppFiles($monograph->getMonographId());
		}
	}

	/**
	 * Delete a supplementary file.
	 * @param $monograph object
	 * @param $suppFileId int
	 */
	function deleteSuppFile($monograph, $suppFileId) {
		import('file.MonographFileManager');

		$suppFileDao =& DAORegistry::getDAO('SuppFileDAO');

		$suppFile =& $suppFileDao->getSuppFile($suppFileId, $monograph->getMonographId());
		if (isset($suppFile) && !HookRegistry::call('LayoutEditorAction::deleteSuppFile', array(&$monograph, &$suppFile))) {
			if ($suppFile->getFileId()) {
				$monographFileManager = new MonographFileManager($monograph->getMonographId());
				$monographFileManager->deleteFile($suppFile->getFileId());
				import('search.MonographSearchIndex');
				MonographSearchIndex::deleteTextIndex($monograph->getMonographId(), MONOGRAPH_SEARCH_SUPPLEMENTARY_FILE, $suppFile->getFileId());
			}
			$suppFileDao->deleteSuppFile($suppFile);
		}
	}

	/**
	 * Delete a file from a monograph.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int (optional)
	 */
/*	function deleteMonographFile($submission, $fileId, $revision) {
		import('file.MonographFileManager');
		$file =& $submission->getEditorFile();

		if (isset($file) && $file->getFileId() == $fileId && !HookRegistry::call('AcquisitionsEditorAction::deleteMonographFile', array(&$submission, &$fileId, &$revision))) {
			$monographFileManager = new MonographFileManager($submission->getMonographId());
			$monographFileManager->deleteFile($fileId, $revision);
		}
	}
*/
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
		$commentForm =& new LayoutCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_ACQUISITIONS_EDITOR);
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
		$commentForm =& new LayoutCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_ACQUISITIONS_EDITOR);
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
		$commentForm =& new ProofreadCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_ACQUISITIONS_EDITOR);
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
		$commentForm =& new ProofreadCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_ACQUISITIONS_EDITOR);
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