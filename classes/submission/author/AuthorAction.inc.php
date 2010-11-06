<?php

/**
 * @file classes/submission/author/AuthorAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorAction
 * @ingroup submission
 *
 * @brief AuthorAction class.
 */


import('classes.submission.common.Action');

class AuthorAction extends Action {

	/**
	 * Constructor.
	 */
	function AuthorAction() {
		parent::Action();
	}

	/**
	 * Actions.
	 */
	/**
	 * Upload the revised version of a copyedit file.
	 * @param $authorSubmission object
	 * @param $copyeditStage string
	 */
	function uploadCopyeditVersion($authorSubmission, $copyeditStage) {
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($authorSubmission->getId());
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		// Authors cannot upload if the assignment is not active, i.e.
		// they haven't been notified or the assignment is already complete.
		$authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_MONOGRAPH, $authorSubmission->getId());
		if (!$authorSignoff->getDateNotified() || $authorSignoff->getDateCompleted()) return;

		$fileName = 'upload';
		if ($monographFileManager->uploadedFileExists($fileName)) {
			HookRegistry::call('AuthorAction::uploadCopyeditVersion', array(&$authorSubmission, &$copyeditStage));
			if ($authorSignoff->getFileId() != null) {
				$fileId = $monographFileManager->uploadCopyeditFile($fileName, $authorSignoff->getFileId());
			} else {
				$fileId = $monographFileManager->uploadCopyeditFile($fileName);
			}
		}

		$authorSignoff->setFileId($fileId);

		if ($copyeditStage == 'author') {
			$authorSignoff->setFileRevision($monographFileDao->getRevisionNumber($fileId));
		}

