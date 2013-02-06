<?php

/**
 * @file pages/admin/AdminContextHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminContextHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for context management in site administration.
 */

import('lib.pkp.pages.admin.PKPAdminContextHandler');

class AdminContextHandler extends PKPAdminContextHandler {
	/**
	 * Constructor
	 */
	function AdminContextHandler() {
		parent::PKPAdminContextHandler();
	}

	/**
	 * Display a list of the contexts hosted on the site.
	 */
	function contexts($args, $request) {
		$openWizard = $request->getUserVar('openWizard');

		// Get the open wizard link action.
		import('lib.pkp.classes.linkAction.request.WizardModal');

		$openWizardLinkAction = null;
		if ($openWizard) {
			$dispatcher = $request->getDispatcher();
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

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('openWizardLinkAction', $openWizardLinkAction);

		return parent::contexts($args, $request);
	}
}

?>
