<?php

/**
 * @file tests/data/20-CreateContextTest.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CreateContextTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create and configure a test press
 */

import('lib.pkp.tests.data.PKPCreateContextTest');

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

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
		$actions = new WebDriverActions(self::$driver);
		$actions->moveToElement($this->waitForElementPresent('css=ul#navigationUser>li.profile>a'))
			->perform();
		$actions = new WebDriverActions(self::$driver);
		$actions->click($this->waitForElementPresent('//ul[@id="navigationUser"]//a[contains(text(),"Dashboard")]'))
			->perform();
		$actions = new WebDriverActions(self::$driver);
		$actions->moveToElement($this->waitForElementPresent('//ul[@id="navigationPrimary"]//a[text()="Settings"]'))
			->perform();
		$actions = new WebDriverActions(self::$driver);
		$actions->click($this->waitForElementPresent('//ul[@id="navigationPrimary"]//a[text()="Press"]'))
			->perform();

		$this->click('//*[@id="masthead"]//button[contains(text(),"Save")]');
		$this->waitForElementPresent('//div[contains(text(),"The masthead details for this press have been updated.")]');

		$this->contactSettings(['contextType' => 'press']);
	}

	/**
	 * Configure roles to permit self-registration for Volume Editors
	 */
	function testSetupRoles() {
		$this->open(self::$baseUrl);

		// Users & Roles
		$actions = new WebDriverActions(self::$driver);
		$actions->moveToElement($this->waitForElementPresent('css=ul#navigationUser>li.profile>a'))
			->perform();
		$actions = new WebDriverActions(self::$driver);
		$actions->click($this->waitForElementPresent('//ul[@id="navigationUser"]//a[contains(text(),"Dashboard")]'))
			->perform();
		$actions = new WebDriverActions(self::$driver);
		$actions->moveToElement($this->waitForElementPresent('//ul[@id="navigationPrimary"]//a[text()="Users & Roles"]'))
			->perform();
		$actions = new WebDriverActions(self::$driver);
		$actions->click($this->waitForElementPresent('//ul[@id="navigationPrimary"]//a[text()="Users"]'))
			->perform();
		$this->waitForElementPresent($selector = '//button[@id="roles-button"]');
		$this->click($selector);

		// "Edit" link below "Volume editor" role
		$element = $this->waitForElementPresent('//table[starts-with(@id, \'component-grid-settings-roles-usergroupgrid-\')]//span[contains(text(), "Volume editor")]/..');
		self::$driver->executeScript('window.scroll(0, document.getElementById(\'' . $element->getAttribute('id') . '\').getBoundingClientRect().top-50);');
		$this->click('//table[starts-with(@id, \'component-grid-settings-roles-usergroupgrid-\')]//span[contains(text(), "Volume editor")]/../../a[@class="show_extras"]');
		$this->click('//table[starts-with(@id, \'component-grid-settings-roles-usergroupgrid-\')]//span[contains(text(), "Volume editor")]/../../../following-sibling::tr[1]//a[text()="Edit"]');

		// Click the "permit self registration" checkbox
		$this->waitForElementPresent('//input[@id=\'permitSelfRegistration\']');
		$this->click('//input[@id=\'permitSelfRegistration\']');
		$this->waitForElementPresent($selector='//form[@id=\'userGroupForm\']//button[text()=\'OK\']');
		$this->click($selector);
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
	}
}