		$signoffDao->updateObject($authorSignoff);
	}

	//
	// Comments
	//

	/**
	 * View layout comments.
	 * @param $monograph object
	 */
	function viewLayoutComments($monograph) {
		if (!HookRegistry::call('AuthorAction::viewLayoutComments', array(&$monograph))) {
			import('classes.submission.form.comment.LayoutCommentForm');
			$commentForm = new LayoutCommentForm($monograph, ROLE_ID_EDITOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post layout comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postLayoutComment($monograph, $emailComment) {
		if (!HookRegistry::call('AuthorAction::postLayoutComment', array(&$monograph, &$emailComment))) {
			import('classes.submission.form.comment.LayoutCommentForm');

			$commentForm = new LayoutCommentForm($monograph, ROLE_ID_AUTHOR);
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

	/**
	 * View editor decision comments.
	 * @param $monograph object
	 */
	function viewEditorDecisionComments($monograph) {
		if (!HookRegistry::call('AuthorAction::viewEditorDecisionComments', array(&$monograph))) {
			import('classes.submission.form.comment.EditorDecisionCommentForm');

			$commentForm = new EditorDecisionCommentForm($monograph, ROLE_ID_AUTHOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Email editor decision comment.
	 * @param $authorSubmission object
	 * @param $send boolean
	 */
	function emailEditorDecisionComment($authorSubmission, $send) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		$user =& Request::getUser();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($authorSubmission);

		// FIXME #5880: Get IDs from Monograph->getAssociatedUserIds, or remove this class if not needed
		$editAssignments = $authorSubmission->getEditAssignments();
		$editors = array();
		foreach ($editAssignments as $editAssignment) {
			array_push($editors, $userDao->getUser($editAssignment->getEditorId()));
		}

		if ($send && !$email->hasErrors()) {
			HookRegistry::call('AuthorAction::emailEditorDecisionComment', array(&$authorSubmission, &$email));
			$email->send();

			$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
			$monographComment = new MonographComment();
			$monographComment->setCommentType(COMMENT_TYPE_EDITOR_DECISION);
			$monographComment->setRoleId(ROLE_ID_AUTHOR);
			$monographComment->setMonographId($authorSubmission->getId());
			$monographComment->setAuthorId($authorSubmission->getUserId());
			$monographComment->setCommentTitle($email->getSubject());
			$monographComment->setComments($email->getBody());
			$monographComment->setDatePosted(Core::getCurrentDate());
			$monographComment->setViewable(true);
			$monographComment->setAssocId($authorSubmission->getId());
			$monographCommentDao->insertMonographComment($monographComment);

			return true;
		} else {
			if (!Request::getUserVar('continued')) {
				$email->setSubject($authorSubmission->getLocalizedTitle());
				if (!empty($editors)) {
					foreach ($editors as $editor) {
						$email->addRecipient($editor->getEmail(), $editor->getFullName());
					}
				} else {
					$email->addRecipient($press->getSetting('contactEmail'), $press->getSetting('contactName'));
				}
			}

			$email->displayEditForm(Request::url(null, null, 'emailEditorDecisionComment', 'send'), array('monographId' => $authorSubmission->getId()), 'submission/comment/editorDecisionEmail.tpl');

			return false;
		}
	}

	/**
	 * View copyedit comments.
	 * @param $monograph object
	 */
	function viewCopyeditComments($monograph) {
		if (!HookRegistry::call('AuthorAction::viewCopyeditComments', array(&$monograph))) {
			import('classes.submission.form.comment.CopyeditCommentForm');

			$commentForm = new CopyeditCommentForm($monograph, ROLE_ID_AUTHOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post copyedit comment.
	 * @param $monograph object
	 */
	function postCopyeditComment($monograph, $emailComment) {
		if (!HookRegistry::call('AuthorAction::postCopyeditComment', array(&$monograph, &$emailComment))) {
			import('classes.submission.form.comment.CopyeditCommentForm');

			$commentForm = new CopyeditCommentForm($monograph, ROLE_ID_AUTHOR);
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

	/**
	 * View proofread comments.
	 * @param $monograph object
	 */
	function viewProofreadComments($monograph) {
		if (!HookRegistry::call('AuthorAction::viewProofreadComments', array(&$monograph))) {
			import('classes.submission.form.comment.ProofreadCommentForm');

			$commentForm = new ProofreadCommentForm($monograph, ROLE_ID_AUTHOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post proofread comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postProofreadComment($monograph, $emailComment) {
		if (!HookRegistry::call('AuthorAction::postProofreadComment', array(&$monograph, &$emailComment))) {
			import('classes.submission.form.comment.ProofreadCommentForm');

			$commentForm = new ProofreadCommentForm($monograph, ROLE_ID_AUTHOR);
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

	//
	// Misc
	//

	/**
	 * Download a file an author has access to.
	 * @param $monograph object
	 * @param $fileId int
	 * @param $revision int
	 * @return boolean
	 * TODO: Complete list of files author has access to
	 */
	function downloadAuthorFile($monograph, $fileId, $revision = null) {
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');

		$submission =& $authorSubmissionDao->getAuthorSubmission($monograph->getId());
		$layoutSignoff =& $submission->getSignoff('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getId());

		$canDownload = false;

		// Authors have access to:
		// 1) The original submission file.
		// 2) Any files uploaded by the reviewers that are "viewable",
		//    although only after a decision has been made by the editor.
		// 3) The initial and final copyedit files, after initial copyedit is complete.
		// 4) Any of the author-revised files.
		// 5) The layout version of the file.
		// 7) Any galley file
		// 8) All review versions of the file
		// 9) Current editor versions of the file
		// THIS LIST SHOULD NOW BE COMPLETE.
		if ($submission->getSubmissionFileId() == $fileId) {
			$canDownload = true;
		} else if ($submission->getCopyeditFileId() == $fileId) {
			if ($revision != null) {
				$copyAssignmentDao =& DAORegistry::getDAO('CopyAssignmentDAO');
				$copyAssignment =& $copyAssignmentDao->getCopyAssignmentByMonographId($monograph->getId());
				if ($copyAssignment && $copyAssignment->getInitialRevision()==$revision && $copyAssignment->getDateCompleted()!=null) $canDownload = true;
				else if ($copyAssignment && $copyAssignment->getFinalRevision()==$revision && $copyAssignment->getDateFinalCompleted()!=null) $canDownload = true;
				else if ($copyAssignment && $copyAssignment->getEditorAuthorRevision()==$revision) $canDownload = true;
			} else {
				$canDownload = false;
			}
		} else if ($submission->getRevisedFileId() == $fileId) {
			$canDownload = true;
		} else if ($layoutSignoff->getFileId() == $fileId) {
			$canDownload = true;
		} else {
			// Check reviewer files
			foreach ($submission->getReviewAssignments() as $roundReviewAssignments) {
				foreach ($roundReviewAssignments as $reviewAssignment) {
					if ($reviewAssignment->getReviewerFileId() == $fileId) {
						$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

						$monographFile =& $monographFileDao->getMonographFile($fileId, $revision);

						if ($monographFile != null && $monographFile->getViewable()) {
							$canDownload = true;
						}
					}
				}
			}

			// Check galley files
			foreach ($submission->getGalleys() as $galleyFile) {
				if ($galleyFile->getFileId() == $fileId) {
					$canDownload = true;
				}
			}

			// Check current review version
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewFilesByRound =& $reviewAssignmentDao->getReviewFilesByRound($monograph->getId());
			$reviewFile = @$reviewFilesByRound[$monograph->getCurrentRound()];
			if ($reviewFile && $fileId == $reviewFile->getFileId()) {
				$canDownload = true;
			}

			// Check editor version
			$editorFiles = $submission->getEditorFileRevisions($monograph->getCurrentRound());
			if (is_array($editorFiles)) foreach ($editorFiles as $editorFile) {
				if ($editorFile->getFileId() == $fileId) {
					$canDownload = true;
				}
			}
		}

		$result = false;
		if (!HookRegistry::call('AuthorAction::downloadAuthorFile', array(&$monograph, &$fileId, &$revision, &$canDownload, &$result))) {
			if ($canDownload) {
				return Action::downloadFile($monograph->getId(), $fileId, $revision);
			} else {
				return false;
			}
		}
		return $result;
	}
}

?>
