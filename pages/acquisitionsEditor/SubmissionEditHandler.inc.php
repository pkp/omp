<?php

/**
 * @file SubmissionEditHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionEditHandler
 * @ingroup pages_acquisitionsEditor
 *
 * @brief Handle requests for submission tracking. 
 */

// $Id$


define('ACQUISITIONS_EDITOR_ACCESS_EDIT', 0x00001);
define('ACQUISITIONS_EDITOR_ACCESS_REVIEW', 0x00002);

import('pages.acquisitionsEditor.AcquisitionsEditorHandler');
import('submission.acquisitionsEditor.AcquisitionsEditorAction');

class SubmissionEditHandler extends AcquisitionsEditorHandler {
	/** The submission associated with this request **/
	var $submission;

	/**
	 * Constructor
	 **/
	function SubmissionEditHandler() {
		parent::AcquisitionsEditorHandler();
	}

	function getFrom($default = 'submissionEditing') {
		$from = Request::getUserVar('from');
		if (!in_array($from, array('submission', 'submissionEditing'))) return $default;
		return $from;
	}
	
	function submission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$press =& Request::getPress();
		$submission =& $this->submission;
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_READER, LOCALE_COMPONENT_OMP_AUTHOR));
		$this->setupTemplate(true, $monographId);

		$user =& Request::getUser();

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettings = $pressSettingsDao->getPressSettings($press->getId());

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->roleExists($press->getId(), $user->getId(), ROLE_ID_EDITOR);

		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
		$arrangement =& $arrangementDao->getById($submission->getAcquisitionsArrangementId());

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('arrangement', $arrangement);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($submission->getMonographId(), 'submission');
		$templateMgr->assign_by_ref('submissionFiles', $monographFiles);

		$templateMgr->assign_by_ref('bookFileTypes', $bookFileTypes);
		$templateMgr->assign_by_ref('submissionFiles', $monographFiles);

		$templateMgr->assign_by_ref('arrangements', $arrangementDao->getTitlesByPressId($press->getId()));

		if ($enableComments) {
			import('monograph.Monograph');
			$templateMgr->assign('commentsStatus', $submission->getCommentsStatus());
			$templateMgr->assign_by_ref('commentsStatusOptions', Monograph::getCommentsStatusOptions());
		}

		if ($isEditor) {
			$templateMgr->assign('helpTopicId', 'editorial.editorsRole.submissionSummary');
		}
		
		$templateMgr->display('acquisitionsEditor/submission.tpl');
	}

	function submissionRegrets($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$press =& Request::getPress();
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'review');

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$cancelsAndRegrets = $reviewAssignmentDao->getCancelsAndRegrets($monographId);
		$reviewFilesByRound = $reviewAssignmentDao->getReviewFilesByRound($monographId);

		$reviewAssignments =& $submission->getReviewAssignments();
		$editorDecisions = $submission->getDecisions();
		$numRounds = $submission->getCurrentRound();
		
		$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewFormResponses = array();
		if (isset($reviewAssignments[$numRounds-1])) {
			foreach ($reviewAssignments[$numRounds-1] as $reviewAssignment) {
				$reviewFormResponses[$reviewAssignment->getReviewId()] = $reviewFormResponseDao->reviewFormResponseExists($reviewAssignment->getReviewId());
			}
		}
		
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('reviewAssignments', $reviewAssignments);
		$templateMgr->assign('reviewFormResponses', $reviewFormResponses);
		$templateMgr->assign_by_ref('cancelsAndRegrets', $cancelsAndRegrets);
		$templateMgr->assign_by_ref('reviewFilesByRound', $reviewFilesByRound);
		$templateMgr->assign_by_ref('editorDecisions', $editorDecisions);
		$templateMgr->assign('numRounds', $numRounds);
		$templateMgr->assign('rateReviewerOnQuality', $press->getSetting('rateReviewerOnQuality'));

		$templateMgr->assign_by_ref('editorDecisionOptions', AcquisitionsEditorSubmission::getEditorDecisionOptions());

		import('submission.reviewAssignment.ReviewAssignment');
		$templateMgr->assign_by_ref('reviewerRatingOptions', ReviewAssignment::getReviewerRatingOptions());
		$templateMgr->assign_by_ref('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());

		$templateMgr->display('acquisitionsEditor/submissionRegrets.tpl');
	}

	function submissionReview($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;		
		$this->setupTemplate(true, $monographId);
		
		$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');

		// Setting the review type and round.
		// Default to current review type and round but allowing it to be set by URL
		$reviewType = isset($args[1]) ? $args[1] : $submission->getCurrentReviewType();
		if ( isset($args[1]) && !isset($args[2]) ) {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$reviewRoundsInfo =& $monographDao->getReviewRoundsInfoById($monographId);			
			$round = isset($reviewRoundsInfo[$reviewType]) ? isset($reviewRoundsInfo[$reviewType]) : 1;
		} else {
			$round = isset($args[2]) ? $args[2] : $submission->getCurrentRound();
		}

		$editorDecisions = $submission->getDecisions($reviewType, $round);
		$lastDecision = count($editorDecisions) >= 1 ? $editorDecisions[count($editorDecisions) - 1]['decision'] : null;

		$editAssignments =& $submission->getEditAssignments();
		$allowRecommendation = $submission->getCurrentReviewType() == $reviewType && $submission->getCurrentRound() == $round && $submission->getReviewFileId() != null && !empty($editAssignments);
		$allowResubmit = $lastDecision == SUBMISSION_EDITOR_DECISION_RESUBMIT && $acquisitionsEditorSubmissionDao->getMaxReviewRound($monographId, $reviewType) == $round ? true : false;

		// Prepare an array to store the 'Notify Reviewer' email logs
		$notifyReviewerLogs = array();
		foreach ($submission->getReviewAssignments($reviewType, $round) as $reviewAssignment) {
			$notifyReviewerLogs[$reviewAssignment->getReviewId()] = array();
		}

		// Parse the list of email logs and populate the array.
		import('monograph.log.MonographLog');
		$emailLogEntries =& MonographLog::getEmailLogEntries($monographId);
		foreach ($emailLogEntries->toArray() as $emailLog) {
			if ($emailLog->getEventType() == MONOGRAPH_EMAIL_REVIEW_NOTIFY_REVIEWER) {
				if (isset($notifyReviewerLogs[$emailLog->getAssocId()]) && is_array($notifyReviewerLogs[$emailLog->getAssocId()])) {
					array_push($notifyReviewerLogs[$emailLog->getAssocId()], $emailLog);
				}
			}
		}

		// get press published review form titles
		$reviewFormTitles =& $reviewFormDao->getTitlesByPressId($press->getId(), 1);

		$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewFormResponses = array();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewFormTitles = array();

		foreach ($submission->getReviewAssignments($reviewType, $round) as $reviewAssignment) {
			$reviewForm =& $reviewFormDao->getById($reviewAssignment->getReviewFormId());
			if ($reviewForm) {
				$reviewFormTitles[$reviewForm->getReviewFormId()] = $reviewForm->getReviewFormTitle();
			}
			unset($reviewForm);
			$reviewFormResponses[$reviewAssignment->getReviewId()] = $reviewFormResponseDao->reviewFormResponseExists($reviewAssignment->getReviewId());
		}

		$templateMgr =& TemplateManager::getManager();
		
		$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
		$reviewProcesses =& $workflowDao->getByEventType($monographId, WORKFLOW_PROCESS_ASSESSMENT);
		$process =& $workflowDao->getCurrent($monographId, WORKFLOW_PROCESS_ASSESSMENT);
		list($nextProcessType, $nextProcessId) = $workflowDao->getNext($process);
		$processId = isset($process) ? $process->getProcessId() : null;
		
		$templateMgr->assign_by_ref('reviewType', $reviewType);
		$templateMgr->assign('round', $round);
		$templateMgr->assign_by_ref('editorDecisions', array_reverse($editorDecisions));
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('reviewIndexes', $reviewAssignmentDao->getReviewIndexesForRound($monographId, $reviewType, $round));
		$templateMgr->assign_by_ref('reviewAssignments', $submission->getReviewAssignments($reviewType, $round));
		$templateMgr->assign('reviewFormResponses', $reviewFormResponses);
		$templateMgr->assign('reviewFormTitles', $reviewFormTitles);
		$templateMgr->assign_by_ref('notifyReviewerLogs', $notifyReviewerLogs);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('copyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('revisedFile', $submission->getRevisedFile());
		$templateMgr->assign_by_ref('editorFile', $submission->getEditorFile());
		$templateMgr->assign('rateReviewerOnQuality', $press->getSetting('rateReviewerOnQuality'));
		if ( $reviewType == WORKFLOW_PROCESS_ASSESSMENT_INTERNAL ) 
			$templateMgr->assign('reviewTypeTitle', 'submission.internalReview');
		else 
			$templateMgr->assign('reviewTypeTitle', 'submission.externalReview');
			
		$templateMgr->assign('nextProcessTitle', $workflowDao->getTitleByProcessId($nextProcessId));

		$templateMgr->assign('editorDecisionOptions',
			array(
				'' => 'common.chooseOne',
				SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.monograph.decision.accept',
				SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.monograph.decision.pendingRevisions',
				SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.monograph.decision.resubmit',
				SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline'
			)
		);
		$templateMgr->assign_by_ref('lastDecision', $lastDecision);

		import('submission.reviewAssignment.ReviewAssignment');
		$templateMgr->assign_by_ref('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());
		$templateMgr->assign_by_ref('reviewerRatingOptions', ReviewAssignment::getReviewerRatingOptions());

		$templateMgr->assign('allowRecommendation', $allowRecommendation);
		$templateMgr->assign('allowResubmit', $allowResubmit);
		$templateMgr->assign('helpTopicId', 'editorial.acquisitionsEditorsRole.review');
		$templateMgr->display('acquisitionsEditor/submissionReview.tpl');
	}

	function submissionEditing($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;		
		$this->setupTemplate(true, $monographId);

		$useCopyeditors = $press->getSetting('useCopyeditors');
		$useLayoutEditors = $press->getSetting('useLayoutEditors');
		$useProofreaders = $press->getSetting('useProofreaders');

		// check if submission is accepted
//		$round = isset($args[1]) ? $args[1] : $submission->getCurrentRound();
//		$editorDecisions = $submission->getDecisions($round);
//		$lastDecision = count($editorDecisions) >= 1 ? $editorDecisions[count($editorDecisions) - 1]['decision'] : null;				
//		$submissionAccepted = ($lastDecision == SUBMISSION_EDITOR_DECISION_ACCEPT) ? true : false;

		$templateMgr =& TemplateManager::getManager();

		$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
		$currentProcess = $workflowDao->getCurrent($monographId);

		$templateMgr->assign_by_ref('currentProcess', $currentProcess);
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('copyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('editorAuthorCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR'));
		$templateMgr->assign_by_ref('finalCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL'));
		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
		$templateMgr->assign_by_ref('copyeditor', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user =& Request::getUser();
		$templateMgr->assign('isEditor', $roleDao->roleExists($press->getId(), $user->getId(), ROLE_ID_EDITOR));

		$templateMgr->assign('useCopyeditors', true);
		$templateMgr->assign('useLayoutEditors', $useLayoutEditors);
		$templateMgr->assign('useProofreaders', $useProofreaders);
		$templateMgr->assign('submissionAccepted', $submissionAccepted);

		$templateMgr->assign('helpTopicId', 'editorial.acquisitionsEditorsRole.editing');
		$templateMgr->display('acquisitionsEditor/submissionEditing.tpl');
	}

	function submissionProduction($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;		
		$this->setupTemplate(true, $monographId);

		$useCopyeditors = $press->getSetting('useCopyeditors');
		$useLayoutEditors = $press->getSetting('useLayoutEditors');
		$useProofreaders = $press->getSetting('useProofreaders');

		// check if submission is accepted
//		$round = isset($args[1]) ? $args[1] : $submission->getCurrentRound();
//		$editorDecisions = $submission->getDecisions($round);
//		$lastDecision = count($editorDecisions) >= 1 ? $editorDecisions[count($editorDecisions) - 1]['decision'] : null;				
//		$submissionAccepted = ($lastDecision == SUBMISSION_EDITOR_DECISION_ACCEPT) ? true : false;
		$submissionAccepted = true;
		$templateMgr =& TemplateManager::getManager();

		$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
		$currentProcess = $workflowDao->getCurrent($monographId);

		$templateMgr->assign_by_ref('currentProcess', $currentProcess);
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
		$templateMgr->assign_by_ref('productionEditor', $submission->getUserBySignoffType('SIGNOFF_PRODUCTION'));

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user =& Request::getUser();
		$templateMgr->assign('isEditor', $roleDao->roleExists($press->getId(), $user->getId(), ROLE_ID_EDITOR));

		$templateMgr->assign('useCopyeditors', true);
		$templateMgr->assign('useLayoutEditors', $useLayoutEditors);
		$templateMgr->assign('useProofreaders', $useProofreaders);
//		$templateMgr->assign_by_ref('proofAssignment', $submission->getProofAssignment());
//		$templateMgr->assign_by_ref('layoutAssignment', $submission->getLayoutAssignment());
		$templateMgr->assign('submissionAccepted', $submissionAccepted);

		$templateMgr->assign('helpTopicId', 'editorial.acquisitionsEditorsRole.editing');
		$templateMgr->display('acquisitionsEditor/submissionProduction.tpl');
	}

	/**
	 * View submission history
	 */
	function submissionHistory($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$this->setupTemplate(true, $monographId);

		// submission notes
		$monographNoteDao =& DAORegistry::getDAO('MonographNoteDAO');

		$rangeInfo =& Handler::getRangeInfo('submissionNotes');
		$submissionNotes =& $monographNoteDao->getMonographNotes($monographId, $rangeInfo);

		import('monograph.log.MonographLog');
		$rangeInfo =& Handler::getRangeInfo('eventLogEntries');
		$eventLogEntries =& MonographLog::getEventLogEntries($monographId, $rangeInfo);
		$rangeInfo =& Handler::getRangeInfo('emailLogEntries');
		$emailLogEntries =& MonographLog::getEmailLogEntries($monographId, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('isEditor', Validation::isEditor());
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('eventLogEntries', $eventLogEntries);
		$templateMgr->assign_by_ref('emailLogEntries', $emailLogEntries);
		$templateMgr->assign_by_ref('submissionNotes', $submissionNotes);

		$templateMgr->display('acquisitionsEditor/submissionHistory.tpl');
	}

	function changeAcquisitionsArrangement() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$arrangementId = Request::getUserVar('arrangementId');

		AcquisitionsEditorAction::changeAcquisitionsArrangement($submission, $arrangementId);

		Request::redirect(null, null, 'submission', $monographId);
	}

	function recordDecision() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$decision = Request::getUserVar('decision');

		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
			case SUBMISSION_EDITOR_DECISION_DECLINE:
				AcquisitionsEditorAction::recordDecision($submission, $decision);
				break;
		}

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function recordReviewFiles() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$fileIds = Request::getUserVar('selectedFiles');

		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		foreach ($fileIds as $fileId) {
			$monographFileManager->copyToReviewFile($fileId);
		}

		Request::redirect(null, null, 'submission', $monographId);
	}

	//
	// Peer Review
	//
	function selectReviewer($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$sort = Request::getUserVar('sort');
		$sort = isset($sort) ? $sort : 'name';
		$sortDirection = Request::getUserVar('sortDirection');
		$sortDirection = (isset($sortDirection) && ($sortDirection == 'ASC' || $sortDirection == 'DESC')) ? $sortDirection : 'ASC';

		$reviewerId = Request::getUserVar('reviewerId');
		$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');

		$reviewType = $submission->getCurrentReviewType();
		$round = $submission->getCurrentRound();

		if (isset($reviewerId)) {
			// Assign reviewer to monograph
			AcquisitionsEditorAction::addReviewer($submission, $reviewerId, $reviewType, $round);
			Request::redirect(null, null, 'submissionReview', $monographId);

			// FIXME: Prompt for due date.
		} else {
			$this->setupTemplate(true, $monographId, 'review');

			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (isset($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} else if (isset($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$rangeInfo =& Handler::getRangeInfo('reviewers');
			$reviewers =& $acquisitionsEditorSubmissionDao->getReviewersForMonograph($press->getId(), $monographId, $reviewType, $round, $searchType, $search, $searchMatch, $rangeInfo);

			$press = Request::getPress();
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('reviewers', $reviewers);
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('reviewerStatistics', $acquisitionsEditorSubmissionDao->getReviewerStatistics($press->getId()));
			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_INTERESTS => 'user.interests',
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('completedReviewCounts', $reviewAssignmentDao->getCompletedReviewCounts($press->getId()));
			$templateMgr->assign('rateReviewerOnQuality', $press->getSetting('rateReviewerOnQuality'));
			$templateMgr->assign('averageQualityRatings', $reviewAssignmentDao->getAverageQualityRatings($press->getId()));

			$templateMgr->assign('helpTopicId', 'press.roles.reviewer');
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
			$templateMgr->assign('reviewerDatabaseLinks', $press->getSetting('reviewerDatabaseLinks'));
			$templateMgr->assign('reviewType', $reviewType);
			$templateMgr->assign('sort', $sort);
			$templateMgr->assign('sortDirection', $sortDirection);
			$templateMgr->display('acquisitionsEditor/selectReviewer.tpl');
		}
	}

	/**
	 * Create a new user as a reviewer.
	 */
	function createReviewer($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		import('acquisitionsEditor.form.CreateReviewerForm');
		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$createReviewerForm =& new CreateReviewerForm($monographId);
		$this->setupTemplate(true, $monographId);

		if (isset($args[1]) && $args[1] === 'create') {
			$createReviewerForm->readInputData();
			if ($createReviewerForm->validate()) {
				// Create a user and enroll them as a reviewer.
				$newUserId = $createReviewerForm->execute();
				Request::redirect(null, null, 'selectReviewer', array($monographId, $newUserId));
			} else {
				$createReviewerForm->display();
			}
		} else {
			// Display the "create user" form.
			if ($createReviewerForm->isLocaleResubmit()) {
				$createReviewerForm->readInputData();
			} else {
				$createReviewerForm->initData();
			}
			$createReviewerForm->display();
		}

	}

	/**
	 * Get a suggested username, making sure it's not
	 * already used by the system. (Poor-man's AJAX.)
	 */
	function suggestUsername() {
		parent::validate();
		$suggestion = Validation::suggestUsername(
			Request::getUserVar('firstName'),
			Request::getUserVar('lastName')
		);
		echo $suggestion;
	}

	/**
	 * Search for users to enroll as reviewers.
	 */
	function enrollSearch($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleId = $roleDao->getRoleIdFromPath('reviewer');

		$user =& Request::getUser();

		$rangeInfo = Handler::getRangeInfo('users');
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);

		$searchType = null;
		$searchMatch = null;
		$search = $searchQuery = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (isset($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} else if (isset($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$userDao =& DAORegistry::getDAO('UserDAO');
		$users =& $userDao->getUsersByField($searchType, $searchMatch, $search, false, $rangeInfo);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $searchQuery);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('fieldOptions', Array(
			USER_FIELD_INTERESTS => 'user.interests',
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email'
		));
		$templateMgr->assign('roleId', $roleId);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));

		$templateMgr->assign('helpTopicId', 'press.roles.index');
		$templateMgr->display('acquisitionsEditor/searchUsers.tpl');
	}

	function enroll($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleId = $roleDao->getRoleIdFromPath('reviewer');

		$users = Request::getUserVar('users');
		if (!is_array($users) && Request::getUserVar('userId') != null) $users = array(Request::getUserVar('userId'));

		// Enroll reviewer
		for ($i=0; $i<count($users); $i++) {
			if (!$roleDao->roleExists($press->getId(), $users[$i], $roleId)) {
				$role = new Role();
				$role->setPressId($press->getId());
				$role->setUserId($users[$i]);
				$role->setRoleId($roleId);

				$roleDao->insertRole($role);
			}
		}
		Request::redirect(null, null, 'selectReviewer', $monographId);
	}

	function notifyReviewer($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'review');

		if (AcquisitionsEditorAction::notifyReviewer($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function clearReview($args) {
		$monographId = isset($args[0])?$args[0]:0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = $args[1];

		AcquisitionsEditorAction::clearReview($submission, $reviewId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function cancelReview($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;
		
		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'review');

		if (AcquisitionsEditorAction::cancelReview($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function remindReviewer($args = null) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');
		$this->setupTemplate(true, $monographId, 'review');

		if (AcquisitionsEditorAction::remindReviewer($submission, $reviewId, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function thankReviewer($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'review');

		if (AcquisitionsEditorAction::thankReviewer($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function rateReviewer() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$this->setupTemplate(true, $monographId, 'review');

		$reviewId = Request::getUserVar('reviewId');
		$quality = Request::getUserVar('quality');

		AcquisitionsEditorAction::rateReviewer($monographId, $reviewId, $quality);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function confirmReviewForReviewer($args) {
		$monographId = (int) isset($args[0])?$args[0]:0;
		$accept = Request::getUserVar('accept')?true:false;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = (int) isset($args[1])?$args[1]:0;

		AcquisitionsEditorAction::confirmReviewForReviewer($reviewId, $accept);
		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function uploadReviewForReviewer($args) {
		$monographId = (int) Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = (int) Request::getUserVar('reviewId');

		AcquisitionsEditorAction::uploadReviewForReviewer($reviewId);
		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function makeReviewerFileViewable() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');
		$fileId = Request::getUserVar('fileId');
		$revision = Request::getUserVar('revision');
		$viewable = Request::getUserVar('viewable');

		AcquisitionsEditorAction::makeReviewerFileViewable($monographId, $reviewId, $fileId, $revision, $viewable);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function setDueDate($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = isset($args[1]) ? $args[1] : 0;
		$dueDate = Request::getUserVar('dueDate');
		$numWeeks = Request::getUserVar('numWeeks');

		if ($dueDate != null || $numWeeks != null) {
			AcquisitionsEditorAction::setDueDate($monographId, $reviewId, $dueDate, $numWeeks);
			Request::redirect(null, null, 'submissionReview', $monographId);

		} else {
			$this->setupTemplate(true, $monographId, 'review');
			$press =& Request::getPress();

			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssignment = $reviewAssignmentDao->getById($reviewId);

			$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
			$settings =& $settingsDao->getPressSettings($press->getId());

			$templateMgr =& TemplateManager::getManager();

			if ($reviewAssignment->getDateDue() != null) {
				$templateMgr->assign('dueDate', $reviewAssignment->getDateDue());
			}

			$numWeeksPerReview = $settings['numWeeksPerReview'] == null ? 0 : $settings['numWeeksPerReview'];

			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('reviewId', $reviewId);
			$templateMgr->assign('todaysDate', date('Y-m-d'));
			$templateMgr->assign('numWeeksPerReview', $numWeeksPerReview);
			$templateMgr->assign('actionHandler', 'setDueDate');

			$templateMgr->display('acquisitionsEditor/setDueDate.tpl');
		}
	}

	function enterReviewerRecommendation($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;;

		$reviewId = Request::getUserVar('reviewId');

		$recommendation = Request::getUserVar('recommendation');

		if ($recommendation != null) {
			AcquisitionsEditorAction::setReviewerRecommendation($monographId, $reviewId, $recommendation, SUBMISSION_REVIEWER_RECOMMENDATION_ACCEPT);
			Request::redirect(null, null, 'submissionReview', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'review');

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('reviewId', $reviewId);

			import('submission.reviewAssignment.ReviewAssignment');
			$templateMgr->assign_by_ref('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());

			$templateMgr->display('acquisitionsEditor/reviewerRecommendation.tpl');
		}
	}

	/**
	 * Display a user's profile.
	 * @param $args array first parameter is the ID or username of the user to display
	 */
	function userProfile($args) {
		parent::validate();
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentUrl', Request::url(null, Request::getRequestedPage()));

		$userDao =& DAORegistry::getDAO('UserDAO');
		$userId = isset($args[0]) ? $args[0] : 0;
		if (is_numeric($userId)) {
			$userId = (int) $userId;
			$user = $userDao->getUser($userId);
		} else {
			$user = $userDao->getUserByUsername($userId);
		}


		if ($user == null) {
			// Non-existent user requested
			$templateMgr->assign('pageTitle', 'manager.people');
			$templateMgr->assign('errorMsg', 'manager.people.invalidUser');
			$templateMgr->display('common/error.tpl');

		} else {
			$site =& Request::getSite();
			$press =& Request::getPress();

			$countryDao =& DAORegistry::getDAO('CountryDAO');
			$country = null;
			if ($user->getCountry() != '') {
				$country = $countryDao->getCountry($user->getCountry());
			}
			$templateMgr->assign('country', $country);

			$templateMgr->assign_by_ref('user', $user);
			$templateMgr->assign('localeNames', Locale::getAllLocales());
			$templateMgr->assign('helpTopicId', 'press.roles.index');
			$templateMgr->display('acquisitionsEditor/userProfile.tpl');
		}
	}

	function viewMetadata($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'summary');

		AcquisitionsEditorAction::viewMetadata($submission);
	}

	function saveMetadata() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'summary');

		if (AcquisitionsEditorAction::saveMetadata($submission)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	/**
	 * Remove cover page from monograph
	 */
	function removeCoverPage($args) {
		$monographId = isset($args[0]) ? (int)$args[0] : 0;
		$formLocale = $args[1];
		$this->validate($monographId);
		$submission =& $this->submission;

		import('file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		$publicFileManager->removePressFile($press->getId(),$submission->getFileName($formLocale));
		$submission->setFileName('', $formLocale);
		$submission->setOriginalFileName('', $formLocale);
		$submission->setWidth('', $formLocale);
		$submission->setHeight('', $formLocale);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographDao->updateMonograph($submission);

		Request::redirect(null, null, 'viewMetadata', $monographId);
	}

	//
	// Review Form
	//

	/**
	 * Preview a review form.
	 * @param $args array ($reviewId, $reviewFormId)
	 */
	function previewReviewForm($args) {
		parent::validate();
		$this->setupTemplate(true);

		$reviewId = isset($args[0]) ? (int) $args[0] : null;
		$reviewFormId = isset($args[1]) ? (int)$args[1] : null;			

		$press =& Request::getPress();
		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, $press->getId());
		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewFormId);
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageTitle', 'manager.reviewForms.preview');	
		$templateMgr->assign_by_ref('reviewForm', $reviewForm);
		$templateMgr->assign('reviewFormElements', $reviewFormElements);
		$templateMgr->assign('reviewId', $reviewId);
		$templateMgr->assign('monographId', $reviewAssignment->getMonographId());
		//$templateMgr->assign('helpTopicId','press.managementPages.reviewForms');
		$templateMgr->display('acquisitionsEditor/previewReviewForm.tpl');
	}

	/**
	 * Clear a review form, i.e. remove review form assignment to the review.
	 * @param $args array ($monographId, $reviewId)
	 */
	function clearReviewForm($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$reviewId = isset($args[1]) ? (int) $args[1] : null;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;
		
		AcquisitionsEditorAction::clearReviewForm($submission, $reviewId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}
	
	/**
	 * Select a review form
	 * @param $args array ($monographId, $reviewId, $reviewFormId)
	 */
	function selectReviewForm($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;
				
		$reviewId = isset($args[1]) ? (int) $args[1] : null;
		$reviewFormId = isset($args[2]) ? (int) $args[2] : null;

		if ($reviewFormId != null) {
			AcquisitionsEditorAction::addReviewForm($submission, $reviewId, $reviewFormId);
			Request::redirect(null, null, 'submissionReview', $monographId);
		} else {
			$press =& Request::getPress();
			$rangeInfo =& Handler::getRangeInfo('reviewForms');
			$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
			$reviewForms =& $reviewFormDao->getActiveByPressId($press->getId(), $rangeInfo);
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

			$this->setupTemplate(true, $monographId, 'review');
			$templateMgr =& TemplateManager::getManager();
				
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('reviewId', $reviewId);
			$templateMgr->assign('assignedReviewFormId', $reviewAssignment->getReviewFormId());
			$templateMgr->assign_by_ref('reviewForms', $reviewForms);
			//$templateMgr->assign('helpTopicId','press.managementPages.reviewForms');
			$templateMgr->display('acquisitionsEditor/selectReviewForm.tpl');
		}
	}
	
	/**
	 * View review form response.
	 * @param $args array ($monographId, $reviewId)
	 */
	function viewReviewFormResponse($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = isset($args[1]) ? (int) $args[1] : null;

		AcquisitionsEditorAction::viewReviewFormResponse($submission, $reviewId);	
	}
	
	//
	// Editor Review
	//

	function editorReview() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$redirectTarget = 'submissionReview';

		// If the Upload button was pressed.
		$submit = Request::getUserVar('submit');
		if ($submit != null) {
			AcquisitionsEditorAction::uploadEditorVersion($submission);
		}		

		if (Request::getUserVar('setAcceptedFile')) {
			// If the Accept button was pressed
			$file = explode(',', Request::getUserVar('editorDecisionFile'));

			if (isset($file[0]) && isset($file[1])) {
				$reviewType = $submission->getCurrentReviewType();
				$round = $submission->getCurrentRound();

				// adva
				$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
				$workflowDao->proceed($monographId);
								
				switch ($submission->getCurrentReviewType()) {
					case WORKFLOW_PROCESS_ASSESSMENT_INTERNAL:
						$submission->setReviewFileId($file[0]);
						$submission->setReviewRevision($file[1]);
						$submission->setCurrentReviewType(WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL);
						$submission->setCurrentRound(1);
						$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
						$acquisitionsEditorSubmissionDao->updateAcquisitionsEditorSubmission($submission);
						break;
					case WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL:
						$signoffDao =& DAORegistry::getDAO('SignoffDAO');
						AcquisitionsEditorAction::setCopyeditFile($submission, $file[0], $file[1]);
	
						$copyeditAuthorSignoff = $signoffDao->build(
										'SIGNOFF_COPYEDITING_AUTHOR', 
										ASSOC_TYPE_MONOGRAPH, 
										$submission->getMonographId()
									  );
						$copyeditFinalSignoff = $signoffDao->build(
										'SIGNOFF_COPYEDITING_FINAL', 
										ASSOC_TYPE_MONOGRAPH, 
										$submission->getMonographId()
									);
						$copyeditAuthorSignoff->setUserId($submission->getUserId());
						$copyeditFinalSignoff->setUserId(0);
	
						$signoffDao->updateObject($copyeditAuthorSignoff);
						$signoffDao->updateObject($copyeditFinalSignoff);
	
						$redirectTarget = 'submissionEditing';
						break;
				}
			}

		} else if (Request::getUserVar('resubmit')) {
			// If the Resubmit button was pressed
			$file = explode(',', Request::getUserVar('editorDecisionFile'));
			if (isset($file[0]) && isset($file[1])) {
				AcquisitionsEditorAction::resubmitFile($submission, $file[0], $file[1]);

				$signoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $submission->getMonographId());
				$signoff->setFileId($file[0]);
				$signoff->setFileRevision($file[1]);
				$signoffDao->updateObject($signoff);
			}
		}

		Request::redirect(null, null, $redirectTarget, $monographId);
	}

	//
	// Copyedit
	//

	function selectCopyeditor($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if (isset($args[1]) && $args[1] != null && $roleDao->roleExists($press->getId(), $args[1], ROLE_ID_COPYEDITOR)) {
			AcquisitionsEditorAction::selectCopyeditor($submission, $args[1]);
			Request::redirect(null, null, 'submissionEditing', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'editing');

			$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');

			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (isset($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} else if (isset($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$copyeditors = $roleDao->getUsersByRoleId(ROLE_ID_COPYEDITOR, $press->getId(), $searchType, $search, $searchMatch);
			$copyeditorStatistics = $acquisitionsEditorSubmissionDao->getCopyeditorStatistics($press->getId());

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('users', $copyeditors);
			$templateMgr->assign('currentUser', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
			$templateMgr->assign_by_ref('statistics', $copyeditorStatistics);
			$templateMgr->assign('pageSubTitle', 'editor.monograph.selectCopyeditor');
			$templateMgr->assign('pageTitle', 'user.role.copyeditors');
			$templateMgr->assign('actionHandler', 'selectCopyeditor');
			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('monographId', $args[0]);

			$templateMgr->assign('helpTopicId', 'press.roles.copyeditor');
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
			$templateMgr->display('acquisitionsEditor/selectUser.tpl');
		}
	}

	function notifyCopyeditor($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send') ? true : false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::notifyCopyeditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/* Initiates the copyediting process when the editor does the copyediting */
	function initiateCopyedit() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		AcquisitionsEditorAction::initiateCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function thankCopyeditor($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankCopyeditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function notifyAuthorCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::notifyAuthorCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function thankAuthorCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankAuthorCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function notifyFinalCopyedit($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::notifyFinalCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function completeCopyedit($args) {
		$monographId = (int) Request::getUserVar('monographId');

		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		AcquisitionsEditorAction::completeCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function completeFinalCopyedit($args) {
		$monographId = (int) Request::getUserVar('monographId');

		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		AcquisitionsEditorAction::completeFinalCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function thankFinalCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankFinalCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function uploadReviewVersion() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		AcquisitionsEditorAction::uploadReviewVersion($submission);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function uploadCopyeditVersion() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$copyeditStage = Request::getUserVar('copyeditStage');
		AcquisitionsEditorAction::uploadCopyeditVersion($submission, $copyeditStage);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Add a supplementary file.
	 * @param $args array ($monographId)
	 */
	function addSuppFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;		
		$this->setupTemplate(true, $monographId, 'summary');

		import('submission.form.SuppFileForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new SuppFileForm($submission);

		if ($submitForm->isLocaleResubmit()) {
			$submitForm->readInputData();
		} else {
			$submitForm->initData();
		}
		$submitForm->display();
	}

	/**
	 * Edit a supplementary file.
	 * @param $args array ($monographId, $suppFileId)
	 */
	function editSuppFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$suppFileId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'summary');

		import('submission.form.SuppFileForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new SuppFileForm($submission, $suppFileId);

		if ($submitForm->isLocaleResubmit()) {
			$submitForm->readInputData();
		} else {
			$submitForm->initData();
		}
		$submitForm->display();
	}

	/**
	 * Set reviewer visibility for a supplementary file.
	 * @param $args array ($suppFileId)
	 */
	function setSuppFileVisibility($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;

		$suppFileId = Request::getUserVar('fileId');
		$suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
		$suppFile = $suppFileDao->getSuppFile($suppFileId, $monographId);

		if (isset($suppFile) && $suppFile != null) {
			$suppFile->setShowReviewers(Request::getUserVar('show')==1?1:0);
			$suppFileDao->updateSuppFile($suppFile);
		}
		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	/**
	 * Save a supplementary file.
	 * @param $args array ($suppFileId)
	 */
	function saveSuppFile($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;

		$suppFileId = isset($args[0]) ? (int) $args[0] : 0;

		import('submission.form.SuppFileForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new SuppFileForm($submission, $suppFileId);
		$submitForm->readInputData();

		if ($submitForm->validate()) {
			$submitForm->execute();

			// Send a notification to associated users
			import('notification.Notification');
			$monographDao =& DAORegistry::getDAO('MonographDAO'); 
			$monograph =& $monographDao->getMonograph($monographId);
			$notificationUsers = $monograph->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $user) {
				$url = Request::url(null, $user['role'], 'submissionEditing', $monograph->getMonographId(), null, 'layout');
				Notification::createNotification($user['id'], "notification.type.suppFileModified",
					$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_SUPP_FILE_MODIFIED);
			}

			Request::redirect(null, null, $this->getFrom(), $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'summary');
			$submitForm->display();
		}
	}

	/**
	 * Delete an editor version file.
	 * @param $args array ($monographId, $fileId)
	 */
	function deleteMonographFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$fileId = isset($args[1]) ? (int) $args[1] : 0;
		$revisionId = isset($args[2]) ? (int) $args[2] : 0;

		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;		
		AcquisitionsEditorAction::deleteMonographFile($submission, $fileId, $revisionId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	/**
	 * Delete a supplementary file.
	 * @param $args array ($monographId, $suppFileId)
	 */
	function deleteSuppFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$suppFileId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;

		AcquisitionsEditorAction::deleteSuppFile($submission, $suppFileId);

		Request::redirect(null, null, $this->getFrom(), $monographId);
	}

	function archiveSubmission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;

		AcquisitionsEditorAction::archiveSubmission($submission);

		Request::redirect(null, null, 'submission', $monographId);
	}

	function restoreToQueue($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;		

		AcquisitionsEditorAction::restoreToQueue($submission);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function unsuitableSubmission($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;		

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'summary');

		if (AcquisitionsEditorAction::unsuitableSubmission($submission, $send)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	/**
	 * Set acquisitions arrangement ID.
	 * @param $args array ($monographId)
	 */
	function updateAcquisitionsArrangement($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		AcquisitionsEditorAction::updateAcquisitionsArrangement($submission, Request::getUserVar('arrangement'));
		Request::redirect(null, null, 'submission', $monographId);
	}

	/**
	 * Set RT comments status for monograph.
	 * @param $args array ($monographId)
	 */
	function updateCommentsStatus($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;	
		AcquisitionsEditorAction::updateCommentsStatus($submission, Request::getUserVar('commentsStatus'));
		Request::redirect(null, null, 'submission', $monographId);
	}

	//
	// Layout Editing
	//

	/**
	 * Upload a layout file (either layout version, galley, or supp. file).
	 */
	function uploadLayoutFile() {
		$layoutFileType = Request::getUserVar('layoutFileType');
		if ($layoutFileType == 'submission') {
			$this->uploadLayoutVersion();

		} else if ($layoutFileType == 'galley') {
			$this->uploadGalley('layoutFile');

		} else if ($layoutFileType == 'supp') {
			$this->uploadSuppFile('layoutFile');

		} else {
			Request::redirect(null, null, $this->getFrom(), Request::getUserVar('monographId'));
		}
	}

	/**
	 * Upload the layout version of the submission file
	 */
	function uploadLayoutVersion() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		AcquisitionsEditorAction::uploadLayoutVersion($submission);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Delete a monograph image.
	 * @param $args array ($monographId, $fileId)
	 */
	function deleteMonographImage($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$fileId = isset($args[2]) ? (int) $args[2] : 0;
		$revisionId = isset($args[3]) ? (int) $args[3] : 0;

		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;
		AcquisitionsEditorAction::deleteMonographImage($submission, $fileId, $revisionId);

		Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
	}

	/**
	 * Assign/reassign a production editor to the submission.
	 * @param $args array ($monographId, [$userId])
	 */
	function assignProductionEditor($args, $op = null) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$editorId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		if ($editorId && $roleDao->roleExists($press->getId(), $editorId, ROLE_ID_PRODUCTION_EDITOR)) {
			AcquisitionsEditorAction::assignProductionEditor($submission, $editorId);
			if ($op == null)
				$op = 'submissionProduction';
			Request::redirect(null, null, $op, $monographId);
		} else {
			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (isset($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} else if (isset($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$productionEditors = $roleDao->getUsersByRoleId(ROLE_ID_PRODUCTION_EDITOR, $press->getId(), $searchType, $search, $searchMatch);

			$this->setupTemplate(true, $monographId, 'editing');

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));

			$templateMgr->assign('pageTitle', 'user.role.productionEditors');
			$templateMgr->assign('pageSubTitle', 'editor.monograph.selectProductionEditor');
			$templateMgr->assign('actionHandler', 'assignProductionEditor');
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign_by_ref('users', $productionEditors);

			$templateMgr->assign('assignedUsers', array($editorId));
			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('helpTopicId', 'press.roles.productionEditor');
			$templateMgr->display('acquisitionsEditor/selectUser.tpl');
		}
	}

	/**
	 * Notify the layout editor.
	 */
	function notifyLayoutDesigner($args) {
		$monographId = Request::getUserVar('monographId');
		$layoutAssignmentId = Request::getUserVar('layoutAssignmentId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$send = Request::getUserVar('send') ? true : false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::notifyLayoutDesigner($submission, $layoutAssignmentId, $send)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
		}
	}

	/**
	 * Thank the layout editor.
	 */
	function thankLayoutEditor($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$send = Request::getUserVar('send') ? true : false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankLayoutEditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Create a new galley with the uploaded file.
	 */
	function uploadGalley($fileName = null) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		import('submission.form.MonographGalleyForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$galleyForm =& new MonographGalleyForm($monographId);
		$galleyId = $galleyForm->execute($fileName);

		Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
	}

	/**
	 * Edit a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function editGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.form.MonographGalleyForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new MonographGalleyForm($monographId, $galleyId);

		if ($submitForm->isLocaleResubmit()) {
			$submitForm->readInputData();
		} else {
			$submitForm->initData();
		}
		$submitForm->display();
	}

	/**
	 * Save changes to a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function saveGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		import('submission.form.MonographGalleyForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new MonographGalleyForm($monographId, $galleyId);

		$submitForm->readInputData();
		if ($submitForm->validate()) {
			$submitForm->execute();

			// Send a notification to associated users
			import('notification.Notification');
			$articleDao =& DAORegistry::getDAO('ArticleDAO'); 
			$article =& $articleDao->getArticle($articleId);
			$notificationUsers = $article->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $user) {
				$url = Request::url(null, $user['role'], 'submissionEditing', $article->getArticleId(), null, 'layout');
				Notification::createNotification($user['id'], "notification.type.galleyModified",
					$article->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_GALLEY_MODIFIED);
			}

			if (Request::getUserVar('uploadImage')) {
				$submitForm->uploadImage();
				Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
			} else if(($deleteImage = Request::getUserVar('deleteImage')) && count($deleteImage) == 1) {
				list($imageId) = array_keys($deleteImage);
				$submitForm->deleteImage($imageId);
				Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
			}
			Request::redirect(null, null, 'submissionEditing', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'editing');
			$submitForm->display();
		}
	}

	/**
	 * Change the sequence order of a galley.
	 */
	function orderGalley() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		AcquisitionsEditorAction::orderGalley($submission, Request::getUserVar('galleyId'), Request::getUserVar('d'));

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Delete a galley file.
	 * @param $args array ($monographId, $galleyId)
	 */
	function deleteGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		AcquisitionsEditorAction::deleteGalley($submission, $galleyId);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Proof / "preview" a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('galleyId', $galleyId);
		$templateMgr->display('submission/layout/proofGalley.tpl');
	}

	/**
	 * Proof galley (shows frame header).
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalleyTop($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign('galleyId', $galleyId);
		$templateMgr->assign('backHandler', 'submissionEditing');
		$templateMgr->display('submission/layout/proofGalleyTop.tpl');
	}

	/**
	 * Proof galley (outputs file contents).
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalleyFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$galley =& $galleyDao->getGalley($galleyId, $monographId);

		import('file.MonographFileManager'); // FIXME

		if (isset($galley)) {
			if ($galley->isHTMLGalley()) {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign_by_ref('galley', $galley);
				if ($galley->isHTMLGalley() && $styleFile =& $galley->getStyleFile()) {
					$templateMgr->addStyleSheet(Request::url(null, 'monograph', 'viewFile', array(
						$monographId, $galleyId, $styleFile->getFileId()
					)));
				}
				$templateMgr->display('submission/layout/proofGalleyHTML.tpl');

			} else {
				// View non-HTML file inline
				$this->viewFile(array($monographId, $galley->getFileId()));
			}
		}
	}

	/**
	 * Upload a new supplementary file.
	 */
	function uploadSuppFile($fileName = null) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;

		import('submission.form.SuppFileForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$suppFileForm =& new SuppFileForm($submission);
		$suppFileForm->setData('title', Locale::translate('common.untitled'));
		$suppFileId = $suppFileForm->execute($fileName);

		Request::redirect(null, null, 'editSuppFile', array($monographId, $suppFileId));
	}

	/**
	 * Change the sequence order of a supplementary file.
	 */
	function orderSuppFile() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;

		AcquisitionsEditorAction::orderSuppFile($submission, Request::getUserVar('suppFileId'), Request::getUserVar('d'));

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}


	//
	// Submission History (FIXME Move to separate file?)
	//

	/**
	 * View submission event log.
	 */
	function submissionEventLog($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$logId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'history');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('isEditor', Validation::isEditor());
		$templateMgr->assign_by_ref('submission', $submission);

		if ($logId) {
			$logDao =& DAORegistry::getDAO('MonographEventLogDAO');
			$logEntry =& $logDao->getLogEntry($logId, $monographId);
		}

		if (isset($logEntry)) {
			$templateMgr->assign('logEntry', $logEntry);
			$templateMgr->display('acquisitionsEditor/submissionEventLogEntry.tpl');

		} else {
			$rangeInfo =& Handler::getRangeInfo('eventLogEntries');

			import('monograph.log.MonographLog');
			$eventLogEntries =& MonographLog::getEventLogEntries($monographId, $rangeInfo);
			$templateMgr->assign('eventLogEntries', $eventLogEntries);
			$templateMgr->display('acquisitionsEditor/submissionEventLog.tpl');
		}
	}

	/**
	 * View submission event log by record type.
	 */
	function submissionEventLogType($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$assocType = isset($args[1]) ? (int) $args[1] : null;
		$assocId = isset($args[2]) ? (int) $args[2] : null;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'history');

		$rangeInfo =& Handler::getRangeInfo('eventLogEntries');
		$logDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$eventLogEntries =& $logDao->getMonographLogEntriesByAssoc($monographId, $assocType, $assocId, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('showBackLink', true);
		$templateMgr->assign('isEditor', Validation::isEditor());
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('eventLogEntries', $eventLogEntries);
		$templateMgr->display('acquisitionsEditor/submissionEventLog.tpl');
	}

	/**
	 * Clear submission event log entries.
	 */
	function clearSubmissionEventLog($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$logId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;

		$logDao =& DAORegistry::getDAO('MonographEventLogDAO');

		if ($logId) {
			$logDao->deleteLogEntry($logId, $monographId);

		} else {
			$logDao->deleteMonographLogEntries($monographId);
		}

		Request::redirect(null, null, 'submissionEventLog', $monographId);
	}

	/**
	 * View submission email log.
	 */
	function submissionEmailLog($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$logId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'history');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('isEditor', Validation::isEditor());
		$templateMgr->assign_by_ref('submission', $submission);

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		import('file.MonographFileManager');
		$templateMgr->assign('attachments', $monographFileDao->getMonographFilesByAssocId($logId, MONOGRAPH_FILE_ATTACHMENT));

		if ($logId) {
			$logDao =& DAORegistry::getDAO('MonographEmailLogDAO');
			$logEntry =& $logDao->getLogEntry($logId, $monographId);
		}

		if (isset($logEntry)) {
			$templateMgr->assign_by_ref('logEntry', $logEntry);
			$templateMgr->display('acquisitionsEditor/submissionEmailLogEntry.tpl');

		} else {
			$rangeInfo =& Handler::getRangeInfo('emailLogEntries');

			import('monograph.log.MonographLog');
			$emailLogEntries =& MonographLog::getEmailLogEntries($monographId, $rangeInfo);
			$templateMgr->assign_by_ref('emailLogEntries', $emailLogEntries);
			$templateMgr->display('acquisitionsEditor/submissionEmailLog.tpl');
		}
	}

	/**
	 * View submission email log by record type.
	 */
	function submissionEmailLogType($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$assocType = isset($args[1]) ? (int) $args[1] : null;
		$assocId = isset($args[2]) ? (int) $args[2] : null;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'history');

		$rangeInfo =& Handler::getRangeInfo('eventLogEntries');
		$logDao =& DAORegistry::getDAO('MonographEmailLogDAO');
		$emailLogEntries =& $logDao->getMonographLogEntriesByAssoc($monographId, $assocType, $assocId, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('showBackLink', true);
		$templateMgr->assign('isEditor', Validation::isEditor());
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('emailLogEntries', $emailLogEntries);
		$templateMgr->display('acquisitionsEditor/submissionEmailLog.tpl');
	}

	/**
	 * Clear submission email log entries.
	 */
	function clearSubmissionEmailLog($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$logId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);

		$logDao =& DAORegistry::getDAO('MonographEmailLogDAO');

		if ($logId) {
			$logDao->deleteLogEntry($logId, $monographId);

		} else {
			$logDao->deleteMonographLogEntries($monographId);
		}

		Request::redirect(null, null, 'submissionEmailLog', $monographId);
	}

	// Submission Notes Functions

	/**
	 * Creates a submission note.
	 * Redirects to submission notes list
	 */
	function addSubmissionNote() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);

		AcquisitionsEditorAction::addSubmissionNote($monographId);
		Request::redirect(null, null, 'submissionNotes', $monographId);
	}

	/**
	 * Removes a submission note.
	 * Redirects to submission notes list
	 */
	function removeSubmissionNote() {
		$monographId = Request::getUserVar('monographId');		
		$this->validate($monographId);

		AcquisitionsEditorAction::removeSubmissionNote($monographId);
		Request::redirect(null, null, 'submissionNotes', $monographId);
	}

	/**
	 * Updates a submission note.
	 * Redirects to submission notes list
	 */
	function updateSubmissionNote() {
		$monographId = Request::getUserVar('monographId');		
		$this->validate($monographId);

		AcquisitionsEditorAction::updateSubmissionNote($monographId);
		Request::redirect(null, null, 'submissionNotes', $monographId);
	}

	/**
	 * Clear all submission notes.
	 * Redirects to submission notes list
	 */
	function clearAllSubmissionNotes() {
		$monographId = Request::getUserVar('monographId');		
		$this->validate($monographId);

		AcquisitionsEditorAction::clearAllSubmissionNotes($monographId);
		Request::redirect(null, null, 'submissionNotes', $monographId);
	}

	/**
	 * View submission notes.
	 */
	function submissionNotes($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$noteViewType = isset($args[1]) ? $args[1] : '';
		$noteId = isset($args[2]) ? (int) $args[2] : 0;

		$this->validate($monographId);
		$this->setupTemplate(true, $monographId, 'history');

		$rangeInfo =& Handler::getRangeInfo('submissionNotes');
		$monographNoteDao =& DAORegistry::getDAO('MonographNoteDAO');

		// submission note edit
		if ($noteViewType == 'edit') {
			$monographNote = $monographNoteDao->getMonographNoteById($noteId);
		}

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('monographId', $monographId);
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign('noteViewType', $noteViewType);
		if (isset($monographNote)) {
			$templateMgr->assign_by_ref('monographNote', $monographNote);		
		}

		if ($noteViewType == 'edit' || $noteViewType == 'add') {
			$templateMgr->assign('showBackLink', true);
		} else {
			$submissionNotes =& $monographNoteDao->getMonographNotes($monographId, $rangeInfo);
			$templateMgr->assign_by_ref('submissionNotes', $submissionNotes);
		}

		$templateMgr->display('acquisitionsEditor/submissionNotes.tpl');
	}


	//
	// Misc
	//

	/**
	 * Download a file.
	 * @param $args array ($monographId, $fileId, [$revision])
	 */
	function downloadFile($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$fileId = isset($args[1]) ? $args[1] : 0;
		$revision = isset($args[2]) ? $args[2] : null;

		$this->validate($monographId);
		if (!AcquisitionsEditorAction::downloadFile($monographId, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	/**
	 * View a file (inlines file).
	 * @param $args array ($monographId, $fileId, [$revision])
	 */
	function viewFile($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$fileId = isset($args[1]) ? $args[1] : 0;
		$revision = isset($args[2]) ? $args[2] : null;

		$this->validate($monographId);
		if (!AcquisitionsEditorAction::viewFile($monographId, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}


	//
	// Proofreading
	//

	/**
	 * Select Proofreader.
	 * @param $args array ($monographId, $userId)
	 */
	function selectProofreader($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$userId = isset($args[1]) ? (int) $args[1] : 0;

		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;		

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		if ($userId && $monographId && $roleDao->roleExists($press->getId(), $userId, ROLE_ID_PROOFREADER)) {
			import('submission.proofreader.ProofreaderAction');
			ProofreaderAction::selectProofreader($userId, $submission);
			Request::redirect(null, null, 'submissionEditing', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'editing');

			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (isset($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} else if (isset($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$proofreaders = $roleDao->getUsersByRoleId(ROLE_ID_PROOFREADER, $press->getId(), $searchType, $search, $searchMatch);

			$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
			$proofreaderStatistics = $acquisitionsEditorSubmissionDao->getProofreaderStatistics($press->getId());

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('users', $proofreaders);

			$proofSignoff = $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
			if ($proofSignoff) {
				$templateMgr->assign('currentUser', $proofSignoff->getUserId());
 			}
			$templateMgr->assign('statistics', $proofreaderStatistics);
			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('pageSubTitle', 'editor.monograph.selectProofreader');
			$templateMgr->assign('pageTitle', 'user.role.proofreaders');
			$templateMgr->assign('actionHandler', 'selectProofreader');

			$templateMgr->assign('helpTopicId', 'press.roles.proofreader');
			$templateMgr->display('acquisitionsEditor/selectUser.tpl');
		}
	}

	/**
	 * Notify author for proofreading
	 */
	function notifyAuthorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_AUTHOR_REQUEST', $send?'':Request::url(null, null, 'notifyAuthorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Thank author for proofreading
	 */
	function thankAuthorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_AUTHOR_ACK', $send?'':Request::url(null, null, 'thankAuthorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Editor initiates proofreading
	 */
	function editorInitiateProofreader() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);


		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
		if (!$signoff->getUserId()) {
			$signoff->setUserId($user->getId());
		}
		$signoff->setDateNotified(Core::getCurrentDate());
		$signoffDao->updateObject($signoff);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Editor completes proofreading
	 */
	function editorCompleteProofreader() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
		$signoff->setDateCompleted(Core::getCurrentDate());
		$signoffDao->updateObject($signoff);


		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Notify proofreader for proofreading
	 */
	function notifyProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_REQUEST', $send?'':Request::url(null, null, 'notifyProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Thank proofreader for proofreading
	 */
	function thankProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_ACK', $send?'':Request::url(null, null, 'thankProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Editor initiates layout editor proofreading
	 */
	function editorInitiateLayoutEditor() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);


		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
		if (!$signoff->getUserId()) {
			$signoff->setUserId($user->getId());
		}
		$signoff->setDateNotified(Core::getCurrentDate());
		$signoff->setDateUnderway(null);
		$signoff->setDateCompleted(null);
		$signoff->setDateAcknowledged(null);
		$signoffDao->updateObject($signoff);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Editor completes layout editor proofreading
	 */
	function editorCompleteLayoutEditor() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
		$signoff->setDateCompleted(Core::getCurrentDate());
		$signoffDao->updateObject($signoff);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Notify layout editor for proofreading
	 */
	function notifyLayoutEditorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$this->setupTemplate(true, $monographId, 'editing');

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
		$signoff->setDateNotified(Core::getCurrentDate());
		$signoff->setDateUnderway(null);
		$signoff->setDateCompleted(null);
		$signoff->setDateAcknowledged(null);
		$signoffDao->updateObject($signoff);

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_LAYOUT_REQUEST', $send?'':Request::url(null, null, 'notifyLayoutEditorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Thank layout editor for proofreading
	 */
	function thankLayoutEditorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		$this->validate($monographId, ACQUISITIONS_EDITOR_ACCESS_EDIT);
		$this->setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_LAYOUT_ACK', $send?'':Request::url(null, null, 'thankLayoutEditorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Schedule/unschedule a monograph for publication.
	 */
	function scheduleForPublication($args) {
		//FIXME implement

	}

	//
	// Validation
	//

	/**
	 * Validate that the user is the assigned acquisitions editor for
	 * the monograph, or is a managing editor.
	 * Redirects to acquisitionsEditor index page if validation fails.
	 * @param $monographId int Monograph ID to validate
	 * @param $access int Optional name of access level required -- see ACQUISITIONS_EDITOR_ACCESS_... constants
	 */
	function validate($monographId, $access = null) {
		parent::validate();

		$isValid = true;

		$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		$acquisitionsEditorSubmission =& $acquisitionsEditorSubmissionDao->getAcquisitionsEditorSubmission($monographId);

		if ($acquisitionsEditorSubmission == null) {
			$isValid = false;

		} else if ($acquisitionsEditorSubmission->getPressId() != $press->getId()) {
			$isValid = false;

		} else if ($acquisitionsEditorSubmission->getDateSubmitted() == null) {
			$isValid = false;

		} else {
			$templateMgr =& TemplateManager::getManager();
						
			if (Validation::isEditor()) {
				// Make canReview and canEdit available to templates.
				// Since this user is an editor, both are available.
				$templateMgr->assign('canReview', true);
				$templateMgr->assign('canEdit', true);
			} else {
				// If this user isn't the submission's editor, they don't have access.
				$editAssignments =& $acquisitionsEditorSubmission->getByIds();
				$wasFound = false;
				foreach ($editAssignments as $editAssignment) {
					if ($editAssignment->getEditorId() == $user->getId()) {
						$templateMgr->assign('canReview', $editAssignment->getCanReview());
						$templateMgr->assign('canEdit', $editAssignment->getCanEdit());
						switch ($access) {
							case ACQUISITIONS_EDITOR_ACCESS_EDIT:
								if ($editAssignment->getCanEdit()) {
									$wasFound = true;
								}
								break;
							case ACQUISITIONS_EDITOR_ACCESS_REVIEW:
								if ($editAssignment->getCanReview()) {
									$wasFound = true;
								}
								break;

							default:
								$wasFound = true;
						}
						break;
					}
				}

				if (!$wasFound) $isValid = false;
			}
		}

		if (!$isValid) {
			Request::redirect(null, Request::getRequestedPage());
		}

		// If necessary, note the current date and time as the "underway" date/time
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignments =& $acquisitionsEditorSubmission->getEditAssignments();
		foreach ($editAssignments as $editAssignment) {
			if ($editAssignment->getEditorId() == $user->getId() && $editAssignment->getDateUnderway() === null) {
				$editAssignment->setDateUnderway(Core::getCurrentDate());
				$editAssignmentDao->updateEditAssignment($editAssignment);
			}
		}

		$this->submission =& $acquisitionsEditorSubmission;
		return true;
	}

}
?>
