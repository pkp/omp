<?php

/**
 * @file SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_copyeditor
 *
 * @brief Handle requests for submission comments. 
 */



import('pages.copyeditor.SubmissionCopyeditHandler');

class SubmissionCommentsHandler extends CopyeditorHandler {

	/** comment associated with this request **/
	var $comment;

	/**
	 * Constructor
	 **/
	function SubmissionCommentsHandler() {
		parent::CopyeditorHandler();
	}

	/**
	 * View layout comments.
	 */
	function viewLayoutComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$submissionCopyeditHandler = new SubmissionCopyeditHandler();
		$submissionCopyeditHandler->validate($monographId);
		$submission =& $submissionCopyeditHandler->submission;
		CopyeditorAction::viewLayoutComments($submission);
	}

	/**
	 * Post layout comment.
	 */
	function postLayoutComment() {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = Request::getUserVar('monographId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionCopyeditHandler = new SubmissionCopyeditHandler();
		$submissionCopyeditHandler->validate($monographId);
		$submission =& $submissionCopyeditHandler->submission;
		if (CopyeditorAction::postLayoutComment($submission, $emailComment)) {
			CopyeditorAction::viewLayoutComments($submission);
		}

	}

	/**
	 * View copyedit comments.
	 */
	function viewCopyeditComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$submissionCopyeditHandler = new SubmissionCopyeditHandler();
		$submissionCopyeditHandler->validate($monographId);
		$submission =& $submissionCopyeditHandler->submission;
		CopyeditorAction::viewCopyeditComments($submission);

	}

	/**
	 * Post copyedit comment.
	 */
	function postCopyeditComment() {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = Request::getUserVar('monographId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionCopyeditHandler = new SubmissionCopyeditHandler();
		$submissionCopyeditHandler->validate($monographId);
		$submission =& $submissionCopyeditHandler->submission;
		if (CopyeditorAction::postCopyeditComment($submission, $emailComment)) {
			CopyeditorAction::viewCopyeditComments($submission);
		}

	}

	/**
	 * Edit comment.
	 */
	function editComment($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];
		$commentId = $args[1];

		$submissionCopyeditHandler = new SubmissionCopyeditHandler();
		$submissionCopyeditHandler->validate($monographId);
		$submission =& $submissionCopyeditHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		CopyeditorAction::editComment($submission, $comment);

	}

	/**
	 * Save comment.
	 */
	function saveComment() {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = Request::getUserVar('monographId');
		$commentId = Request::getUserVar('commentId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionCopyeditHandler = new SubmissionCopyeditHandler();
		$submissionCopyeditHandler->validate($monographId);
		$submission =& $submissionCopyeditHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		CopyeditorAction::saveComment($submission, $comment, $emailComment);

		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$comment =& $monographCommentDao->getMonographCommentById($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
			Request::redirect(null, null, 'viewCopyeditComments', $monographId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
			Request::redirect(null, null, 'viewLayoutComments', $monographId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
			Request::redirect(null, null, 'viewProofreadComments', $monographId);
		}
	}

	/**
	 * Delete comment.
	 */
	function deleteComment($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];
		$commentId = $args[1];

		$submissionCopyeditHandler = new SubmissionCopyeditHandler();
		$submissionCopyeditHandler->validate($monographId);
		$this->validate($commentId);
		$comment = $this->comment;

		CopyeditorAction::deleteComment($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
			Request::redirect(null, null, 'viewCopyeditComments', $monographId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
			Request::redirect(null, null, 'viewLayoutComments', $monographId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
			Request::redirect(null, null, 'viewProofreadComments', $monographId);
		}
	}

	//
	// Validation
	//

	/**
	 * Validate that the user is the author of the comment.
	 */
	function validate($commentId = null) {
		parent::validate();

		if (!is_null($commentId)) {
			$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
			$user =& Request::getUser();

			$comment =& $monographCommentDao->getMonographCommentById($commentId);

			if ($comment == null || $comment->getAuthorId() != $user->getId()) {
				Request::redirect(null, Request::getRequestedPage());
			}

			$this->comment =& $comment;
		}

		return true;
	}
}
?>
