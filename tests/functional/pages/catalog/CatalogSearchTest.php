<?php

/**
 * @file tests/functional/pages/catalog/CatalogSearchTest.php
 *
 * Copyright (c) 2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogSearchTest
 * @ingroup tests_functional_pages_catalog
 *
 * @brief Test for catalog search
 */

import('tests.data.ContentBaseTestCase');

class CatalogSearchTest extends ContentBaseTestCase {
	/**
	 * @copydoc WebTestCase::getAffectedTables
	 */
	protected function getAffectedTables() {
		return array();
	}

	/**
	 * Test searching for "bomb"
	 */
	function testBombSearch() {
		// Search for "bomb"
		$this->open(self::$baseUrl);
		$this->waitForElementPresent($selector = 'id=topSearchFormField');
		$this->type($selector, 'bomb');
		$this->click('//form[@id="topSearchForm"]//button');

		// Should be 1 result
		$this->waitForElementPresent('//em[text()="Browse 1 Titles"]');
		$this->waitForElementPresent('//a[text()="Bomb Canada and Other Unkind Remarks in the American Media"]');
	}

	/**
	 * Test searching for "zorg"
	 */
	function testZorgSearch() {
		// Search for "bomb"
		$this->open(self::$baseUrl);
		$this->waitForElementPresent($selector = 'id=topSearchFormField');
		$this->type($selector, 'zorg');
		$this->click('//form[@id="topSearchForm"]//button');

		// Should be 1 result
		$this->waitForElementPresent('//em[text()="Browse 0 Titles"]');
	}
}
