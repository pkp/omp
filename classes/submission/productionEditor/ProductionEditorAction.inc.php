<?php

/**
 * @file classes/submission/productionEditor/ProductionEditorAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionEditorAction
 * @ingroup submission
 *
 * @brief ProductionEditorAction class.
 */

// $Id$

import('classes.submission.designer.DesignerAction');

class ProductionEditorAction extends DesignerAction {

	//
	// Layout Editing
	//

	/**
	 * Upload the layout version of a monograph.
	 * @param $submission object
	 */
	function uploadLayoutVersion($submission) {
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($submission->getId());
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getId());

		$fileName = 'layoutFile';
		if ($monographFileManager->uploadedFileExists($fileName) && !HookRegistry::call('SeriesEditorAction::uploadLayoutVersion', array(&$submission, &$layoutAssignment))) {
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
	 * Notifies the current designer about an assignment.
	 * @param $submission object
	 * @param $assignmentId int
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function notifyDesigner($submission, $assignmentId, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_REQUEST');
		$designSignoff = $signoffDao->getBySymbolic('PRODUCTION_DESIGN', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);
		$designer =& $userDao->getUser($designSignoff->getUserId());
		if (!isset($designer)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('ProductionEditorAction::notifyDesigner', array(&$submission, &$designer, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_NOTIFY_EDITOR, MONOGRAPH_EMAIL_TYPE_LAYOUT, $designSignoff->getId());
				$email->send();
			}

			$designSignoff->setDateNotified(Core::getCurrentDate());
			$designSignoff->setDateUnderway(null);
			$designSignoff->setDateCompleted(null);
			$designSignoff->setDateAcknowledged(null);
			$signoffDao->updateObject($designSignoff);
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($designer->getEmail(), $designer->getFullName());
				$paramArray = array(
					'layoutEditorName' => $designer->getFullName(),
					'layoutEditorUsername' => $designer->getUsername(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionLayoutUrl' => Request::url(null, 'designer', 'submission', $submission->getId())
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'notifyDesigner', 'send'), array('monographId' => $submission->getId(), 'assignmentId' => $assignmentId));
			return false;
		}
		return true;
	}

	/**
	 * Sends acknowledgement email to the current layout designer.
	 * @param $submission object
	 * @param $assignmentId int
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function thankLayoutDesigner($submission, $assignmentId, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_ACK');

		$layoutSignoff = $signoffDao->getBySymbolic('PRODUCTION_DESIGN', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);
		$layoutDesigner =& $userDao->getUser($layoutSignoff->getUserId());
		if (!isset($layoutDesigner)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::thankLayoutDesigner', array(&$submission, &$layoutDesigner, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_THANK_EDITOR, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutSignoff->getId());
				$email->send();
			}

			$layoutSignoff->setDateAcknowledged(Core::getCurrentDate());
			$signoffDao->updateObject($layoutSignoff);

		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($layoutDesigner->getEmail(), $layoutDesigner->getFullName());
				$paramArray = array(
					'layoutEditorName' => $layoutDesigner->getFullName(),
					'editorialContactSignature' => $user->getContactSignature()
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'thankLayoutDesigner', 'send'), array('monographId' => $submission->getId(), 'assignmentId' => $assignmentId));
			return false;
		}
		return true;
	}

	//
	// Comments
	//
	/**
	 * View layout comments.
	 * @param $monograph object
	 */
	function viewLayoutComments($monograph) {
		if (HookRegistry::call('SeriesEditorAction::viewLayoutComments', array(&$monograph))) return;

		import('classes.submission.form.comment.LayoutCommentForm');

		$commentForm = new LayoutCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post layout comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postLayoutComment($monograph, $emailComment) {
		if (HookRegistry::call('SeriesEditorAction::postLayoutComment', array(&$monograph, &$emailComment))) return;

		import('classes.submission.form.comment.LayoutCommentForm');

		$commentForm = new LayoutCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
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
		if (HookRegistry::call('SeriesEditorAction::viewProofreadComments', array(&$monograph))) return;

		import('classes.submission.form.comment.ProofreadCommentForm');

		$commentForm = new ProofreadCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post proofread comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postProofreadComment($monograph, $emailComment) {
		if (HookRegistry::call('SeriesEditorAction::postProofreadComment', array(&$monograph, &$emailComment))) return;

		import('classes.submission.form.comment.ProofreadCommentForm');

		$commentForm = new ProofreadCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
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
