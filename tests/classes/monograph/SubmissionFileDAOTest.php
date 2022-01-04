<?php

/**
 * @file tests/classes/monograph/SubmissionFileDAOTest.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileDAOTest
 * @ingroup tests_classes_monograph
 *
 * @see SubmissionFileDAO
 *
 * @brief Test class for SubmissionFileDAO.
 */

import('classes.core.Request'); // Cause mocked Request class to load
import('classes.i18n.AppLocale'); // Cause mocked AppLocale class to load

import('lib.pkp.tests.DatabaseTestCase');

use APP\core\Services;
use APP\facades\Repo;
use APP\press\Press;
use APP\submission\Submission;

use PKP\core\PKPRouter;
use PKP\db\DAORegistry;
use PKP\submission\GenreDAO;
use PKP\submissionFile\SubmissionFile;

// Define test ids.
define('SUBMISSION_FILE_DAO_TEST_PRESS_ID', 999);
define('SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID', 9999);
define('SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID', 1);
define('SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID', 2);

// Define a temp file location for testing.
define('TMP_FILES', '/tmp');

class SubmissionFileDAOTest extends DatabaseTestCase
{
    private $testFile1;
    private $testFile2;
    private $testFile3;

    protected function setUp(): void
    {
        // Create a test file on the file system.
        $this->testFile1 = tempnam(TMP_FILES, 'SubmissionFile1');
        $this->testFile2 = tempnam(TMP_FILES, 'SubmissionFile2');
        $this->testFile3 = tempnam(TMP_FILES, 'SubmissionFile3');

        // Mock a press
        $press = new Press();
        $press->setPrimaryLocale('en_US');
        $press->setPath('press-path');
        $press->setId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);

