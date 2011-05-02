<?php

/**
 * @file controllers/tab/settings/ProcessSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProcessSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Publication Process page.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');
import('lib.pkp.classes.core.JSONMessage');

class ProcessSettingsTabHandler extends SettingsTabHandler {

	/**
	 * Constructor
	 */
	function ProcessSettingsTabHandler() {
		parent::SettingsTabHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
					'general',
					'submissionStage',
					'reviewStage',
					'editorialStage',
					'productionStage',
					'emailTemplates'
				)
		);
	}


	//
	// Public handler methods
	//
	/**
	 * Handle general settings management requests.
	 * @param $args
	 * @param $request PKPRequest
	 */
	function general($args, &$request) {
	}

	/**
	 * Handle submission stage management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submissionStage($args, &$request) {
	}

	/**
	 * Handle review stage management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function reviewStage($args, &$request) {
	}

	/**
	 * Handle editorial stage management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editorialStage($args, &$request) {
	}

	/**
	 * Handle production stage management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function productionStage($args, &$request) {
	}

	/**
	 * Handle email templates management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function emailTemplates($args, &$request) {
		// Instantiate the files form.
		import('controllers.tab.settings.emailTemplates.form.EmailTemplatesForm');
		$emailTemplatesForm = new EmailTemplatesForm();
		$emailTemplatesForm->initData();
		$json = new JSONMessage(true, $emailTemplatesForm->fetch($request));
		return $json->getString();
	}
}
?>
