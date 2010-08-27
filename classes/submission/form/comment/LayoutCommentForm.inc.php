<?php

/**
 * @file classes/submission/form/comment/LayoutCommentForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LayoutCommentForm
 * @ingroup submission_form
 *
 * @brief LayoutComment form.
 */

// $Id$


import('classes.submission.form.comment.CommentForm');

class LayoutCommentForm extends CommentForm {

	/**
	 * Constructor.
	 * @param $monograph object
	 */
	function LayoutCommentForm($monograph, $roleId) {
		parent::CommentForm($monograph, COMMENT_TYPE_LAYOUT, $roleId, $monograph->getId());
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageTitle', 'submission.comments.comments');
		$templateMgr->assign('commentAction', 'postLayoutComment');
		$templateMgr->assign('commentType', 'layout');
		$templateMgr->assign('hiddenFormParams',
			array(
				'monographId' => $this->monograph->getId()
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
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		// Create list of recipients:

		// Layout comments are to be sent to the editor or layout editor;
		// the opposite of whomever posted the comment.
		$recipients = array();

		if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SERIES_EDITOR) {
			// Then add layout editor
			$layoutAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
			$layoutAssignment =& $layoutAssignmentDao->getLayoutAssignmentByMonographId($this->monograph->getId());

			// Check to ensure that there is a layout editor assigned to this monograph.
			if ($layoutAssignment != null && $layoutAssignment->getEditorId() > 0) {
				$user =& $userDao->getUser($layoutAssignment->getEditorId());

				if ($user) $recipients = array_merge($recipients, array($user->getEmail() => $user->getFullName()));
			}
		} else {
			// Then add editor
			// FIXME #5557: Get IDs from Monograph->getAssociatedUserIds
			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editAssignments =& $editAssignmentDao->getByIdsByMonographId($this->monograph->getId());
			$editorAddresses = array();
			while (!$editAssignments->eof()) {
				$editAssignment =& $editAssignments->next();
				if ($editAssignment->getCanEdit()) $editorAddresses[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
				unset($editAssignment);
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
