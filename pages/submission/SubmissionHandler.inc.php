<?php

/**
 * @file SubmissionHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHandler
 * @ingroup pages_submission
 *
 * @brief Handle requests for monograph submission functions.
 *
 * FIXME: #5807 Implement common user home page ("submission list")
 */


import('classes.handler.Handler');

class SubmissionHandler extends Handler {
	/**
	 * Constructor
	 */
	function SubmissionHandler() {
		parent::Handler();
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('index', 'details'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// FIXME: #5815 Specify permission policy for SubmissionHandler and implement its authorize() method.

		// Use a temporary user group based policy until #5815 is fixed.
		import('lib.pkp.classes.security.authorization.LoggedInWithValidUserGroupPolicy');
		$this->addPolicy(new LoggedInWithValidUserGroupPolicy($request));

		import('lib.pkp.classes.security.authorization.PolicySet');
		$temporaryRolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		foreach($roleAssignments as $role => $operations) {
			$temporaryRolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
		}
		$this->addPolicy($temporaryRolePolicy);
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Display index page (shows all submissions associated with user).
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index(&$args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();

		$press =& $request->getPress();

		$user =& Request::getUser();
		$rangeInfo =& Handler::getRangeInfo('submissions');
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');

		$page = isset($args[0]) ? $args[0] : '';
		switch($page) {
			case 'completed':
				$active = false;
				break;
			default:
				$page = 'active';
				$active = true;
		}

		$submissions = $authorSubmissionDao->getAuthorSubmissions($user->getId(), $press->getId(), $active, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageToDisplay', $page);
		if (!$active) {
			// Make view counts available if enabled.
			$templateMgr->assign('statViews', $press->getSetting('statViews'));
		}
		$templateMgr->assign_by_ref('submissions', $submissions);

 		$templateMgr->display('submission/index.tpl');
	}

	/**
	 * Displays the details of a single submission.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function details(&$args, &$request) {
		$monographId = array_shift($args);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		$this->setupTemplate();

		$user =& Request::getUser();
		$rangeInfo =& Handler::getRangeInfo('submissions');
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('monograph', $monograph);

 		$templateMgr->display('submission/details.tpl');
	}


	//
	// Protected helper methods
	//
	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false, $monographId = 0, $parentPage = null) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION));
		$templateMgr =& TemplateManager::getManager();

		$pageHierarchy = $subclass ? array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'author'), 'user.role.author'), array(Request::url(null, 'author'), 'manuscript.submissions'))
			: array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'author'), 'user.role.author'));

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$submissionCrumb = SeriesEditorAction::submissionBreadcrumb($monographId, $parentPage, 'author');
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}

		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}

?>
