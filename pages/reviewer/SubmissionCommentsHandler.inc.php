<?php

/**
 * @file SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for submission comments. 
 */



import('pages.reviewer.SubmissionReviewHandler');

class SubmissionCommentsHandler extends ReviewerHandler {
	/** comment associated with the request **/
	var $comment;
	
	/**
	 * Constructor
	 **/
	function SubmissionCommentsHandler() {
		parent::ReviewerHandler();
	}
	
	/**
	 * View peer review comments.
	 */
	function viewPeerReviewComments($args) {
		$monographId = $args[0];
		$reviewId = $args[1];

		$submissionReviewHandler = new SubmissionReviewHandler();
		$submissionReviewHandler->validate($reviewId);
		$submission =& $submissionReviewHandler->submission;
		$user =& $submissionReviewHandler->user;

		$this->setupTemplate(true);
		ReviewerAction::viewPeerReviewComments($user, $submission, $reviewId);

	}

	/**
	 * Post peer review comments.
	 */
	function postPeerReviewComment() {
		$monographId = Request::getUserVar('monographId');
		$reviewId = Request::getUserVar('reviewId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionReviewHandler = new SubmissionReviewHandler();
		$submissionReviewHandler->validate($reviewId);
		$submission =& $submissionReviewHandler->submission;
		$user =& $submissionReviewHandler->user;

		$this->setupTemplate(true);
		if (ReviewerAction::postPeerReviewComment($user, $submission, $reviewId, $emailComment)) {
			ReviewerAction::viewPeerReviewComments($user, $submission, $reviewId);
		}
	}

	/**
	 * Edit comment.
	 */
	function editComment($args) {
		$monographId = $args[0];
		$commentId = $args[1];
		$reviewId = Request::getUserVar('reviewId');

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getMonograph($monographId);

		$submissionReviewHandler = new SubmissionReviewHandler();
		$submissionReviewHandler->validate($reviewId);
		$submission =& $submissionReviewHandler->submission;
		$user =& $submissionReviewHandler->user;
		$this->validate($commentId);
		$comment =& $this->comment;

		$this->setupTemplate(true);

		ReviewerAction::editComment($monograph, $comment, $reviewId);
	}

	/**
	 * Save comment.
	 */
	function saveComment() {
		$monographId = Request::getUserVar('monographId');
		$commentId = Request::getUserVar('commentId');
		$reviewId = Request::getUserVar('reviewId');

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getMonograph($monographId);

		$submissionReviewHandler = new SubmissionReviewHandler();
		$submissionReviewHandler->validate($reviewId);
		$submission =& $submissionReviewHandler->submission;
		$user =& $submissionReviewHandler->user;
		$this->validate($commentId);
		$comment =& $this->comment;

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$this->setupTemplate(true);

		ReviewerAction::saveComment($monograph, $comment, $emailComment);

		// Refresh the comment
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$comment =& $monographCommentDao->getMonographCommentById($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
			Request::redirect(null, null, 'viewPeerReviewComments', array($monographId, $comment->getAssocId()));
		}
	}

	/**
	 * Delete comment.
	 */
	function deleteComment($args) {
		$monographId = $args[0];
		$commentId = $args[1];
		$reviewId = Request::getUserVar('reviewId');

		$submissionReviewHandler = new SubmissionReviewHandler();
		$submissionReviewHandler->validate($reviewId);
		$submission =& $submissionReviewHandler->submission;
		$user =& $submissionReviewHandler->user;
		$this->validate($commentId);
		$comment =& $this->comment;

		$this->setupTemplate(true);

		ReviewerAction::deleteComment($commentId, $user);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
			Request::redirect(null, null, 'viewPeerReviewComments', array($monographId, $comment->getAssocId()));
		}
	}

	//
	// Validation
	//

	/**
	 * Validate that the user is the author of the comment.
	 */
	function validate($user, $commentId) {
		$isValid = true;

		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
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
		return true;
	}
}
?>
