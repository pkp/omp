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

define('EDITOR_ARRANGEMENT_HOME', 0);
define('EDITOR_ARRANGEMENT_SUBMISSIONS', 1);

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

import('acquisitionsEditor.AcquisitionsEditorHandler');
import('submission.editor.EditorAction');

class EditorHandler extends AcquisitionsEditorHandler {
	/**
	 * Constructor
	 */
	function EditorHandler() {
		parent::AcquisitionsEditorHandler();

		$this->addCheck(new HandlerValidatorPress($this));
		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_EDITOR)));
	}

	/**
	 * Displays the editor role selection page.
	 */

	function index($args) {
		$this->validate();
		$this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$press =& Request::getPress();
		$pressId = $press->getId();
		$user =& Request::getUser();

		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');

		$submissionsCount =& $editorSubmissionDao->getCount($press->getId());
		$templateMgr->assign('submissionsCount', $submissionsCount);
		$templateMgr->assign('helpTopicId', 'editorial.editorsRole');
		$templateMgr->display('editor/index.tpl');
	}
	
	function viewMetadata($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$submission =& $monographDao->getMonograph($monographId);
		$this->validate();
		$this->setupTemplate(EDITOR_ARRANGEMENT_SUBMISSIONS);
		
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR));
		import('submission.common.Action');
		Action::viewMetadata($submission);
	}

	function selectReviewer($args) {
		import('pages.acquisitionsEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectReviewer($args);
	}
	
	/**
	 * Display editor submission queue pages.
	 */
	function submissions($args) {
		$this->validate();
		$this->setupTemplate(EDITOR_ARRANGEMENT_HOME);

		$press =& Request::getPress();
		$pressId = $press->getId();
		$user =& Request::getUser();

		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');

		$page = isset($args[0]) ? $args[0] : '';

		$rangeInfo = Handler::getRangeInfo('submissions');

		switch($page) {
			case 'submissionsUnassigned':
				$functionName = 'getUnassigned';
				$helpTopicId = 'editorial.editorsRole.submissions.unassigned';
				break;
			case 'submissionsInEditing':
				$functionName = 'getInEditing';
				$helpTopicId = 'editorial.editorsRole.submissions.inEditing';
				break;
			case 'submissionsArchives':
				$functionName = 'getArchives';
				$helpTopicId = 'editorial.editorsRole.submissions.archives';
				break;
			default:
				$page = 'submissionsInReview';
				$functionName = 'getInReview';
				$helpTopicId = 'editorial.editorsRole.submissions.inReview';
		}

		// TODO: nulls represent search options which have not yet been implemented
		$submissions =& $editorSubmissionDao->$functionName(
			$pressId,
			FILTER_EDITOR_ALL,
			null,
			null,
			null,
			null,
			null,
			null,
			$rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageToDisplay', $page);
		$templateMgr->assign('editor', $user->getFullName());

		$templateMgr->assign_by_ref('submissions', $submissions);

		$templateMgr->assign('helpTopicId', $helpTopicId);
		$templateMgr->display('editor/submissions.tpl');
	}

	/**
	 * Delete the specified edit assignment.
	 */
	function deleteEditAssignment($args) {
		$this->validate();

		$press =& Request::getPress();
		$editId = (int) (isset($args[0])?$args[0]:0);

		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignment =& $editAssignmentDao->getById($editId);

		if ($editAssignment) {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getMonograph($editAssignment->getMonographId());

			if ($monograph && $monograph->getPressId() === $press->getId()) {
				$editAssignmentDao->deleteById($editAssignment->getEditId());
				Request::redirect(null, null, 'submission', $monograph->getMonographId());
			}
		}

		Request::redirect(null, null, 'submissions');
	}

	/**
	 * Assigns the selected editor to the submission.
	 */
	function assignEditor($args) {
		$this->validate();
		$press =& Request::getPress();
		$monographId = Request::getUserVar('monographId');
		$editorId = Request::getUserVar('editorId');
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$submission =& $this->submission;

		$isAcquisitionsEditor = $roleDao->roleExists($press->getId(), $editorId, ROLE_ID_ACQUISITIONS_EDITOR);
		$isEditor = $roleDao->roleExists($press->getId(), $editorId, ROLE_ID_EDITOR);

		if (isset($editorId) && $editorId != null && ($isEditor || $isAcquisitionsEditor)) {
			// A valid acquisitions editor has already been chosen;
			// either prompt with a modifiable email or, if this
			// has been done, send the email and store the editor
			// selection.

			$this->setupTemplate(EDITOR_ARRANGEMENT_SUBMISSIONS, $monographId, 'summary');

			$workflowDao =& DAORegistry::getDAO('WorkflowDAO');

			$workflowDao->build(
					$monographId,
					WORKFLOW_PROCESS_ASSESSMENT,
					null
				);
			$workflowDao->build(
					$monographId, 
					WORKFLOW_PROCESS_ASSESSMENT, 
					WORKFLOW_PROCESS_ASSESSMENT_INTERNAL, 
					WORKFLOW_PROCESS_STATUS_CURRENT
				);
			
			if (EditorAction::assignEditor($monographId, $editorId, $isEditor, Request::getUserVar('send'))) {
				Request::redirect(null, null, 'submission', $monographId);
			}
		} else {
			// Allow the user to choose a acquisitions editor or editor.
			$this->setupTemplate(EDITOR_ARRANGEMENT_SUBMISSIONS, $monographId, 'summary');

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

			$rangeInfo =& Handler::getRangeInfo('editors');
			$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');

			if (isset($args[0]) && $args[0] === 'editor') {
				$roleName = 'user.role.editor';
				$editors =& $editorSubmissionDao->getUsersNotAssignedToMonograph($press->getId(), $monographId, RoleDAO::getRoleIdFromPath('editor'), $searchType, $search, $searchMatch, $rangeInfo);
			} else {
				$roleName = 'user.role.acquisitionsEditor';
				$editors =& $editorSubmissionDao->getUsersNotAssignedToMonograph($press->getId(), $monographId, RoleDAO::getRoleIdFromPath('acquisitionsEditor'), $searchType, $search, $searchMatch, $rangeInfo);
			}

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign_by_ref('editors', $editors);
			$templateMgr->assign('roleName', $roleName);
			$templateMgr->assign('monographId', $monographId);

			$acquisitionsArrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
			$acquisitionsEditorArrangements =& $acquisitionsArrangementDao->getEditorAcquisitionArrangements($press->getId());

			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editorStatistics = $editAssignmentDao->getEditorStatistics($press->getId());

			$templateMgr->assign_by_ref('editorArrangements', $acquisitionsEditorArrangements);
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
		$this->validate();

		$press =& Request::getPress();
		$monographId = (int) Request::getUserVar('monographId');

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		if ($monograph && $monograph->getPressId() === $press->getId()) {
			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editAssignments =& $editAssignmentDao->getByMonographId($monographId);

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

	function setupTemplate($level = EDITOR_ARRANGEMENT_HOME, $monographId = 0, $parentPage = null) {
		parent::setupTemplate();
		// Layout Editors have access to some management functions. Make sure we give them
		// the appropriate breadcrumbs and sidebar.
		$isLayoutEditor = Request::getRequestedPage() == 'layoutEditor';

		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();

		if ($level == EDITOR_ARRANGEMENT_HOME) $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'));
		else if ($level == EDITOR_ARRANGEMENT_SUBMISSIONS) $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'editor'), 'user.role.editor'), array(Request::url(null, 'editor', 'submissions'), 'manuscript.submissions'));

		import('submission.acquisitionsEditor.AcquisitionsEditorAction');
		$submissionCrumb = AcquisitionsEditorAction::submissionBreadcrumb($monographId, $parentPage, 'editor');
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);

	}
}

?>
