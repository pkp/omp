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
