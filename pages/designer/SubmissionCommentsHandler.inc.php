<?php

/**
 * @file SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_designer
 *
 * @brief Handle requests for submission comments. 
 */



import('pages.designer.SubmissionLayoutHandler');

class SubmissionCommentsHandler extends SubmissionLayoutHandler {
	/** comment associated with request **/
	var $comment;
	
	function SubmissionCommentsHandler() {
		parent::SubmissionLayoutHandler();
	}

	/**
	 * View layout comments.
	 */
	function viewLayoutComments($args) {
		$monographId = $args[0];

		$submissionLayoutHandler = new SubmissionLayoutHandler();
		$submissionLayoutHandler->validate($monographId);
		$press =& $submissionLayoutHandler->press;
		$submission =& $submissionLayoutHandler->submission;

		$this->setupTemplate(true);		
		DesignerAction::viewLayoutComments($submission);

	}

	/**
	 * Post layout comment.
	 */
	function postLayoutComment() {
		$monographId = Request::getUserVar('monographId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionLayoutHandler = new SubmissionLayoutHandler();
		$submissionLayoutHandler->validate($monographId);
		$press =& $submissionLayoutHandler->press;
		$submission =& $submissionLayoutHandler->submission;
		
		$this->setupTemplate(true);		
		if (DesignerAction::postLayoutComment($submission, $emailComment)) {
			DesignerAction::viewLayoutComments($submission);
		}

	}

	/**
	 * View proofread comments.
	 */
	function viewProofreadComments($args) {
		$monographId = $args[0];

		$submissionLayoutHandler = new SubmissionLayoutHandler();
		$submissionLayoutHandler->validate($monographId);
		$press =& $submissionLayoutHandler->press;
		$submission =& $submissionLayoutHandler->submission;

		$this->setupTemplate(true);
		DesignerAction::viewProofreadComments($submission);

	}

	/**
	 * Post proofread comment.
	 */
	function postProofreadComment() {
		$monographId = Request::getUserVar('monographId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionLayoutHandler = new SubmissionLayoutHandler();
		$submissionLayoutHandler->validate($monographId);
		$press =& $submissionLayoutHandler->press;
		$submission =& $submissionLayoutHandler->submission;
		
		$this->setupTemplate(true);		
		if (DesignerAction::postProofreadComment($submission, $emailComment)) {
			DesignerAction::viewProofreadComments($submission);
		}

	}

	/**
	 * Edit comment.
	 */
	function editComment($args) {
		$monographId = $args[0];
		$commentId = $args[1];

		$submissionLayoutHandler = new SubmissionLayoutHandler();
		$submissionLayoutHandler->validate($monographId);
		$press =& $submissionLayoutHandler->press;
		$submission =& $submissionLayoutHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		
		$this->setupTemplate(true);		
		DesignerAction::editComment($submission, $comment);

	}

	/**
	 * Save comment.
	 */
	function saveComment() {
		$monographId = Request::getUserVar('monographId');
		$commentId = Request::getUserVar('commentId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionLayoutHandler = new SubmissionLayoutHandler();
		$submissionLayoutHandler->validate($monographId);
		$press =& $submissionLayoutHandler->press;
		$submission =& $submissionLayoutHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		
		$this->setupTemplate(true);

		DesignerAction::saveComment($submission, $comment, $emailComment);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
			Request::redirect(null, null, 'viewLayoutComments', $monographId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
			Request::redirect(null, null, 'viewProofreadComments', $monographId);
		}
	}

	/**
	 * Delete comment.
	 */
	function deleteComment($args) {
		$monographId = $args[0];
		$commentId = $args[1];

		$submissionLayoutHandler = new SubmissionLayoutHandler();
		$submissionLayoutHandler->validate($monographId);
		$press =& $submissionLayoutHandler->press;
		$submission =& $submissionLayoutHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		
		$this->setupTemplate(true);		
		DesignerAction::deleteComment($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
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
	function validate($commentId) {
		parent::validate();

		if ( !is_null($commentId) ) {
			$isValid = true;

			$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
			$user =& Request::getUser();

			$comment =& $monographCommentDao->getMonographCommentById($commentId);

			if ($comment == null) {
				$isValid = false;

			} else if ($comment->getAuthorId() != $user->getId()) {
				$isValid = false;
			}

			if (!$isValid) {
				Request::redirect(null, Request::getRequestedPage());
			}
			$this->comment =& $comment;
		}
		return true;
	}
}
?>
