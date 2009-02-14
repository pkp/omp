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

import('acquisitionsEditor.AcquisitionsEditorHandler');
import ('submission.editor.EditorAction');


define('EDITOR_SECTION_HOME', 0);
define('EDITOR_SECTION_SUBMISSIONS', 1);
define('EDITOR_SECTION_ISSUES', 2);

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

class EditorHandler extends AcquisitionsEditorHandler {

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
		//list($press, $submission) = SubmissionEditHandler::validate($monographId);
		parent::setupTemplate();//true, $monographId, 'summary');
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
	function selectReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectReviewer($args);
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
	 * Delete the specified edit assignment.
	 */
	function deleteEditAssignment($args) {
		EditorHandler::validate();

		$press =& Request::getPress();
		$editId = (int) (isset($args[0])?$args[0]:0);

		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignment =& $editAssignmentDao->getEditAssignment($editId);

		if ($editAssignment) {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getMonograph($editAssignment->getMonographId());

			if ($monograph && $monograph->getPressId() === $press->getPressId()) {
				$editAssignmentDao->deleteEditAssignmentById($editAssignment->getEditId());
				Request::redirect(null, null, 'submission', $monograph->getMonographId());
			}
		}

		Request::redirect(null, null, 'submissions');
	}

	/**
	 * Assigns the selected editor to the submission.
	 */
	function assignEditor($args) {
		EditorHandler::validate();

		$press =& Request::getPress();
		$monographId = Request::getUserVar('monographId');
		$editorId = Request::getUserVar('editorId');
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		$isSectionEditor = $roleDao->roleExists($press->getPressId(), $editorId, ROLE_ID_ACQUISITIONS_EDITOR);
		$isEditor = $roleDao->roleExists($press->getPressId(), $editorId, ROLE_ID_EDITOR);

		if (isset($editorId) && $editorId != null && ($isEditor || $isSectionEditor)) {
			// A valid section editor has already been chosen;
			// either prompt with a modifiable email or, if this
			// has been done, send the email and store the editor
			// selection.

			EditorHandler::setupTemplate(EDITOR_SECTION_SUBMISSIONS, $monographId, 'summary');

			// FIXME: Prompt for due date.
			if (EditorAction::assignEditor($monographId, $editorId, $isEditor, Request::getUserVar('send'))) {
				Request::redirect(null, null, 'submission', $monographId);
			}
		} else {
			// Allow the user to choose a section editor or editor.
			EditorHandler::setupTemplate(EDITOR_SECTION_SUBMISSIONS, $monographId, 'summary');

			$searchType = null;
			$searchMatch = null;
			$search = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (isset($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} else if (isset($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$rangeInfo =& PKPHandler::getRangeInfo('editors');
			$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');

			if (isset($args[0]) && $args[0] === 'editor') {
				$roleName = 'user.role.editor';
				$editors =& $editorSubmissionDao->getUsersNotAssignedToMonograph($press->getPressId(), $monographId, RoleDAO::getRoleIdFromPath('editor'), $searchType, $search, $searchMatch, $rangeInfo);
			} else {
				$roleName = 'user.role.sectionEditor';
				$editors =& $editorSubmissionDao->getUsersNotAssignedToMonograph($press->getPressId(), $monographId, RoleDAO::getRoleIdFromPath('sectionEditor'), $searchType, $search, $searchMatch, $rangeInfo);
			}

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign_by_ref('editors', $editors);
			$templateMgr->assign('roleName', $roleName);
			$templateMgr->assign('monographId', $monographId);

//			$sectionDao =& DAORegistry::getDAO('SectionDAO');
//			$sectionEditorSections =& $sectionDao->getEditorSections($press->getPressId());

			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editorStatistics = $editAssignmentDao->getEditorStatistics($press->getPressId());

			$templateMgr->assign_by_ref('editorSections', $sectionEditorSections);
			$templateMgr->assign('editorStatistics', $editorStatistics);

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $search);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
			$templateMgr->assign('helpTopicId', 'editorial.editorsRole.submissionSummary.submissionManagement');	
			$templateMgr->display('editor/selectAcquisitionsEditor.tpl');
		}
	}

	/**
	 * Set the canEdit / canReview flags for this submission's edit assignments.
	 */
	function setEditorFlags($args) {
		EditorHandler::validate();

		$press =& Request::getPress();
		$monographId = (int) Request::getUserVar('monographId');

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		if ($monograph && $monograph->getPressId() === $press->getPressId()) {
			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editAssignments =& $editAssignmentDao->getEditAssignmentsByMonographId($monographId);

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

	function setupTemplate($level = EDITOR_SECTION_HOME, $monographId = 0, $parentPage = null) {
		parent::setupTemplate();
		// Layout Editors have access to some Issue Mgmt functions. Make sure we give them
		// the appropriate breadcrumbs and sidebar.
		$isLayoutEditor = Request::getRequestedPage() == 'layoutEditor';

		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();

		if ($level==EDITOR_SECTION_HOME) $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'));
		else if ($level==EDITOR_SECTION_SUBMISSIONS) $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'editor'), 'user.role.editor'), array(Request::url(null, 'editor', 'submissions'), 'manuscript.submissions'));
		else if ($level==EDITOR_SECTION_ISSUES) $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, $isLayoutEditor?'layoutEditor':'editor'), $isLayoutEditor?'user.role.layoutEditor':'user.role.editor'), array(Request::url(null, $isLayoutEditor?'layoutEditor':'editor', 'futureIssues'), 'issue.issues'));

		import('submission.acquisitionsEditor.AcquisitionsEditorAction');
		$submissionCrumb = AcquisitionsEditorAction::submissionBreadcrumb($monographId, $parentPage, 'editor');
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);

	}
	function validate() {
		
		$press =& Request::getPress();
		// FIXME This is kind of evil
		$page = Request::getRequestedPage();
		if (!isset($press) || ($page == 'acquisitionsEditor' && !Validation::isAcquisitionsEditor($press->getPressId())) || ($page == 'editor' && !Validation::isEditor($press->getPressId()))) {
			Validation::redirectLogin();
		}
	}
	function userProfile($args) {
		import('pages.acquisitionsEditor.AcquisitionsEditorHandler');
		AcquisitionsEditorHandler::userProfile($args);
	}
	function submissionReview($args) {
		import('pages.acquisitionsEditor.AcquisitionsEditorHandler');
		AcquisitionsEditorHandler::submissionReview($args);
	}


}

?>