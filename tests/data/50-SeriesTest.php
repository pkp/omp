<?php

/**
 * @file tests/data/50-SeriesTest.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
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

		// Create a new "Education" series
		$this->waitForElementPresent('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Education');
		$this->type('css=[id^=path-]', 'education');

		// Add Series Editor (David Buskins)
		$this->waitForElementPresent('css=[id^=component-listbuilder-settings-subeditorslistbuilder-addItem-button-]');
		$this->clickAt('css=[id^=component-listbuilder-settings-subeditorslistbuilder-addItem-button-]', '10,10');

		$this->waitForElementPresent('//select[@name=\'newRowId[name]\']//option[text()=\'David Buskins\']');
		$this->select('name=newRowId[name]', 'label=David Buskins');

		// Persist this one and add another (Stephanie Berardo)
		$this->clickAt("css=[id^=component-listbuilder-settings-subeditorslistbuilder-addItem-button-]", "10,10");
		$this->waitForElementPresent('css=span:contains(\'David Buskins\')');
		$this->waitForElementPresent('xpath=(//select[@name="newRowId[name]"])[2]//option[text()=\'Stephanie Berardo\']');
		$this->select('xpath=(//select[@name="newRowId[name]"])[2]', 'label=Stephanie Berardo');

		// Save changes
		$this->click('//form[@id=\'seriesForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();

		// Verify resulting grid row
		$this->assertEquals('Berardo, Buskins', $this->getText('css=#cell-1-editors > span'));

		// Create a new "Access" series
		$this->click('css=[id^=component-grid-settings-series-seriesgrid-addSeries-button-]');
		$this->waitForElementPresent('css=[id^=title-]');
		$this->type('css=[id^=title-]', 'Access');
		$this->type('css=[id^=path-]', 'access');

		// Add a Series Editor (Minoti Inoue)
		$this->waitForElementPresent('css=[id^=component-listbuilder-settings-subeditorslistbuilder-addItem-button-]');
		$this->clickAt('css=[id^=component-listbuilder-settings-subeditorslistbuilder-addItem-button-]', '10,10');
		$this->waitForElementPresent('//select[@name=\'newRowId[name]\']//option[text()=\'Minoti Inoue\']');
		$this->select('name=newRowId[name]', 'label=Minoti Inoue');
		$this->click('//form[@id=\'seriesForm\']//span[text()=\'Save\']/..');
		$this->waitJQuery();
	}
}
