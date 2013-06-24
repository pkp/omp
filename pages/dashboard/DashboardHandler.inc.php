<?php
/**
 * @file pages/dashboard/DashboardHandler.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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

		$this->addRoleAssignment(array(ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_ASSISTANT),
				array('index', 'tasks', 'submissions', 'archives'));
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display about index page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function index($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->display('dashboard/index.tpl');
	}

	/**
	 * View tasks tab
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function tasks($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);

		// Get all the presses in the system, to determine which 'new submission' entry point we display
		$pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$presses = $pressDao->getAll();

		// Check each press to see if user has access to it.
		$user = $request->getUser();
		$roleDao = DAORegistry::getDAO('RoleDAO');
		$allContextsUserRoles = $roleDao->getByUserIdGroupedByContext($user->getId());
		$userRolesThatCanSubmit = array(ROLE_ID_AUTHOR, ROLE_ID_ASSISTANT, ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR);
		$accessiblePresses = array();
		while ($press = $presses->next()) {
			if (array_key_exists($press->getId(), $allContextsUserRoles)) {
				$pressContextUserRoles = array_keys($allContextsUserRoles[$press->getId()]);
				if (array_intersect($userRolesThatCanSubmit, $pressContextUserRoles)) {
					$accessiblePresses[] = $press;
				}
			}
		}

		// Assign presses to template.
		$pressCount = count($accessiblePresses);
		$templateMgr->assign('pressCount', $pressCount);
		if ($pressCount == 1) {
			$templateMgr->assign_by_ref('press', $accessiblePresses[0]);
		} elseif ($pressCount > 1) {
			$presses = array();
			foreach ($accessiblePresses as $press) {
				$url = $request->url($press->getPath(), 'submission', 'wizard');
				$presses[$url] = $press->getLocalizedName();
			}
			$templateMgr->assign_by_ref('presses', $presses);
		}

		return $templateMgr->fetchJson('dashboard/tasks.tpl');
	}

	/**
	 * View submissions tab
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submissions($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);

		return $templateMgr->fetchJson('dashboard/submissions.tpl');
	}

	/**
	 * View archives tab
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function archives($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);

		return $templateMgr->fetchJson('dashboard/archives.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $request PKPRequest
	 */
	function setupTemplate($request = null) {
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_APP_SUBMISSION);
		parent::setupTemplate($request);
	}
}

?>
