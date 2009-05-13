<?php

/**
 * @file SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_designer
 *
 * @brief Handle requests for submission comments. 
 */

// $Id$


import('pages.designer.SubmissionLayoutHandler');

class SubmissionCommentsHandler extends LayoutEditorHandler {
	/** comment associated with request **/
	var $comment;
	
	function SubmissionCommentsHandler() {
		parent::LayoutEditorHandler();
	}

	/**
	 * View layout comments.
	 */
	function viewLayoutComments($args) {
		$articleId = $args[0];

		$submissionLayoutHandler =& new SubmissionLayoutHandler()
		$submissionLayoutHandler->validate($articleId);
		$journal =& $submissionLayoutHandler->journal;
		$submission =& $submissionLayoutHandler->submission;

		$this->setupTemplate(true);		
		LayoutEditorAction::viewLayoutComments($submission);

	}

	/**
	 * Post layout comment.
	 */
	function postLayoutComment() {
		$articleId = Request::getUserVar('articleId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionLayoutHandler =& new SubmissionLayoutHandler()
		$submissionLayoutHandler->validate($articleId);
		$journal =& $submissionLayoutHandler->journal;
		$submission =& $submissionLayoutHandler->submission;
		
		$this->setupTemplate(true);		
		if (LayoutEditorAction::postLayoutComment($submission, $emailComment)) {
			LayoutEditorAction::viewLayoutComments($submission);
		}

	}

	/**
	 * View proofread comments.
	 */
	function viewProofreadComments($args) {
		$articleId = $args[0];

		$submissionLayoutHandler =& new SubmissionLayoutHandler()
		$submissionLayoutHandler->validate($articleId);
		$journal =& $submissionLayoutHandler->journal;
		$submission =& $submissionLayoutHandler->submission;

		$this->setupTemplate(true);
		LayoutEditorAction::viewProofreadComments($submission);

	}

	/**
	 * Post proofread comment.
	 */
	function postProofreadComment() {
		$articleId = Request::getUserVar('articleId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionLayoutHandler =& new SubmissionLayoutHandler()
		$submissionLayoutHandler->validate($articleId);
		$journal =& $submissionLayoutHandler->journal;
		$submission =& $submissionLayoutHandler->submission;
		
		$this->setupTemplate(true);		
		if (LayoutEditorAction::postProofreadComment($submission, $emailComment)) {
			LayoutEditorAction::viewProofreadComments($submission);
		}

	}

	/**
	 * Edit comment.
	 */
	function editComment($args) {
		$articleId = $args[0];
		$commentId = $args[1];

		$submissionLayoutHandler =& new SubmissionLayoutHandler()
		$submissionLayoutHandler->validate($articleId);
		$journal =& $submissionLayoutHandler->journal;
		$submission =& $submissionLayoutHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		
		$this->setupTemplate(true);		
		LayoutEditorAction::editComment($submission, $comment);

	}

	/**
	 * Save comment.
	 */
	function saveComment() {
		$articleId = Request::getUserVar('articleId');
		$commentId = Request::getUserVar('commentId');

		// If the user pressed the "Save and email" button, then email the comment.
		$emailComment = Request::getUserVar('saveAndEmail') != null ? true : false;

		$submissionLayoutHandler =& new SubmissionLayoutHandler()
		$submissionLayoutHandler->validate($articleId);
		$journal =& $submissionLayoutHandler->journal;
		$submission =& $submissionLayoutHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		
		$this->setupTemplate(true);

		LayoutEditorAction::saveComment($submission, $comment, $emailComment);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
			Request::redirect(null, null, 'viewLayoutComments', $articleId);
		} else if ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
			Request::redirect(null, null, 'viewProofreadComments', $articleId);
		}
	}

	/**
	 * Delete comment.
	 */
	function deleteComment($args) {
		$articleId = $args[0];
		$commentId = $args[1];

		$submissionLayoutHandler =& new SubmissionLayoutHandler()
		$submissionLayoutHandler->validate($articleId);
		$journal =& $submissionLayoutHandler->journal;
		$submission =& $submissionLayoutHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		
		$this->setupTemplate(true);		
		LayoutEditorAction::deleteComment($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
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

		if ( !is_null($commentId) ) {
			$isValid = true;

			$articleCommentDao =& DAORegistry::getDAO('ArticleCommentDAO');
			$user =& Request::getUser();

			$comment =& $articleCommentDao->getArticleCommentById($commentId);

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
