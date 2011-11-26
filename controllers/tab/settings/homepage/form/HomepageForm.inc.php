<?php

/**
 * @file controllers/tab/settings/homepage/form/HomepageForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HomepageForm
 * @ingroup controllers_tab_settings_homepage_form
 *
 * @brief Form to edit press homepage information and settings.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class HomepageForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function HomepageForm($wizardMode = false) {
		$settings = array(
			'enableAnnouncements' => 'bool',
			'enableAnnouncementsHomepage' => 'bool',
			'numAnnouncementsHomepage' => 'int',
			'announcementsIntroduction' => 'string',
			'readerInformation' => 'string',
			'authorInformation' => 'string',
			'librarianInformation' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/homepage/form/homepageForm.tpl', $wizardMode);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('announcementsIntroduction', 'readerInformation', 'authorInformation', 'librarianInformation');
	}


	//
	// Implement template methods from PressSettingsForm.
	//
	/**
	 * @see PressSettingsForm::fetch()
	 */
	function fetch(&$request) {
		for($x = 1; $x < 11; $x++) {
			$numAnnouncementsHomepageOptions[$x] = $x;
		}

		$params = array(
			'numAnnouncementsHomepageOptions' => $numAnnouncementsHomepageOptions,
			'disableAnnouncementsHomepage' => !$this->getData('enableAnnouncementsHomepage')
		);

		return parent::fetch(&$request, $params);
	}
}

?>