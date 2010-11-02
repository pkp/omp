<?php

/**
 * @file classes/submission/form/comment/CommentForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CommentForm
 * @ingroup submission_form
 * @see Comment, MonographCommentDAO
 *
 * @brief Comment form.
 */



import('lib.pkp.classes.form.Form');

class CommentForm extends Form {

	/** @var int the comment type */
	var $commentType;

	/** @var int the role id of the comment poster */
	var $roleId;

	/** @var Monograph current monograph */
	var $monograph;

	/** @var User comment author */
	var $user;

	/** @var int the ID of the comment after insertion */
	var $commentId;

	/**
	 * Constructor.
	 * @param $monograph object
	 */
	function CommentForm($monograph, $commentType, $roleId, $assocId = null) {
		if ($commentType == COMMENT_TYPE_PEER_REVIEW) {
			parent::Form('submission/comment/peerReviewComment.tpl');
		} else if ($commentType == COMMENT_TYPE_EDITOR_DECISION) {
			parent::Form('submission/comment/editorDecisionComment.tpl');
		} else {
			parent::Form('submission/comment/comment.tpl');
		}

		$this->monograph = $monograph;
		$this->commentType = $commentType;
		$this->roleId = $roleId;
		$this->assocId = $assocId == null ? $monograph->getId() : $assocId;

		$this->user =& Request::getUser();

		if ($commentType != COMMENT_TYPE_PEER_REVIEW) $this->addCheck(new FormValidator($this, 'comments', 'required', 'editor.monograph.commentsRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Set the user this comment form is associated with.
	 * @param $user object
	 */
	function setUser(&$user) {
		$this->user =& $user;
	}

	/**
	 * Display the form.
	 */
	function display() {
		$monograph = $this->monograph;

		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$monographComments =& $monographCommentDao->getMonographComments($monograph->getId(), $this->commentType, $this->assocId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('commentTitle', strip_tags($monograph->getLocalizedTitle()));
		$templateMgr->assign('userId', $this->user->getId());
		$templateMgr->assign('monographComments', $monographComments);

		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'commentTitle',
				'comments',
				'viewable'
			)
		);
	}

	/**
	 * Add the comment.
	 */
	function execute() {

		$commentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$monograph = $this->monograph;

		// Insert new comment		
		$comment = new MonographComment();
		$comment->setCommentType($this->commentType);
		$comment->setRoleId($this->roleId);
		$comment->setMonographId($monograph->getId());
		$comment->setAssocId($this->assocId);
		$comment->setAuthorId($this->user->getId());
		$comment->setCommentTitle($this->getData('commentTitle'));
		$comment->setComments($this->getData('comments'));
		$comment->setDatePosted(Core::getCurrentDate());
		$comment->setViewable($this->getData('viewable'));

		$this->commentId = $commentDao->insertMonographComment($comment);
	}

	/**
	 * Email the comment.
	 * @param $recipients array of recipients (email address => name)
	 */
	function email($recipients) {
		$monograph = $this->monograph;
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$press =& Request::getPress();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'SUBMISSION_COMMENT');
		$email->setFrom($this->user->getEmail(), $this->user->getFullName());

		$commentText = $this->getData('comments');

		// Individually send an email to each of the recipients.
		foreach ($recipients as $emailAddress => $name) {
			$email->addRecipient($emailAddress, $name);

			$paramArray = array(
				'name' => $name,
				'commentName' => $this->user->getFullName(),
				'comments' => $commentText	
			);

			$email->sendWithParams($paramArray);
			$email->clearRecipients();
		}
	}
}

?>
