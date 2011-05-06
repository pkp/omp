<?php

/**
 * @file controllers/tab/settings/PressSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Press page.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');

class PressSettingsTabHandler extends SettingsTabHandler {


	/**
	 * Constructor
	 */
	function PressSettingsTabHandler() {
		parent::SettingsTabHandler();
		$pageTabsAndForms = array(
			'masthead' => 'controllers.tab.settings.masthead.form.MastheadForm',
			'contact' => 'controllers.tab.settings.contact.form.ContactForm',
			'policies' => 'controllers.tab.settings.policies.form.PoliciesForm',
			'guidelines' => 'controllers.tab.settings.guidelines.form.GuidelinesForm',
			'affiliationAndSupport' => 'controllers.tab.settings.affiliation.form.AffiliationForm'
		);
		$this->setPageTabsAndForms($pageTabsAndForms);
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
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_USER));
	}
}
?>
