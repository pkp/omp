<?php

/**
 * @file EditorHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorHandler
 * @ingroup pages_editor
 *
 * @brief Handle requests for editor functions. 
 */

// $Id$

import('core.PKPHandler');

define('EDITOR_SECTION_HOME', 0);
define('EDITOR_SECTION_SUBMISSIONS', 1);
define('EDITOR_SECTION_ISSUES', 2);

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

class EditorHandler extends PKPHandler {

	/**
	 * Displays the editor role selection page.
	 */

	function index($args) {
		EditorHandler::validate();
		EditorHandler::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$press =& Request::getPress();
		$pressId = $press->getPressId();
		$user =& Request::getUser();

		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
	//	$sectionDao =& DAORegistry::getDAO('SectionDAO');

	//	$sections =& $sectionDao->getSectionTitles($press->getPressId());
	//	$templateMgr->assign('sectionOptions', array(0 => Locale::Translate('editor.allSections')) + $sections);
	//	$templateMgr->assign('fieldOptions', EditorHandler::getSearchFieldOptions());
	//	$templateMgr->assign('dateFieldOptions', EditorHandler::getDateFieldOptions());


		$submissionsCount =& $editorSubmissionDao->getEditorSubmissionsCount($press->getPressId());
		$templateMgr->assign('submissionsCount', $submissionsCount);
		$templateMgr->assign('helpTopicId', 'editorial.editorsRole');
		$templateMgr->display('editor/index.tpl');
	}
	function viewMetadata($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$submission =& $monographDao->getMonograph($monographId);
		//list($journal, $submission) = SubmissionEditHandler::validate($articleId);
		parent::setupTemplate();//true, $articleId, 'summary');
		import('submission.common.Action');
		Action::viewMetadata($submission, ROLE_ID_EDITOR);
	}
	function viewAuthorMetadata($args) {
		$authorId = isset($args[0]) ? (int) $args[0] : 0;
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author =& $authorDao->getAuthor($authorId);

		import('submission.common.Action');
		Action::viewAuthorMetadata($author, ROLE_ID_EDITOR);
	}
	function saveAuthorMetadata($args) {

		$authorId = isset($args[0]) ? (int) $args[0] : 0;
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author =& $authorDao->getAuthor($authorId);

		import('submission.common.Action');
		Action::saveAuthorMetadata($author, ROLE_ID_EDITOR);
	}
	/**
	 * Display editor submission queue pages.
	 */
	function submissions($args) {
		EditorHandler::validate();
		EditorHandler::setupTemplate(EDITOR_SECTION_SUBMISSIONS);

		$press =& Request::getPress();
		$pressId = $press->getPressId();
		$user =& Request::getUser();

		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
//		$sectionDao =& DAORegistry::getDAO('SectionDAO');

		$page = isset($args[0]) ? $args[0] : '';
//		$sections =& $sectionDao->getSectionTitles($pressId);

		$filterEditorOptions = array(
			FILTER_EDITOR_ALL => Locale::Translate('editor.allEditors'),
			FILTER_EDITOR_ME => Locale::Translate('editor.me')
		);

/*		$filterSectionOptions = array(
			FILTER_SECTION_ALL => Locale::Translate('editor.allSections')
		) + $sections;
 */
		// Get the user's search conditions, if any
		$searchField = Request::getUserVar('searchField');
		$dateSearchField = Request::getUserVar('dateSearchField');
		$searchMatch = Request::getUserVar('searchMatch');
		$search = Request::getUserVar('search');

		$fromDate = Request::getUserDateVar('dateFrom', 1, 1);
		if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
		$toDate = Request::getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
		if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

		$rangeInfo = PKPHandler::getRangeInfo('submissions');

		switch($page) {
			case 'submissionsUnassigned':
				$functionName = 'getEditorSubmissionsUnassigned';
				$helpTopicId = 'editorial.editorsRole.submissions.unassigned';
				break;
			case 'submissionsInEditing':
				$functionName = 'getEditorSubmissionsInEditing';
				$helpTopicId = 'editorial.editorsRole.submissions.inEditing';
				break;
			case 'submissionsArchives':
				$functionName = 'getEditorSubmissionsArchives';
				$helpTopicId = 'editorial.editorsRole.submissions.archives';
				break;
			default:
				$page = 'submissionsInReview';
				$functionName = 'getEditorSubmissionsInReview';
				$helpTopicId = 'editorial.editorsRole.submissions.inReview';
		}

		$filterEditor = Request::getUserVar('filterEditor');
		if ($filterEditor != '' && array_key_exists($filterEditor, $filterEditorOptions)) {
			$user->updateSetting('filterEditor', $filterEditor, 'int', $pressId);
		} else {
			$filterEditor = $user->getSetting('filterEditor', $pressId);
			if ($filterEditor == null) {
				$filterEditor = FILTER_EDITOR_ALL;
				$user->updateSetting('filterEditor', $filterEditor, 'int', $pressId);
			}	
		}

		if ($filterEditor == FILTER_EDITOR_ME) {
			$editorId = $user->getUserId();
		} else {
			$editorId = FILTER_EDITOR_ALL;
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

		$submissions =& $editorSubmissionDao->$functionName(
			$pressId,
			$filterSection,
			$editorId,
			$searchField,
			$searchMatch,
			$search,
			$dateSearchField,
			$fromDate,
			$toDate,
			$rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageToDisplay', $page);
		$templateMgr->assign('editor', $user->getFullName());
		$templateMgr->assign('editorOptions', $filterEditorOptions);
	//	$templateMgr->assign('sectionOptions', $filterSectionOptions);

		$templateMgr->assign_by_ref('submissions', $submissions);
		$templateMgr->assign('filterEditor', $filterEditor);
		$templateMgr->assign('filterSection', $filterSection);

		// Set search parameters
//		foreach (EditorHandler::getSearchFormDuplicateParameters() as $param)
//			$templateMgr->assign($param, Request::getUserVar($param));

		$templateMgr->assign('dateFrom', $fromDate);
		$templateMgr->assign('dateTo', $toDate);
//		$templateMgr->assign('fieldOptions', EditorHandler::getSearchFieldOptions());
//		$templateMgr->assign('dateFieldOptions', EditorHandler::getDateFieldOptions());

//		import('issue.IssueAction');
//		$issueAction = new IssueAction();
//		$templateMgr->register_function('print_issue_id', array($issueAction, 'smartyPrintIssueId'));

		$templateMgr->assign('helpTopicId', $helpTopicId);
		$templateMgr->display('editor/submissions.tpl');
	}


	/**
	 * Set the canEdit / canReview flags for this submission's edit assignments.
	 */
	function setEditorFlags($args) {
		EditorHandler::validate();

		$press =& Request::getPress();
		$monographId = (int) Request::getUserVar('articleId');

		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($monographId);

		if ($article && $article->getPressId() === $press->getPressId()) {
			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editAssignments =& $editAssignmentDao->getEditAssignmentsByArticleId($monographId);

			while($editAssignment =& $editAssignments->next()) {
				if ($editAssignment->getIsEditor()) continue;

				$canReview = Request::getUserVar('canReview-' . $editAssignment->getEditId()) ? 1 : 0;
				$canEdit = Request::getUserVar('canEdit-' . $editAssignment->getEditId()) ? 1 : 0;

				$editAssignment->setCanReview($canReview);
				$editAssignment->setCanEdit($canEdit);

				$editAssignmentDao->updateEditAssignment($editAssignment);
			}
		}

		Request::redirect(null, null, 'submission', $monographId);
	}



	/**
	 * Validate that user is an editor in the selected journal.
	 * Redirects to user index page if not properly authenticated.
	 */
	function validate() {
		$press =& Request::getPress();
		if (!isset($press) || !Validation::isEditor($press->getPressId())) {
			Validation::redirectLogin();
		}
	}

	function setupTemplate() {
		parent::setupTemplate();

	}
/*	function validate() {
		parent::validate();
		$journal =& Request::getJournal();
		// FIXME This is kind of evil
		$page = Request::getRequestedPage();
		if (!isset($journal) || ($page == 'sectionEditor' && !Validation::isSectionEditor($journal->getPressId())) || ($page == 'editor' && !Validation::isEditor($journal->getPressId()))) {
			Validation::redirectLogin();
		}
	}*/
	function submission($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
	//	list($journal, $submission) = $this->validate($monographId);
		$monographDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$submission = $monographDao->getAuthorSubmission($monographId);

		$journal =& Request::getPress();
		parent::setupTemplate(true, $monographId);
		$user =& Request::getUser();

		$journalSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$journalSettings = $journalSettingsDao->getPressSettings($journal->getPressId());

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->roleExists($journal->getPressId(), $user->getUserId(), ROLE_ID_EDITOR);

//		$sectionDao =& DAORegistry::getDAO('SectionDAO');
//		$section =& $sectionDao->getSection($submission->getSectionId());

		$enableComments = $journal->getSetting('enableComments');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('submission', $submission);
//		$templateMgr->assign_by_ref('section', $section);
		$templateMgr->assign_by_ref('authors', $submission->getAuthors());
		$templateMgr->assign_by_ref('submissionFile', $submission->getSubmissionFile());
//		$templateMgr->assign_by_ref('suppFiles', $submission->getSuppFiles());
//		$templateMgr->assign_by_ref('reviewFile', $submission->getReviewFile());
		$templateMgr->assign_by_ref('pressSettings', $journalSettings);
		$templateMgr->assign('userId', $user->getUserId());
		$templateMgr->assign('isEditor', $isEditor);
		$templateMgr->assign('enableComments', $enableComments);

//		$sectionDao =& DAORegistry::getDAO('SectionDAO');
//		$templateMgr->assign_by_ref('sections', $sectionDao->getSectionTitles($journal->getPressId()));
/*		if ($enableComments) {
			import('article.Article');
			$templateMgr->assign('commentsStatus', $submission->getCommentsStatus());
			$templateMgr->assign_by_ref('commentsStatusOptions', Article::getCommentsStatusOptions());
		}

		$publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');
		$publishedArticle =& $publishedArticleDao->getPublishedArticleByArticleId($submission->getArticleId());
		if ($publishedArticle) {
			$issueDao =& DAORegistry::getDAO('IssueDAO');
			$issue =& $issueDao->getIssueById($publishedArticle->getIssueId());
			$templateMgr->assign_by_ref('issue', $issue);
			$templateMgr->assign_by_ref('publishedArticle', $publishedArticle);
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
				$templateMgr->assign_by_ref('submissionPayment', $completedPaymentDAO->getSubmissionCompletedPayment ( $journal->getPressId(), $monographId ));
			}
			
			if ( $paymentManager->fastTrackEnabled()  ) {
				$templateMgr->assign_by_ref('fastTrackPayment', $completedPaymentDAO->getFastTrackCompletedPayment ( $journal->getPressId(), $monographId ));
			}

			if ( $paymentManager->publicationEnabled()  ) {
				$templateMgr->assign_by_ref('publicationPayment', $completedPaymentDAO->getPublicationCompletedPayment ( $journal->getPressId(), $monographId ));
			}				   
		}	*/	

		$templateMgr->display('editor/submission.tpl');
	}

}

?>
