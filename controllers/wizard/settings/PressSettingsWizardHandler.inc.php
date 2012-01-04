<?php
/**
 * @defgroup controllers_wizard_settings
 */

/**
 * @file controllers/wizard/settings/PressSettingsWizardHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSettingsWizardHandler
 * @ingroup controllers_wizard_settings
 *
 * @brief A controller that handles basic server-side
 *  operations of the press settings wizard.
 */

// Import base class.
import('classes.handler.Handler');

class PressSettingsWizardHandler extends Handler {

	/**
	 * Constructor
	 */
	function PressSettingsWizardHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array('startWizard')
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Displays the press settings wizard.
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function startWizard($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_MANAGER,
			LOCALE_COMPONENT_PKP_MANAGER
		);

		$this->setupTemplate();
		return $templateMgr->fetchJson('controllers/wizard/settings/settingsWizard.tpl');
	}
}

?>
