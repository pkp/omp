<?php

/**
 * @file SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_author
 *
 * @brief Handle requests for submission comments. 
 */



import('pages.author.TrackSubmissionHandler');

class SubmissionCommentsHandler extends AuthorHandler {
	/** comment associated with the request **/
	var $comment;
	
	/**
	 * Constructor
	 **/
	function SubmissionCommentsHandler() {
		parent::AuthorHandler();
	}

	/**
	 * View editor decision comments.
	 */
	function viewEditorDecisionComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$trackSubmissionHandler = new TrackSubmissionHandler;
		$trackSubmissionHandler->validate($monographId);
		AuthorAction::viewEditorDecisionComments($trackSubmissionHandler->submission);
	}

	/**
	 * View copyedit comments.
	 */
	function viewCopyeditComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$trackSubmissionHandler = new TrackSubmissionHandler;
		$trackSubmissionHandler->validate($monographId);
		AuthorAction::viewCopyeditComments($trackSubmissionHandler->submission);

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

		$trackSubmissionHandler = new TrackSubmissionHandler;
		$trackSubmissionHandler->validate($monographId);
		if (AuthorAction::postCopyeditComment($trackSubmissionHandler->submission, $emailComment)) {
			AuthorAction::viewCopyeditComments($trackSubmissionHandler->submission);
		}

	}

	/**
	 * View proofread comments.
	 */
	function viewProofreadComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$trackSubmissionHandler = new TrackSubmissionHandler;
		$trackSubmissionHandler->validate($monographId);
		AuthorAction::viewProofreadComments($authorSubmission->submission);

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

		$trackSubmissionHandler = new TrackSubmissionHandler;
		$trackSubmissionHandler->validate($monographId);
		if (AuthorAction::postProofreadComment($trackSubmissionHandler->submission, $emailComment)) {
			AuthorAction::viewProofreadComments($trackSubmissionHandler->submission);
		}

	}

	/**
	 * View layout comments.
	 */
	function viewLayoutComments($args) {
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];

		$trackSubmissionHandler = new TrackSubmissionHandler;
		$trackSubmissionHandler->validate($monographId);
		AuthorAction::viewLayoutComments($trackSubmissionHandler->submission);

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

		$trackSubmissionHandler = new TrackSubmissionHandler;
		$trackSubmissionHandler->validate($monographId);
		if (AuthorAction::postLayoutComment($trackSubmissionHandler->submission, $emailComment)) {
			AuthorAction::viewLayoutComments($trackSubmissionHandler->submission);
		}

	}

	/**
	 * Email an editor decision comment.
	 */
	function emailEditorDecisionComment() {
		$monographId = (int) Request::getUserVar('monographId');
		$trackSubmissionHandler = new TrackSubmissionHandler;
		$trackSubmissionHandler->validate($monographId);

		parent::setupTemplate(true);		
		if (AuthorAction::emailEditorDecisionComment($trackSubmissionHandler->submission, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submissionReview', array($monographId));
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

		$trackSubmissionHandler = new TrackSubmissionHandler();
		$trackSubmissionHandler->validate($monographId);
		$authorSubmission =& $trackSubmissionHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;

		if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			// Cannot edit an editor decision comment.
			Request::redirect(null, Request::getRequestedPage());
		}

		AuthorAction::editComment($authorSubmission, $comment);

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

		$trackSubmissionHandler = new TrackSubmissionHandler();
		$trackSubmissionHandler->validate($monographId);
		$authorSubmission =& $trackSubmissionHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;

		if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
			// Cannot edit an editor decision comment.
			Request::redirect(null, Request::getRequestedPage());
		}

		AuthorAction::saveComment($authorSubmission, $comment, $emailComment);

		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$comment =& $monographCommentDao->getMonographCommentById($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
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
		$this->validate();
		$this->setupTemplate(true);

		$monographId = $args[0];
		$commentId = $args[1];

		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$comment =& $monographCommentDao->getMonographCommentById($commentId);

		$trackSubmissionHandler = new TrackSubmissionHandler();
		$trackSubmissionHandler->validate($monographId);
		$authorSubmission =& $trackSubmissionHandler->submission;
		$this->validate($commentId);
		$comment =& $this->comment;
		AuthorAction::deleteComment($commentId);

		// Redirect back to initial comments page
		if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
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

		if (!is_null($commentId)) {
			$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
			$user =& Request::getUser();

			$comment =& $monographCommentDao->getMonographCommentById($commentId);

			if ($comment->getAuthorId() != $user->getId()) {
				Request::redirect(null, Request::getRequestedPage());
			}

			$this->comment =& $comment;
		}
		return true;
	}
}
?>
