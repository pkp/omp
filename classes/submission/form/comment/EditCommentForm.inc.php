<?php

/**
 * @file classes/submission/form/comment/EditCommentForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditCommentForm
 * @ingroup submission_form
 *
 * @brief Edit comment form.
 */

// $Id$


import('lib.pkp.classes.form.Form');

class EditCommentForm extends Form {

	/** @var object the monograph */
	var $monograph;

	/** @var MonographComment the comment */
	var $comment;

	/** @var int the role of the comment author */
	var $roleId;

	/** @var User the user */
	var $user;

	/**
	 * Constructor.
	 * @param $monograph object
	 * @param $comment object
	 */
	function EditCommentForm(&$monograph, &$comment) {
		parent::Form('submission/comment/editComment.tpl');
		$this->addCheck(new FormValidatorPost($this));

		$this->comment = $comment;
		$this->roleId = $comment->getRoleId();

		$this->monograph = $monograph;
		$this->user =& Request::getUser();
	}

	/**
	 * Initialize form data from current comment.
	 */
	function initData() {
		$comment =& $this->comment;
		$this->_data = array(
			'commentId' => $comment->getCommentId(),
			'commentTitle' => $comment->getCommentTitle(),
			'comments' => $comment->getComments(),
			'viewable' => $comment->getViewable(),
		);
	}

