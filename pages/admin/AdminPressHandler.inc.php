<?php

/**
 * @file pages/admin/AdminPressHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminPressHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for press management in site administration.
 */

import('pages.admin.AdminHandler');

class AdminPressHandler extends AdminHandler {
	function AdminPressHandler() {

		parent::AdminHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_SITE_ADMIN),
			array('presses')
		);
	}

	/**
	 * Display a list of the presses hosted on the site.
	 */
	function presses($args, &$request) {
		$this->setupTemplate($request, true);

		$openWizard = $request->getUserVar('openWizard');

		// Get the open wizard link action.
		import('lib.pkp.classes.linkAction.request.WizardModal');

		$openWizardLinkAction = null;
		if ($openWizard) {
			$dispatcher =& $request->getDispatcher();
			$ajaxModal = new WizardModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null,
						'wizard.settings.PressSettingsWizardHandler', 'startWizard', null),
				__('manager.settings.wizard')
			);

			$openWizardLinkAction = new LinkAction(
				'openWizard',
				$ajaxModal,
				__('manager.settings.wizard'),
				null
			);
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('openWizardLinkAction', $openWizardLinkAction);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
		$templateMgr->display('admin/presses.tpl');
	}
}

?>
