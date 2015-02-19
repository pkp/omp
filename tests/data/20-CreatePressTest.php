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
		$this->waitForElementPresent('link=Administration');
		$this->click('link=Administration');
		$this->waitForElementPresent('link=Hosted Presses');
		$this->click('link=Hosted Presses');
		$this->waitForElementPresent('css=[id^=component-grid-admin-press-pressgrid-createContext-button-]');
		$this->click('css=[id^=component-grid-admin-press-pressgrid-createContext-button-]');

		// Enter press data
		$this->waitForElementPresent('css=[id^=name-en_US-]');
		$this->type('css=[id^=name-en_US-]', 'Public Knowledge Press');
		$this->type('css=[id^=name-fr_CA-]', 'Press de la connaissance du public');
		$this->typeTinyMCE('description-en_US', 'Public Knowledge Press is a publisher dedicated to the subject of public access to science.');
		$this->typeTinyMCE('description-fr_CA', 'Le Press de Public Knowledge est une presse sur le thème de l\'accès du public à la science.');
		$this->type('css=[id^=path-]', 'publicknowledge');
		$this->click('css=[id^=submitFormButton-]');
		$this->waitForElementPresent('css=h2:contains(\'Settings Wizard\')');
		$this->waitJQuery();
	}

	/**
	 * Set up the test press.
	 */
	function testSetupPress() {
		$this->open(self::$baseUrl);

		// Management > Settings > Press
		$this->waitForElementPresent('//ul[contains(@class, \'sf-js-enabled\')]//a[text()=\'Press\']');
		$this->clickAndWait('//ul[contains(@class, \'sf-js-enabled\')]//a[text()=\'Press\']');
		$this->waitForElementPresent('//form[@id=\'mastheadForm\']//span[text()=\'Save\']/..');
		$this->click('//form[@id=\'mastheadForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();

		// Management > Settings > Contact
		$this->click('link=Contact');
		$this->waitForElementPresent('css=[id^=contactEmail-]');
		$this->type('css=[id^=contactEmail-]', 'rvaca@mailinator.com');
		$this->type('css=[id^=contactName-]', 'Ramiro Vaca');
		$this->type('css=[id^=supportEmail-]', 'rvaca@mailinator.com');
		$this->type('css=[id^=supportName-]', 'Ramiro Vaca');
		$this->click('//form[@id=\'contactForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();

		// Management > Settings > Website
		$this->click('link=Website');
		$this->waitForElementPresent('css=[id^=pageHeaderTitle-]');
		$this->type('css=[id^=pageHeaderTitle-]', 'Public Knowledge Press');
		$this->click('//form[@id=\'appearanceForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();
	}

	/**
	 * Configure roles to permit self-registration for Volume Editors
	 */
	function testSetupRoles() {
		$this->open(self::$baseUrl);

		// Users & Roles
		$this->waitForElementPresent('link=Users & Roles');
		$this->click('link=Users & Roles');
		$this->waitForElementPresent('link=Roles');
		$this->click('link=Roles');

		// "Edit" link below "Volume editor" role
		$this->waitForElementPresent('//table[starts-with(@id, \'component-grid-settings-roles-usergroupgrid-\')]//span[contains(text(), \'Volume editor\')]/../../../../../following-sibling::tr//a[contains(text(),\'Edit\')]');
		$this->click('//table[starts-with(@id, \'component-grid-settings-roles-usergroupgrid-\')]//span[contains(text(), \'Volume editor\')]/../../../../../following-sibling::tr//a[contains(text(),\'Edit\')]');

		// Click the "permit self registration" checkbox
		$this->waitForElementPresent('//input[@id=\'permitSelfRegistration\']');
		$this->click('//input[@id=\'permitSelfRegistration\']');
		$this->waitForElementPresent('//form[@id=\'userGroupForm\']//span[text()=\'OK\']/..');
		$this->click('//form[@id=\'userGroupForm\']//span[text()=\'OK\']/..');
		$this->waitJQuery();
	}
}