	/**
	 * Display the form.
	 */
	function display($additionalHiddenParams = null) {
		$hiddenFormParams = array(
			'monographId' => $this->monograph->getMonographId(),
			'commentId' => $this->comment->getCommentId()
		);
		if (isset($additionalHiddenParams)) {
			$hiddenFormParams = array_merge ($hiddenFormParams, $additionalHiddenParams);
		}

		$templateMgr =& TemplateManager::getManager();

		$isPeerReviewComment = $this->comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW;
		$templateMgr->assign('isPeerReviewComment', $isPeerReviewComment); // FIXME
		$templateMgr->assign_by_ref('comment', $this->comment);
		$templateMgr->assign_by_ref('hiddenFormParams', $hiddenFormParams);

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
	 * Update the comment.
	 */
	function execute() {
		$commentDao =& DAORegistry::getDAO('MonographCommentDAO');

		// Update comment
		$comment = $this->comment;
		$comment->setCommentTitle($this->getData('commentTitle'));
		$comment->setComments($this->getData('comments'));
		$comment->setViewable($this->getData('viewable') ? 1 : 0);
		$comment->setDateModified(Core::getCurrentDate());

		$commentDao->updateMonographComment($comment);
	}

	/**
	 * UGLEEE function that gets the recipients for a comment.
	 * @return $recipients array of recipients (email address => name)
	 */
	function emailHelper() {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		$recipients = array();

		// Get editors for monograph
		// FIXME #5557: Get IDs from Monograph->getAssociatedUserIds
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignments =& $editAssignmentDao->getByIdsByMonographId($this->monograph->getId());
		$editAssignments =& $editAssignments->toArray();
		$editorAddresses = array();
		foreach ($editAssignments as $editAssignment) {
			$editorAddresses[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
		}

		// If no editors are currently assigned, send this message to
		// all of the press's editors.
		if (empty($editorAddresses)) {
			$editors =& $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $press->getId());
			while (!$editors->eof()) {
				$editor =& $editors->next();
				$editorAddresses[$editor->getEmail()] = $editor->getFullName();
			}
		}

		// Get proofreader
		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($this->monograph->getId());
		if ($proofAssignment != null && $proofAssignment->getProofreaderId() > 0) {
			$proofreader =& $userDao->getUser($proofAssignment->getProofreaderId());
		} else {
			$proofreader = null;
		}

		// Get layout editor
		$layoutAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
		$layoutAssignment =& $layoutAssignmentDao->getLayoutAssignmentByMonographId($this->monograph->getId());
		if ($layoutAssignment != null && $layoutAssignment->getEditorId() > 0) {
			$layoutEditor =& $userDao->getUser($layoutAssignment->getEditorId());
		} else {
			$layoutEditor = null;
		}

		// Get copyeditor
		$copyAssignmentDao =& DAORegistry::getDAO('CopyAssignmentDAO');
		$copyAssignment =& $copyAssignmentDao->getCopyAssignmentByMonographId($this->monograph->getId());
		if ($copyAssignment != null && $copyAssignment->getCopyeditorId() > 0) {
			$copyeditor =& $userDao->getUser($copyAssignment->getCopyeditorId());
		} else {
			$copyeditor = null;
		}

		// Get reviewer
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($this->comment->getAssocId());
		if ($reviewAssignment != null && $reviewAssignment->getReviewerId() != null) {
			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		} else {
			$reviewer = null;
		}

		// Get author
		$author =& $userDao->getUser($this->monograph->getUserId());

		switch ($this->comment->getCommentType()) {
		case COMMENT_TYPE_PEER_REVIEW:
			if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SERIES_EDITOR) {
				// Then add reviewer
				if ($reviewer != null) {
					$recipients = array_merge($recipients, array($reviewer->getEmail() => $reviewer->getFullName()));
				}
			}
			break;

		case COMMENT_TYPE_EDITOR_DECISION:
			if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SERIES_EDITOR) {
				// Then add author
				if (isset($author)) $recipients = array_merge($recipients, array($author->getEmail() => $author->getFullName()));
			} else {
				// Then add editors
				$recipients = array_merge($recipients, $editorAddresses);
			}
			break;

		case COMMENT_TYPE_COPYEDIT:
			if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SERIES_EDITOR) {
				// Then add copyeditor and author
				if ($copyeditor != null) {
					$recipients = array_merge($recipients, array($copyeditor->getEmail() => $copyeditor->getFullName()));
				}

				$recipients = array_merge($recipients, array($author->getEmail() => $author->getFullName()));

			} else if ($this->roleId == ROLE_ID_COPYEDITOR) {
				// Then add editors and author
				$recipients = array_merge($recipients, $editorAddresses);

				if (isset($author)) $recipients = array_merge($recipients, array($author->getEmail() => $author->getFullName()));

			} else {
				// Then add editors and copyeditor
				$recipients = array_merge($recipients, $editorAddresses);

				if ($copyeditor != null) {
					$recipients = array_merge($recipients, array($copyeditor->getEmail() => $copyeditor->getFullName()));
				}
			}
			break;
		case COMMENT_TYPE_LAYOUT:
			if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SERIES_EDITOR) {
				// Then add layout editor

				// Check to ensure that there is a layout editor assigned to this monograph.
				if ($layoutEditor != null) {
					$recipients = array_merge($recipients, array($layoutEditor->getEmail() => $layoutEditor->getFullName()));
				}
			} else {
				// Then add editors
				$recipients = array_merge($recipients, $editorAddresses);
			}
			break;
		case COMMENT_TYPE_PROOFREAD:
			if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SERIES_EDITOR) {
				// Then add layout editor, proofreader and author
				if ($layoutEditor != null) {
					$recipients = array_merge($recipients, array($layoutEditor->getEmail() => $layoutEditor->getFullName()));
				}

				if ($proofreader != null) {
					$recipients = array_merge($recipients, array($proofreader->getEmail() => $proofreader->getFullName()));
				}

				if (isset($author)) $recipients = array_merge($recipients, array($author->getEmail() => $author->getFullName()));

			} else if ($this->roleId == ROLE_ID_LAYOUT_EDITOR) {
				// Then add editors, proofreader and author
				$recipients = array_merge($recipients, $editorAddresses);

				if ($proofreader != null) {
					$recipients = array_merge($recipients, array($proofreader->getEmail() => $proofreader->getFullName()));
				}

				if (isset($author)) $recipients = array_merge($recipients, array($author->getEmail() => $author->getFullName()));

			} else if ($this->roleId == ROLE_ID_PROOFREADER) {
				// Then add editors, layout editor, and author
				$recipients = array_merge($recipients, $editorAddresses);

				if ($layoutEditor != null) {
					$recipients = array_merge($recipients, array($layoutEditor->getEmail() => $layoutEditor->getFullName()));
				}

				if (isset($author)) $recipients = array_merge($recipients, array($author->getEmail() => $author->getFullName()));

			} else {
				// Then add editors, layout editor, and proofreader
				$recipients = array_merge($recipients, $editorAddresses);

				if ($layoutEditor != null) {
					$recipients = array_merge($recipients, array($layoutEditor->getEmail() => $layoutEditor->getFullName()));
				}

				if ($proofreader != null) {
					$recipients = array_merge($recipients, array($proofreader->getEmail() => $proofreader->getFullName()));
				}
			}
			break;
		}

		return $recipients;
	}

	/**
	 * Email the comment.
	 * @param $recipients array of recipients (email address => name)
	 */
	function email($recipients) {
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($this->monograph, 'SUBMISSION_COMMENT');
		$press =& Request::getPress();
		if ($press) $email->setFrom($press->getSetting('contactEmail'), $press->getSetting('contactName'));

		foreach ($recipients as $emailAddress => $name) {
			$email->addRecipient($emailAddress, $name);
			$email->setSubject(strip_tags($this->monograph->getMonographTitle()));

			$paramArray = array(
				'name' => $name,
				'commentName' => $this->user->getFullName(),
				'comments' => $this->getData('comments')
			);
			$email->assignParams($paramArray);

			$email->send();
			$email->clearRecipients();
		}
	}
}

?>
