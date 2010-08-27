<?php

/**
 * @file classes/submission/form/comment/CopyeditCommentForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditCommentForm
 * @ingroup submission_form
 * @see Form
 *
 * @brief CopyeditComment form.
 */

// $Id$


import('classes.submission.form.comment.CommentForm');

class CopyeditCommentForm extends CommentForm {

	/**
	 * Constructor.
	 * @param $monograph object
	 */
	function CopyeditCommentForm($monograph, $roleId) {
		parent::CommentForm($monograph, COMMENT_TYPE_COPYEDIT, $roleId, $monograph->getId());
	}

	/**
	 * Display the form.
	 */
	function display() {
		$monograph = $this->monograph;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageTitle', 'submission.comments.copyeditComments');
		$templateMgr->assign('commentAction', 'postCopyeditComment');
		$templateMgr->assign('commentType', 'copyedit');
		$templateMgr->assign('hiddenFormParams',
			array(
				'monographId' => $monograph->getId()
			)
		);

		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();
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
		$monograph = $this->monograph;
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		// Create list of recipients:
		$recipients = array();

		// Copyedit comments are to be sent to the editor, author, and copyeditor,
		// excluding whomever posted the comment.

		// Get editors
		// FIXME #5557: Ensure compatibility with monograph stage assignment DAO
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignments =& $editAssignmentDao->getEditAssignmentsByMonographId($monograph->getId());
		$editAssignments =& $editAssignments->toArray();
		$editorAddresses = array();
		foreach ($editAssignments as $editAssignment) {
			if ($editAssignment->getCanEdit()) $editorAddresses[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
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

		// Get copyeditor
		$copySignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $monograph->getId());
		if ($copySignoff != null && $copySignoff->getUserId() > 0) {
			$copyeditor =& $userDao->getUser($copySignoff->getUserId());
		} else {
			$copyeditor = null;
		}

		// Get author
		$author =& $userDao->getUser($monograph->getUserId());

		// Choose who receives this email
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

		parent::email($recipients);
	}
}

?>