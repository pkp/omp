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


define('SECTION_EDITOR_ACCESS_EDIT', 0x00001);
define('SECTION_EDITOR_ACCESS_REVIEW', 0x00002);

import('pages.acquisitionsEditor.AcquisitionsEditorHandler');

class SubmissionEditHandler extends AcquisitionsEditorHandler {
	function getFrom($default = 'submissionEditing') {
		$from = Request::getUserVar('from');
		if (!in_array($from, array('submission', 'submissionEditing'))) return $default;
		return $from;
	}
	function submission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_READER, LOCALE_COMPONENT_OMP_AUTHOR));
		parent::setupTemplate(true, $monographId);

		$user =& Request::getUser();

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettings = $pressSettingsDao->getPressSettings($press->getId());

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->roleExists($press->getId(), $user->getUserId(), ROLE_ID_EDITOR);

		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
		$arrangement =& $arrangementDao->getAcquisitionsArrangement($submission->getAcquisitionsArrangementId());

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('arrangement', $arrangement);
		$templateMgr->assign_by_ref('authors', $submission->getAuthors());
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getUserId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

		$templateMgr->assign_by_ref('arrangements', $arrangementDao->getAcquisitionsArrangementsTitles($press->getId()));

		if ($enableComments) {
			import('monograph.Monograph');
			$templateMgr->assign('commentsStatus', $submission->getCommentsStatus());
			$templateMgr->assign_by_ref('commentsStatusOptions', Monograph::getCommentsStatusOptions());
		}

/*		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getPublishedMonographByMonographId($submission->getMonographId());
		if ($publishedMonograph) {
			$issueDao =& DAORegistry::getDAO('IssueDAO');
			$issue =& $issueDao->getIssueById($publishedMonograph->getIssueId());
			$templateMgr->assign_by_ref('issue', $issue);
			$templateMgr->assign_by_ref('publishedMonograph', $publishedMonograph);
		}
*/
		if ($isEditor) {
			$templateMgr->assign('helpTopicId', 'editorial.editorsRole.submissionSummary');
		}
		
		// Set up required Payment Related Information
