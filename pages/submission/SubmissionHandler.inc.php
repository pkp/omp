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
		$this->addRoleAssignment(array(ROLE_ID_REVIEWER), $reviewerOperations = array('index'));
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array_merge($reviewerOperations, array('details')));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		$router =& $request->getRouter();
		$operation = $router->getRequestedOp($request);

		switch($operation) {
			case 'index':
				// The user only needs press-level permission to see a list
				// of submissions.
				import('classes.security.authorization.OmpPressAccessPolicy');
				$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
				break;

			default:
				// All other operations require full submission access.
				import('classes.security.authorization.OmpSubmissionAccessPolicy');
				$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		}
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Display index page (shows all submissions associated with user).
	 * FIXME: This operation does not have a spec, see #5849
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index(&$args, &$request) {
		// Set up the template.
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate();

		// Retrieve the authorized user group.
		$activeUserGroup =& $this->getAuthorizedContextObject(ASSOC_TYPE_USER_GROUP);
		assert(is_a($activeUserGroup, 'UserGroup'));

		// Display the submission list according to the
		// role of the active user group.
		$templateMgr->assign('roleId', $activeUserGroup->getRoleId());
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
	 */
	function setupTemplate($parentPage = null) {
		parent::setupTemplate();

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION));

		$templateMgr =& TemplateManager::getManager();
		$pageHierarchy = array(
			array(Request::url(null, 'user'), 'navigation.user'),
			array(Request::url(null, 'author'), 'manuscript.submissions')
		);
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}

?>
