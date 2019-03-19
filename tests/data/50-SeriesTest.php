<?php

/**
 * @file tests/data/50-SeriesTest.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create/configure series
 */

import('lib.pkp.tests.WebTestCase');

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

class SeriesTest extends WebTestCase {
	/**
	 * Configure series editors
	 */
	function testConfigureSeries() {
		$this->open(self::$baseUrl);

		// Management > Settings > Press
		$actions = new WebDriverActions(self::$driver);
		$actions->moveToElement($this->waitForElementPresent('css=ul#navigationUser>li.profile>a'))
			->click($this->waitForElementPresent('//ul[@id="navigationUser"]//a[contains(text(),"Dashboard")]'))
			->perform();
		$actions = new WebDriverActions(self::$driver);
		$actions->moveToElement($this->waitForElementPresent('//ul[@id="navigationPrimary"]//a[text()="Settings"]'))
			->click($this->waitForElementPresent('//ul[@id="navigationPrimary"]//a[text()="Press"]'))
			->perform();
		$this->waitForElementPresent($selector='link=Series');
		$this->click($selector);

		// Create a new "Library & Information Studies" series
		$this->waitForElementPresent($selector='css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->click($selector);
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Library & Information Studies');
		$this->type('css=[id^=path-]', 'lis');

		// Add Series Editor (David Buskins)
		$this->waitForElementPresent($selector='//*[contains(@class,"pkpListPanelItem__item") and contains(text(),"David Buskins")]');
		$this->click($selector);

		// Save changes
		$this->click('//form[@id=\'seriesForm\']//button[text()=\'Save\']');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Verify resulting grid row
		$this->waitForElementPresent('//*[@id="cell-1-editors"]//span[contains(text(),"David Buskins")]');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Create a new "Political Economy" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Political Economy');
		$this->type('css=[id^=path-]', 'pe');

		// Add a Series Editor (Stephanie Berardo)
		$this->waitForElementPresent($selector='//*[contains(@class,"pkpListPanelItem__item") and contains(text(),"Stephanie Berardo")]');
		$this->click($selector);
		$this->click('//form[@id=\'seriesForm\']//button[text()=\'Save\']');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Create a new "History" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'History');
		$this->type('css=[id^=path-]', 'his');
		$this->click('//form[@id=\'seriesForm\']//button[text()=\'Save\']');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Create a new "Education" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Education');
		$this->type('css=[id^=path-]', 'ed');
		$this->click('//form[@id=\'seriesForm\']//button[text()=\'Save\']');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Create a new "Psychology" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Psychology');
		$this->type('css=[id^=path-]', 'psy');
		$this->click('//form[@id=\'seriesForm\']//button[text()=\'Save\']');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
	}
}
