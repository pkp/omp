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
	 * Assigns a section editor to a submission.
	 * @param $monographId int
	 * @return boolean true iff ready for redirect
	 */
	function assignEditor($monographId, $sectionEditorId, $isEditor = false, $send = false) {
		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$user =& Request::getUser();
		$press =& Request::getPress();

		$editorSubmission =& $editorSubmissionDao->getEditorSubmission($monographId);
		$sectionEditor =& $userDao->getUser($sectionEditorId);
		if (!isset($sectionEditor)) return true;

		import('mail.MonographMailTemplate');
		$email = new MonographMailTemplate($editorSubmission, 'EDITOR_ASSIGN');

		if ($user->getUserId() === $sectionEditorId || !$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('EditorAction::assignEditor', array(&$editorSubmission, &$sectionEditor, &$isEditor, &$email));
			if ($email->isEnabled() && $user->getUserId() !== $sectionEditorId) {
				$email->setAssoc(ARTICLE_EMAIL_EDITOR_ASSIGN, ARTICLE_EMAIL_TYPE_EDITOR, $sectionEditor->getUserId());
				$email->send();
			}

			$editAssignment = new EditAssignment();
			$editAssignment->setMonographId($monographId);
			$editAssignment->setCanEdit(1);
			$editAssignment->setCanReview(1);

			// Make the selected editor the new editor
			$editAssignment->setEditorId($sectionEditorId);
			$editAssignment->setDateNotified(Core::getCurrentDate());
			$editAssignment->setDateUnderway(null);

			$editAssignments =& $editorSubmission->getEditAssignments();
			array_push($editAssignments, $editAssignment);
			$editorSubmission->setEditAssignments($editAssignments);

			$editorSubmissionDao->updateEditorSubmission($editorSubmission);

			// Add log
			import('monograph.log.MonographLog');
			import('monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($monographId, ARTICLE_LOG_EDITOR_ASSIGN, ARTICLE_LOG_TYPE_EDITOR, $sectionEditorId, 'log.editor.editorAssigned', array('editorName' => $sectionEditor->getFullName(), 'monographId' => $monographId));
			return true;
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($sectionEditor->getEmail(), $sectionEditor->getFullName());
				$paramArray = array(
					'editorialContactName' => $sectionEditor->getFullName(),
					'editorUsername' => $sectionEditor->getUsername(),
					'editorPassword' => $sectionEditor->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionUrl' => Request::url(null, $isEditor?'editor':'sectionEditor', 'submissionReview', $monographId),
					'submissionEditingUrl' => Request::url(null, $isEditor?'editor':'sectionEditor', 'submissionReview', $monographId)
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'assignEditor', 'send'), array('monographId' => $monographId, 'editorId' => $sectionEditorId));
			return false;
		}
	}

	/**
	 * Rush a new submission into the end of the editing queue.
	 * @param $monograph object
	 */
	function expediteSubmission($monograph) {
		$user =& Request::getUser();

		import('submission.editor.EditorAction');
		import('submission.sectionEditor.SectionEditorAction');
		import('submission.proofreader.ProofreaderAction');

		$sectionEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO');
		$sectionEditorSubmission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($monograph->getMonographId());

		$submissionFile = $sectionEditorSubmission->getSubmissionFile();

		// Add a long entry before doing anything.
		import('monograph.log.MonographLog');
		import('monograph.log.MonographEventLogEntry');
		MonographLog::logEvent($monograph->getMonographId(), ARTICLE_LOG_EDITOR_EXPEDITE, ARTICLE_LOG_TYPE_EDITOR, $user->getUserId(), 'log.editor.submissionExpedited', array('editorName' => $user->getFullName(), 'monographId' => $monograph->getMonographId()));

		// 1. Ensure that an editor is assigned.
		$editAssignments =& $sectionEditorSubmission->getEditAssignments();
		if (empty($editAssignments)) {
			// No editors are currently assigned; assign self.
			EditorAction::assignEditor($monograph->getMonographId(), $user->getUserId(), true);
		}

		// 2. Accept the submission and send to copyediting.
		$sectionEditorSubmission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($monograph->getMonographId());
		if (!$sectionEditorSubmission->getCopyeditFile()) {
			SectionEditorAction::recordDecision($sectionEditorSubmission, SUBMISSION_EDITOR_DECISION_ACCEPT);
			$reviewFile = $sectionEditorSubmission->getReviewFile();
			SectionEditorAction::setCopyeditFile($sectionEditorSubmission, $reviewFile->getFileId(), $reviewFile->getRevision());
		}

		// 3. Add a galley.
		$sectionEditorSubmission =& $sectionEditorSubmissionDao->getSectionEditorSubmission($monograph->getMonographId());
		$galleys =& $sectionEditorSubmission->getGalleys();
		if (empty($galleys)) {
			// No galley present -- use copyediting file.
			import('file.MonographFileManager');
			$copyeditFile =& $sectionEditorSubmission->getCopyeditFile();
			$fileType = $copyeditFile->getFileType();
			$monographFileManager = new MonographFileManager($monograph->getMonographId());
			$fileId = $monographFileManager->copyPublicFile($copyeditFile->getFilePath(), $fileType);

			if (strstr($fileType, 'html')) {
				$galley = new MonographHTMLGalley();
			} else {
				$galley = new MonographGalley();
			}
			$galley->setMonographId($monograph->getMonographId());
			$galley->setFileId($fileId);
			$galley->setLocale(Locale::getLocale());

			if ($galley->isHTMLGalley()) {
				$galley->setLabel('HTML');
			} else {
				if (strstr($fileType, 'pdf')) {
					$galley->setLabel('PDF');
				} else if (strstr($fileType, 'postscript')) {
					$galley->setLabel('Postscript');
				} else if (strstr($fileType, 'xml')) {
					$galley->setLabel('XML');
				} else {
					$galley->setLabel(Locale::translate('common.untitled'));
				}
			}

			$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
			$galleyDao->insertGalley($galley);
		}

		$sectionEditorSubmission->setStatus(STATUS_QUEUED);
		$sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);
	}
}

?>
