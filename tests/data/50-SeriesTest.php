<?php

/**
 * @file tests/data/50-SeriesTest.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create/configure series
 */

import('lib.pkp.tests.WebTestCase');

class SeriesTest extends WebTestCase {
	/**
	 * Configure series editors
	 */
	function testConfigureSeries() {
		$this->open(self::$baseUrl);

		// Management > Settings > Press
		$this->waitForElementPresent('link=Press');
		$this->click('link=Press');
		$this->waitForElementPresent('link=Series');
		$this->click('link=Series');

		// Create a new "Library & Information Studies" series
		$this->waitForElementPresent('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Library & Information Studies');
		$this->type('css=[id^=path-]', 'lis');

		// Add Series Editor (David Buskins)
		$this->waitForElementPresent('css=[id^=component-listbuilder-settings-serieseditorslistbuilder-addItem-button-]');
		$this->clickAt('css=[id^=component-listbuilder-settings-serieseditorslistbuilder-addItem-button-]', '10,10');

		$this->waitForElementPresent('//select[@name=\'newRowId[name]\']//option[text()=\'David Buskins\']');
		$this->select('name=newRowId[name]', 'label=David Buskins');

		// Save changes
		$this->click('//form[@id=\'seriesForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();

		// Verify resulting grid row
		$this->assertEquals('Buskins', $this->getText('css=#cell-1-editors > span'));

		// Create a new "Political Economy" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Political Economy');
		$this->type('css=[id^=path-]', 'pe');

		// Add a Series Editor (Minoti Inoue)
		$this->waitForElementPresent('css=[id^=component-listbuilder-settings-serieseditorslistbuilder-addItem-button-]');
		$this->clickAt('css=[id^=component-listbuilder-settings-serieseditorslistbuilder-addItem-button-]', '10,10');
		$this->waitForElementPresent('//select[@name=\'newRowId[name]\']//option[text()=\'Stephanie Berardo\']');
		$this->select('name=newRowId[name]', 'label=Stephanie Berardo');
		$this->click('//form[@id=\'seriesForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();

		// Create a new "History" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'History');
		$this->type('css=[id^=path-]', 'his');
		$this->click('//form[@id=\'seriesForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();

		// Create a new "Education" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Education');
		$this->type('css=[id^=path-]', 'ed');
		$this->click('//form[@id=\'seriesForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();

		// Create a new "Psychology" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Psychology');
		$this->type('css=[id^=path-]', 'psy');
		$this->click('//form[@id=\'seriesForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();
	}
}
