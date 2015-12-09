<?php

/**
 * @file tests/data/20-CreatePressTest.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CreatePressTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create and configure a test press
 */

import('lib.pkp.tests.WebTestCase');

class CreatePressTest extends WebTestCase {
	/**
	 * Prepare for tests.
	 */
	function testCreatePressLogin() {
		parent::logIn('admin', 'admin');
	}

	/**
	 * Create and set up test data press.
	 */
	function testCreatePress() {
		$this->open(self::$baseUrl);
		$this->waitForElementPresent($selector='link=Administration');
		$this->click($selector);
		$this->waitForElementPresent($selector='link=Hosted Presses');
		$this->click($selector);
		$this->waitForElementPresent($selector='css=[id^=component-grid-admin-press-pressgrid-createContext-button-]');
		$this->click($selector);

		// Enter press data
		$this->waitForElementPresent('css=[id^=name-en_US-]');
		$this->type('css=[id^=name-en_US-]', 'Public Knowledge Press');
		$this->type('css=[id^=name-fr_CA-]', 'Press de la connaissance du public');
		$this->typeTinyMCE('description-en_US', 'Public Knowledge Press is a publisher dedicated to the subject of public access to science.');
		$this->typeTinyMCE('description-fr_CA', 'Le Press de Public Knowledge est une presse sur le thème de l\'accès du public à la science.');
		$this->type('css=[id^=path-]', 'publicknowledge');
		$this->click('css=[id^=submitFormButton-]');
		$this->waitForElementPresent('css=div.header:contains(\'Settings Wizard\')');
		$this->waitJQuery();
	}

	/**
	 * Set up the test press.
	 */
	function testSetupPress() {
		$this->open(self::$baseUrl);

		// Management > Settings > Press
		$this->waitForElementPresent($selector='link=Dashboard');
		$this->clickAndWait($selector);
		$this->waitForElementPresent($selector='css=ul#navigationPrimary a:contains(\'Press\')');
		$this->clickAndWait($selector);
		$this->waitForElementPresent($selector='//form[@id=\'mastheadForm\']//button[text()=\'Save\']');
		$this->click($selector);
		$this->waitForTextPresent('Your changes have been saved.');

		// Management > Settings > Contact
		$this->click('link=Contact');
		$this->waitForElementPresent('css=[id^=contactEmail-]');
		$this->type('css=[id^=contactEmail-]', 'rvaca@mailinator.com');
		$this->type('css=[id^=contactName-]', 'Ramiro Vaca');
		$this->type('css=[id^=supportEmail-]', 'rvaca@mailinator.com');
		$this->type('css=[id^=supportName-]', 'Ramiro Vaca');
		$this->click('//form[@id=\'contactForm\']//button[text()=\'Save\']');
		$this->waitForTextPresent('Your changes have been saved.');

		// Management > Settings > Website
		$this->click('link=Website');
		$this->waitForElementPresent('css=[id^=pageHeaderTitle-]');
		$this->type('css=[id^=pageHeaderTitle-]', 'Public Knowledge Press');
		$this->type('css=[id^=itemsPerPage-]', '100'); // To avoid having to scroll down to find submissions.
		$this->click('//form[@id=\'appearanceForm\']//button[text()=\'Save\']');
		$this->waitForTextPresent('Your changes have been saved.');
	}

	/**
	 * Configure roles to permit self-registration for Volume Editors
	 */
	function testSetupRoles() {
		$this->open(self::$baseUrl);

		// Users & Roles
		$this->waitForElementPresent($selector='link=Dashboard');
		$this->clickAndWait($selector);
		$this->waitForElementPresent($selector='link=Users & Roles');
		$this->click($selector);
		$this->waitForElementPresent($selector='link=Roles');
		$this->click($selector);

		// "Edit" link below "Volume editor" role
		$this->waitForElementPresent('//table[starts-with(@id, \'component-grid-settings-roles-usergroupgrid-\')]//span[contains(text(), \'Volume editor\')]/../../../following-sibling::tr//a[contains(text(),\'Edit\')]');
		$this->click('//table[starts-with(@id, \'component-grid-settings-roles-usergroupgrid-\')]//span[contains(text(), \'Volume editor\')]/../../../following-sibling::tr//a[contains(text(),\'Edit\')]');

		// Click the "permit self registration" checkbox
		$this->waitForElementPresent('//input[@id=\'permitSelfRegistration\']');
		$this->click('//input[@id=\'permitSelfRegistration\']');
		$this->waitForElementPresent($selector='//form[@id=\'userGroupForm\']//button[text()=\'OK\']');
		$this->click($selector);
		$this->waitJQuery();
	}
}
