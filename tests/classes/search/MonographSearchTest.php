<?php

/**
 * @file tests/classes/search/MonographSearchTest.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographSearchTest
 *
 * @brief Test class for the MonographSearch class
 */

namespace APP\tests\classes\search;

use APP\core\Application;
use APP\core\PageRouter;
use APP\press\Press;
use APP\press\PressDAO;
use APP\search\MonographSearch;
use APP\search\MonographSearchDAO;
use PHPUnit\Framework\Attributes\CoversClass;
use PKP\core\ItemIterator;
use PKP\core\VirtualArrayIterator;
use PKP\db\DAORegistry;
use PKP\plugins\Hook;
use PKP\tests\PKPTestCase;

#[CoversClass(MonographSearch::class)]
class MonographSearchTest extends PKPTestCase
{
    private const SUBMISSION_SEARCH_TEST_DEFAULT_MONOGRAPH = 1;

    private array $_retrieveResultsParams;

    //
    // Implementing protected template methods from PKPTestCase
    //
    /**
     * @see PKPTestCase::getMockedDAOs()
     */
    protected function getMockedDAOs(): array
    {
        return [...parent::getMockedDAOs(), 'MonographSearchDAO', 'PressDAO'];
    }

    /**
     * @see PKPTestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();
        Hook::rememberCalledHooks();

        // Prepare the mock environment for this test.
        $this->registerMockMonographSearchDAO();
        $this->registerMockPressDAO();

        $request = Application::get()->getRequest();
        if (is_null($request->getRouter())) {
            $router = new PageRouter();
            $request->setRouter($router);
        }
    }

    /**
     * @see PKPTestCase::tearDown()
     */
    protected function tearDown(): void
    {
        Hook::resetCalledHooks();
        parent::tearDown();
    }


    //
    // Unit tests
    //

    public function testRetrieveResults()
    {
        // Make sure that no hook is being called.
        Hook::clear('SubmissionSearch::retrieveResults');

        // Test a simple search with a mock database back-end.
        $press = new Press();
        $keywords = [null => 'test'];
        $monographSearch = new MonographSearch();
        $error = '';
        $request = Application::get()->getRequest();
        $searchResult = $monographSearch->retrieveResults($request, $press, $keywords, $error);

        // Test whether the result from the mocked DAOs is being returned.
        self::assertInstanceOf(ItemIterator::class, $searchResult);
        $firstResult = $searchResult->next();
        self::assertArrayHasKey('monograph', $firstResult);
        self::assertEquals(self::SUBMISSION_SEARCH_TEST_DEFAULT_MONOGRAPH, $firstResult['monograph']->getId());
        self::assertEquals('', $error);
    }

    public function testRetrieveResultsViaPluginHook()
    {
        // Diverting a search to the search plugin hook.
        Hook::add('SubmissionSearch::retrieveResults', [$this, 'callbackRetrieveResults']);

        $testCases = [
            [null => 'query'], // Simple Search - "All"
            ['1' => 'author'], // Simple Search - "Authors"
            ['2' => 'title'], // Simple Search - "Title"
            [
                null => 'query',
                1 => 'author',
                2 => 'title'
            ], // Advanced Search
        ];

        $testFromDate = date('Y-m-d H:i:s', strtotime('2011-03-15 00:00:00'));
        $testToDate = date('Y-m-d H:i:s', strtotime('2012-03-15 18:30:00'));
        $error = '';

        $request = Application::get()->getRequest();

        foreach ($testCases as $testCase) {
            // Test a simple search with the simulated callback.
            $press = new Press();
            $keywords = $testCase;
            $monographSearch = new MonographSearch();
            Hook::resetCalledHooks(true);
            $searchResult = $monographSearch->retrieveResults($request, $press, $keywords, $error, $testFromDate, $testToDate);

            // Check the parameters passed into the callback.
            foreach ([
                $press, $testCase, $testFromDate, $testToDate, $orderBy = 'score', $orderDir = 'desc',
                $exclude = [], $page = 1, $itemsPerPage = 20, $totalResults = 3, $error = '',
                //the last item, the result,  will be checked later on
            ] as $position => $expected) {
                self::assertEquals($expected, $this->_retrieveResultsParams[$position]);
            }

            // Test the call history of the hook registry.
            $calledHooks = Hook::getCalledHooks();
            self::assertCount(1, array_filter($calledHooks, fn ($hook) => $hook[0] === 'SubmissionSearch::retrieveResults'));

            // Test whether the result from the hook is being returned.
            self::assertInstanceOf(VirtualArrayIterator::class, $searchResult);

            // Test the total count.
            self::assertEquals(3, $searchResult->getCount());

            // Test the search result.
            $firstResult = $searchResult->next();
            self::assertArrayHasKey('monograph', $firstResult);
            self::assertEquals(self::SUBMISSION_SEARCH_TEST_DEFAULT_MONOGRAPH, $firstResult['monograph']->getId());
            self::assertEquals('', $error);
        }

        // Remove the test hook.
        Hook::clear('SubmissionSearch::retrieveResults');
    }


    //
    // Public callback methods
    //
    /**
     * Simulate a search plug-ins "retrieve results" hook.
     *
     * @see SubmissionSearch::retrieveResults()
     */
    public function callbackRetrieveResults($hook, $params): bool
    {
        // Save the test parameters
        $this->_retrieveResultsParams = $params;

        // Test returning count by-ref.
        $totalCount = & $params[9];
        $totalCount = 3;

        // Mock a result set and return it.
        $results = & $params[11];
        $results = [3 => self::SUBMISSION_SEARCH_TEST_DEFAULT_MONOGRAPH];
        return true;
    }


    //
    // Private helper methods
    //
    /**
     * Mock and register an MonographSearchDAO as a test
     * back end for the MonographSearch class.
     */
    private function registerMockMonographSearchDAO()
    {
        // Mock an MonographSearchDAO.
        $monographSearchDao = $this->getMockBuilder(MonographSearchDAO::class)
            ->onlyMethods(['getPhraseResults'])
            ->getMock();

        // Mock a result set.
        $searchResult = [
            self::SUBMISSION_SEARCH_TEST_DEFAULT_MONOGRAPH => [
                'count' => 3,
                'press_id' => 2,
                'issuePublicationDate' => '2013-05-01 20:30:00',
                'publicationDate' => '2013-05-01 20:30:00'
            ]
        ];

        // Mock the getPhraseResults() method.
        $monographSearchDao->expects($this->any())
            ->method('getPhraseResults')
            ->willReturn($searchResult);

        // Register the mock DAO.
        DAORegistry::registerDAO('MonographSearchDAO', $monographSearchDao);
    }


    /**
     * Mock and register an PressDAO as a test
     * back end for the MonographSearch class.
     */
    private function registerMockPressDAO()
    {
        // Mock a PressDAO.
        $pressDao = $this->getMockBuilder(PressDAO::class)
            ->onlyMethods(['getById'])
            ->getMock();

        // Mock a press.
        $press = new Press();
        $press->setId(1);

        // Mock the getById() method.
        $pressDao->expects($this->any())
            ->method('getById')
            ->willReturn($press);

        // Register the mock DAO.
        DAORegistry::registerDAO('PressDAO', $pressDao);
    }
}
