<?php

/**
 * @file tests/classes/monograph/SubmissionFileDAOTest.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileDAOTest
 *
 * @ingroup tests_classes_monograph
 *
 * @see SubmissionFileDAO
 *
 * @brief Test class for SubmissionFileDAO.
 */

namespace APP\tests\classes\monograph;

use APP\core\Application;
use APP\core\PageRouter;
use APP\core\Request;
use APP\facades\Repo;
use APP\press\Press;
use APP\submission\Submission;
use PHPUnit\Framework\Attributes\CoversClass;
use PKP\core\Registry;
use PKP\db\DAORegistry;
use PKP\submission\GenreDAO;
use PKP\submissionFile\SubmissionFile;
use PKP\tests\DatabaseTestCase;

#[CoversClass(\APP\submissionFile\DAO::class)]
class SubmissionFileDAOTest extends DatabaseTestCase
{
    // Define test ids. WARNING: These are expected to match present data!
    // The dataset data (https://github.com/pkp/datasets) provides conforming data.
    private const SUBMISSION_FILE_DAO_TEST_PRESS_ID = 1;
    private const SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID = 1;
    private const SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID = 1;
    private const SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID = 9;
    // Define a temp file location for testing.
    private const TMP_FILES = '/tmp';
    private string $testFile1;
    private string $testFile2;
    private string $testFile3;

    /**
     * @see DatabaseTestCase::getAffectedTables()
     */
    protected function getAffectedTables()
    {
        return \PKP\tests\PKPTestHelper::PKP_TEST_ENTIRE_DB;
    }

