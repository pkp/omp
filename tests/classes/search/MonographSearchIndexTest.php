<?php

/**
 * @file tests/classes/search/MonographSearchIndexTest.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographSearchIndexTest
 *
 * @brief Test class for the MonographSearchIndex class
 */

namespace APP\tests\classes\search;

use APP\core\Application;
use APP\press\PressDAO;
use APP\publication\Publication;
use APP\search\MonographSearchDAO;
use APP\search\MonographSearchIndex;
use APP\submission\Submission;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PKP\db\DAORegistry;
use PKP\db\DAOResultFactory;
use PKP\plugins\Hook;
use PKP\submissionFile\Collector as SubmissionFileCollector;
use PKP\submissionFile\SubmissionFile;
use PKP\tests\PKPTestCase;

#[CoversClass(MonographSearchIndex::class)]
class MonographSearchIndexTest extends PKPTestCase
{
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
     * @see PKPTestCase::getMockedContainerKeys()
     */
    protected function getMockedContainerKeys(): array
    {
        return [...parent::getMockedContainerKeys(), SubmissionFileCollector::class];
    }

    /**
     * @see PKPTestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();
        Hook::rememberCalledHooks();
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

    public function testUpdateFileIndexViaPluginHook()
    {
        // Diverting to the search plugin hook.
        Hook::add('MonographSearchIndex::submissionFileChanged', [$this, 'callbackUpdateFileIndex']);

        // Simulate updating an monograph file via hook.
        $submissionFile = new SubmissionFile();
        $submissionFile->setId(2);
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->submissionFileChanged(0, 1, $submissionFile);

        // Test whether the hook was called.
        $calledHooks = Hook::getCalledHooks();
        $lastHook = array_pop($calledHooks);
        self::assertEquals('MonographSearchIndex::submissionFileChanged', $lastHook[0]);

        // Remove the test hook.
        Hook::clear('MonographSearchIndex::submissionFileChanged');
    }

    public function testDeleteTextIndex()
    {
        // Prepare the mock environment for this test.
        $this->registerMockMonographSearchDAO($this->never(), $this->atLeastOnce());

        // Make sure that no hook is being called.
        Hook::clear('MonographSearchIndex::submissionFileDeleted');

        // Test deleting an monograph from the index with a mock database back-end.#
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->submissionFileDeleted(0);
    }

    public function testDeleteTextIndexViaPluginHook()
    {
        // Diverting to the search plugin hook.
        Hook::add('MonographSearchIndex::submissionFileDeleted', [$this, 'callbackDeleteTextIndex']);

        // The search DAO should not be called.
        $this->registerMockMonographSearchDAO($this->never(), $this->never());

        // Simulate deleting monograph index via hook.
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->submissionFileDeleted(0, 1, 2);

        // Test whether the hook was called.
        $calledHooks = Hook::getCalledHooks();
        $lastHook = array_pop($calledHooks);
        self::assertEquals('MonographSearchIndex::submissionFileDeleted', $lastHook[0]);

        // Remove the test hook.
        Hook::clear('MonographSearchIndex::submissionFileDeleted');
    }

    public function testRebuildIndex()
    {
        // Prepare the mock environment for this test.
        $this->registerMockMonographSearchDAO($this->atLeastOnce(), $this->never());
        $this->registerMockPressDAO();

        // Make sure that no hook is being called.
        Hook::clear('MonographSearchIndex::rebuildIndex');

        // Test log output.
        $this->expectOutputString(__('search.cli.rebuildIndex.clearingIndex') . ' ... ' . __('search.cli.rebuildIndex.done') . "\n");

        // Test rebuilding the index with a mock database back-end.
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->rebuildIndex(true);
    }

    public function testRebuildIndexViaPluginHook()
    {
        // Diverting to the search plugin hook.
        Hook::add('MonographSearchIndex::rebuildIndex', [$this, 'callbackRebuildIndex']);

        // Test log output.
        $this->expectOutputString('Some log message from the plug-in.');

        // Simulate rebuilding the index via hook.
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->rebuildIndex(true); // With log
        $monographSearchIndex->rebuildIndex(false); // Without log (that's why we expect the log message to appear only once).

        // Remove the test hook.
        Hook::clear('MonographSearchIndex::rebuildIndex');
    }

    public function testIndexMonographMetadata()
    {
        // Make sure that no hook is being called.
        Hook::clear('MonographSearchIndex::monographMetadataChanged');

        /** @var Publication|MockObject */
        $publication = $this->getMockBuilder(Publication::class)
            ->onlyMethods([])
            ->getMock();
        $publication->setData('authors', []);
        $publication->setData('subjects', []);
        $publication->setData('keywords', []);
        $publication->setData('disciplines', []);

