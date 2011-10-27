<?php

/**
 * @file pages/manager/ManagerHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManagerHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for press management functions.
 */


import('classes.handler.Handler');

class ManagerHandler extends Handler {
	/**
	 * Constructor
	 */
	function ManagerHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'index'
			)
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display press management index page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->setupTemplate($request);

		$press =& $request->getPress();
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$announcementsEnabled = $pressSettingsDao->getSetting($press->getId(), 'enableAnnouncements');
		$customSignoffInternal = $pressSettingsDao->getSetting($press->getId(), 'useCustomInternalReviewSignoff');
		$customSignoffExternal = $pressSettingsDao->getSetting($press->getId(), 'useCustomExternalReviewSignoff');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('customSignoffEnabled', $customSignoffInternal || $customSignoffExternal );

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByContextId($press->getId());
		$templateMgr->assign_by_ref('userGroups', $userGroups);

		$session =& $request->getSession();
		$session->unsetSessionVar('enrolmentReferrer');

		$templateMgr->assign('announcementsEnabled', $announcementsEnabled);
		$templateMgr->assign('helpTopicId','press.index');
		$templateMgr->display('manager/index.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $request PKPRequest
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate();
		AppLocale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy',
			$subclass ? array(array($request->url(null, 'user'), 'navigation.user'), array($request->url(null, 'manager'), 'manager.pressManagement'))
				: array(array($request->url(null, 'user'), 'navigation.user'))
		);
	}
}

?>
