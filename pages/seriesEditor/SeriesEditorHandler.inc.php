<?php

/**
 * @file SeriesEditorHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorHandler
 * @ingroup pages_seriesEditor
 *
 * @brief Handle requests for series editor functions.
 */



// Filter series
define('FILTER_SERIES_ALL', 0);

define('EDITOR_SERIES_HOME', 0);
define('EDITOR_SERIES_SUBMISSIONS', 1);

import('classes.handler.Handler');

class SeriesEditorHandler extends Handler {
	/**
	 * Constructor
	 */
	 function SeriesEditorHandler() {
	 	parent::Handler();

		$this->addCheck(new HandlerValidatorPress($this));
		// FIXME This is kind of evil
		$page = Request::getRequestedPage();
		if ( $page == 'seriesEditor' )
			$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_SERIES_EDITOR)));
		elseif ( $page == 'editor' )
			$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_EDITOR)));
	 }

	/**
	 * Display series editor index page.
	 */
	function index($args) {
		$this->validate();
		$this->setupTemplate();

		$press =& Request::getPress();
		$pressId = $press->getId();
		$user =& Request::getUser();

		$rangeInfo = Handler::getRangeInfo('submissions');

		// Get the user's search conditions, if any
		$searchField = Request::getUserVar('searchField');
		$dateSearchField = Request::getUserVar('dateSearchField');
		$searchMatch = Request::getUserVar('searchMatch');
		$search = Request::getUserVar('search');

		$fromDate = Request::getUserDateVar('dateFrom', 1, 1);
		if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
		$toDate = Request::getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
		if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');

		$page = isset($args[0]) ? $args[0] : '';
		$series =& $seriesDao->getTitlesByPressId($press->getId());

		$sort = Request::getUserVar('sort');
		$sort = isset($sort) ? $sort : 'id';
		$sortDirection = Request::getUserVar('sortDirection');

		$filterSeriesOptions = array(
			FILTER_SERIES_ALL => Locale::Translate('editor.allSeries')
		) + $series;

		switch($page) {
			case 'submissionsInEditing':
				$functionName = 'getSeriesEditorSubmissionsInEditing';
				$helpTopicId = 'editorial.seriesEditorsRole.submissions.inEditing';
				break;
			case 'submissionsArchives':
				$functionName = 'getSeriesEditorSubmissionsArchives';
				$helpTopicId = 'editorial.seriesEditorsRole.submissions.archives';
				break;
			default:
				$page = 'submissionsInReview';
				$functionName = 'getSeriesEditorSubmissionsInReview';
				$helpTopicId = 'editorial.seriesEditorsRole.submissions.inReview';
		}

		$filterSeries = Request::getUserVar('filterSeries');
		if ($filterSeries != '' && array_key_exists($filterSeries, $filterSeriesOptions)) {
			$user->updateSetting('filterSeries', $filterSeries, 'int', $pressId);
		} else {
			$filterSeries = $user->getSetting('filterSeries', $pressId);
			if ($filterSeries == null) {
				$filterSeries = FILTER_SERIES_ALL;
				$user->updateSetting('filterSeries', $filterSeries, 'int', $pressId);
			}
		}

		$submissions =& $seriesEditorSubmissionDao->$functionName(
			$user->getId(),
			$press->getId(),
			$filterSeries,
			$searchField,
			$searchMatch,
			$search,
			$dateSearchField,
			$fromDate,
			$toDate,
			$rangeInfo,
			$sort,
			$sortDirection
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', $helpTopicId);
		$templateMgr->assign('seriesOptions', $filterSeriesOptions);
		$templateMgr->assign_by_ref('submissions', $submissions);
		$templateMgr->assign('filterSeries', $filterSeries);
		$templateMgr->assign('pageToDisplay', $page);
		$templateMgr->assign('seriesEditor', $user->getFullName());

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
			SUBMISSION_FIELD_TITLE => 'monograph.title',
			SUBMISSION_FIELD_AUTHOR => 'user.role.author',
			SUBMISSION_FIELD_EDITOR => 'user.role.editor'
		));
		$templateMgr->assign('dateFieldOptions', Array(
			SUBMISSION_FIELD_DATE_SUBMITTED => 'submissions.submitted',
			SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE => 'submissions.copyeditComplete',
			SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE => 'submissions.layoutComplete',
			SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE => 'submissions.proofreadingComplete'
		));

		$templateMgr->assign('sort', $sort);
		$templateMgr->assign('sortDirection', $sortDirection);

		$templateMgr->display('seriesEditor/index.tpl');
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

		if (($page = Request::getRequestedPage()) == 'editor') {
			$templateMgr->assign('helpTopicId', 'editorial.editorsRole');

		} else {
			$templateMgr->assign('helpTopicId', 'editorial.seriesEditorsRole');
		}

		$pageHierarchy = $subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, $isEditor?'editor':'seriesEditor'), $isEditor?'user.role.editor':'user.role.seriesEditor'), array(Request::url(null, 'seriesEditor'), 'manuscript.submissions'))
			: array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, $isEditor?'editor':'seriesEditor'), $isEditor?'user.role.editor':'user.role.seriesEditor'));

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$submissionCrumb = SeriesEditorAction::submissionBreadcrumb($monographId, $parentPage, $page);
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}

?>
