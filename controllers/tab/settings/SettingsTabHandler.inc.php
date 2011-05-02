<?php

/**
 * @file controllers/tab/settings/SettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on settings pages.
 */

// Import the base Handler.
import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class SettingsTabHandler extends Handler {

	/**
	 * Constructor
	 */
	function SettingsTabHandler() {
		parent::Handler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array('saveData'));
	}


	//
	// Overridden methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Load grid-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Handle forms data saving.
	 */
	function saveData($request) {
		$formVar = Request::getUserVar('form');
		$tabForm = $this->_getTabForm($formVar);

		$tabForm->readInputData();
		if($tabForm->validate()) {
			$tabForm->execute($request);
			// Create notification to indicate that setup was saved
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationManager->createTrivialNotification('notification.notification', 'manager.setup.pressSetupUpdated');
			return DAO::getDataChangedEvent();
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Return an instance of the form based on its name.
	 * Used by saveData() to execute any form used in this handler.
	 * @param $formVar String
	 * @return Form
	 */
	function _getTabForm($formVar) {
		switch($formVar) {
			case 'masthead':
				import('controllers.tab.settings.masthead.form.MastheadForm');
				$form = new MastheadForm();
				break;
			case 'emailTemplates':
				import('controllers.tab.settings.emailTemplates.form.EmailTemplatesForm');
				$form = new EmailTemplatesForm();
				break;
			default:
				break;
		}
		assert(is_a($form, 'Form'));
		return $form;
	}
}
?>
