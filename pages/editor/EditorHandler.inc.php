<?php

/**
 * @file EditorHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorHandler
 * @ingroup pages_editor
 *
 * @brief Handle requests for editor functions.
 */


// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

import('pages.seriesEditor.SeriesEditorHandler');
import('classes.submission.editor.EditorAction');

class EditorHandler extends SeriesEditorHandler {
	/**
	 * Constructor
	 */
	function EditorHandler() {
		parent::SeriesEditorHandler();

		$this->addCheck(new HandlerValidatorPress($this));
		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_EDITOR)));
	}

	function viewMetadata($args) {
		$monographId = isset($args[0]) ? (int) $args[0] : 0;
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$submission =& $monographDao->getMonograph($monographId);
		$this->validate();
		$this->setupTemplate(EDITOR_SERIES_SUBMISSIONS);

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));
		import('classes.submission.common.Action');
		Action::viewMetadata($submission);
	}

	function selectReviewer($args) {
		import('pages.seriesEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectReviewer($args);
	}

	/**
	 * Display editor submission queue pages.
	 */
	function submissions($args) {
		$this->validate();
		$this->setupTemplate(EDITOR_SERIES_HOME);

		$press =& Request::getPress();
		$pressId = $press->getId();
		$user =& Request::getUser();

		$sort = Request::getUserVar('sort');
		$sort = isset($sort) ? $sort : 'id';
		$sortDirection = Request::getUserVar('sortDirection');
		$sortDirection = (isset($sortDirection) && ($sortDirection == 'ASC' || $sortDirection == 'DESC')) ? $sortDirection : 'ASC';

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
			null,
			$rangeInfo,
			$sort,
			$sortDirection
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageToDisplay', $page);
		$templateMgr->assign('editor', $user->getFullName());

		$templateMgr->assign_by_ref('submissions', $submissions);

		$templateMgr->assign('helpTopicId', $helpTopicId);
		$templateMgr->display('editor/submissions.tpl');
	}

	/**
	 * Set the canEdit / canReview flags for this submission's edit assignments.
	 */
	function setEditorFlags($args) {
		// FIXME #5880: Get IDs from signoffDao, or remove this class if not needed
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

	function setupTemplate($level = EDITOR_SERIES_HOME, $monographId = 0, $parentPage = null) {
		parent::setupTemplate();
		// Layout Editors have access to some management functions. Make sure we give them
		// the appropriate breadcrumbs and sidebar.
		$isLayoutEditor = Request::getRequestedPage() == 'layoutEditor';

		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();

		if ($level == EDITOR_SERIES_HOME) $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'));
		else if ($level == EDITOR_SERIES_SUBMISSIONS) $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'editor'), 'user.role.editor'), array(Request::url(null, 'editor', 'submissions'), 'manuscript.submissions'));

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$submissionCrumb = SeriesEditorAction::submissionBreadcrumb($monographId, $parentPage, 'editor');
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);

	}
}

?>