        // Mock a request
        $mockRequest = $this->getMockBuilder(Request::class)
            ->setMethods(['getContext'])
            ->getMock();
        $mockRequest->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($press));
        Registry::get('request', true, $mockRequest);

        // Register a mock monograph DAO.
        $submissionDao = $this->getMockBuilder(\APP\submission\DAO::class)
            ->setConstructorArgs([Services::get('schema')])
            ->setMethods(['get'])
            ->getMock();
        $monograph = new Submission();
        $monograph->setId(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID);
        $monograph->setPressId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);
        $monograph->setLocale('en_US');
        $submissionDao->expects($this->any())
            ->method('get')
            ->will($this->returnValue($monograph));
        Repo::submission()->dao = $submissionDao;

        // Register a mock genre DAO.
        $genreDao = $this->getMockBuilder(GenreDAO::class)
            ->setMethods(['getById'])
            ->getMock();
        DAORegistry::registerDAO('GenreDAO', $genreDao);
        $genreDao->expects($this->any())
            ->method('getById')
            ->will($this->returnCallback([$this, 'getTestGenre']));

        $this->_cleanFiles();

        $request = Application::get()->getRequest();
        if (is_null($request->getRouter())) {
            $router = new PKPRouter();
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

    /**
     * @covers SubmissionFileDAO
     * @covers SubmissionFileDAO
     * @covers SubmissionFileDAODelegate
     * @covers SubmissionArtworkFileDAODelegate
     * @covers SubmissionFileDAODelegate
     */
    public function testSubmissionFileCrud()
    {
        //
        // Create test data.
        //
        // Create a submission
        $submissionDao = Repo::submission()->dao;
        $submission = Repo::submission()->newDataObject();
        $submission->setPressId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);
        $submission->setLocale('en_US');
        $submissionId = Repo::submission()->dao->insert($submission);

        $publication = Repo::publication()->newDataObject(['submissionId' => $submissionId]);
        Repo::publication()->dao->insert($publication);

        $submission->setData('currentPublicationId', $publication->getId());
        $submissionDao->update($submission);

        $submissionDao = $this->getMockBuilder(\APP\submission\SubmissionDAO::class)
            ->setMethods(['get'])
            ->getMock();
        $monograph = new Submission();
        $monograph->setId($submissionId);
        $monograph->setPressId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);
        $monograph->setLocale('en_US');
        $submissionDao->expects($this->any())
            ->method('get')
            ->will($this->returnValue($monograph));
        Repo::submission()->dao = $submissionDao;

        // Create test files
        $submissionDir = Repo::submissionFile()
            ->getSubmissionDir(SUBMISSION_FILE_DAO_TEST_PRESS_ID, $submissionId);
        $fileId1 = Services::get('file')->add(
            $this->testFile1,
            $submissionDir . '/' . uniqid() . '.txt'
        );
        $submissionFile1 = new SubmissionFile();
        $submissionFile1->setAllData([
            'fileStage' => SubmissionFile::SUBMISSION_FILE_SUBMISSION,
            'submissionId' => $submissionId,
            'uploaderUserId' => 1,
            'fileId' => $fileId1,
            'genreId' => SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID,
            'createdAt' => '2011-12-05 00:00:00',
            'updatedAt' => '2011-12-05 00:00:00',
        ]);
        $fileId2 = Services::get('file')->add(
            $this->testFile2,
            $submissionDir . '/' . uniqid() . '.txt'
        );
        $submissionFile2 = clone $submissionFile1;
        $submissionFile2->setData('assocType', ASSOC_TYPE_REPRESENTATION);
        $submissionFile2->setData('assocId', 1);
        $submissionFile2->setData('fileStage', SubmissionFile::SUBMISSION_FILE_PROOF);
        $submissionFile2->setData('fileId', $fileId2);

        // Persist files and check retrieval
        $submissionFileDao = Repo::submissionFile()->dao;
        $submissionFile1Id = $submissionFileDao->insert($submissionFile1);
        $submissionFile1 = $submissionFileDao->get($submissionFile1Id);
        self::assertTrue(is_a($submissionFile1, 'SubmissionFile'));
        self::assertEquals($submissionFile1->getData('assocType'), null);
        self::assertEquals($submissionFile1->getData('assocId'), null);
        self::assertEquals($submissionFile1->getData('fileStage'), SubmissionFile::SUBMISSION_FILE_SUBMISSION);
        self::assertEquals($submissionFile1->getData('submissionId'), $submissionId);
        self::assertEquals($submissionFile1->getData('fileId'), $fileId1);
        self::assertEquals($submissionFile1->getData('genreId'), SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);
        self::assertEquals($submissionFile1->getData('createdAt'), '2011-12-05 00:00:00');
        self::assertEquals($submissionFile1->getData('updatedAt'), '2011-12-05 00:00:00');

        $submissionFile2Id = $submissionFileDao->insert($submissionFile2);
        $submissionFile2 = $submissionFileDao->get($submissionFile2Id);
        self::assertTrue(is_a($submissionFile2, 'SubmissionFile'));
        self::assertEquals($submissionFile2->getData('assocType'), ASSOC_TYPE_REPRESENTATION);
        self::assertEquals($submissionFile2->getData('assocId'), 1);
        self::assertEquals($submissionFile2->getData('fileStage'), SubmissionFile::SUBMISSION_FILE_PROOF);
        self::assertEquals($submissionFile2->getData('fileId'), $fileId2);
        self::assertEquals($submissionFile2->getData('genreId'), SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);

        // Save changes to a file without creating a new revision
        $submissionFile2->setData('genreId', SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID);
        $submissionFileDao->update($submissionFile2);
        $submissionFile2 = $submissionFileDao->get($submissionFile2->getId());
        self::assertEquals($submissionFile2->getData('genreId'), SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID);

        // Save a new revision of a submission file
        $fileId3 = Services::get('file')->add(
            $this->testFile3,
            $submissionDir . '/' . uniqid() . '.txt'
        );
        $submissionFile2->setData('fileId', $fileId3);
        $submissionFileDao->update($submissionFile2);
        $submissionFile2 = $submissionFileDao->get($submissionFile2->getId());
        self::assertEquals($submissionFile2->getData('fileId'), $fileId3);
        $revisions = Repo::submissionFile()->getRevisions($submissionFile2->getId());
        $revisionFileIds = [];
        foreach ($revisions as $revision) {
            $revisionFileIds[] = $revision->fileId;
        }
        self::assertEquals($revisionFileIds, [$fileId3, $fileId2]);

        // Delete a file
        $submissionFileDao->delete($submissionFile2);
        $submissionFile2 = $submissionFileDao->get($submissionFile2Id);
        self::assertNull($submissionFile2);

        $this->_cleanFiles($submissionId);

        // Delete the test submission
        Repo::submission()->dao->deleteById($submissionId);
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
            $submissionId = SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID;
        }
        $collector = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$submissionId]);
        $submissionFileIds = Repo::submissionFile()->getIds($collector);
        foreach ($submissionFileIds as $submissionFileId) {
            Repo::submissionFile()->dao->deleteById($submissionFileId);
        }
    }
}