/*		import('payment.ojs.OJSPaymentManager');
		$paymentManager =& OJSPaymentManager::getManager();
		if ( $paymentManager->submissionEnabled() || $paymentManager->fastTrackEnabled() || $paymentManager->publicationEnabled()) {
			$templateMgr->assign('authorFees', true);
			$completedPaymentDAO =& DAORegistry::getDAO('OJSCompletedPaymentDAO');
			
			if ( $paymentManager->submissionEnabled() ) {
				$templateMgr->assign_by_ref('submissionPayment', $completedPaymentDAO->getSubmissionCompletedPayment ( $press->getId(), $monographId ));
			}
			
			if ( $paymentManager->fastTrackEnabled()  ) {
				$templateMgr->assign_by_ref('fastTrackPayment', $completedPaymentDAO->getFastTrackCompletedPayment ( $press->getId(), $monographId ));
			}

			if ( $paymentManager->publicationEnabled()  ) {
				$templateMgr->assign_by_ref('publicationPayment', $completedPaymentDAO->getPublicationCompletedPayment ( $press->getId(), $monographId ));
			}				   
		}		
*/
		$templateMgr->display('acquisitionsEditor/submission.tpl');
	}

	function endWorkflowProcess($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$processId = isset($args[1]) ? (int) $args[1] : 0;//fixme: validate
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		$process =& Action::endSignoffProcess($monographId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}
	function submissionRegrets($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		parent::setupTemplate(true, $monographId, 'review');

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

		$templateMgr->assign_by_ref('editorDecisionOptions', SectionEditorSubmission::getEditorDecisionOptions());

		import('submission.reviewAssignment.ReviewAssignment');
		$templateMgr->assign_by_ref('reviewerRatingOptions', ReviewAssignment::getReviewerRatingOptions());
		$templateMgr->assign_by_ref('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());

		$templateMgr->display('acquisitionsEditor/submissionRegrets.tpl');
	}

	function submissionReview($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);
		parent::setupTemplate(true, $monographId);

		$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');

		// Setting the round.
		$round = $submission->getCurrentReviewRound();

//		$sectionDao =& DAORegistry::getDAO('SectionDAO');
//		$sections =& $sectionDao->getPressSections($press->getId());

//		$showPeerReviewOptions = $round == $submission->getCurrentRound() && $submission->getReviewFile() != null ? true : false;

		$editorDecisions = $submission->getDecisions($submission->getCurrentReviewType(), $submission->getCurrentReviewRound());
		if (!is_array($editorDecisions)) {
			$editorDecisions = array($editorDecisions);
		}
		$lastDecision = count($editorDecisions) >= 1 ? $editorDecisions[count($editorDecisions) - 1]['decision'] : null;

		$editAssignments =& $submission->getEditAssignments();
		$allowRecommendation = 1;//$submission->getCurrentRound() == $round && $submission->getReviewFileId() != null && !empty($editAssignments);
		$allowResubmit = $lastDecision == SUBMISSION_EDITOR_DECISION_RESUBMIT && $acquisitionsEditorSubmissionDao->getMaxReviewRound($monographId) == $round ? true : false;
	//	$allowCopyedit = $lastDecision == SUBMISSION_EDITOR_DECISION_ACCEPT && $submission->getCopyeditFileId() == null ? true : false;

		// Prepare an array to store the 'Notify Reviewer' email logs
		$notifyReviewerLogs = array();
//		foreach ($submission->getReviewAssignments($round) as $reviewAssignment) {
//			$notifyReviewerLogs[$reviewAssignment->getReviewId()] = array();
//		}


		// Parse the list of email logs and populate the array.
/*		import('monograph.log.MonographLog');
		$emailLogEntries =& MonographLog::getEmailLogEntries($monographId);
		foreach ($emailLogEntries->toArray() as $emailLog) {
			if ($emailLog->getEventType() == ARTICLE_EMAIL_REVIEW_NOTIFY_REVIEWER) {
				if (isset($notifyReviewerLogs[$emailLog->getAssocId()]) && is_array($notifyReviewerLogs[$emailLog->getAssocId()])) {
					array_push($notifyReviewerLogs[$emailLog->getAssocId()], $emailLog);
				}
			}
		}
*/
		// get press published review form titles
		$reviewFormTitles =& $reviewFormDao->getTitlesByPressId($press->getId(), 1);

		$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewFormResponses = array();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewFormTitles = array();

		if (is_array($submission->getReviewAssignments($round)) && false)
		foreach ($submission->getReviewAssignments($round) as $reviewAssignment) {
			$reviewForm =& $reviewFormDao->getById($reviewAssignment->getReviewFormId());
			if ($reviewForm) {
				$reviewFormTitles[$reviewForm->getReviewFormId()] = $reviewForm->getReviewFormTitle();
			}
			unset($reviewForm);
			$reviewFormResponses[$reviewAssignment->getReviewId()] = $reviewFormResponseDao->reviewFormResponseExists($reviewAssignment->getReviewId());
		}
		
$reviewFormResponses = null;
$reviewFormTitles = null;
$sections = null;

		$templateMgr =& TemplateManager::getManager();

		$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
		$reviewProcesses =& $workflowDao->getByEventType($monographId, WORKFLOW_PROCESS_ASSESSMENT);

		$reviewAssignments =& $submission->getReviewAssignments();

		$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
		$process =& $workflowDao->getCurrent($monographId, WORKFLOW_PROCESS_ASSESSMENT);

		$reviewAssignments = isset($process) && isset($reviewAssignments[$process->getProcessId()]) ? $reviewAssignments[$process->getProcessId()] : null;

		$processId = isset($process) ? $process->getProcessId() : null;

		$templateMgr->assign('signoffWait', 0);
		$templateMgr->assign('signoffQueue', 0);
		$templateMgr->assign_by_ref('reviewType', $processId);
		$templateMgr->assign_by_ref('editorDecisions', array_reverse($editorDecisions));
		$templateMgr->assign_by_ref('reviewProcesses', $reviewProcesses);
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('reviewIndexes', $reviewAssignmentDao->getReviewIndexesForRound($monographId, $round));
		$templateMgr->assign('round', $round);
		$templateMgr->assign_by_ref('reviewAssignments', $reviewAssignments);
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
//		$templateMgr->assign('showPeerReviewOptions', $showPeerReviewOptions);
//		$templateMgr->assign_by_ref('sections', $sections->toArray());
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
		//$templateMgr->assign('allowCopyedit', $allowCopyedit);
		$templateMgr->assign('helpTopicId', 'editorial.acquisitionsEditorsRole.review');
		$templateMgr->display('acquisitionsEditor/submissionReview.tpl');
	}

	function submissionEditing($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		parent::setupTemplate(true, $monographId);

		$useCopyeditors = $press->getSetting('useCopyeditors');
		$useLayoutEditors = $press->getSetting('useLayoutEditors');
		$useProofreaders = $press->getSetting('useProofreaders');

		// check if submission is accepted
//		$round = isset($args[1]) ? $args[1] : $submission->getCurrentReviewRound();
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
		$templateMgr->assign_by_ref('copyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('editorAuthorCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR'));
		$templateMgr->assign_by_ref('finalCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL'));
		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
		$templateMgr->assign_by_ref('copyeditor', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user =& Request::getUser();
		$templateMgr->assign('isEditor', $roleDao->roleExists($press->getId(), $user->getUserId(), ROLE_ID_EDITOR));

/*		import('issue.IssueAction');
		$templateMgr->assign('issueOptions', IssueAction::getIssueOptions());
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getPublishedMonographByMonographId($submission->getMonographId());
		$templateMgr->assign_by_ref('publishedMonograph', $publishedMonograph);
*/
		$templateMgr->assign('useCopyeditors', true);
		$templateMgr->assign('useLayoutEditors', $useLayoutEditors);
		$templateMgr->assign('useProofreaders', $useProofreaders);
//		$templateMgr->assign_by_ref('proofAssignment', $submission->getProofAssignment());
//		$templateMgr->assign_by_ref('layoutAssignment', $submission->getLayoutAssignment());
		$templateMgr->assign('submissionAccepted', $submissionAccepted);

		// Set up required Payment Related Information
/*		import('payment.ojs.OJSPaymentManager');
		$paymentManager =& OJSPaymentManager::getManager();
		$completedPaymentDAO =& DAORegistry::getDAO('OJSCompletedPaymentDAO');
		
		$publicationFeeEnabled = $paymentManager->publicationEnabled();
		$templateMgr->assign('publicatonFeeEnabled',  $publicationFeeEnabled);
		if ( $publicationFeeEnabled ) {
			$templateMgr->assign_by_ref('publicationPayment', $completedPaymentDAO->getPublicationCompletedPayment ( $press->getId(), $monographId ));			   
		}	
*/
		$templateMgr->assign('helpTopicId', 'editorial.acquisitionsEditorsRole.editing');
		$templateMgr->display('acquisitionsEditor/submissionEditing.tpl');
	}

	function submissionProduction($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		parent::setupTemplate(true, $monographId);

		$useCopyeditors = $press->getSetting('useCopyeditors');
		$useLayoutEditors = $press->getSetting('useLayoutEditors');
		$useProofreaders = $press->getSetting('useProofreaders');

		// check if submission is accepted
//		$round = isset($args[1]) ? $args[1] : $submission->getCurrentReviewRound();
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
		$templateMgr->assign_by_ref('finalCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL'));
		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
		$templateMgr->assign_by_ref('copyeditor', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user =& Request::getUser();
		$templateMgr->assign('isEditor', $roleDao->roleExists($press->getId(), $user->getUserId(), ROLE_ID_EDITOR));

/*		import('issue.IssueAction');
		$templateMgr->assign('issueOptions', IssueAction::getIssueOptions());
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getPublishedMonographByMonographId($submission->getMonographId());
		$templateMgr->assign_by_ref('publishedMonograph', $publishedMonograph);
*/
		$templateMgr->assign('useCopyeditors', true);
		$templateMgr->assign('useLayoutEditors', $useLayoutEditors);
		$templateMgr->assign('useProofreaders', $useProofreaders);
//		$templateMgr->assign_by_ref('proofAssignment', $submission->getProofAssignment());
//		$templateMgr->assign_by_ref('layoutAssignment', $submission->getLayoutAssignment());
		$templateMgr->assign('submissionAccepted', $submissionAccepted);

		// Set up required Payment Related Information
/*		import('payment.ojs.OJSPaymentManager');
		$paymentManager =& OJSPaymentManager::getManager();
		$completedPaymentDAO =& DAORegistry::getDAO('OJSCompletedPaymentDAO');
		
		$publicationFeeEnabled = $paymentManager->publicationEnabled();
		$templateMgr->assign('publicatonFeeEnabled',  $publicationFeeEnabled);
		if ( $publicationFeeEnabled ) {
			$templateMgr->assign_by_ref('publicationPayment', $completedPaymentDAO->getPublicationCompletedPayment ( $press->getId(), $monographId ));			   
		}	
*/
		$templateMgr->assign('helpTopicId', 'editorial.acquisitionsEditorsRole.editing');
		$templateMgr->display('acquisitionsEditor/submissionProduction.tpl');
	}

	/**
	 * View submission history
	 */
	function submissionHistory($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		parent::setupTemplate(true, $monographId);

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

	function changeSection() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		$sectionId = Request::getUserVar('sectionId');

		AcquisitionsEditorAction::changeSection($submission, $sectionId);

		Request::redirect(null, null, 'submission', $monographId);
	}

	function recordDecision() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

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

	//
	// Peer Review
	//
	function selectReviewer($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);
		$reviewerId = Request::getUserVar('reviewerId');
		$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');

		$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
		$currentProcess =& $workflowDao->getCurrent($monographId);

		$reviewType = $currentProcess->getProcessId();

		$submission->setCurrentReviewType($reviewType);

		$round = $submission->getCurrentReviewRound();

		if (isset($reviewerId)) {
			// Assign reviewer to monograph
			AcquisitionsEditorAction::addReviewer($submission, $reviewerId, $reviewType);
			Request::redirect(null, null, 'submissionReview', $monographId);

			// FIXME: Prompt for due date.
		} else {
			parent::setupTemplate(true, $monographId, 'review');

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

			$templateMgr->display('acquisitionsEditor/selectReviewer.tpl');
		}
	}

	/**
	 * Create a new user as a reviewer.
	 */
	function createReviewer($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		import('acquisitionsEditor.form.CreateReviewerForm');
		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$createReviewerForm =& new CreateReviewerForm($monographId);
		parent::setupTemplate(true, $monographId);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleId = $roleDao->getRoleIdFromPath('reviewer');

		$user =& Request::getUser();

		$rangeInfo = Handler::getRangeInfo('users');
		$templateMgr =& TemplateManager::getManager();
		parent::setupTemplate(true);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'review');

		if (AcquisitionsEditorAction::notifyReviewer($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function clearReview($args) {
		$monographId = isset($args[0])?$args[0]:0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = $args[1];

		AcquisitionsEditorAction::clearReview($submission, $reviewId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function cancelReview($args) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'review');

		if (AcquisitionsEditorAction::cancelReview($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function remindReviewer($args = null) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = Request::getUserVar('reviewId');
		parent::setupTemplate(true, $monographId, 'review');

		if (AcquisitionsEditorAction::remindReviewer($submission, $reviewId, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function thankReviewer($args = array()) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = Request::getUserVar('reviewId');

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'review');

		if (AcquisitionsEditorAction::thankReviewer($submission, $reviewId, $send)) {
			Request::redirect(null, null, 'submissionReview', $monographId);
		}
	}

	function rateReviewer() {
		$monographId = Request::getUserVar('monographId');
		SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);
		parent::setupTemplate(true, $monographId, 'review');

		$reviewId = Request::getUserVar('reviewId');
		$quality = Request::getUserVar('quality');

		AcquisitionsEditorAction::rateReviewer($monographId, $reviewId, $quality);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function confirmReviewForReviewer($args) {
		$monographId = (int) isset($args[0])?$args[0]:0;
		$accept = Request::getUserVar('accept')?true:false;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = (int) isset($args[1])?$args[1]:0;

		AcquisitionsEditorAction::confirmReviewForReviewer($reviewId, $accept);
		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function uploadReviewForReviewer($args) {
		$monographId = (int) Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = (int) Request::getUserVar('reviewId');

		AcquisitionsEditorAction::uploadReviewForReviewer($reviewId);
		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function makeReviewerFileViewable() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = Request::getUserVar('reviewId');
		$fileId = Request::getUserVar('fileId');
		$revision = Request::getUserVar('revision');
		$viewable = Request::getUserVar('viewable');

		AcquisitionsEditorAction::makeReviewerFileViewable($monographId, $reviewId, $fileId, $revision, $viewable);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function setDueDate($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = isset($args[1]) ? $args[1] : 0;
		$dueDate = Request::getUserVar('dueDate');
		$numWeeks = Request::getUserVar('numWeeks');

		if ($dueDate != null || $numWeeks != null) {
			AcquisitionsEditorAction::setDueDate($monographId, $reviewId, $dueDate, $numWeeks);
			Request::redirect(null, null, 'submissionReview', $monographId);

		} else {
			parent::setupTemplate(true, $monographId, 'review');
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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = Request::getUserVar('reviewId');

		$recommendation = Request::getUserVar('recommendation');

		if ($recommendation != null) {
			AcquisitionsEditorAction::setReviewerRecommendation($monographId, $reviewId, $recommendation, SUBMISSION_REVIEWER_RECOMMENDATION_ACCEPT);
			Request::redirect(null, null, 'submissionReview', $monographId);
		} else {
			parent::setupTemplate(true, $monographId, 'review');

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
		parent::setupTemplate(true);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_AUTHOR));
		parent::setupTemplate(true, $monographId, 'summary');

		AcquisitionsEditorAction::viewMetadata($submission, ROLE_ID_SECTION_EDITOR);
	}

	function saveMetadata() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		parent::setupTemplate(true, $monographId, 'summary');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

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
		parent::setupTemplate(true);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);
		
		AcquisitionsEditorAction::clearReviewForm($submission, $reviewId);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}
	
	/**
	 * Select a review form
	 * @param $args array ($monographId, $reviewId, $reviewFormId)
	 */
	function selectReviewForm($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);
		
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

			parent::setupTemplate(true, $monographId, 'review');
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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$reviewId = isset($args[1]) ? (int) $args[1] : null;

		AcquisitionsEditorAction::viewReviewFormResponse($submission, $reviewId);	
	}
	
	//
	// Editor Review
	//

	function editorReview() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		$redirectTarget = 'submissionReview';

		// If the Upload button was pressed.
		$submit = Request::getUserVar('submit');
		if ($submit != null) {
			AcquisitionsEditorAction::uploadEditorVersion($submission);
		}		
//advance workflow


		if (Request::getUserVar('setAcceptedFile')) {
			// If the Accept button was pressed
			$file = explode(',', Request::getUserVar('editorDecisionFile'));

			if (isset($file[0]) && isset($file[1])) {
				$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
				$currentProcess =& $workflowDao->getCurrent($monographId);
				$nextProcess =& $workflowDao->getNext($currentProcess);
				$nextProcessType = isset($nextProcess[1]) ? $nextProcess[1] : null; 

				switch ($nextProcess[0]) {
				case WORKFLOW_PROCESS_ASSESSMENT:
					$submission->setReviewFileId($file[0]);
					$submission->setReviewRevision($file[1]);
					$submission->setCurrentReviewType($nextProcessType);
					$submission->setCurrentReviewRound(1);
					$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
					$acquisitionsEditorSubmissionDao->updateAcquisitionsEditorSubmission($submission);
					break;
				case WORKFLOW_PROCESS_EDITING:
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

				$workflowDao->proceed($monographId);

			}

		} else if (Request::getUserVar('resubmit')) {
			// If the Resubmit button was pressed
			$file = explode(',', Request::getUserVar('editorDecisionFile'));
			if (isset($file[0]) && isset($file[1])) {
				AcquisitionsEditorAction::resubmitFile($submission, $file[0], $file[1]);
			}
		}

		Request::redirect(null, null, $redirectTarget, $monographId);
	}

	//
	// Copyedit
	//

	function selectCopyeditor($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if (isset($args[1]) && $args[1] != null && $roleDao->roleExists($press->getId(), $args[1], ROLE_ID_COPYEDITOR)) {
			AcquisitionsEditorAction::selectCopyeditor($submission, $args[1]);
			Request::redirect(null, null, 'submissionEditing', $monographId);
		} else {
			parent::setupTemplate(true, $monographId, 'editing');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$send = Request::getUserVar('send') ? true : false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::notifyCopyeditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/* Initiates the copyediting process when the editor does the copyediting */
	function initiateCopyedit() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		AcquisitionsEditorAction::initiateCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function thankCopyeditor($args = array()) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankCopyeditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function notifyAuthorCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::notifyAuthorCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function thankAuthorCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankAuthorCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function notifyFinalCopyedit($args = array()) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::notifyFinalCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function completeCopyedit($args) {
		$monographId = (int) Request::getUserVar('monographId');

		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		AcquisitionsEditorAction::completeCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function completeFinalCopyedit($args) {
		$monographId = (int) Request::getUserVar('monographId');

		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		AcquisitionsEditorAction::completeFinalCopyedit($submission);
		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function thankFinalCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankFinalCopyedit($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	function uploadReviewVersion() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);

		AcquisitionsEditorAction::uploadReviewVersion($submission);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function uploadCopyeditVersion() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR));
		parent::setupTemplate(true, $monographId, 'summary');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR));
		parent::setupTemplate(true, $monographId, 'summary');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		$suppFileId = isset($args[0]) ? (int) $args[0] : 0;

		import('submission.form.SuppFileForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new SuppFileForm($submission, $suppFileId);
		$submitForm->readInputData();

		if ($submitForm->validate()) {
			$submitForm->execute();
			Request::redirect(null, null, SubmissionEditHandler::getFrom(), $monographId);
		} else {
			parent::setupTemplate(true, $monographId, 'summary');
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

		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_REVIEW);
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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		AcquisitionsEditorAction::deleteSuppFile($submission, $suppFileId);

		Request::redirect(null, null, SubmissionEditHandler::getFrom(), $monographId);
	}

	function archiveSubmission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		AcquisitionsEditorAction::archiveSubmission($submission);

		Request::redirect(null, null, 'submission', $monographId);
	}

	function restoreToQueue($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		AcquisitionsEditorAction::restoreToQueue($submission);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function unsuitableSubmission($args) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		$send = Request::getUserVar('send')?true:false;
		parent::setupTemplate(true, $monographId, 'summary');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		AcquisitionsEditorAction::updateAcquisitionsArrangement($submission, Request::getUserVar('arrangement'));
		Request::redirect(null, null, 'submission', $monographId);
	}

	/**
	 * Set RT comments status for monograph.
	 * @param $args array ($monographId)
	 */
	function updateCommentsStatus($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId);		
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
			SubmissionEditHandler::uploadLayoutVersion();

		} else if ($layoutFileType == 'galley') {
			SubmissionEditHandler::uploadGalley('layoutFile');

		} else if ($layoutFileType == 'supp') {
			SubmissionEditHandler::uploadSuppFile('layoutFile');

		} else {
			Request::redirect(null, null, SubmissionEditHandler::getFrom(), Request::getUserVar('monographId'));
		}
	}

	/**
	 * Upload the layout version of the submission file
	 */
	function uploadLayoutVersion() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		AcquisitionsEditorAction::uploadLayoutVersion($submission);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Delete an monograph image.
	 * @param $args array ($monographId, $fileId)
	 */
	function deleteMonographImage($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$fileId = isset($args[2]) ? (int) $args[2] : 0;
		$revisionId = isset($args[3]) ? (int) $args[3] : 0;

		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		AcquisitionsEditorAction::deleteMonographImage($submission, $fileId, $revisionId);

		Request::redirect(null, null, 'editGalley', array($monographId, $galleyId));
	}

	/**
	 * Assign/reassign a layout editor to the submission.
	 * @param $args array ($monographId, [$userId])
	 */
	function assignLayoutEditor($args, $op = null) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$editorId = isset($args[1]) ? (int) $args[1] : 0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if ($editorId && $roleDao->roleExists($press->getId(), $editorId, ROLE_ID_DESIGNER)) {
			AcquisitionsEditorAction::assignLayoutEditor($submission, $editorId);
			if ($op == null)
				$op = 'submissionEditing';
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

			$layoutEditors = $roleDao->getUsersByRoleId(ROLE_ID_DESIGNER, $press->getId(), $searchType, $search, $searchMatch);

			$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
			$layoutEditorStatistics = $acquisitionsEditorSubmissionDao->getLayoutEditorStatistics($press->getId());

			parent::setupTemplate(true, $monographId, 'editing');

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));

			$templateMgr->assign('pageTitle', 'user.role.layoutEditors');
			$templateMgr->assign('pageSubTitle', 'editor.monograph.selectLayoutEditor');
			$templateMgr->assign('actionHandler', 'assignLayoutEditor');
			$templateMgr->assign('monographId', $monographId);
			$templateMgr->assign_by_ref('users', $layoutEditors);

			$layoutAssignments =& $submission->getLayoutAssignments();
			$assignedDesigners = array();

			foreach ($layoutAssignments as $layoutAssignment) {
				$assignedDesigners[] = $layoutAssignment->getDesignerId();
			}

			$templateMgr->assign('assignedUsers', $assignedDesigners);
			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('statistics', $layoutEditorStatistics);
			$templateMgr->assign('helpTopicId', 'press.roles.layoutEditor');
			$templateMgr->display('acquisitionsEditor/selectUser.tpl');
		}
	}

	/**
	 * Notify the layout editor.
	 */
	function notifyLayoutDesigner($args) {
		$monographId = Request::getUserVar('monographId');
		$layoutAssignmentId = Request::getUserVar('layoutAssignmentId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$send = Request::getUserVar('send') ? true : false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::notifyLayoutDesigner($submission, $layoutAssignmentId, $send)) {
			Request::redirect(null, null, 'submissionLayout', $monographId);
		}
	}

	/**
	 * Thank the layout editor.
	 */
	function thankLayoutEditor($args) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$send = Request::getUserVar('send') ? true : false;
		parent::setupTemplate(true, $monographId, 'editing');

		if (AcquisitionsEditorAction::thankLayoutEditor($submission, $send)) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Create a new galley with the uploaded file.
	 */
	function uploadGalley($fileName = null) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		parent::setupTemplate(true, $monographId, 'editing');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		import('submission.form.MonographGalleyForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$submitForm =& new MonographGalleyForm($monographId, $galleyId);

		$submitForm->readInputData();
		if ($submitForm->validate()) {
			$submitForm->execute();

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
			parent::setupTemplate(true, $monographId, 'editing');
			$submitForm->display();
		}
	}

	/**
	 * Change the sequence order of a galley.
	 */
	function orderGalley() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

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
				SubmissionEditHandler::viewFile(array($monographId, $galley->getFileId()));
			}
		}
	}

	/**
	 * Upload a new supplementary file.
	 */
	function uploadSuppFile($fileName = null) {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		parent::setupTemplate(true, $monographId, 'history');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		parent::setupTemplate(true, $monographId, 'history');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		parent::setupTemplate(true, $monographId, 'history');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('isEditor', Validation::isEditor());
		$templateMgr->assign_by_ref('submission', $submission);

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		import('file.MonographFileManager');
		$templateMgr->assign('attachments', $monographFileDao->getMonographFilesByAssocId($logId, ARTICLE_FILE_ATTACHMENT));

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		parent::setupTemplate(true, $monographId, 'history');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		AcquisitionsEditorAction::addSubmissionNote($monographId);
		Request::redirect(null, null, 'submissionNotes', $monographId);
	}

	/**
	 * Removes a submission note.
	 * Redirects to submission notes list
	 */
	function removeSubmissionNote() {
		$monographId = Request::getUserVar('monographId');		
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		AcquisitionsEditorAction::removeSubmissionNote($monographId);
		Request::redirect(null, null, 'submissionNotes', $monographId);
	}

	/**
	 * Updates a submission note.
	 * Redirects to submission notes list
	 */
	function updateSubmissionNote() {
		$monographId = Request::getUserVar('monographId');		
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

		AcquisitionsEditorAction::updateSubmissionNote($monographId);
		Request::redirect(null, null, 'submissionNotes', $monographId);
	}

	/**
	 * Clear all submission notes.
	 * Redirects to submission notes list
	 */
	function clearAllSubmissionNotes() {
		$monographId = Request::getUserVar('monographId');		
		list($press, $submission) = SubmissionEditHandler::validate($monographId);

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

		list($press, $submission) = SubmissionEditHandler::validate($monographId);
		parent::setupTemplate(true, $monographId, 'history');

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

		list($press, $submission) = SubmissionEditHandler::validate($monographId);
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

		list($press, $submission) = SubmissionEditHandler::validate($monographId);
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

		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if ($userId && $monographId && $roleDao->roleExists($press->getId(), $userId, ROLE_ID_PROOFREADER)) {
			import('submission.proofreader.ProofreaderAction');
			ProofreaderAction::selectProofreader($userId, $submission);
			Request::redirect(null, null, 'submissionEditing', $monographId);
		} else {
			parent::setupTemplate(true, $monographId, 'editing');

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

			$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO');
			$proofreaderStatistics = $acquisitionsEditorSubmissionDao->getProofreaderStatistics($press->getId());

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('users', $proofreaders);

			$proofAssignment =& $submission->getProofAssignment();
			if ($proofAssignment) {
				$templateMgr->assign('currentUser', $proofAssignment->getProofreaderId());
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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		parent::setupTemplate(true, $monographId, 'editing');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		parent::setupTemplate(true, $monographId, 'editing');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
		$proofAssignment->setDateProofreaderNotified(Core::getCurrentDate());
		$proofAssignmentDao->updateProofAssignment($proofAssignment);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Editor completes proofreading
	 */
	function editorCompleteProofreader() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
		$proofAssignment->setDateProofreaderCompleted(Core::getCurrentDate());
		$proofAssignmentDao->updateProofAssignment($proofAssignment);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Notify proofreader for proofreading
	 */
	function notifyProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		parent::setupTemplate(true, $monographId, 'editing');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		parent::setupTemplate(true, $monographId, 'editing');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
		$proofAssignment->setDateLayoutEditorNotified(Core::getCurrentDate());
		$proofAssignmentDao->updateProofAssignment($proofAssignment);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Editor completes layout editor proofreading
	 */
	function editorCompleteLayoutEditor() {
		$monographId = Request::getUserVar('monographId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
		$proofAssignment->setDateLayoutEditorCompleted(Core::getCurrentDate());
		$proofAssignmentDao->updateProofAssignment($proofAssignment);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	/**
	 * Notify layout editor for proofreading
	 */
	function notifyLayoutEditorProofreader($args) {
		$monographId = Request::getUserVar('monographId');
		$send = Request::getUserVar('send')?1:0;
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		parent::setupTemplate(true, $monographId, 'editing');

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
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		parent::setupTemplate(true, $monographId, 'editing');

		import('submission.proofreader.ProofreaderAction');
		if (ProofreaderAction::proofreadEmail($monographId, 'PROOFREAD_LAYOUT_ACK', $send?'':Request::url(null, null, 'thankLayoutEditorProofreader'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Schedule/unschedule an monograph for publication.
	 */
	function scheduleForPublication($args) {
		$monographId = (int) array_shift($args);
		$issueId = (int) Request::getUserVar('issueId');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('SectionEditorSubmissionDAO');
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$sectionDao =& DAORegistry::getDAO('SectionDAO');
		$publishedMonograph =& $publishedMonographDao->getPublishedMonographByMonographId($monographId);

		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$issue =& $issueDao->getIssueById($issueId, $press->getId());

		if ($issue) {
			// Schedule against an issue.
			if ($publishedMonograph) {
				$publishedMonograph->setIssueId($issueId);
				$publishedMonographDao->updatePublishedMonograph($publishedMonograph);
			} else {
				$publishedMonograph = new PublishedMonograph();
				$publishedMonograph->setMonographId($submission->getMonographId());
				$publishedMonograph->setIssueId($issueId);
				$publishedMonograph->setDatePublished(Core::getCurrentDate());
				$publishedMonograph->setSeq(REALLY_BIG_NUMBER);
				$publishedMonograph->setViews(0);
				$publishedMonograph->setAccessStatus(0);

				$publishedMonographDao->insertPublishedMonograph($publishedMonograph);

				// Resequence the monographs.
				$publishedMonographDao->resequencePublishedMonographs($submission->getSectionId(), $issueId);

				// If we're using custom section ordering, and if this is the first
				// monograph published in a section, make sure we enter a custom ordering
				// for it. (Default at the end of the list.)
				if ($sectionDao->customSectionOrderingExists($issueId)) {
					if ($sectionDao->getCustomSectionOrder($issueId, $submission->getSectionId()) === null) {
						$sectionDao->insertCustomSectionOrder($issueId, $submission->getSectionId(), REALLY_BIG_NUMBER);
						$sectionDao->resequenceCustomSectionOrders($issueId);
					}
				}
			}
		} else {
			if ($publishedMonograph) {
				// This was published elsewhere; make sure we don't
				// mess up sequencing information.
				$publishedMonographDao->resequencePublishedMonographs($submission->getSectionId(), $publishedMonograph->getIssueId());
				$publishedMonographDao->deletePublishedMonographByMonographId($monographId);
			}
		}
		$submission->stampStatusModified();

		if ($issue && $issue->getPublished()) {
			$submission->setStatus(STATUS_PUBLISHED);
		} else {
			$submission->setStatus(STATUS_QUEUED);
		}

		$acquisitionsEditorSubmissionDao->updateSectionEditorSubmission($submission);

		Request::redirect(null, null, 'submissionEditing', array($monographId), null, 'scheduling');
	}

	/**
	 * Payments
	 */

	function waiveSubmissionFee($args) {
		$monographId = (int) array_shift($args);
		$markAsPaid = Request::getUserVar('markAsPaid');

		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		import('payment.ojs.OJSPaymentManager');
		$paymentManager =& OJSPaymentManager::getManager();
		$user =& Request::getUser();

		$queuedPayment =& $paymentManager->createQueuedPayment(
			$press->getId(),
			PAYMENT_TYPE_SUBMISSION,
			$markAsPaid ? $submission->getUserId() : $user->getUserId(),
			$monographId,
			$markAsPaid ? $press->getSetting('submissionFee') : 0,
			$markAsPaid ? $press->getSetting('currency') : ''
		);
			
		$queuedPaymentId = $paymentManager->queuePayment($queuedPayment);
		
		// Since this is a waiver, fulfill the payment immediately
		$paymentManager->fulfillQueuedPayment($queuedPayment, $markAsPaid?'ManualPayment':'Waiver');
		Request::redirect(null, null, 'submission', array($monographId));
	}
	
	function waiveFastTrackFee($args) {
		$monographId = (int) array_shift($args);
		$markAsPaid = Request::getUserVar('markAsPaid');
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		import('payment.ojs.OJSPaymentManager');
		$paymentManager =& OJSPaymentManager::getManager();
		$user =& Request::getUser();

		$queuedPayment =& $paymentManager->createQueuedPayment(
			$press->getId(),
			PAYMENT_TYPE_FASTTRACK,
			$markAsPaid ? $submission->getUserId() : $user->getUserId(),
			$monographId,
			$markAsPaid ? $press->getSetting('fastTrackFee') : 0,
			$markAsPaid ? $press->getSetting('currency') : ''
		);
			
		$queuedPaymentId = $paymentManager->queuePayment($queuedPayment);
		
		// Since this is a waiver, fulfill the payment immediately
		$paymentManager->fulfillQueuedPayment($queuedPayment, $markAsPaid?'ManualPayment':'Waiver');
		Request::redirect(null, null, 'submission', array($monographId));
	}	
	
	function waivePublicationFee($args) {
		$monographId = (int) array_shift($args);
		$markAsPaid = Request::getUserVar('markAsPaid');
		$sendToScheduling = Request::getUserVar('sendToScheduling')?true:false;
		
		list($press, $submission) = SubmissionEditHandler::validate($monographId, SECTION_EDITOR_ACCESS_EDIT);
		import('payment.ojs.OJSPaymentManager');
		$paymentManager =& OJSPaymentManager::getManager();
		$user =& Request::getUser();

		$queuedPayment =& $paymentManager->createQueuedPayment(
			$press->getId(),
			PAYMENT_TYPE_PUBLICATION,
			$markAsPaid ? $submission->getUserId() : $user->getUserId(),
			$monographId,
			$markAsPaid ? $press->getSetting('publicationFee') : 0,
			$markAsPaid ? $press->getSetting('currency') : ''
		);

		$queuedPaymentId = $paymentManager->queuePayment($queuedPayment);
		
		// Since this is a waiver, fulfill the payment immediately
		$paymentManager->fulfillQueuedPayment($queuedPayment, $markAsPaid?'ManualPayment':'Waiver');
		
		if ( $sendToScheduling ) {
			Request::redirect(null, null, 'submissionEditing', array($monographId), null, 'scheduling');
		} else { 
			Request::redirect(null, null, 'submission', array($monographId));
		}
	}

	//
	// Validation
	//

	/**
	 * Validate that the user is the assigned section editor for
	 * the monograph, or is a managing editor.
	 * Redirects to acquisitionsEditor index page if validation fails.
	 * @param $monographId int Monograph ID to validate
	 * @param $access int Optional name of access level required -- see SECTION_EDITOR_ACCESS_... constants
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
					if ($editAssignment->getEditorId() == $user->getUserId()) {
						$templateMgr->assign('canReview', $editAssignment->getCanReview());
						$templateMgr->assign('canEdit', $editAssignment->getCanEdit());
						switch ($access) {
							case SECTION_EDITOR_ACCESS_EDIT:
								if ($editAssignment->getCanEdit()) {
									$wasFound = true;
								}
								break;
							case SECTION_EDITOR_ACCESS_REVIEW:
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
			if ($editAssignment->getEditorId() == $user->getUserId() && $editAssignment->getDateUnderway() === null) {
				$editAssignment->setDateUnderway(Core::getCurrentDate());
				$editAssignmentDao->updateEditAssignment($editAssignment);
			}
		}

		return array(&$press, &$acquisitionsEditorSubmission);
	}

}
?>