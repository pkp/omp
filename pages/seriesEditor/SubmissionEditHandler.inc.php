<?php

/**
 * @file SubmissionEditHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionEditHandler
 * @ingroup pages_seriesEditor
 *
 * @brief Handle requests for submission tracking.
 */

// $Id$


define('SERIES_EDITOR_ACCESS_EDIT', 0x00001);
define('SERIES_EDITOR_ACCESS_REVIEW', 0x00002);

import('pages.seriesEditor.SeriesEditorHandler');
import('classes.submission.seriesEditor.SeriesEditorAction');

class SubmissionEditHandler extends SeriesEditorHandler {
	/** The submission associated with this request **/
	var $submission;

	/**
	 * Constructor
	 **/
	function SubmissionEditHandler() {
		parent::SeriesEditorHandler();
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
		$isEditor = $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_EDITOR);

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$series =& $seriesDao->getById($submission->getSeriesId());

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $submission);
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignments =& $editAssignmentDao->getByMonographId($monographId);
		$templateMgr->assign_by_ref('editAssignments', $editAssignments);
		$templateMgr->assign_by_ref('series', $series);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($submission->getId(), 'submission');
		$templateMgr->assign_by_ref('submissionFiles', $monographFiles);

		$templateMgr->assign_by_ref('bookFileTypes', $bookFileTypes);
		$templateMgr->assign_by_ref('submissionFiles', $monographFiles);
		$templateMgr->assign('pageToDisplay', 'submissionSummary');

		$templateMgr->assign_by_ref('series', $seriesDao->getTitlesByPressId($press->getId()));

		if ($enableComments) {
			import('classes.monograph.Monograph');
			$templateMgr->assign('commentsStatus', $submission->getCommentsStatus());
			$templateMgr->assign_by_ref('commentsStatusOptions', Monograph::getCommentsStatusOptions());
		}

		if ($isEditor) {
			$templateMgr->assign('helpTopicId', 'editorial.editorsRole.submissionSummary');
		}

		$templateMgr->display('seriesEditor/submission.tpl');
	}

	function showReview(&$args, &$request) {
		$this->setupTemplate(EDITOR_SERIES_HOME);
		$monographId = array_shift($args);
		
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);
		
		$templateMgr =& TemplateManager::getManager();
		$currentRound = $monograph->getCurrentRound();
		$templateMgr->assign('currentRound', $currentRound);

		// Set allRounds to an array of all values > 0 and less than currentRound--This will determine the tabs to show
		$allRounds = array();
		for ($i = 1; $i <= $currentRound; $i++) $allRounds[] = $i;
		$templateMgr->assign('rounds', $allRounds);
		
		$templateMgr->assign('currentReviewType', $monograph->getCurrentReviewType());
		$templateMgr->assign('monographId', $monographId);
		$templateMgr->display('seriesEditor/showReviewers.tpl');
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

		$templateMgr->assign_by_ref('editorDecisionOptions', SeriesEditorSubmission::getEditorDecisionOptions());

		import('classes.submission.reviewAssignment.ReviewAssignment');
		$templateMgr->assign_by_ref('reviewerRatingOptions', ReviewAssignment::getReviewerRatingOptions());
		$templateMgr->assign_by_ref('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());

		$templateMgr->display('seriesEditor/submissionRegrets.tpl');
	}

	function submissionReview($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
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
		$allowResubmit = $lastDecision == SUBMISSION_EDITOR_DECISION_RESUBMIT && $seriesEditorSubmissionDao->getMaxReviewRound($monographId, $reviewType) == $round ? true : false;

		// Prepare an array to store the 'Notify Reviewer' email logs
		$notifyReviewerLogs = array();
		foreach ((array) $submission->getReviewAssignments($reviewType, $round) as $reviewAssignment) {
			$notifyReviewerLogs[$reviewAssignment->getReviewId()] = array();
		}

		// Parse the list of email logs and populate the array.
		import('classes.monograph.log.MonographLog');
		$emailLogEntries =& MonographLog::getEmailLogEntries($monographId);
		foreach ($emailLogEntries->toArray() as $emailLog) {
			if ($emailLog->getEventType() == MONOGRAPH_EMAIL_REVIEW_NOTIFY_REVIEWER) {
				if (isset($notifyReviewerLogs[$emailLog->getAssocId()]) && is_array($notifyReviewerLogs[$emailLog->getAssocId()])) {
					array_push($notifyReviewerLogs[$emailLog->getAssocId()], $emailLog);
				}
			}
		}

		// get press published review form titles
		$reviewFormTitles =& $reviewFormDao->getTitlesByAssocId(ASSOC_TYPE_PRESS, $press->getId(), 1);

		$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewFormResponses = array();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewFormTitles = array();

		foreach ((array) $submission->getReviewAssignments($reviewType, $round) as $reviewAssignment) {
			$reviewForm =& $reviewFormDao->getReviewForm($reviewAssignment->getReviewFormId());
			if ($reviewForm) {
				$reviewFormTitles[$reviewForm->getId()] = $reviewForm->geLocalizedTitle();
			}
			unset($reviewForm);
			$reviewFormResponses[$reviewAssignment->getReviewId()] = $reviewFormResponseDao->reviewFormResponseExists($reviewAssignment->getReviewId());
		}

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('pageToDisplay', 'submissionReview');
		$templateMgr->assign_by_ref('reviewType', $reviewType);
		$templateMgr->assign('round', $round);
		$templateMgr->assign_by_ref('editorDecisions', $editorDecisions);
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('reviewIndexes', $reviewAssignmentDao->getReviewIndexesForRound($monographId, $reviewType, $round));
		$templateMgr->assign_by_ref('reviewAssignments', $submission->getReviewAssignments($reviewType, $round));
		$templateMgr->assign('reviewFormResponses', $reviewFormResponses);
		$templateMgr->assign('reviewFormTitles', $reviewFormTitles);
		$templateMgr->assign_by_ref('notifyReviewerLogs', $notifyReviewerLogs);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('copyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('revisedFile', $submission->getRevisedFile());
		$templateMgr->assign_by_ref('editorFile', $submission->getEditorFile());
		$templateMgr->assign('rateReviewerOnQuality', $press->getSetting('rateReviewerOnQuality'));
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

		import('classes.submission.reviewAssignment.ReviewAssignment');
		$templateMgr->assign_by_ref('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());
		$templateMgr->assign_by_ref('reviewerRatingOptions', ReviewAssignment::getReviewerRatingOptions());

		$templateMgr->assign('allowRecommendation', $allowRecommendation);
		$templateMgr->assign('allowResubmit', $allowResubmit);
		$templateMgr->assign('helpTopicId', 'editorial.seriesEditorsRole.review');
		$templateMgr->display('seriesEditor/submission.tpl');
	}

	function submissionEditing($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
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

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('copyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('editorAuthorCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR'));
		$templateMgr->assign_by_ref('finalCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL'));
		$templateMgr->assign_by_ref('copyeditor', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign('pageToDisplay', 'submissionEditing');

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user =& Request::getUser();
		$templateMgr->assign('isEditor', $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_EDITOR));

		$templateMgr->assign('useCopyeditors', true);
		$templateMgr->assign('useLayoutEditors', $useLayoutEditors);
		$templateMgr->assign('useProofreaders', $useProofreaders);
//		$templateMgr->assign('submissionAccepted', $submissionAccepted);

		$templateMgr->assign('helpTopicId', 'editorial.seriesEditorsRole.editing');
		$templateMgr->display('seriesEditor/submission.tpl');
	}

	function submissionProduction($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
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

		$templateMgr->assign('pageToDisplay', 'submissionProduction');
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('productionEditor', $submission->getUserBySignoffType('SIGNOFF_PRODUCTION'));

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user =& Request::getUser();
		$templateMgr->assign('isEditor', $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_EDITOR));

		$templateMgr->assign('useCopyeditors', true);
		$templateMgr->assign('useLayoutEditors', $useLayoutEditors);
		$templateMgr->assign('useProofreaders', $useProofreaders);
//		$templateMgr->assign_by_ref('proofAssignment', $submission->getProofAssignment());
//		$templateMgr->assign_by_ref('layoutAssignment', $submission->getLayoutAssignment());
		$templateMgr->assign('submissionAccepted', $submissionAccepted);

		$templateMgr->assign('helpTopicId', 'editorial.seriesEditorsRole.editing');
		$templateMgr->display('seriesEditor/submission.tpl');
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

		import('classes.monograph.log.MonographLog');
		$rangeInfo =& Handler::getRangeInfo('eventLogEntries');
		$eventLogEntries =& MonographLog::getEventLogEntries($monographId, $rangeInfo);
		$rangeInfo =& Handler::getRangeInfo('emailLogEntries');
		$emailLogEntries =& MonographLog::getEmailLogEntries($monographId, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('pageToDisplay', 'submissionHistory');
		$templateMgr->assign('isEditor', Validation::isEditor());
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('eventLogEntries', $eventLogEntries);
		$templateMgr->assign_by_ref('emailLogEntries', $emailLogEntries);

		$templateMgr->display('seriesEditor/submission.tpl');
	}

	function changeSeries() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$seriesId = Request::getUserVar('seriesId');

		SeriesEditorAction::changeSeries($submission, $seriesId);

		Request::redirect(null, null, 'submission', $monographId);
	}

	function recordDecision() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$decision = Request::getUserVar('decision');

		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
			case SUBMISSION_EDITOR_DECISION_DECLINE:
				SeriesEditorAction::recordDecision($submission, $decision);
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

		import('classes.file.MonographFileManager');
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
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$sort = Request::getUserVar('sort');
		$sort = isset($sort) ? $sort : 'name';
		$sortDirection = Request::getUserVar('sortDirection');
		$sortDirection = (isset($sortDirection) && ($sortDirection == 'ASC' || $sortDirection == 'DESC')) ? $sortDirection : 'ASC';

		$reviewerId = Request::getUserVar('reviewerId');
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');

		$reviewType = $submission->getCurrentReviewType();
		$round = $submission->getCurrentRound();

		if (isset($reviewerId)) {
			// Assign reviewer to monograph
			SeriesEditorAction::addReviewer($submission, $reviewerId, $reviewType, $round);
			Request::redirect(null, null, 'submissionReview', $monographId);

			// FIXME: Prompt for due date.
		} else {
			$this->setupTemplate(true, $monographId, 'review');

			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (!empty($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} elseif (!empty($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$rangeInfo =& Handler::getRangeInfo('reviewers');
			//$reviewers =& $seriesEditorSubmissionDao->getReviewersForMonograph($press->getId(), $monographId, $reviewType, $round, $searchType, $search, $searchMatch, $rangeInfo);
			$reviewers =& $seriesEditorSubmissionDao->getReviewersNotAssignedToMonograph($press->getId(), $monographId);

			$press = Request::getPress();
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('reviewers', $reviewers);
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('reviewerStatistics', $seriesEditorSubmissionDao->getReviewerStatistics($press->getId()));
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
			$templateMgr->assign('reviewType', $reviewType);
			$templateMgr->assign('sort', $sort);
			$templateMgr->assign('sortDirection', $sortDirection);
			$templateMgr->display('seriesEditor/selectReviewer.tpl');
		}
	}

	/**
	 * Create a new user as a reviewer.
	 */
	function createReviewer(&$args, &$request) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		import('classes.seriesEditor.form.CreateReviewerForm');
		$createReviewerForm = new CreateReviewerForm($monographId);
		$this->setupTemplate(true, $monographId);

		if (isset($args[1]) && $args[1] === 'create') {
			$createReviewerForm->readInputData();
			if ($createReviewerForm->validate()) {
				// Create a user and enroll them as a reviewer.
				$newUserId = $createReviewerForm->execute();
				Request::redirect(null, null, 'selectReviewer', array($monographId, $newUserId));
			} else {
				$createReviewerForm->display($args, $request);
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
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER)); // manager.people.enrollment, manager.people.enroll
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
		if (!empty($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} elseif (!empty($searchInitial)) {
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
		$templateMgr->display('seriesEditor/searchUsers.tpl');
	}

	function enroll($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$reviewerUserGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_REVIEWER);

		$users = Request::getUserVar('users');
		if (!is_array($users) && Request::getUserVar('userId') != null) $users = array(Request::getUserVar('userId'));

		// Enroll reviewer
		for ($i=0; $i<count($users); $i++) {
			if (!$userGroupDao->userInGroup($press->getId(), $users[$i], $reviewerUserGroup->getId())) {
				$userGroupDao->assignUserToGroup($users[$i], $reviewerUserGroup->getId());
			}
		}
		Request::redirect(null, null, 'selectReviewer', $monographId);
	}

	function notifyReviewer($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'review');

		if (SeriesEditorAction::notifyReviewer($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function clearReview($args) {
		$monographId = isset($args[0])?$args[0]:0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = $args[1];

		SeriesEditorAction::clearReview($submission, $reviewId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function cancelReview($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'review');

		if (SeriesEditorAction::cancelReview($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function remindReviewer($args = null) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');
		$this->setupTemplate(true, $monographId, 'review');

		if (SeriesEditorAction::remindReviewer($submission, $reviewId, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function thankReviewer($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'review');

		if (SeriesEditorAction::thankReviewer($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function rateReviewer() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$this->setupTemplate(true, $monographId, 'review');

		$reviewId = Request::getUserVar('reviewId');
		$quality = Request::getUserVar('quality');

		SeriesEditorAction::rateReviewer($monographId, $reviewId, $quality);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function confirmReviewForReviewer($args) {
		$monographId = (int) isset($args[0])?$args[0]:0;
		$accept = Request::getUserVar('accept')?true:false;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = (int) isset($args[1])?$args[1]:0;

		SeriesEditorAction::confirmReviewForReviewer($reviewId, $accept);
		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function uploadReviewForReviewer($args) {
		$monographId = (int) Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = (int) Request::getUserVar('reviewId');

		SeriesEditorAction::uploadReviewForReviewer($reviewId);
		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function makeReviewerFileViewable() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = Request::getUserVar('reviewId');
		$fileId = Request::getUserVar('fileId');
		$revision = Request::getUserVar('revision');
		$viewable = Request::getUserVar('viewable');

		SeriesEditorAction::makeReviewerFileViewable($monographId, $reviewId, $fileId, $revision, $viewable);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function setDueDate($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$reviewId = isset($args[1]) ? $args[1] : 0;
		$dueDate = Request::getUserVar('dueDate');
		$numWeeks = Request::getUserVar('numWeeks');

		if ($dueDate != null || $numWeeks != null) {
			SeriesEditorAction::setDueDate($monographId, $reviewId, $dueDate, $numWeeks);
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

			$templateMgr->display('seriesEditor/setDueDate.tpl');
		}
	}

	function enterReviewerRecommendation($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;;

		$reviewId = Request::getUserVar('reviewId');

		$recommendation = Request::getUserVar('recommendation');

		if ($recommendation != null) {
			SeriesEditorAction::setReviewerRecommendation($monographId, $reviewId, $recommendation, SUBMISSION_REVIEWER_RECOMMENDATION_ACCEPT);
			Request::redirect(null, null, 'submissionReview', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'review');

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('reviewId', $reviewId);

			import('classes.submission.reviewAssignment.ReviewAssignment');
			$templateMgr->assign_by_ref('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());

			$templateMgr->display('seriesEditor/reviewerRecommendation.tpl');
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
			$templateMgr->display('seriesEditor/userProfile.tpl');
		}
	}

	function viewMetadata($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'summary');

		SeriesEditorAction::viewMetadata($submission);
	}

	function saveMetadata() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'summary');

		if (SeriesEditorAction::saveMetadata($submission)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
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
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_PRESS, $press->getId());
		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewFormId);
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageTitle', 'manager.reviewForms.preview');
		$templateMgr->assign_by_ref('reviewForm', $reviewForm);
		$templateMgr->assign('reviewFormElements', $reviewFormElements);
		$templateMgr->assign('reviewId', $reviewId);
		$templateMgr->assign('monographId', $reviewAssignment->getSubmissionId());
		//$templateMgr->assign('helpTopicId','press.managementPages.reviewForms');
		$templateMgr->display('seriesEditor/previewReviewForm.tpl');
	}

	/**
	 * Clear a review form, i.e. remove review form assignment to the review.
	 * @param $args array ($monographId, $reviewId)
	 */
	function clearReviewForm($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$reviewId = isset($args[1]) ? (int) $args[1] : null;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		SeriesEditorAction::clearReviewForm($submission, $reviewId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	/**
	 * Select a review form
	 * @param $args array ($monographId, $reviewId, $reviewFormId)
	 */
	function selectReviewForm($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = isset($args[1]) ? (int) $args[1] : null;
		$reviewFormId = isset($args[2]) ? (int) $args[2] : null;

		if ($reviewFormId != null) {
			SeriesEditorAction::addReviewForm($submission, $reviewId, $reviewFormId);
			Request::redirect(null, null, 'submissionReview', $monographId);
		} else {
			$press =& Request::getPress();
			$rangeInfo =& Handler::getRangeInfo('reviewForms');
			$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
			$reviewForms =& $reviewFormDao->getActiveByAssocId(ASSOC_TYPE_PRESS, $press->getId(), $rangeInfo);
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

			$this->setupTemplate(true, $monographId, 'review');
			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign('reviewId', $reviewId);
			$templateMgr->assign('assignedReviewFormId', $reviewAssignment->getReviewFormId());
			$templateMgr->assign_by_ref('reviewForms', $reviewForms);
			$templateMgr->assign('helpTopicId','press.managementPages.reviewForms');
			$templateMgr->display('seriesEditor/selectReviewForm.tpl');
		}
	}

	/**
	 * View review form response.
	 * @param $args array ($monographId, $reviewId)
	 */
	function viewReviewFormResponse($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$reviewId = isset($args[1]) ? (int) $args[1] : null;

		SeriesEditorAction::viewReviewFormResponse($submission, $reviewId);
	}

	//
	// Editor Review
	//

	function editorReview() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$redirectTarget = 'submissionReview';

		// If the Upload button was pressed.
		$submit = Request::getUserVar('submit');
		if ($submit != null) {
			SeriesEditorAction::uploadEditorVersion($submission);
		}

		if (Request::getUserVar('setCopyeditFile')) {
			// If the Send To Copyedit button was pressed
			$file = explode(',', Request::getUserVar('editorDecisionFile'));
			if (isset($file[0]) && isset($file[1])) {
				if ($submission->getMostRecentEditorDecisionComment()) {
					// The conditions are met for being able
					// to send a file to copyediting.
					SeriesEditorAction::setCopyeditFile($submission, $file[0], $file[1]);

					$signoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_PRESS, $submission->getId());
					$signoff->setFileId($file[0]);
					$signoff->setFileRevision($file[1]);
					$signoffDao->updateObject($signoff);
				}
				$redirectTarget = 'submissionEditing';
			}
		} else if (Request::getUserVar('resubmit')) {
			// If the Resubmit button was pressed
			$file = explode(',', Request::getUserVar('editorDecisionFile'));
			if (isset($file[0]) && isset($file[1])) {
				SeriesEditorAction::resubmitFile($submission, $file[0], $file[1]);

				$signoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $submission->getId());
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
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if (isset($args[1]) && $args[1] != null && $roleDao->userHasRole($press->getId(), $args[1], ROLE_ID_COPYEDITOR)) {
			SeriesEditorAction::selectCopyeditor($submission, $args[1]);
			Request::redirect(null, null, 'submissionEditing', $monographId);
		} else {
			$this->setupTemplate(true, $monographId, 'editing');

			$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');

			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (!empty($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} elseif (!empty($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$copyeditors = $roleDao->getUsersByRoleId(ROLE_ID_COPYEDITOR, $press->getId(), $searchType, $search, $searchMatch);
			$copyeditorStatistics = $seriesEditorSubmissionDao->getCopyeditorStatistics($press->getId());

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
			$templateMgr->display('seriesEditor/selectUser.tpl');
		}
	}

	function notifyCopyeditor($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send') ? true : false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (SeriesEditorAction::notifyCopyeditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/* Initiates the copyediting process when the editor does the copyediting */
	function initiateCopyedit() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		SeriesEditorAction::initiateCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function thankCopyeditor($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (SeriesEditorAction::thankCopyeditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function notifyAuthorCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (SeriesEditorAction::notifyAuthorCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function thankAuthorCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (SeriesEditorAction::thankAuthorCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function notifyFinalCopyedit($args = array()) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (SeriesEditorAction::notifyFinalCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function completeCopyedit($args) {
		$monographId = (int) Request::getUserVar('monographId');

		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		SeriesEditorAction::completeCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function completeFinalCopyedit($args) {
		$monographId = (int) Request::getUserVar('monographId');

		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		SeriesEditorAction::completeFinalCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function thankFinalCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (SeriesEditorAction::thankFinalCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function uploadReviewVersion() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;

		SeriesEditorAction::uploadReviewVersion($submission);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function uploadCopyeditVersion() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;


		$copyeditStage = Request::getUserVar('copyeditStage');
		SeriesEditorAction::uploadCopyeditVersion($submission, $copyeditStage);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Delete an editor version file.
	 * @param $args array ($monographId, $fileId)
	 */
	function deleteMonographFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$fileId = isset($args[1]) ? (int) $args[1] : 0;
		$revisionId = isset($args[2]) ? (int) $args[2] : 0;

		$this->validate($monographId, SERIES_EDITOR_ACCESS_REVIEW);
		$submission =& $this->submission;
		SeriesEditorAction::deleteMonographFile($submission, $fileId, $revisionId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function archiveSubmission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;

		SeriesEditorAction::archiveSubmission($submission);

		Request::redirect(null, null, 'submission', $monographId);
	}

	function restoreToQueue($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;

		SeriesEditorAction::restoreToQueue($submission);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function unsuitableSubmission($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;

		$send = Request::getUserVar('send')?true:false;
		$this->setupTemplate(true, $monographId, 'summary');

		if (SeriesEditorAction::unsuitableSubmission($submission, $send)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	/**
	 * Set series ID.
	 * @param $args array ($monographId)
	 */
	function updateSeries($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		SeriesEditorAction::updateSeries($submission, Request::getUserVar('series'));
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
		SeriesEditorAction::updateCommentsStatus($submission, Request::getUserVar('commentsStatus'));
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

		} else {
			Request::redirect(null, null, $this->getFrom(), Request::getUserVar('monographId'));
		}
	}

	/**
	 * Upload the layout version of the submission file
	 */
	function uploadLayoutVersion() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		SeriesEditorAction::uploadLayoutVersion($submission);

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

		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;
		SeriesEditorAction::deleteMonographImage($submission, $fileId, $revisionId);

		Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
	}

	/**
	 * Assign/reassign a production editor to the submission.
	 * @param $args array ($monographId, [$userId])
	 */
	function assignProductionEditor($args, $op = null) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$editorId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		if ($editorId && $roleDao->userHasRole($press->getId(), $editorId, ROLE_ID_PRODUCTION_EDITOR)) {
			SeriesEditorAction::assignProductionEditor($submission, $editorId);
			if ($op == null)
				$op = 'submissionProduction';
			Request::redirect(null, null, $op, $monographId);
		} else {
			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (!empty($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} elseif (!empty($searchInitial)) {
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
			$templateMgr->display('seriesEditor/selectUser.tpl');
		}
	}

	/**
	 * Notify the layout editor.
	 */
	function notifyLayoutDesigner($args) {
		$monographId = Request::getUserVar('monographId');
		$layoutAssignmentId = Request::getUserVar('layoutAssignmentId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$send = Request::getUserVar('send') ? true : false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (SeriesEditorAction::notifyLayoutDesigner($submission, $layoutAssignmentId, $send)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
		}
	}

	/**
	 * Thank the layout editor.
	 */
	function thankLayoutEditor($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$send = Request::getUserVar('send') ? true : false;
		$this->setupTemplate(true, $monographId, 'editing');

		if (SeriesEditorAction::thankLayoutEditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Create a new galley with the uploaded file.
	 */
	function uploadGalley($fileName = null) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		import('classes.submission.form.MonographGalleyForm');

		$galleyForm = new MonographGalleyForm($monographId);
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
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		$this->setupTemplate(true, $monographId, 'editing');

		import('classes.submission.form.MonographGalleyForm');

		$submitForm = new MonographGalleyForm($monographId, $galleyId);

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
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$this->setupTemplate(true, $monographId, 'editing');
		$press =& Request::getPress();
		$submission =& $this->submission;

		import('classes.submission.form.MonographGalleyForm');

		$submitForm = new MonographGalleyForm($monographId, $galleyId);

		$submitForm->readInputData();
		if ($submitForm->validate()) {
			$submitForm->execute();

			// Send a notification to associated users
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager =& new NotificationManager();
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getMonograph($monographId);
			$notificationUsers = $monograph->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $userRole) {
				$url = Request::url(null, $userRole['role'], 'submissionEditing', $monograph->getId(), null, 'layout');
				$notificationManager->createNotification(
					$userRole['id'], 'notification.type.galleyModified',
					$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_GALLEY_MODIFIED
				);
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
			$submitForm->display();
		}
	}

	/**
	 * Change the sequence order of a galley.
	 */
	function orderGalley() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$press =& Request::getPress();
		$submission =& $this->submission;

		SeriesEditorAction::orderGalley($submission, Request::getUserVar('galleyId'), Request::getUserVar('d'));

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Delete a galley file.
	 * @param $args array ($monographId, $galleyId)
	 */
	function deleteGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		SeriesEditorAction::deleteGalley($submission, $galleyId);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Proof / "preview" a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
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
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
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
		$this->validate($monographId, SERIES_EDITOR_ACCESS_EDIT);
		$submission =& $this->submission;

		$galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$galley =& $galleyDao->getGalley($galleyId, $monographId);

		import('classes.file.MonographFileManager'); // FIXME

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
			$templateMgr->display('seriesEditor/submissionEventLogEntry.tpl');

		} else {
			$rangeInfo =& Handler::getRangeInfo('eventLogEntries');

			import('classes.monograph.log.MonographLog');
			$eventLogEntries =& MonographLog::getEventLogEntries($monographId, $rangeInfo);
			$templateMgr->assign('eventLogEntries', $eventLogEntries);
			$templateMgr->display('seriesEditor/submissionEventLog.tpl');
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
		$templateMgr->display('seriesEditor/submissionEventLog.tpl');
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
		import('classes.file.MonographFileManager');
		$templateMgr->assign('attachments', $monographFileDao->getMonographFilesByAssocId($logId, MONOGRAPH_FILE_ATTACHMENT));

		if ($logId) {
			$logDao =& DAORegistry::getDAO('MonographEmailLogDAO');
			$logEntry =& $logDao->getLogEntry($logId, $monographId);
		}

		if (isset($logEntry)) {
			$templateMgr->assign_by_ref('logEntry', $logEntry);
			$templateMgr->display('seriesEditor/submissionEmailLogEntry.tpl');

		} else {
			$rangeInfo =& Handler::getRangeInfo('emailLogEntries');

			import('classes.monograph.log.MonographLog');
			$emailLogEntries =& MonographLog::getEmailLogEntries($monographId, $rangeInfo);
			$templateMgr->assign_by_ref('emailLogEntries', $emailLogEntries);
			$templateMgr->display('seriesEditor/submissionEmailLog.tpl');
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
		$templateMgr->display('seriesEditor/submissionEmailLog.tpl');
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
		if (!SeriesEditorAction::downloadFile($monographId, $fileId, $revision)) {
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
		if (!SeriesEditorAction::viewFile($monographId, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $monographId);
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
	 * Validate that the user is the assigned series editor for
	 * the monograph, or is a managing editor.
	 * Redirects to seriesEditor index page if validation fails.
	 * @param $monographId int Monograph ID to validate
	 * @param $access int Optional name of access level required -- see SERIES_EDITOR_ACCESS_... constants
	 */
	function validate($monographId, $access = null) {
		parent::validate();

		$isValid = true;

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($monographId);

		if ($seriesEditorSubmission == null) {
			$isValid = false;

		} else if ($seriesEditorSubmission->getPressId() != $press->getId()) {
			$isValid = false;

		} else if ($seriesEditorSubmission->getDateSubmitted() == null) {
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
				$editAssignments =& $seriesEditorSubmission->getEditAssignments();
				$wasFound = false;
				foreach ($editAssignments as $editAssignment) {
					if ($editAssignment->getEditorId() == $user->getId()) {
						$templateMgr->assign('canReview', $editAssignment->getCanReview());
						$templateMgr->assign('canEdit', $editAssignment->getCanEdit());
						switch ($access) {
							case SERIES_EDITOR_ACCESS_EDIT:
								if ($editAssignment->getCanEdit()) {
									$wasFound = true;
								}
								break;
							case SERIES_EDITOR_ACCESS_REVIEW:
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
		$editAssignments =& $seriesEditorSubmission->getEditAssignments();
		foreach ($editAssignments as $editAssignment) {
			if ($editAssignment->getEditorId() == $user->getId() && $editAssignment->getDateUnderway() === null) {
				$editAssignment->setDateUnderway(Core::getCurrentDate());
				$editAssignmentDao->updateEditAssignment($editAssignment);
			}
		}

		$this->submission =& $seriesEditorSubmission;
		return true;
	}

}
?>
