<?php
/**
 * @file pages/dashboard/DashboardHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DashboardHandler
 * @ingroup pages_dashboard
 *
 * @brief Handle requests for user's dashboard.
 */

import('classes.handler.Handler');

class DashboardHandler extends Handler {
	/**
	 * Constructor
	 */
	function DashboardHandler() {
		parent::Handler();

		$this->addRoleAssignment(array(ROLE_ID_SITE_ADMIN, ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_PRESS_ASSISTANT),
				array('index', 'overview', 'tasks', 'submissions'));
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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function overview($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate($request);

		$templateMgr->assign('selectedTab', 1);
		$templateMgr->assign('templateToDisplay', 'dashboard/overview.tpl');
		$templateMgr->display('dashboard/index.tpl');
	}

	/**
	 * View tasks tab
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function tasks($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate($request);

		$templateMgr->assign('selectedTab', 2);
		$templateMgr->assign('templateToDisplay', 'dashboard/tasks.tpl');
		$templateMgr->display('dashboard/index.tpl');
	}

	/**
	 * View submissions tab
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submissions($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate($request);

		// Get all the presses in the system, to determine which 'new submission' entry point we display
		$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$presses = $pressDao->getPresses();

		// Check each press to see if user has access to it.
		$user =& $request->getUser();
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$allContextsUserRoles = $roleDao->getByUserIdGroupedByContext($user->getId());
		$userRolesThatCanSubmit = array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR);
		$accessiblePresses = array();
		while ($press =& $presses->next()) {
			if (array_key_exists($press->getId(), $allContextsUserRoles)) {
				$pressContextUserRoles = array_keys($allContextsUserRoles[$press->getId()]);
				if (array_intersect($userRolesThatCanSubmit, $pressContextUserRoles)) {
					$accessiblePresses[] =& $press;
				}
			}
			unset($press);
		}

		// Assign presses to template.
		$pressCount = count($accessiblePresses);
		$templateMgr->assign('pressCount', $pressCount);
		if ($pressCount == 1) {
			$templateMgr->assign_by_ref('press', $accessiblePresses[0]);
		} elseif ($pressCount > 1) {
			$templateMgr->assign_by_ref('presses', $accessiblePresses);
		}

		$templateMgr->assign('selectedTab', 3);
		$templateMgr->assign('templateToDisplay', 'dashboard/submissions.tpl');
		$templateMgr->display('dashboard/index.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $request PKPRequest
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_SUBMISSION);

		if ($subclass) $templateMgr->assign('pageHierarchy', array(array($request->url(null, 'dashboard'), 'dashboard.dashboard')));
	}
}

?>