        /** @var Submission|MockObject */
        $monograph = $this->getMockBuilder(Submission::class)
            ->onlyMethods(['getCurrentPublication'])
            ->getMock();
        $monograph->expects($this->any())
            ->method('getCurrentPublication')
            ->willReturn($publication);

        // Test indexing an monograph with a mock environment.
        $monographSearchIndex = $this->getMockMonographSearchIndex($this->atLeastOnce());
        $monographSearchIndex->submissionMetadataChanged($monograph);
    }

    public function testIndexMonographMetadataViaPluginHook()
    {
        // Diverting to the search plugin hook.
        Hook::add('MonographSearchIndex::monographMetadataChanged', [$this, 'callbackIndexMonographMetadata']);

        // Simulate indexing via hook.
        $monograph = new Submission();
        $monographSearchIndex = $this->getMockMonographSearchIndex($this->never());
        $monographSearchIndex->submissionMetadataChanged($monograph);

        // Test whether the hook was called.
        $calledHooks = Hook::getCalledHooks();
        self::assertEquals('MonographSearchIndex::monographMetadataChanged', $calledHooks[0][0]);

        // Remove the test hook.
        Hook::clear('MonographSearchIndex::monographMetadataChanged');
    }

    public function testIndexSubmissionFiles()
    {
        // Make sure that no hook is being called.
        Hook::clear('MonographSearchIndex::submissionFilesChanged');
        $this->registerFileDAOs(true);

        // Test indexing an monograph with a mock environment.
        $monograph = new Submission();
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->submissionFilesChanged($monograph);
        $this->assertTrue(true);
    }

    public function testIndexSubmissionFilesViaPluginHook()
    {
        // Diverting to the search plugin hook.
        Hook::add('MonographSearchIndex::submissionFilesChanged', [$this, 'callbackIndexSubmissionFiles']);
        // The file DAOs should not be called.
        $this->registerFileDAOs(false);

        // Simulate indexing via hook.
        $monograph = new Submission();
        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->submissionFilesChanged($monograph);

        // Test whether the hook was called.
        $calledHooks = Hook::getCalledHooks();
        $lastHook = array_pop($calledHooks);
        self::assertEquals('MonographSearchIndex::submissionFilesChanged', $lastHook[0]);

        // Remove the test hook.
        Hook::clear('MonographSearchIndex::submissionFilesChanged');
    }


    //
    // Public callback methods
    //
    /**
     * Simulate a search plug-ins "update file index"
     * hook.
     *
     * @see MonographSearchIndex::submissionFileChanged()
     */
    public function callbackUpdateFileIndex($hook, $params)
    {
        self::assertEquals('MonographSearchIndex::submissionFileChanged', $hook);

        [$monographId, $type, $submissionFileId] = $params;
        self::assertEquals(0, $monographId);
        self::assertEquals(1, $type);
        self::assertEquals(2, $submissionFileId);

        // Returning "true" is required so that the default submissionMetadataChanged()
        // code won't run.
        return true;
    }

    /**
     * Simulate a search plug-ins "delete text index"
     * hook.
     *
     * @see MonographSearchIndex::submissionFileDeleted()
     */
    public function callbackDeleteTextIndex($hook, $params)
    {
        self::assertEquals('MonographSearchIndex::submissionFileDeleted', $hook);

        [$monographId, $type, $assocId] = $params;
        self::assertEquals(0, $monographId);
        self::assertEquals(1, $type);
        self::assertEquals(2, $assocId);

        // Returning "true" is required so that the default submissionMetadataChanged()
        // code won't run.
        return true;
    }

    /**
     * Simulate a search plug-ins "rebuild index" hook.
     *
     * @see MonographSearchIndex::rebuildIndex()
     */
    public function callbackRebuildIndex($hook, $params)
    {
        self::assertEquals('MonographSearchIndex::rebuildIndex', $hook);

        [$log] = $params;
        if ($log) {
            echo 'Some log message from the plug-in.';
        }

        // Returning "true" is required so that the default rebuildIndex()
        // code won't run.
        return true;
    }

    /**
     * Simulate a search plug-ins "index monograph metadata"
     * hook.
     *
     * @see MonographSearchIndex::submissionMetadataChanged()
     */
    public function callbackIndexMonographMetadata($hook, $params)
    {
        self::assertEquals('MonographSearchIndex::monographMetadataChanged', $hook);

        [$monograph] = $params;
        self::assertInstanceOf(Submission::class, $monograph);

        // Returning "true" is required so that the default submissionMetadataChanged()
        // code won't run.
        return true;
    }

    /**
     * Simulate a search plug-ins "index monograph files"
     * hook.
     *
     * @see MonographSearchIndex::submissionFilesChanged()
     */
    public function callbackIndexSubmissionFiles($hook, $params)
    {
        self::assertEquals('MonographSearchIndex::submissionFilesChanged', $hook);

        [$monograph] = $params;
        self::assertInstanceOf(Submission::class, $monograph);

        // Returning "true" is required so that the default submissionMetadataChanged()
        // code won't run.
        return true;
    }


    //
    // Private helper methods
    //
    /**
     * Mock and register an MonographSearchDAO as a test
     * back end for the MonographSearchIndex class.
     */
    private function registerMockMonographSearchDAO($clearIndexExpected, $deleteMonographExpected)
    {
        // Mock an MonographSearchDAO.
        $monographSearchDao = $this->getMockBuilder(MonographSearchDAO::class)
            ->onlyMethods(['clearIndex', 'deleteSubmissionKeywords'])
            ->getMock();

        // Test the clearIndex() method.
        $monographSearchDao->expects($clearIndexExpected)
            ->method('clearIndex')
            ->willReturn(null);

        // Test the deleteSubmissionKeywords() method.
        $monographSearchDao->expects($deleteMonographExpected)
            ->method('deleteSubmissionKeywords')
            ->willReturn(null);

        // Register the mock DAO.
        DAORegistry::registerDAO('MonographSearchDAO', $monographSearchDao);
    }

    /**
     * Mock and register a PressDAO as a test
     * back end for the MonographSearchIndex class.
     */
    private function registerMockPressDAO()
    {
        // Mock a PressDAO.
        $pressDao = $this->getMockBuilder(PressDAO::class)
            ->onlyMethods(['getAll'])
            ->getMock();

        // Mock an empty result set.
        $pressIterator = $this->getMockBuilder(DAOResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toIterator'])
            ->getMock();
        $pressIterator
            ->method('toIterator')
            ->willReturn(new \ArrayIterator());

        // Mock the getAll() method.
        $pressDao->expects($this->any())
            ->method('getAll')
            ->willReturn($pressIterator);

        // Register the mock DAO.
        DAORegistry::registerDAO('PressDAO', $pressDao);
    }

    /**
     * Mock and register an SubmissionFile collector as a test back end for
     * the PreprintSearchIndex class.
     */
    private function registerFileDAOs(bool $expectMethodCall)
    {
        /** @var SubmissionFileCollector|MockInterface */
        $mock = Mockery::mock(
            app(SubmissionFileCollector::class),
            fn (MockInterface $mock) => $expectMethodCall
                ? $mock->shouldReceive('filterBySubmissionIds')->andReturn($mock)
                : $mock->shouldNotReceive('filterBySubmissionIds')
        );
        app()->instance(SubmissionFileCollector::class, $mock);
    }

    /**
     * Mock an MonographSearchIndex implementation.
     *
     * @return MonographSearchIndex
     */
    private function getMockMonographSearchIndex($expectedCall)
    {
        // Mock MonographSearchIndex.
        /** @var MonographSearchIndex|MockObject  */
        $monographSearchIndex = $this->getMockBuilder(MonographSearchIndex::class)
            ->onlyMethods(['updateTextIndex'])
            ->getMock();

        // Check for updateTextIndex() calls.
        $monographSearchIndex->expects($expectedCall)
            ->method('updateTextIndex')
            ->willReturn(null);
        return $monographSearchIndex;
    }
}
