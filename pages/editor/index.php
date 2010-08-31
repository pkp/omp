<?php

/**
 * @defgroup pages_editor
 */

/**
 * @file pages/editor/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_editor
 * @brief Handle requests for editor functions.
 *
 */


switch ($op) {
	//
	// Submission Tracking
	//
	case 'enrollSearch':
	case 'createReviewer':
	case 'suggestUsername':
	case 'enroll':
	case 'submission':
	case 'submissionRegrets':
	case 'submissionReview':
	case 'submissionEditing':
	case 'submissionProduction':
	case 'submissionHistory':
	case 'changeSeries':
	case 'recordDecision':
	case 'recordReviewFiles':
	case 'selectReviewer':
	case 'notifyReviewer':
	case 'notifyAllReviewers':
	case 'userProfile':
	case 'clearReview':
	case 'cancelReview':
	case 'remindReviewer':
	case 'thankReviewer':
	case 'rateReviewer':
	case 'confirmReviewForReviewer':
	case 'uploadReviewForReviewer':
	case 'enterReviewerRecommendation':
	case 'makeReviewerFileViewable':
	case 'setDueDate':
	case 'viewMetadata':
	case 'saveMetadata':
	case 'removeMonographCoverPage':
	case 'editorReview':
	case 'selectCopyeditor':
	case 'notifyCopyeditor':
	case 'initiateCopyedit':
	case 'thankCopyeditor':
	case 'notifyAuthorCopyedit':
	case 'thankAuthorCopyedit':
	case 'notifyFinalCopyedit':
	case 'thankFinalCopyedit':
	case 'selectCopyeditRevisions':
	case 'uploadReviewVersion':
	case 'uploadCopyeditVersion':
	case 'completeCopyedit':
	case 'completeFinalCopyedit':
	case 'deleteMonographFile':
	case 'archiveSubmission':
	case 'unsuitableSubmission':
	case 'restoreToQueue':
	case 'updateSeries':
	case 'updateCommentsStatus':
	//
	// Layout Editing
	//
	case 'deleteMonographImage':
	case 'uploadLayoutFile':
	case 'uploadLayoutVersion':
	case 'assignProductionEditor':
	case 'notifyLayoutEditor':
	case 'thankLayoutEditor':
	case 'uploadGalley':
	case 'editGalley':
	case 'saveGalley':
	case 'orderGalley':
	case 'deleteGalley':
	case 'proofGalley':
	case 'proofGalleyTop':
	case 'proofGalleyFile':
	//
	// Submission History
	//
	case 'submissionEventLog':
	case 'submissionEventLogType':
	case 'clearSubmissionEventLog':
	case 'submissionEmailLog':
	case 'submissionEmailLogType':
	case 'clearSubmissionEmailLog':
	//
	// Misc.
	//
	case 'downloadFile':
	case 'viewFile':
	// Submission Review Form
	case 'clearReviewForm':
	case 'selectReviewForm':
	case 'previewReviewForm':
	case 'viewReviewFormResponse':
	case 'selectInternalReviewer':
	/**
	 * Scheduling functions
	 */
	case 'scheduleForPublication':
		import('pages.seriesEditor.SubmissionEditHandler');
		define('HANDLER_CLASS', 'SubmissionEditHandler');
		break;
	//
	// Submission Comments
	//
	case 'viewPeerReviewComments':
	case 'postPeerReviewComment':
	case 'viewEditorDecisionComments':
	case 'blindCcReviewsToReviewers':
	case 'postEditorDecisionComment':
	case 'viewCopyeditComments':
	case 'postCopyeditComment':
	case 'emailEditorDecisionComment':
	case 'viewLayoutComments':
	case 'postLayoutComment':
	case 'viewProofreadComments':
	case 'postProofreadComment':
	case 'editComment':
	case 'saveComment':
	case 'deleteComment':
		import('pages.seriesEditor.SubmissionCommentsHandler');
		define('HANDLER_CLASS', 'SubmissionCommentsHandler');
		break;
	case 'viewMetadata':
	case 'selectReviewer':
	case 'submissions':
	case 'deleteEditAssignment':
	case 'assignEditor':
	case 'setEditorFlags':
		define('HANDLER_CLASS', 'EditorHandler');
		import('pages.editor.EditorHandler');
		break;
}


?>
