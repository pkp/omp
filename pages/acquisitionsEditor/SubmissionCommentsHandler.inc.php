<?php

/**
 * @file SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_sectionEditor
 *
 * @brief Handle requests for submission comments. 
 */

// $Id$


import('pages.acquisitionsEditor.SubmissionEditHandler');

class SubmissionCommentsHandler extends AcquisitionsEditorHandler {

	/**
	 * View peer review comments.
	 */
	function viewPeerReviewComments($args) {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = $args[0];
		$reviewId = $args[1];

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		AcquisitionsEditorAction::viewPeerReviewComments($submission, $reviewId);

	}

	/**
	 * Post peer review comments.
	 */
	function postPeerReviewComment() {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$monographId = Request::getUserVar('monographId');
		$reviewId = Request::getUserVar('reviewId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		list($journal, $submission) = SubmissionEditHandler::validate($monographId);
		if (AcquisitionsEditorAction::postPeerReviewComment($submission, $reviewId, $emailComment)) {
			AcquisitionsEditorAction::viewPeerReviewComments($submission, $reviewId);
		}

	}

	/**
	 * View editor decision comments.
	 */
	function viewEditorDecisionComments($args) {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = $args[0];

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		AcquisitionsEditorAction::viewEditorDecisionComments($submission);

	}

	/**
	 * Post peer review comments.
	 */
	function postEditorDecisionComment() {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = Request::getUserVar('articleId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		if (AcquisitionsEditorAction::postEditorDecisionComment($submission, $emailComment)) {
			AcquisitionsEditorAction::viewEditorDecisionComments($submission);
		}

	}

	/**
	 * Blind CC the reviews to reviewers.
	 */
	function blindCcReviewsToReviewers($args = array()) {
		$articleId = Request::getUserVar('articleId');
		list($journal, $submission) = SubmissionEditHandler::validate($articleId);

		$send = Request::getUserVar('send')?true:false;
		$inhibitExistingEmail = Request::getUserVar('blindCcReviewers')?true:false;

		if (!$send) parent::setupTemplate(true, $articleId, 'editing');
		if (AcquisitionsEditorAction::blindCcReviewsToReviewers($submission, $send, $inhibitExistingEmail)) {
			Request::redirect(null, null, 'submissionReview', $articleId);
		}
	}

	/**
	 * View copyedit comments.
	 */
	function viewCopyeditComments($args) {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = $args[0];

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		AcquisitionsEditorAction::viewCopyeditComments($submission);

	}

	/**
	 * Post copyedit comment.
	 */
	function postCopyeditComment() {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = Request::getUserVar('articleId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		if (AcquisitionsEditorAction::postCopyeditComment($submission, $emailComment)) {
			AcquisitionsEditorAction::viewCopyeditComments($submission);
		}

	}

	/**
	 * View layout comments.
	 */
	function viewLayoutComments($args) {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = $args[0];

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		AcquisitionsEditorAction::viewLayoutComments($submission);

	}

	/**
	 * Post layout comment.
	 */
	function postLayoutComment() {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = Request::getUserVar('articleId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		if (AcquisitionsEditorAction::postLayoutComment($submission, $emailComment)) {
			AcquisitionsEditorAction::viewLayoutComments($submission);
		}

	}

	/**
	 * View proofread comments.
	 */
	function viewProofreadComments($args) {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = $args[0];

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		AcquisitionsEditorAction::viewProofreadComments($submission);

	}

	/**
	 * Post proofread comment.
	 */
	function postProofreadComment() {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = Request::getUserVar('articleId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		if (AcquisitionsEditorAction::postProofreadComment($submission, $emailComment)) {
			AcquisitionsEditorAction::viewProofreadComments($submission);
		}

	}

	/**
	 * Email an editor decision comment.
	 */
	function emailEditorDecisionComment() {
		$articleId = (int) Request::getUserVar('monographId');
		list($journal, $submission) = SubmissionEditHandler::validate($monographId);

		parent::setupTemplate(true);		
		if (AcquisitionsEditorAction::emailEditorDecisionComment($submission, Request::getUserVar('send'))) {
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
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = $args[0];
		$commentId = $args[1];

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		list($comment) = SubmissionCommentsHandler::validate($commentId);

		if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			// Cannot edit an editor decision comment.
			Request::redirect(null, Request::getRequestedPage());
		}

		AcquisitionsEditorAction::editComment($submission, $comment);

	}

	/**
	 * Save comment.
	 */
	function saveComment() {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = Request::getUserVar('articleId');
		$commentId = Request::getUserVar('commentId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		list($comment) = SubmissionCommentsHandler::validate($commentId);

		if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			// Cannot edit an editor decision comment.
			Request::redirect(null, Request::getRequestedPage());
		}

		// Save the comment.
		AcquisitionsEditorAction::saveComment($submission, $comment, $emailComment);

		$articleCommentDao = &DAORegistry::getDAO('ArticleCommentDAO');
		$comment = &$articleCommentDao->getArticleCommentById($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
			Request::redirect(null, null, 'viewPeerReviewComments', array($articleId, $comment->getAssocId()));
		} else if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			Request::redirect(null, null, 'viewEditorDecisionComments', $articleId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
			Request::redirect(null, null, 'viewCopyeditComments', $articleId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
			Request::redirect(null, null, 'viewLayoutComments', $articleId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
			Request::redirect(null, null, 'viewProofreadComments', $articleId);
		}
	}

	/**
	 * Delete comment.
	 */
	function deleteComment($args) {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate(true);

		$articleId = $args[0];
		$commentId = $args[1];

		list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		list($comment) = SubmissionCommentsHandler::validate($commentId);
		AcquisitionsEditorAction::deleteComment($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
			Request::redirect(null, null, 'viewPeerReviewComments', array($articleId, $comment->getAssocId()));
		} else if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			Request::redirect(null, null, 'viewEditorDecisionComments', $articleId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
			Request::redirect(null, null, 'viewCopyeditComments', $articleId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
			Request::redirect(null, null, 'viewLayoutComments', $articleId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
			Request::redirect(null, null, 'viewProofreadComments', $articleId);
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

		$articleCommentDao = &DAORegistry::getDAO('ArticleCommentDAO');
		$user = &Request::getUser();

		$comment = &$articleCommentDao->getArticleCommentById($commentId);

		if (
			$comment == null ||
			$comment->getAuthorId() != $user->getUserId()
		) {
			Request::redirect(null, Request::getRequestedPage());
		}

		return array($comment);
	}
}
?>