    protected function setUp(): void
    {
        // Create a test file on the file system.
        $this->testFile1 = tempnam(static::TMP_FILES, 'SubmissionFile1');
        $this->testFile2 = tempnam(static::TMP_FILES, 'SubmissionFile2');
        $this->testFile3 = tempnam(static::TMP_FILES, 'SubmissionFile3');

        // Mock a press
        $press = new Press();
        $press->setPrimaryLocale('en');
        $press->setPath('press-path');
        $press->setId(static::SUBMISSION_FILE_DAO_TEST_PRESS_ID);

        // Mock a request
        $mockRequest = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getContext'])
            ->getMock();
        $mockRequest->expects($this->any())
            ->method('getContext')
            ->willReturn($press);
        Registry::get('request', true, $mockRequest);

        // Register a mock monograph DAO.
        $submissionDao = $this->getMockBuilder(\APP\submission\DAO::class)
            ->setConstructorArgs([app()->get('schema')])
            ->onlyMethods(['get'])
            ->getMock();
        $monograph = new Submission();
        $monograph->setId(static::SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID);
        $monograph->setData('contextId', static::SUBMISSION_FILE_DAO_TEST_PRESS_ID);
        $monograph->setData('locale', 'en');
        $submissionDao->expects($this->any())
            ->method('get')
            ->willReturn($monograph);
        Repo::submission()->dao = $submissionDao;

        // Register a mock genre DAO.
        $genreDao = $this->getMockBuilder(GenreDAO::class)
            ->onlyMethods(['getById'])
            ->getMock();
        DAORegistry::registerDAO('GenreDAO', $genreDao);

        $this->_cleanFiles();

        $request = Application::get()->getRequest();
        if (is_null($request->getRouter())) {
            $router = new PageRouter();
            $request->setRouter($router);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile1)) {
            unlink($this->testFile1);
        }
        if (file_exists($this->testFile2)) {
            unlink($this->testFile2);
        }
        $this->_cleanFiles();
    }

    public function testSubmissionFileCrud()
    {
        //
        // Create test data.
        //
        // Create a submission
        $submissionDao = Repo::submission()->dao;
        $submission = Repo::submission()->newDataObject();
        $submission->setData('contextId', static::SUBMISSION_FILE_DAO_TEST_PRESS_ID);
        $submission->setData('locale', 'en');
        $submissionId = Repo::submission()->dao->insert($submission);

        $publication = Repo::publication()->newDataObject(['submissionId' => $submissionId]);
        Repo::publication()->dao->insert($publication);

        $submission->setData('currentPublicationId', $publication->getId());
        $submissionDao->update($submission);

        $submissionDao = $this->getMockBuilder(\APP\submission\DAO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $monograph = new Submission();
        $monograph->setId($submissionId);
        $monograph->setData('contextId', static::SUBMISSION_FILE_DAO_TEST_PRESS_ID);
        $monograph->setData('locale', 'en');
        $submissionDao->expects($this->any())
            ->method('get')
            ->willReturn($monograph);
        Repo::submission()->dao = $submissionDao;

        // Create test files
        $submissionDir = Repo::submissionFile()
            ->getSubmissionDir(static::SUBMISSION_FILE_DAO_TEST_PRESS_ID, $submissionId);
        $fileId1 = app()->get('file')->add(
            $this->testFile1,
            $submissionDir . '/' . uniqid() . '.txt'
        );
        $submissionFile1 = new SubmissionFile();
        $submissionFile1->setAllData([
            'fileStage' => SubmissionFile::SUBMISSION_FILE_SUBMISSION,
            'submissionId' => $submissionId,
            'uploaderUserId' => 1,
            'fileId' => $fileId1,
            'genreId' => static::SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID,
            'createdAt' => '2011-12-05 00:00:00',
            'updatedAt' => '2011-12-05 00:00:00',
        ]);
        $fileId2 = app()->get('file')->add(
            $this->testFile2,
            $submissionDir . '/' . uniqid() . '.txt'
        );
        $submissionFile2 = clone $submissionFile1;
        $submissionFile2->setData('assocType', Application::ASSOC_TYPE_REPRESENTATION);
        $submissionFile2->setData('assocId', 1);
        $submissionFile2->setData('fileStage', SubmissionFile::SUBMISSION_FILE_PROOF);
        $submissionFile2->setData('fileId', $fileId2);

        // Persist files and check retrieval
        $submissionFileDao = Repo::submissionFile()->dao;
        $submissionFile1Id = $submissionFileDao->insert($submissionFile1);
        $submissionFile1 = $submissionFileDao->get($submissionFile1Id);
        static::assertTrue(is_a($submissionFile1, 'SubmissionFile'));
        static::assertEquals($submissionFile1->getData('assocType'), null);
        static::assertEquals($submissionFile1->getData('assocId'), null);
        static::assertEquals($submissionFile1->getData('fileStage'), SubmissionFile::SUBMISSION_FILE_SUBMISSION);
        static::assertEquals($submissionFile1->getData('submissionId'), $submissionId);
        static::assertEquals($submissionFile1->getData('fileId'), $fileId1);
        static::assertEquals($submissionFile1->getData('genreId'), static::SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);
        static::assertEquals($submissionFile1->getData('createdAt'), '2011-12-05 00:00:00');
        static::assertEquals($submissionFile1->getData('updatedAt'), '2011-12-05 00:00:00');

        $submissionFile2Id = $submissionFileDao->insert($submissionFile2);
        $submissionFile2 = $submissionFileDao->get($submissionFile2Id);
        static::assertTrue(is_a($submissionFile2, 'SubmissionFile'));
        static::assertEquals($submissionFile2->getData('assocType'), Application::ASSOC_TYPE_REPRESENTATION);
        static::assertEquals($submissionFile2->getData('assocId'), 1);
        static::assertEquals($submissionFile2->getData('fileStage'), SubmissionFile::SUBMISSION_FILE_PROOF);
        static::assertEquals($submissionFile2->getData('fileId'), $fileId2);
        static::assertEquals($submissionFile2->getData('genreId'), static::SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);

        // Save changes to a file without creating a new revision
        $submissionFile2->setData('genreId', static::SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID);
        $submissionFileDao->update($submissionFile2);
        $submissionFile2 = $submissionFileDao->get($submissionFile2->getId());
        static::assertEquals($submissionFile2->getData('genreId'), static::SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID);

        // Save a new revision of a submission file
        $fileId3 = app()->get('file')->add(
            $this->testFile3,
            $submissionDir . '/' . uniqid() . '.txt'
        );
        $submissionFile2->setData('fileId', $fileId3);
        $submissionFileDao->update($submissionFile2);
        $submissionFile2 = $submissionFileDao->get($submissionFile2->getId());
        static::assertEquals($submissionFile2->getData('fileId'), $fileId3);
        $revisions = Repo::submissionFile()->getRevisions($submissionFile2->getId());
        $revisionFileIds = [];
        foreach ($revisions as $revision) {
            $revisionFileIds[] = $revision->fileId;
        }
        static::assertEquals($revisionFileIds, [$fileId3, $fileId2]);

        // Delete a file
        $submissionFileDao->delete($submissionFile2);
        $submissionFile2 = $submissionFileDao->get($submissionFile2Id);
        static::assertNull($submissionFile2);

        $this->_cleanFiles($submissionId);
    }

    /**
     * Remove remnants from the tests.
     *
     * @param null|mixed $submissionId
     */
    private function _cleanFiles($submissionId = null)
    {
        // Delete the test submission's files.
        if (!$submissionId) {
            $submissionId = static::SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID;
        }
        $submissionFileIds = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$submissionId])
            ->getIds();

        foreach ($submissionFileIds as $submissionFileId) {
            Repo::submissionFile()->dao->deleteById($submissionFileId);
        }
    }
}
