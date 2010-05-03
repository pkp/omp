<?php

/**
 * @file classes/submission/formform/comment/EditorDecisionCommentForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionCommentForm
 * @ingroup submission_form
 *
 * @brief EditorDecisionComment form.
 */

// $Id$


import('classes.submission.form.comment.CommentForm');

class EditorDecisionCommentForm extends CommentForm {

	/**
	 * Constructor.
	 * @param $monograph object
	 */
	function EditorDecisionCommentForm($monograph, $roleId) {
		parent::CommentForm($monograph, COMMENT_TYPE_EDITOR_DECISION, $roleId, $monograph->getId());
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageTitle', 'submission.comments.editorAuthorCorrespondence');
		$templateMgr->assign('monographId', $this->monograph->getMonographId());
		$templateMgr->assign('commentAction', 'postEditorDecisionComment');
		$templateMgr->assign('hiddenFormParams', 
			array(
				'monographId' => $this->monograph->getMonographId()
			)
		);

		$isEditor = $this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SERIES_EDITOR ? true : false;
		$templateMgr->assign('isEditor', $isEditor);

		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'commentTitle',
				'comments'
			)
		);
	}

	/**
	 * Add the comment.
	 */
	function execute() {
		parent::execute();
	}

	/**
	 * Email the comment.
	 */
	function email() {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		// Create list of recipients:

		// Editor Decision comments are to be sent to the editor or author,
		// the opposite of whomever wrote the comment.
		$recipients = array();

		if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SERIES_EDITOR) {
			// Then add author
			$user =& $userDao->getUser($this->monograph->getUserId());

			if ($user) $recipients = array_merge($recipients, array($user->getEmail() => $user->getFullName()));
		} else {
			// Then add editor
			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editAssignments =& $editAssignmentDao->getByIdsByMonographId($this->monograph->getMonographId());
			$editorAddresses = array();
			while (!$editAssignments->eof()) {
				$editAssignment =& $editAssignments->next();
				$editorAddresses[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
			}

			// If no editors are currently assigned to this monograph,
			// send the email to all editors for the press
			if (empty($editorAddresses)) {
				$editors =& $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $press->getPressId());
				while (!$editors->eof()) {
					$editor =& $editors->next();
					$editorAddresses[$editor->getEmail()] = $editor->getFullName();
				}
			}
			$recipients = array_merge($recipients, $editorAddresses);
		}

		parent::email($recipients);	
	}
}

?>
