<?php

/**
 * @file AcquisitionsEditorHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcquisitionsEditorHandler
 * @ingroup pages_acquisitionsEditor
 *
 * @brief Handle requests for acquistions editor functions. 
 */

// $Id$


// Filter section
define('FILTER_SECTION_ALL', 0);

import('submission.acquisitionsEditor.AcquisitionsEditorAction');
import('core.PKPHandler');

class AcquisitionsEditorHandler extends PKPHandler {

	/**
	 * Display acquisitions editor index page.
	 */
	function index($args) {
		AcquisitionsEditorHandler::validate();
		AcquisitionsEditorHandler::setupTemplate();

		$press =& Request::getPress();
		$pressId = $press->getPressId();
		$user =& Request::getUser();

		$rangeInfo = PKPHandler::getRangeInfo('submissions');

		// Get the user's search conditions, if any
		$searchField = Request::getUserVar('searchField');
		$dateSearchField = Request::getUserVar('dateSearchField');
		$searchMatch = Request::getUserVar('searchMatch');
		$search = Request::getUserVar('search');

		$fromDate = Request::getUserDateVar('dateFrom', 1, 1);
		if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
		$toDate = Request::getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
		if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
		$acquisitionsEditorSubmissionDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');

		$page = isset($args[0]) ? $args[0] : '';
		$arrangements =& $arrangementDao->getSectionTitles($press->getPressId());

		$filterSectionOptions = array(
			FILTER_SECTION_ALL => Locale::Translate('editor.allSections')
		) + $arrangements;

		switch($page) {
			case 'submissionsInEditing':
				$functionName = 'getSectionEditorSubmissionsInEditing';
				$helpTopicId = 'editorial.acquisitionsEditorsRole.submissions.inEditing';
				break;
			case 'submissionsArchives':
				$functionName = 'getSectionEditorSubmissionsArchives';
				$helpTopicId = 'editorial.acquisitionsEditorsRole.submissions.archives';
				break;
			default:
				$page = 'submissionsInReview';
				$functionName = 'getSectionEditorSubmissionsInReview';
				$helpTopicId = 'editorial.acquisitionsEditorsRole.submissions.inReview';
		}

		$filterSection = Request::getUserVar('filterSection');
		if ($filterSection != '' && array_key_exists($filterSection, $filterSectionOptions)) {
			$user->updateSetting('filterSection', $filterSection, 'int', $pressId);
		} else {
			$filterSection = $user->getSetting('filterSection', $pressId);
			if ($filterSection == null) {
				$filterSection = FILTER_SECTION_ALL;
				$user->updateSetting('filterSection', $filterSection, 'int', $pressId);
			}	
		}

		$submissions =& $acquisitionsEditorSubmissionDao->$functionName(
			$user->getUserId(),
			$press->getPressId(),
			$filterSection,
			$searchField,
			$searchMatch,
			$search,
			$dateSearchField,
			$fromDate,
			$toDate,
			$rangeInfo
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', $helpTopicId);
		$templateMgr->assign('sectionOptions', $filterSectionOptions);
		$templateMgr->assign_by_ref('submissions', $submissions);
		$templateMgr->assign('filterSection', $filterSection);
		$templateMgr->assign('pageToDisplay', $page);
		$templateMgr->assign('acquisitionsEditor', $user->getFullName());

		// Set search parameters
		$duplicateParameters = array(
			'searchField', 'searchMatch', 'search',
			'dateFromMonth', 'dateFromDay', 'dateFromYear',
			'dateToMonth', 'dateToDay', 'dateToYear',
			'dateSearchField'
		);
		foreach ($duplicateParameters as $param)
			$templateMgr->assign($param, Request::getUserVar($param));

		$templateMgr->assign('dateFrom', $fromDate);
		$templateMgr->assign('dateTo', $toDate);
		$templateMgr->assign('fieldOptions', Array(
			SUBMISSION_FIELD_TITLE => 'article.title',
			SUBMISSION_FIELD_AUTHOR => 'user.role.author',
			SUBMISSION_FIELD_EDITOR => 'user.role.editor'
		));
		$templateMgr->assign('dateFieldOptions', Array(
			SUBMISSION_FIELD_DATE_SUBMITTED => 'submissions.submitted',
			SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE => 'submissions.copyeditComplete',
			SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE => 'submissions.layoutComplete',
			SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE => 'submissions.proofreadingComplete'
		));

		import('issue.IssueAction');
		$issueAction = new IssueAction();
		$templateMgr->register_function('print_issue_id', array($issueAction, 'smartyPrintIssueId'));

		$templateMgr->display('acquisitionsEditor/index.tpl');
	}

	function selectInternalReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectInternalReviewer($args);
	}

