<?php
/**
 * @file tests/classes/monograph/ChapterTest.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterTest
 *
 * @ingroup tests_classes_monograph
 *
 * @see Chapter
 *
 * @brief Test class for the Chapter class
 */

namespace APP\tests\classes\monograph;

use APP\core\Application;
use APP\core\PageRouter;
use APP\monograph\Chapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PKP\facades\Locale;
use PKP\tests\PKPTestCase;

#[CoversClass(Chapter::class)]
class ChapterTest extends PKPTestCase
{
    /** @var Chapter */
    public $chapter;

    /**
     * @see PKPTestCase::setUp()
     */
    protected function setUp(): void
    {
        $request = Application::get()->getRequest();
        if (is_null($request->getRouter())) {
            $router = new PageRouter();
            $request->setRouter($router);
        }

        $this->chapter = new Chapter();
    }

    /**
     * @see PKPTestCase::tearDown()
     */
    protected function tearDown(): void
    {
        unset($this->chapter);
    }

    public function testGetFullTitles()
    {
        $expected = [
            'en' => 'The chapter title: and its subtitle',
            'es' => 'El título del capítulo: y su subtítulo'
        ];
        $this->chapter->setData('title', 'The chapter title', 'en');
        $this->chapter->setData('subtitle', 'and its subtitle', 'en');
        $this->chapter->setData('title', 'El título del capítulo', 'es');
        $this->chapter->setData('subtitle', 'y su subtítulo', 'es');
        $fullTitles = $this->chapter->getFullTitles();
        $this->assertSame($expected, $fullTitles);
    }

    public function testGetLocalizedFullTitle()
    {
        // no preferred locale specified
        $this->chapter->setData('title', 'The chapter title', 'en');
        $this->chapter->setData('subtitle', 'and its subtitle', 'en');
        $expected = 'The chapter title: and its subtitle';
        $fullTitle = $this->chapter->getLocalizedFullTitle();
        $this->assertSame($expected, $fullTitle);

        // specifying a locale thats not the app locale
        $this->chapter->setData('title', 'The chapter title', 'en');
        $this->chapter->setData('subtitle', 'and its subtitle', 'en');
        $this->chapter->setData('title', 'El título del capítulo', 'es');
        $this->chapter->setData('subtitle', 'y su subtítulo', 'es');
        if (Locale::getLocale() == 'en') {
            $expected = 'El título del capítulo: y su subtítulo';
            $preferredLocale = 'es';
        } else {
            $expected = 'The chapter title: and its subtitle';
            $preferredLocale = 'en';
        }
        $fullTitle = $this->chapter->getLocalizedFullTitle($preferredLocale);
        $this->assertSame($expected, $fullTitle);
    }
}
