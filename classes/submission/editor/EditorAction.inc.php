<?php

/**
 * @file classes/submission/editor/EditorAction.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorAction
 * @ingroup submission
 *
 * @brief EditorAction class.
 */

// $Id$


import('submission.acquisitionsEditor.AcquisitionsEditorAction');

class EditorAction extends AcquisitionsEditorAction {
	/**
	 * Actions.
	 */

	/**
	 * Assigns an acquisitions editor to a submission.
	 * @param $monographId int
	 * @param $acquisitionsEditorId int
	 * @return boolean true iff ready for redirect
	 */
	function assignEditor($monographId, $acquisitionsEditorId, $isEditor = false, $send = false) {
		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$user =& Request::getUser();
		$press =& Request::getPress();

		$editorSubmission =& $editorSubmissionDao->getByMonographId($monographId);
		$acquisitionsEditor =& $userDao->getUser($acquisitionsEditorId);
		if (!isset($acquisitionsEditor)) return true;

		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($editorSubmission, 'EDITOR_ASSIGN');

		if ($user->getUserId() === $acquisitionsEditorId || !$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('EditorAction::assignEditor', array(&$editorSubmission, &$acquisitionsEditor, &$isEditor, &$email));
			if ($email->isEnabled() && $user->getUserId() !== $acquisitionsEditorId) {
				$email->setAssoc(MONOGRAPH_EMAIL_EDITOR_ASSIGN, MONOGRAPH_EMAIL_TYPE_EDITOR, $acquisitionsEditor->getUserId());
				$email->send();
			}

			$editAssignment = new EditAssignment();
			$editAssignment->setMonographId($monographId);
			$editAssignment->setCanEdit(1);
			$editAssignment->setCanReview(1);

			// Make the selected editor the new editor
			$editAssignment->setEditorId($acquisitionsEditorId);
			$editAssignment->setDateNotified(Core::getCurrentDate());
			$editAssignment->setDateUnderway(null);

			$editAssignments =& $editorSubmission->getByIds();
			array_push($editAssignments, $editAssignment);
			$editorSubmission->setEditAssignments($editAssignments);

			$editorSubmissionDao->updateObject($editorSubmission);

			// Add log
			import('monograph.log.MonographLog');
			import('monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($monographId, MONOGRAPH_LOG_EDITOR_ASSIGN, MONOGRAPH_LOG_TYPE_EDITOR, $acquisitionsEditorId, 'log.editor.editorAssigned', array('editorName' => $acquisitionsEditor->getFullName(), 'monographId' => $monographId));
			return true;
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($acquisitionsEditor->getEmail(), $acquisitionsEditor->getFullName());
				$paramArray = array(
					'editorialContactName' => $acquisitionsEditor->getFullName(),
					'editorUsername' => $acquisitionsEditor->getUsername(),
					'editorPassword' => $acquisitionsEditor->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionUrl' => Request::url(null, $isEditor?'editor':'acquisitionsEditor', 'submissionReview', $monographId),
					'submissionEditingUrl' => Request::url(null, $isEditor?'editor':'acquisitionsEditor', 'submissionReview', $monographId)
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'assignEditor', 'send'), array('monographId' => $monographId, 'editorId' => $acquisitionsEditorId));
			return false;
		}
	}

}

?>