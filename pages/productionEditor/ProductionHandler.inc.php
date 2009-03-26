<?php

/**
 * @file ProductionHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionHandler
 * @ingroup pages_information
 *
 * @brief Display press information.
 */

// $Id$


import('core.PKPHandler');
import('pages.acquisitionsEditor.SubmissionEditHandler');

class ProductionHandler extends SubmissionEditHandler {

	/**
	 * Display the information page for the press..
	 */
	function index($args) {

		list($press) = ProductionHandler::validate();
		ProductionHandler::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$user =& Request::getUser();
		$rangeInfo =& PKPHandler::getRangeInfo('submissions');
		$productionSubmissionDao =& DAORegistry::getDAO('MonographDAO');

		$page = isset($args[0]) ? $args[0] : '';
		switch($page) {
			case 'completed':
				$active = false;
				break;
			default:
				$page = 'active';
				$active = true;
		}

		$submissions = $productionSubmissionDao->getMonographs($press->getId(), $rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageToDisplay', $page);
		if (!$active) {
			// Make view counts available if enabled.
			$templateMgr->assign('statViews', $press->getSetting('statViews'));
		}
		$templateMgr->assign_by_ref('submissions', $submissions);
		$templateMgr->display('productionEditor/index.tpl');
	}
	/**

	 * View a file (inline file).
	 * @param $args array ($monographId, $fileId, [$revision])
	 */
	function viewFile($args) {
		$monographId = isset($args[0]) ? $args[0] : 0;
		$fileId = isset($args[1]) ? $args[1] : 0;
		$revision = isset($args[2]) ? $args[2] : null;

		list($press) = ProductionHandler::validate();
		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
$monographFileManager->viewFile($fileId, $revision);
//		if (!$monographFileManager->viewFile($fileId, $revision)) {
//			Request::redirect(null, null, 'submission', $monographId);
//		}
	}
	function submission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = ProductionHandler::validate($monographId);
		ProductionHandler::setupTemplate(false, $monographId);

		$user =& Request::getUser();

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettings = $pressSettingsDao->getPressSettings($press->getId());

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->roleExists($press->getId(), $user->getUserId(), ROLE_ID_EDITOR);

//		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
//		$arrangement =& $arrangementDao->getAcquisitionsArrangement($submission->getAcquisitionsArrangementId());

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('arrangement', $arrangement);
		$templateMgr->assign_by_ref('authors', $submission->getAuthors());
//		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
//		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
//		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getUserId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

//		$templateMgr->assign_by_ref('arrangements', $arrangementDao->getAcquisitionsArrangementsTitles($press->getId()));

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
		$templateMgr->display('productionEditor/submission.tpl');


	}

	function submissionArt($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = ProductionHandler::validate($monographId);
		ProductionHandler::setupTemplate(false, $monographId);

		$user =& Request::getUser();

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$artworks =& $monographFileDao->getMonographFilesByAssocId(null, MONOGRAPH_FILE_ARTWORK);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettings = $pressSettingsDao->getPressSettings($press->getId());

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->roleExists($press->getId(), $user->getUserId(), ROLE_ID_EDITOR);

//		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
//		$arrangement =& $arrangementDao->getAcquisitionsArrangement($submission->getAcquisitionsArrangementId());

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('arrangement', $arrangement);
		$templateMgr->assign_by_ref('authors', $submission->getAuthors());

		$templateMgr->assign_by_ref('artworks', $artworks);
		

//		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
//		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
//		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getUserId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

//		$templateMgr->assign_by_ref('arrangements', $arrangementDao->getAcquisitionsArrangementsTitles($press->getId()));

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
		$templateMgr->display('productionEditor/art.tpl');


	}

	/**
	 * Upload a layout file (either layout version, galley, or supp. file).
	 */
/*	function removeArtworkFile($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$fileId = isset($args[1]) ? (int) $args[1] : 0;

		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		$monographFileManager->deleteFile($fileId);

		Request::redirect(null, null, 'submissionArt', $monographId);
	}
*/
	/**
	 * Upload a layout file (either layout version, galley, or supp. file).
	 */
	function uploadArtworkFile() {
		$monographId = Request::getUserVar('monographId');
		import('file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);

		if ($monographFileManager->uploadedFileExists('artworkFile')) {
			$fileId = $monographFileManager->uploadArtworkFile('artworkFile', null);
		}
		Request::redirect(null, null, 'submissionArt', Request::getUserVar('monographId'));
	}
	function submissionLayout($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		list($press, $submission) = ProductionHandler::validate($monographId);
		ProductionHandler::setupTemplate(false, $monographId);

		$user =& Request::getUser();

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettings = $pressSettingsDao->getPressSettings($press->getId());

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->roleExists($press->getId(), $user->getUserId(), ROLE_ID_EDITOR);

//		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
//		$arrangement =& $arrangementDao->getAcquisitionsArrangement($submission->getAcquisitionsArrangementId());

		$enableComments = $press->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('layoutAssignment', $submission->getLayoutAssignment());
		$templateMgr->assign_by_ref('submission', $submission);
		$templateMgr->assign_by_ref('arrangement', $arrangement);
		$templateMgr->assign_by_ref('authors', $submission->getAuthors());
//		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
//		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
//		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('userId', $user->getUserId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

//		$templateMgr->assign_by_ref('arrangements', $arrangementDao->getAcquisitionsArrangementsTitles($press->getId()));

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
		$templateMgr->display('productionEditor/layout.tpl');


	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false, $monographId = 0, $parentPage = null) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));
		$templateMgr =& TemplateManager::getManager();

		$pageHierarchy = $subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'productionEditor'), 'user.role.productionEditor'), array(Request::url(null, 'productionEditor'), 'manuscript.submissions'))
			: array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'productionEditor'), 'user.role.productionEditor'));

/*		import('submission.sectionEditor.SectionEditorAction');
		$submissionCrumb = SectionEditorAction::submissionBreadcrumb($monographId, $parentPage, 'author');
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}
*/
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}

	/**
	 * Validate that user has production editor permissions in the selected press.
	 * Redirects to user index page if not properly authenticated.
	 */
	function validate($monographId = null, $reason = null) {
	//	parent::validate();
		$press =& Request::getPress();
		$productionEditorSubmission = null;
		$isValid = true;

		if ($monographId != null) {
			$monographDao =& DAORegistry::getDAO('AcquisitionsEditorSubmissionDAO');
			$productionEditorSubmission =& $monographDao->getAcquisitionsEditorSubmission($monographId);

			if ($productionEditorSubmission == null) {
				$isValid = false;

			} else if ($productionEditorSubmission->getPressId() != $press->getId()) {
				$isValid = false;

			} else if ($productionEditorSubmission->getDateSubmitted() == null) {
				$isValid = false;

			}
		}
		if (!isset($press) || !Validation::isProductionEditor($press->getId())) {
				Validation::redirectLogin($reason);
		}

		if (!$isValid) {
			Request::redirect(null, Request::getRequestedPage());
		}

		return array(&$press, &$productionEditorSubmission);
	}
}

?>