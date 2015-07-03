<?php

/**
 * @file controllers/tab/settings/PressSettingsTabHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Press page.
 */

// Import the base Handler.
import('lib.pkp.controllers.tab.settings.ManagerSettingsTabHandler');

class PressSettingsTabHandler extends ManagerSettingsTabHandler {


	/**
	 * Constructor
	 */
	function PressSettingsTabHandler() {
		parent::ManagerSettingsTabHandler();
		$this->setPageTabs(array(
			'masthead' => 'controllers.tab.settings.masthead.form.MastheadForm',
			'contact' => 'lib.pkp.controllers.tab.settings.contact.form.ContactForm',
			'policies' => 'lib.pkp.controllers.tab.settings.policies.form.PoliciesForm',
			'guidelines' => 'lib.pkp.controllers.tab.settings.guidelines.form.GuidelinesForm',
			'series' => 'management/series.tpl',
			'categories' => 'management/categories.tpl',
			'affiliationAndSupport' => 'lib.pkp.controllers.tab.settings.affiliation.form.AffiliationForm',
		));
	}

	//
	// Overridden methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);

		// Load grid-specific translations
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_USER);
	}
}

?>
