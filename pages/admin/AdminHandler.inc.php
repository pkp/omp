<?php

/**
 * @file pages/admin/AdminHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for site administration functions.
 */



import('classes.handler.Handler');

class AdminHandler extends Handler {
	/**
	 * Constructor
	 */
	function AdminHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SITE_ADMIN),
			array('index', 'settings')
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		$returner = parent::authorize($request, $args, $roleAssignments);

		// Make sure user is in press context. Otherwise, redirect.
		$press =& $request->getPress();
		$router =& $request->getRouter();
		$requestedOp = $router->getRequestedOp($request);

		// The only operation logged users may access outside a press
		// context is to create presses.
		if (!$press && $requestedOp !== 'presses') {

			// Try to find a press that user has access to.
			$targetPress =& $this->getTargetPress($request);
			if ($targetPress) {
				$url = $router->url($request, $targetPress->getPath(), 'admin', $requestedOp);
			} else {
				$url = $router->url($request, 'index');
			}
			$request->redirectUrl($url);
		}

		return $returner;
	}

	/**
	 * Display site admin index page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.index');

		$templateMgr->display('admin/index.tpl');
	}

	/**
	 * Display the administration settings page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function settings($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate($request, true);
		$templateMgr->display('admin/settings.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate();
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_ADMIN, LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_OMP_ADMIN);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy',
			$subclass ? array(array($request->url(null, 'user'), 'navigation.user'), array($request->url(null, 'admin'), 'admin.siteAdmin'))
				: array(array($request->url(null, 'user'), 'navigation.user'))
		);
	}
}

?>
