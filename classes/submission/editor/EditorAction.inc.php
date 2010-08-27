<?php

/**
 * @file classes/submission/editor/EditorAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorAction
 * @ingroup submission
 *
 * @brief EditorAction class.
 */

// $Id$


import('classes.submission.seriesEditor.SeriesEditorAction');

class EditorAction extends SeriesEditorAction {
	/**
	 * Actions.
	 */

	/**
	 * Assigns an series editor to a submission.
	 * @param $monographId int
	 * @param $seriesEditorId int
	 * @return boolean true iff ready for redirect
	 */
	function assignEditor($monographId, $seriesEditorId, $isEditor = false, $send = false) {
		// FIXME #5557: Either implement assignments with signoffs here, or make sure they are done elsewhere
		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$user =& Request::getUser();
		$press =& Request::getPress();

		$editorSubmission =& $editorSubmissionDao->getByMonographId($monographId);
		$seriesEditor =& $userDao->getUser($seriesEditorId);
		if (!isset($seriesEditor)) return true;

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($editorSubmission, 'EDITOR_ASSIGN');

		if ($user->getId() === $seriesEditorId || !$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('EditorAction::assignEditor', array(&$editorSubmission, &$seriesEditor, &$isEditor, &$email));
			if ($email->isEnabled() && $user->getId() !== $seriesEditorId) {
				$email->setAssoc(MONOGRAPH_EMAIL_EDITOR_ASSIGN, MONOGRAPH_EMAIL_TYPE_EDITOR, $seriesEditor->getId());
				$email->send();
			}

			$editAssignment = new EditAssignment();
			$editAssignment->setMonographId($monographId);
			$editAssignment->setCanEdit(1);
			$editAssignment->setCanReview(1);

			// Make the selected editor the new editor
			$editAssignment->setEditorId($seriesEditorId);
			$editAssignment->setDateNotified(Core::getCurrentDate());
			$editAssignment->setDateUnderway(null);

			$editAssignments =& $editorSubmission->getEditAssignments();
			array_push($editAssignments, $editAssignment);
			$editorSubmission->setEditAssignments($editAssignments);

			$editorSubmissionDao->updateObject($editorSubmission);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($monographId, MONOGRAPH_LOG_EDITOR_ASSIGN, MONOGRAPH_LOG_TYPE_EDITOR, $seriesEditorId, 'log.editor.editorAssigned', array('editorName' => $seriesEditor->getFullName(), 'monographId' => $monographId));
			return true;
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($seriesEditor->getEmail(), $seriesEditor->getFullName());
				$paramArray = array(
					'editorialContactName' => $seriesEditor->getFullName(),
					'editorUsername' => $seriesEditor->getUsername(),
					'editorPassword' => $seriesEditor->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionUrl' => Request::url(null, $isEditor?'editor':'seriesEditor', 'submissionReview', $monographId),
					'submissionEditingUrl' => Request::url(null, $isEditor?'editor':'seriesEditor', 'submissionReview', $monographId)
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'assignEditor', 'send'), array('monographId' => $monographId, 'editorId' => $seriesEditorId));
			return false;
		}
	}

}

?>
