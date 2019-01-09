<?php

/**
 * @file tests/data/20-CreateContextTest.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CreateContextTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create and configure a test press
 */

import('lib.pkp.tests.data.PKPCreateContextTest');

class CreateContextTest extends PKPCreateContextTest {
	/** @var array */
	public $contextName = [
		'en_US' => 'Public Knowledge Press',
		'fr_CA' => 'Press de la connaissance du public',
	];

	/** @var string journal or press*/
	public $contextType = 'press';

	/** @var array */
	public $contextDescription = [
		'en_US' => 'Public Knowledge Press is a publisher dedicated to the subject of public access to science.',
		'fr_CA' => 'Le Press de Public Knowledge est une presse sur le thème de l\'accès du public à la science.',
	];

	/** @var array */
	public $contextAcronym = [
		'en_US' => 'PKP',
		'fr_CA' => 'PCP',
	];

	/**
	 * Prepare for tests.
	 */
	function testCreateContextLogin() {
		parent::logIn('admin', 'admin');
	}

	/**
	 * Create and set up test data press.
	 */
	function testCreatePress() {
		$this->createContext();
	}

	/**
	 * Test the settings wizard
	 */
	function testSettingsWizard() {
		$this->settingsWizard();
	}

	/**
	 * Set up the test press.
	 */
	function testSetupContext() {
		$this->open(self::$baseUrl);

		// Settings > Press > Masthead
		$this->waitForElementPresent($selector='css=li.profile a:contains(\'Dashboard\')');
		$this->clickAndWait($selector);
		$this->waitForElementPresent($selector='css=ul#navigationPrimary a:contains(\'Press\')');
		$this->clickAndWait($selector);

		$this->click('css=#masthead button:contains(\'Save\')');
		$this->waitForTextPresent('The masthead details for this press have been updated.');

		$this->contactSettings(['contextType' => 'press']);
	}

	/**
	 * Configure roles to permit self-registration for Volume Editors
	 */
	function testSetupRoles() {
		$this->open(self::$baseUrl);

		// Users & Roles
		$this->waitForElementPresent($selector='css=li.profile a:contains(\'Dashboard\')');
		$this->clickAndWait($selector);
		$this->waitForElementPresent($selector='link=Users & Roles');
		$this->mouseOver($selector);
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

	/**
	 * Helper function to go to the hosted presses page
	 */
	function goToHostedContexts() {
		$this->open(self::$baseUrl);
		$this->waitForElementPresent('link=Administration');
		$this->click('link=Administration');
		$this->waitForElementPresent('link=Hosted Presses');
		$this->click('link=Hosted Presses');
	}
}
