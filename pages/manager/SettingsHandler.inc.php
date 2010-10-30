<?php
/**
 * @file pages/manager/SettingsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for OMP settings.
 */

import('lib.pkp.classes.core.JSON');
import('pages.manager.ManagerHandler');

class SettingsHandler extends ManagerHandler {
	/**
	 * Constructor
	 */
	function SettingsHandler() {
		parent::ManagerHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
					'settings',
					'data',
					'system',
				)
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		return parent::authorize($request, $args, $roleAssignments);
	}
 
   /**
	 * Display settings index page.
	 */
	function settings(&$request, &$args) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);
		$templateMgr->display('manager/settings/index.tpl');
	}

	/**
	 * Display data settings index page.
	 */
	function data(&$request, &$args) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);
		$templateMgr->display('manager/data/index.tpl');
	}

	/**
	 * Display system settings index page.
	 */
	function system(&$request, &$args) {
		$templateMgr =& TemplateManager::getManager();
		$this->setupTemplate(true);
		$templateMgr->display('manager/system/index.tpl');
	}
}

?>
