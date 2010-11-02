<?php

/**
 * @file TrackSubmissionHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TrackSubmissionHandler
 * @ingroup pages_author
 *
 * @brief Handle requests for submission tracking.
 */


import('pages.author.AuthorHandler');

class TrackSubmissionHandler extends AuthorHandler {
	/** submission associated with this request **/
	var $submission;

	/**
	 * Constructor
	 **/
	function TrackSubmissionHandler() {
		parent::AuthorHandler();
	}

	/**
	 * Delete a submission.
	 */
	function deleteSubmission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);

		$authorSubmission =& $this->submission;
		$this->setupTemplate(true);

		// If the submission is incomplete, allow the author to delete it.
		if ($authorSubmission->getSubmissionProgress()!=0) {
			import('classes.file.MonographFileManager');
			$monographFileManager = new MonographFileManager($monographId);
			$monographFileManager->deleteMonographTree();

			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monographDao->deleteMonographById($args[0]);
		}

		Request::redirect(null, null, 'index');
	}

	/**
	 * Delete an author version file.
	 * @param $args array ($monographId, $fileId)
	 */
	function deleteMonographFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$fileId = isset($args[1]) ? (int) $args[1] : 0;
		$revisionId = isset($args[2]) ? (int) $args[2] : 0;

		$this->validate($monographId);
		$authorSubmission =& $this->submission;
		if ($authorSubmission->getStatus() != STATUS_PUBLISHED && $authorSubmission->getStatus() != STATUS_ARCHIVED) {
			AuthorAction::deleteMonographFile($authorSubmission, $fileId, $revisionId);
		}

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	/**
	 * Display a summary of the status of an author's submission.
	 */
	function submission($args) {
		$press =& Request::getPress();
		$user =& Request::getUser();
		$monographId = isset($args[0]) ? (int) $args[0] : 0;

		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettings = $pressSettingsDao->getPressSettings($press->getId());

		// Setting the round.
		$round = isset($args[1]) ? $args[1] : $submission->getCurrentRound();

		$templateMgr =& TemplateManager::getManager();

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$series =& $seriesDao->getById($submission->getSeriesId());
		$templateMgr->assign_by_ref('series', $series);

		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('reviewAssignments', $submission->getReviewAssignments($round));
		$templateMgr->assign('round', $round);
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('revisedFile', $submission->getRevisedFile());
		$templateMgr->assign('pageToDisplay', 'submissionSummary');

		import('classes.submission.seriesEditor.SeriesEditorSubmission');
		$templateMgr->assign_by_ref('editorDecisionOptions', SeriesEditorSubmission::getEditorDecisionOptions());

		$templateMgr->assign('helpTopicId','editorial.authorsRole');

		$initialCopyeditSignoff = $submission->getSignoff('SIGNOFF_COPYEDITING_INITIAL');
		$templateMgr->assign('canEditMetadata', isset($initialCopyeditSignoff) && !$initialCopyeditSignoff->getDateCompleted() && $submission->getStatus() != STATUS_PUBLISHED);

		$templateMgr->display('author/submission.tpl');
	}

	/**
	 * Display specific details of an author's submission.
	 */
	function submissionReview($args) {
		$user =& Request::getUser();
		$monographId = isset($args[0]) ? (int) $args[0] : 0;

		$this->validate($monographId);
		$authorSubmission =& $this->submission;
		$this->setupTemplate(true, $monographId);
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR)); // editor.article.decision etc. FIXME?

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewModifiedByRound = $reviewAssignmentDao->getLastModifiedByRound($monographId);
		$reviewEarliestNotificationByRound = $reviewAssignmentDao->getEarliestNotificationByRound($monographId);
		$reviewFilesByRound =& $reviewAssignmentDao->getReviewFilesByRound($monographId);
		$authorViewableFilesByRound =& $reviewAssignmentDao->getAuthorViewableFilesByRound($monographId);

		$editorDecisions = $authorSubmission->getDecisions($authorSubmission->getCurrentReviewType(), $authorSubmission->getCurrentRound());
		$lastDecision = count($editorDecisions) >= 1 ? $editorDecisions[count($editorDecisions) - 1] : null;

		$templateMgr =& TemplateManager::getManager();
		$reviewAssignments =& $authorSubmission->getReviewAssignments();
		$templateMgr->assign_by_ref('reviewAssignments', $reviewAssignments);
		$templateMgr->assign_by_ref('submission', $authorSubmission);
		$templateMgr->assign_by_ref('reviewFilesByRound', $reviewFilesByRound);
		$templateMgr->assign_by_ref('authorViewableFilesByRound', $authorViewableFilesByRound);
		$templateMgr->assign_by_ref('reviewModifiedByRound', $reviewModifiedByRound);

		$reviewIndexesByRound = array();
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$reviewRounds =& $monographDao->getReviewRoundsInfoById($monographId);

		$reviewIndexesByRound = array();
		for ($round = 1; $round <= $authorSubmission->getCurrentRound(); $round++) {
			$reviewIndexesByRound[$round] = $reviewAssignmentDao->getReviewIndexesForRound($articleId, $round);
		}
		$templateMgr->assign_by_ref('reviewIndexesByRound', $reviewIndexesByRound);

		$templateMgr->assign('pageToDisplay', 'submissionReview');
		$templateMgr->assign_by_ref('reviewRounds', $reviewRounds);
		$templateMgr->assign('reviewEarliestNotificationByRound', $reviewEarliestNotificationByRound);
		$templateMgr->assign_by_ref('submissionFile', $authorSubmission->getSubmissionFile());
		$templateMgr->assign_by_ref('revisedFile', $authorSubmission->getRevisedFile());
		$templateMgr->assign('lastEditorDecision', $lastDecision);
		$templateMgr->assign('editorDecisionOptions',
			array(
				'' => 'common.chooseOne',
				SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.monograph.decision.accept',
				SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.monograph.decision.pendingRevisions',
				SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.monograph.decision.resubmit',
				SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline'
			)
		);
		$templateMgr->assign('helpTopicId', 'editorial.authorsRole.review');
		$templateMgr->display('author/submission.tpl');
	}

	/**
	 * Display the status and other details of an author's submission.
	 */
	function submissionEditing($args) {
		$press =& Request::getPress();
		$user =& Request::getUser();
		$monographId = isset($args[0]) ? (int) $args[0] : 0;

		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);

		AuthorAction::copyeditUnderway($submission);
		import('classes.submission.proofreader.ProofreaderAction');
		ProofreaderAction::proofreadingUnderway($submission, 'SIGNOFF_PROOFREADING_AUTHOR');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageToDisplay', 'submissionEditing');
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('copyeditor', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
		$templateMgr->assign_by_ref('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
		$templateMgr->assign_by_ref('editorAuthorCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR'));
		$templateMgr->assign_by_ref('finalCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL'));
		$templateMgr->assign('useCopyeditors', $press->getSetting('useCopyeditors'));
		$templateMgr->assign('useLayoutEditors', $press->getSetting('useLayoutEditors'));
		$templateMgr->assign('useProofreaders', $press->getSetting('useProofreaders'));
		$templateMgr->assign('helpTopicId', 'editorial.authorsRole.editing');
		$templateMgr->display('author/submission.tpl');
	}

	/**
	 * Upload the author's revised version of a monograph.
	 */
	function uploadRevisedVersion() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true);

		AuthorAction::uploadRevisedVersion($submission);

		Request::redirect(null, null, 'submissionReview', $monographId);
	}

	function viewMetadata($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId, 'summary');

		AuthorAction::viewMetadata($submission);
	}

	function saveMetadata() {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);

		// If the copy editor has completed copyediting, disallow
		// the author from changing the metadata.

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $submission->getId());
		if ($initialSignoff->getDateCompleted() != null || AuthorAction::saveMetadata($submission)) {
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
		$press =& Request::getPress();

		import('classes.file.PublicFileManager');
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

	function uploadCopyeditVersion() {
		$copyeditStage = Request::getUserVar('copyeditStage');
		$monographId = Request::getUserVar('monographId');

		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true, $monographId);

		AuthorAction::uploadCopyeditVersion($submission, $copyeditStage);

		Request::redirect(null, null, 'submissionEditing', $monographId);
	}

	function completeAuthorCopyedit($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$submission =& $this->submission;
		$this->setupTemplate(true);

		if (AuthorAction::completeAuthorCopyedit($submission, Request::getUserVar('send'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
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
		$submission =& $this->submission;
		if (!AuthorAction::downloadAuthorFile($submission, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

	/**
	 * Download a file.
	 * @param $args array ($monographId, $fileId, [$revision])
	 */
	function download($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$fileId = isset($args[1]) ? $args[1] : 0;
		$revision = isset($args[2]) ? $args[2] : null;

		$this->validate($monographId);
		Action::downloadFile($monographId, $fileId, $revision);
	}

	//
	// Validation
	//

	/**
	 * Validate that the user is the author for the monograph.
	 * Redirects to author index page if validation fails.
	 */
	function validate($monographId) {
		parent::validate();

		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$user =& Request::getUser();
		$press =& Request::getPress();
		$isValid = true;

		$authorSubmission =& $authorSubmissionDao->getAuthorSubmission($monographId);

		if ($authorSubmission == null) {
			$isValid = false;
		} else if ($authorSubmission->getPressId() != $press->getId()) {
			$isValid = false;
		} else {
			if ($authorSubmission->getUserId() != $user->getId()) {
				$isValid = false;
			}
		}

		if (!$isValid) {
			Request::redirect(null, Request::getRequestedPage());
		}

		$this->submission =& $authorSubmission;
		return true;
	}

	//
	// Proofreading
	//

	/**
	 * Set the author proofreading date completion
	 */
	function authorProofreadingComplete($args) {
		$monographId = Request::getUserVar('monographId');
		$this->validate($monographId);
		$this->setupTemplate(true);

		$send = isset($args[0]) && $args[0] == 'send' ? true : false;

		import('classes.submission.proofreader.ProofreaderAction');

		if (ProofreaderAction::proofreadEmail($monographId,'PROOFREAD_AUTHOR_COMPLETE', $send?'':Request::url(null, 'author', 'authorProofreadingComplete', 'send'))) {
			Request::redirect(null, null, 'submissionEditing', $monographId);
		}
	}

	/**
	 * Proof / "preview" a galley.
	 * @param $args array ($monographId, $galleyId)
	 */
	function proofGalley($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$galleyId = isset($args[1]) ? (int) $args[1] : 0;
		$this->validate($monographId);
		$this->setupTemplate();

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
		$this->validate($monographId);
		$this->setupTemplate();

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
		$this->validate($monographId);

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
				TrackSubmissionHandler::viewFile(array($monographId, $galley->getFileId()));
			}
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
		if (!AuthorAction::viewFile($monographId, $fileId, $revision)) {
			Request::redirect(null, null, 'submission', $monographId);
		}
	}

}
?>