	/**
	 * Validate that user is a section editor in the selected journal.
	 * Redirects to user index page if not properly authenticated.
	 */
	function validate() {
		parent::validate();
		$press =& Request::getPress();
		// FIXME This is kind of evil
		$page = Request::getRequestedPage();
		if (!isset($press) || ($page == 'acquisitionsEditor' && !Validation::isAcquisitionsEditor($press->getPressId())) || ($page == 'editor' && !Validation::isEditor($press->getPressId()))) {
			Validation::redirectLogin();
		}
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false, $monographId = 0, $parentPage = null, $showSidebar = true) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR));
		$templateMgr =& TemplateManager::getManager();
		$isEditor = Validation::isEditor();

		if (Request::getRequestedPage() == 'editor') {
			$templateMgr->assign('helpTopicId', 'editorial.editorsRole');

		} else {
			$templateMgr->assign('helpTopicId', 'editorial.acquisitionsEditorsRole');
		}

		$pageHierarchy = $subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, $isEditor?'editor':'acquisitionsEditor'), $isEditor?'user.role.editor':'user.role.acquisitionsEditor'), array(Request::url(null, 'acquisitionsEditor'), 'manuscript.submissions'))
			: array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, $isEditor?'editor':'acquisitionsEditor'), $isEditor?'user.role.editor':'user.role.acquisitionsEditor'));

		import('submission.acquisitionsEditor.AcquisitionsEditorAction');
		$submissionCrumb = AcquisitionsEditorAction::submissionBreadcrumb($monographId, $parentPage, 'acquisitionsEditor');
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}

	//
	// Submission Tracking
	//

	function enrollSearch($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::enrollSearch($args);
	}

	function createReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::createReviewer($args);
	}

	function suggestUsername() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::suggestUsername();
	}

	function enroll($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::enroll($args);
	}

	function submission($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submission($args);
	}

	function submissionRegrets($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionRegrets($args);
	}

	function submissionReview($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionReview($args);
	}

	function submissionEditing($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEditing($args);
	}

	function submissionHistory($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionHistory($args);
	}

	function changeSection() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::changeSection();
	}

	function recordDecision() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::recordDecision();
	}

	function selectReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectReviewer($args);
	}

	function notifyReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyReviewer($args);
	}

	function notifyAllReviewers($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyAllReviewers($args);
	}

	function userProfile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::userProfile($args);
	}

	function clearReview($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearReview($args);
	}

	function cancelReview($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::cancelReview($args);
	}

	function remindReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::remindReviewer($args);
	}

	function thankReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankReviewer($args);
	}

	function rateReviewer() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::rateReviewer();
	}

	function confirmReviewForReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::confirmReviewForReviewer($args);
	}

	function uploadReviewForReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadReviewForReviewer($args);
	}

	function enterReviewerRecommendation($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::enterReviewerRecommendation($args);
	}

	function makeReviewerFileViewable() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::makeReviewerFileViewable();
	}

	function setDueDate($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::setDueDate($args);
	}

	function viewMetadata($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::viewMetadata($args);
	}

	function saveMetadata() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::saveMetadata();
	}

	function removeArticleCoverPage($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::removeCoverPage($args);
	}

	function editorReview() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorReview();
	}

	function selectCopyeditor($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectCopyeditor($args);
	}

	function notifyCopyeditor($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyCopyeditor($args);
	}

	function initiateCopyedit() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::initiateCopyedit();
	}

	function thankCopyeditor($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankCopyeditor($args);
	}

	function notifyAuthorCopyedit($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyAuthorCopyedit($args);
	}

	function thankAuthorCopyedit($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankAuthorCopyedit($args);
	}

	function notifyFinalCopyedit($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyFinalCopyedit($args);
	}

	function thankFinalCopyedit($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankFinalCopyedit($args);
	}

	function selectCopyeditRevisions() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectCopyeditRevisions();
	}

	function uploadReviewVersion() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadReviewVersion();
	}

	function uploadCopyeditVersion() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadCopyeditVersion();
	}

	function completeCopyedit($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::completeCopyedit($args);
	}

	function completeFinalCopyedit($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::completeFinalCopyedit($args);
	}

	function addSuppFile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::addSuppFile($args);
	}

	function setSuppFileVisibility($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::setSuppFileVisibility($args);
	}

	function editSuppFile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::editSuppFile($args);
	}

	function saveSuppFile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::saveSuppFile($args);
	}

	function deleteSuppFile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::deleteSuppFile($args);
	}

	function deleteArticleFile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::deleteArticleFile($args);
	}

	function archiveSubmission($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::archiveSubmission($args);
	}

	function unsuitableSubmission($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::unsuitableSubmission($args);
	}

	function restoreToQueue($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::restoreToQueue($args);
	}

	function updateAcquisitionsArrangement($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::updateAcquisitionsArrangement($args);
	}

	function updateCommentsStatus($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::updateCommentsStatus($args);
	}

	//
	// Layout Editing
	//

	function deleteArticleImage($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::deleteArticleImage($args);
	}

	function uploadLayoutFile() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadLayoutFile();
	}

	function uploadLayoutVersion() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadLayoutVersion();
	}

	function assignLayoutEditor($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::assignLayoutEditor($args);
	}

	function notifyLayoutEditor($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyLayoutEditor($args);
	}

	function thankLayoutEditor($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankLayoutEditor($args);
	}

	function uploadGalley() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadGalley();
	}

	function editGalley($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::editGalley($args);
	}

	function saveGalley($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::saveGalley($args);
	}

	function orderGalley() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::orderGalley();
	}

	function deleteGalley($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::deleteGalley($args);
	}

	function proofGalley($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::proofGalley($args);
	}

	function proofGalleyTop($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::proofGalleyTop($args);
	}

	function proofGalleyFile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::proofGalleyFile($args);
	}	

	function uploadSuppFile() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadSuppFile();
	}

	function orderSuppFile() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::orderSuppFile();
	}


	//
	// Submission History
	//

	function submissionEventLog($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEventLog($args);
	}		

	function submissionEventLogType($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEventLogType($args);
	}

	function clearSubmissionEventLog($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearSubmissionEventLog($args);
	}

	function submissionEmailLog($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEmailLog($args);
	}

	function submissionEmailLogType($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEmailLogType($args);
	}

	function clearSubmissionEmailLog($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearSubmissionEmailLog($args);
	}

	function addSubmissionNote() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::addSubmissionNote();
	}

	function removeSubmissionNote() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::removeSubmissionNote();
	}		

	function updateSubmissionNote() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::updateSubmissionNote();
	}

	function clearAllSubmissionNotes() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearAllSubmissionNotes();
	}

	function submissionNotes($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionNotes($args);
	}


	//
	// Misc.
	//

	function downloadFile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::downloadFile($args);
	}

	function viewFile($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::viewFile($args);
	}

	//
	// Submission Comments
	//

	function viewPeerReviewComments($args) {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewPeerReviewComments($args);
	}

	function postPeerReviewComment() {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postPeerReviewComment();
	}

	function viewEditorDecisionComments($args) {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewEditorDecisionComments($args);
	}

	function blindCcReviewsToReviewers($args) {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::blindCcReviewsToReviewers($args);
	}

	function postEditorDecisionComment() {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postEditorDecisionComment();
	}

	function viewCopyeditComments($args) {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewCopyeditComments($args);
	}

	function postCopyeditComment() {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postCopyeditComment();
	}

	function emailEditorDecisionComment() {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::emailEditorDecisionComment();
	}

	function viewLayoutComments($args) {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewLayoutComments($args);
	}

	function postLayoutComment() {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postLayoutComment();
	}

	function viewProofreadComments($args) {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewProofreadComments($args);
	}

	function postProofreadComment() {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postProofreadComment();
	}

	function editComment($args) {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::editComment($args);
	}

	function saveComment() {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::saveComment();
	}

	function deleteComment($args) {
		import('pages.acquisitionsEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::deleteComment($args);
	}

	// Submission Review Form

	function clearReviewForm($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearReviewForm($args);
	}

	function selectReviewForm($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectReviewForm($args);
	}

	function previewReviewForm($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::previewReviewForm($args);
	}

	function viewReviewFormResponse($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::viewReviewFormResponse($args);
	}

	/** Proof Assignment Functions */
	function selectProofreader($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectProofreader($args);
	}

	function notifyAuthorProofreader($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyAuthorProofreader($args);
	}

	function thankAuthorProofreader($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankAuthorProofreader($args);	
	}

	function editorInitiateProofreader() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorInitiateProofreader();
	}

	function editorCompleteProofreader() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorCompleteProofreader();
	}

	function notifyProofreader($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyProofreader($args);
	}

	function thankProofreader($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankProofreader($args);
	}

	function editorInitiateLayoutEditor() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorInitiateLayoutEditor();
	}

	function editorCompleteLayoutEditor() {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorCompleteLayoutEditor();
	}

	function notifyLayoutEditorProofreader($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyLayoutEditorProofreader($args);
	}

	function thankLayoutEditorProofreader($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankLayoutEditorProofreader($args);
	}

	/**
	 * Scheduling functions
	 */

	function scheduleForPublication($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::scheduleForPublication($args);
	}
	
	/**
	 * Payments
	 */

	 function waiveSubmissionFee($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::waiveSubmissionFee($args);
	 }

	 function waiveFastTrackFee($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::waiveFastTrackFee($args);
	 }
	 
	 function waivePublicationFee($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::waivePublicationFee($args);
	 }
}

?>
