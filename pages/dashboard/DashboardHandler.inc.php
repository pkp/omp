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

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSON');

class DashboardHandler extends Handler {
	/**
	 * Constructor
	 */
	function DashboardHandler() {
		parent::Handler();

		$this->addRoleAssignment(array(ROLE_ID_SITE_ADMIN, ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_PRESS_ASSISTANT),
				array('index', 'overview', 'tasks', 'status'));
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display about index page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function index($args, &$request) {
		$this->overview($args, $request);
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
		$templateMgr = &TemplateManager::getManager();
		$this->setupTemplate();

		// Get all the presses in the system, to determine which 'new submission' entry point we display
		$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$presses = $pressDao->getPresses();

		$pressCount = $presses->getCount();
		$templateMgr->assign('pressCount', $pressCount);
		if ($pressCount == 1) {
			$press =& $presses->next();
			$templateMgr->assign_by_ref('press', $press);
		} else {
			$templateMgr->assign_by_ref('presses', $presses);
		}

		// Get the roles of the current user. This is necessary
		// as the user may not have access to all grids in the
		// status tab.
		$user =& $request->getUser();
		$roleDao =& DAORegistry::getDAO('RoleDAO'); /* @var $roleDao RoleDAO */
		$roles =& $roleDao->getByUserId($user->getId());
		$roleIds = array();
		foreach($roles as $role) {
			$roleIds[] = $role->getId();
		}
		$templateMgr->assign_by_ref('roleIds', $roleIds);

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
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_SUBMISSION));

		if ($subclass) $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'dashboard'), 'dashboard.dashboard')));
	}
}

?>
