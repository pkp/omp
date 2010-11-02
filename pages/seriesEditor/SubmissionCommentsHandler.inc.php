<?php

/**
 * @file SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_seriesEditor
 *
 * @brief Handle requests for submission comments. 
 */



import('pages.seriesEditor.SubmissionEditHandler');

class SubmissionCommentsHandler extends SeriesEditorHandler {
	var $comment;

	/**
	 * Constructor
	 */
	function SubmissionCommentsHandler() {
		parent::SeriesEditorHandler();
	}

	/**
	 * View peer review comments.
	 */
	function viewPeerReviewComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];
		$reviewId = $args[1];

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;

		SeriesEditorAction::viewPeerReviewComments($submission, $reviewId);

	}

	/**
	 * Post peer review comments.
	 */
	function postPeerReviewComment() {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = Request::getUserVar('monographId');
		$reviewId = Request::getUserVar('reviewId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		if (SeriesEditorAction::postPeerReviewComment($submission, $reviewId, $emailComment)) {
			SeriesEditorAction::viewPeerReviewComments($submission, $reviewId);
		}

	}

	/**
	 * View editor decision comments.
	 */
	function viewEditorDecisionComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		SeriesEditorAction::viewEditorDecisionComments($submission);

	}

	/**
	 * Post peer review comments.
	 */
	function postEditorDecisionComment() {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = Request::getUserVar('monographId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		if (SeriesEditorAction::postEditorDecisionComment($submission, $emailComment)) {
			SeriesEditorAction::viewEditorDecisionComments($submission);
		}

	}

	/**
	 * Blind CC the reviews to reviewers.
	 */
	function blindCcReviewsToReviewers($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;

		$send = Request::getUserVar('send')?true:false;
		$inhibitExistingEmail = Request::getUserVar('blindCcReviewers')?true:false;

		if (!$send) parent::setupTemplate(true, $monographId, 'editing');
		if (SeriesEditorAction::blindCcReviewsToReviewers($submission, $send, $inhibitExistingEmail)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	/**
	 * View copyedit comments.
	 */
	function viewCopyeditComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		SeriesEditorAction::viewCopyeditComments($submission);

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

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		if (SeriesEditorAction::postCopyeditComment($submission, $emailComment)) {
			SeriesEditorAction::viewCopyeditComments($submission);
		}

	}

	/**
	 * View layout comments.
	 */
	function viewLayoutComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		SeriesEditorAction::viewLayoutComments($submission);

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

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		if (SeriesEditorAction::postLayoutComment($submission, $emailComment)) {
			SeriesEditorAction::viewLayoutComments($submission);
		}

	}

	/**
	 * View proofread comments.
	 */
	function viewProofreadComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		SeriesEditorAction::viewProofreadComments($submission);

	}

	/**
	 * Post proofread comment.
	 */
	function postProofreadComment() {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = Request::getUserVar('monographId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		if (SeriesEditorAction::postProofreadComment($submission, $emailComment)) {
			SeriesEditorAction::viewProofreadComments($submission);
		}

	}

	/**
	 * Email an editor decision comment.
	 */
	function emailEditorDecisionComment() {
		$monographId = (int) Request::getUserVar('monographId');

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;

		parent::setupTemplate(true);		
		if (SeriesEditorAction::emailEditorDecisionComment($submission, Request::getUserVar('send'))) {
			if (Request::getUserVar('blindCcReviewers')) {
				SubmissionCommentsHandler::blindCcReviewsToReviewers();
			} else {
				Request::redirect(null, null, 'submissionReview', array($monographId));
			}
		}
	}

	/**
	 * Edit comment.
	 */
	function editComment($args) {
		$monographId = $args[0];
		$commentId = $args[1];

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;

		$this->setupTemplate(true);

		if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			// Cannot edit an editor decision comment.
			Request::redirect(null, Request::getRequestedPage());
		}

		SeriesEditorAction::editComment($submission, $comment);

	}

	/**
	 * Save comment.
	 */
	function saveComment() {
		$monographId = Request::getUserVar('monographId');
		$commentId = Request::getUserVar('commentId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;

		$this->setupTemplate(true);

		if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			// Cannot edit an editor decision comment.
			Request::redirect(null, Request::getRequestedPage());
		}

		// Save the comment.
		SeriesEditorAction::saveComment($submission, $comment, $emailComment);

		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$comment =& $monographCommentDao->getMonographCommentById($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
			Request::redirect(null, null, 'viewPeerReviewComments', array($monographId, $comment->getAssocId()));
		} else if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			Request::redirect(null, null, 'viewEditorDecisionComments', $monographId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
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
		$monographId = $args[0];
		$commentId = $args[1];

		$submissionEditHandler = new SubmissionEditHandler();
		$submissionEditHandler->validate($monographId);
		$submission =& $submissionEditHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;

		$this->setupTemplate(true);

		SeriesEditorAction::deleteComment($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
			Request::redirect(null, null, 'viewPeerReviewComments', array($monographId, $comment->getAssocId()));
		} else if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			Request::redirect(null, null, 'viewEditorDecisionComments', $monographId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
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

		if ( !is_null($commentId) ) {
			$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
			$user =& Request::getUser();
	
			$comment =& $monographCommentDao->getMonographCommentById($commentId);
	
			if (
				$comment == null ||
				$comment->getAuthorId() != $user->getId()
			) {
				Request::redirect(null, Request::getRequestedPage());
			}
	
			$this->comment =& $comment;
		}
		return true;
	}
}
?>
