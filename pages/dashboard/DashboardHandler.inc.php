<?php
/**
 * @file pages/dashboard/DashboardHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DashboardHandler
 * @ingroup pages_dashboard
 *
 * @brief Handle requests for user's dashboard.
 */

import('handler.Handler');
import('lib.pkp.classes.core.JSON');

class DashboardHandler extends Handler {
	/**
	 * Constructor
	 */
	function DashboardHandler() {
		parent::Handler();

		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER,
				array('index', 'overview', 'tasks', 'status'));
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		// FIXME: Implement site access policy
		return true;
		/*import('classes.security.authorization.OmpSiteAccessPolicy');
		$this->addPolicy(new OmpSiteAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);*/
	}

	/**
	 * Display about index page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function index($args, &$request) {
		$templateMgr = &TemplateManager::getManager();
		$this->setupTemplate();

		$templateMgr->assign('selectedTab', 1);
		$templateMgr->assign('pageToDisplay', 'dashboard/overview.tpl');
		$templateMgr->display('dashboard/index.tpl');
	}

	/**
	 * View overview tab
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function overview($args, &$request) {
		$templateMgr = &TemplateManager::getManager();
		$this->setupTemplate();

		$templateMgr->assign('selectedTab', 1);
		$templateMgr->assign('pageToDisplay', 'dashboard/overview.tpl');
		$templateMgr->display('dashboard/index.tpl');
	}

	/**
	 * View tasks tab
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function tasks($args, &$request) {
		$templateMgr = &TemplateManager::getManager();
		$this->setupTemplate();

		$templateMgr->assign('selectedTab', 2);
		$templateMgr->assign('pageToDisplay', 'dashboard/tasks.tpl');
		$templateMgr->display('dashboard/index.tpl');
	}

	/**
	 * View status tab
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function status($args, &$request) {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		$templateMgr = &TemplateManager::getManager();
		$this->setupTemplate();

		$templateMgr->assign('selectedTab', 3);
		$templateMgr->assign('pageToDisplay', 'dashboard/status.tpl');
		$templateMgr->display('dashboard/index.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER));

		if ($subclass) $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'dashboard'), 'dashboard.dashboard')));
	}
}

?>
