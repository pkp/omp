<?php
/**
 * @file tests/classes/monograph/ChapterTest.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterTest
 * @ingroup tests_classes_monograph
 * @see Chapter
 *
 * @brief Test class for the Chapter class
 */
import('lib.pkp.tests.PKPTestCase');
class ChapterTest extends PKPTestCase {
	
	/**
	 * @see PKPTestCase::setUp()
	 */
	protected function setUp() : void {
		$this->chapter = DAORegistry::getDAO('ChapterDAO')->newDataObject();
	}
	
	/**
	 * @see PKPTestCase::tearDown()
	 */
	protected function tearDown() : void {
		unset($this->chapter);
	}	
	
    /**
	 * @covers chapter
	 */
	public function testGetFullTitles() {
		$expected = array(
			'en_US' => 'The chapter title: and its subtitle',
			'es_ES' => 'El título del capítulo: y su subtítulo'
		);
		$this->chapter->setData('title', 'The chapter title', 'en_US');
		$this->chapter->setData('subtitle', 'and its subtitle', 'en_US');
		$this->chapter->setData('title', 'El título del capítulo', 'es_ES');
		$this->chapter->setData('subtitle', 'y su subtítulo', 'es_ES');
		$fullTitles = $this->chapter->getFullTitles();
		$this->assertSame($expected, $fullTitles);
	}

	/**
	 * @covers chapter
	 */
	public function testGetLocalizedFullTitle() {
		// no preferred locale specified
		$this->chapter->setData('title', 'The chapter title', 'en_US');
		$this->chapter->setData('subtitle', 'and its subtitle', 'en_US');
		$expected = 'The chapter title: and its subtitle';
		$fullTitle = $this->chapter->getLocalizedFullTitle();
		$this->assertSame($expected, $fullTitle);

		// specifying a locale thats not the app locale
		$this->chapter->setData('title', 'The chapter title', 'en_US');
		$this->chapter->setData('subtitle', 'and its subtitle', 'en_US');
		$this->chapter->setData('title', 'El título del capítulo', 'es_ES');
		$this->chapter->setData('subtitle', 'y su subtítulo', 'es_ES');
		if (AppLocale::getLocale() == 'en_US') {
			$expected = 'El título del capítulo: y su subtítulo';
			$preferredLocale = 'es_ES';
		} else {
			$expected = 'The chapter title: and its subtitle';
			$preferredLocale = 'en_US';
		}
		$fullTitle = $this->chapter->getLocalizedFullTitle($preferredLocale);
		$this->assertSame($expected, $fullTitle);
	}
}

