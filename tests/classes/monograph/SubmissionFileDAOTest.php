<?php

/**
 * @file tests/classes/monograph/SubmissionFileDAOTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileDAOTest
 * @ingroup tests_classes_monograph
 * @see SubmissionFileDAO
 *
 * @brief Test class for SubmissionFileDAO.
 */

import('lib.pkp.tests.DatabaseTestCase');
import('lib.pkp.classes.submission.SubmissionFileDAO');
import('lib.pkp.classes.submission.SubmissionArtworkFileDAODelegate');
import('lib.pkp.classes.submission.SubmissionFile');
import('lib.pkp.classes.submission.SubmissionArtworkFile');
import('classes.submission.SubmissionDAO');
import('lib.pkp.classes.submission.Genre');
import('lib.pkp.classes.submission.reviewRound.ReviewRound');
import('lib.pkp.classes.db.DBResultRange');
import('lib.pkp.classes.core.PKPRouter');
import('lib.pkp.classes.core.PKPRequest');

// Define test ids.
define('SUBMISSION_FILE_DAO_TEST_PRESS_ID', 999);
define('SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID', 9999);
define('SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID', 1);
define('SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID', 2);


// Define a temp file location for testing.
define('TMP_FILES', '/tmp');

class SubmissionFileDAOTest extends DatabaseTestCase {
	private $testFile;

	protected function setUp() : void {
		// Create a test file on the file system.
		$this->testFile = tempnam(TMP_FILES, 'SubmissionFile');

		// Mock a press
		import('classes.press.Press');
		$press = new Press();
		$press->setPrimaryLocale('en_US');
		$press->setPath('press-path');
		$press->setId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);

		// Mock a request
		$mockRequest = $this->getMockBuilder(PKPRequest::class)
			->setMethods(array('getContext'))
			->getMock();
		$mockRequest->expects($this->any())
			->method('getContext')
			->will($this->returnValue($press));
		Registry::get('request', true, $mockRequest);

		// Register a mock monograph DAO.
		$submissionDao = $this->getMockBuilder(SubmissionDAO::class)
			->setMethods(array('getById'))
			->getMock();
		$monograph = new Submission();
		$monograph->setId(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID);
		$monograph->setPressId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);
		$monograph->setLocale('en_US');
		$submissionDao->expects($this->any())
			->method('getById')
			->will($this->returnValue($monograph));
		DAORegistry::registerDAO('SubmissionDAO', $submissionDao);

		// Register a mock genre DAO.
		$genreDao = $this->getMockBuilder(GenreDAO::class)
			->setMethods(array('getById'))
			->getMock();
		DAORegistry::registerDAO('GenreDAO', $genreDao);
		$genreDao->expects($this->any())
			->method('getById')
			->will($this->returnCallback(array($this, 'getTestGenre')));

		$this->_cleanFiles();

		$request = Application::get()->getRequest();
		if (is_null($request->getRouter())) {
			$router = new PKPRouter();
			$request->setRouter($router);
		}
	}

	protected function tearDown() : void {
		if (file_exists($this->testFile)) unlink($this->testFile);
		$this->_cleanFiles();
	}

	/**
	 * @covers SubmissionFileDAO
	 * @covers SubmissionFileDAO
	 * @covers SubmissionFileDAODelegate
	 * @covers SubmissionArtworkFileDAODelegate
	 * @covers SubmissionFileDAODelegate
	 */
	public function testSubmissionFileCrud() {
		//
		// Create test data.
		//
		// Create a submission
		$submissionDao = Application::getSubmissionDao();
		$submission = $submissionDao->newDataObject();
		$submission->setPressId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);
		$submission->setLocale('en_US');
		$submissionId = $submissionDao->insertObject($submission);

		$publicationDao = DAORegistry::getDAO('PublicationDAO');
		$publication = $publicationDao->newDataObject();
		$publication->setData('submissionId', $submissionId);
		$publication->setData('locale', 'en_US');
		$publicationDao->insertObject($publication);

		$submission->setData('currentPublicationId', $publication->getId());
		$submissionDao->updateObject($submission);

		$submissionDao = $this->getMockBuilder(SubmissionDAO::class)
			->setMethods(array('getById'))
			->getMock();
		$monograph = new Submission();
		$monograph->setId($submissionId);
		$monograph->setPressId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);
		$monograph->setLocale('en_US');
		$submissionDao->expects($this->any())
			->method('getById')
			->will($this->returnValue($monograph));
		DAORegistry::registerDAO('SubmissionDAO', $submissionDao);

		// Create two test files, one monograph file one artwork file.
		$file1Rev1 = new SubmissionArtworkFile();
		$file1Rev1->setSubmissionLocale('en_US');
		$file1Rev1->setUploaderUserId(1);
		$file1Rev1->setCaption('test-caption');
		$file1Rev1->setFileStage(SUBMISSION_FILE_PROOF);
		$file1Rev1->setSubmissionId($submissionId);
		$file1Rev1->setFileType('image/jpeg');
		$file1Rev1->setFileSize(512);
		$file1Rev1->setDateUploaded('2011-12-04 00:00:00');
		$file1Rev1->setDateModified('2011-12-04 00:00:00');
		$file1Rev1->setAssocType(ASSOC_TYPE_REVIEW_ASSIGNMENT);
		$file1Rev1->setAssocId(5);

		$file2Rev1 = new SubmissionFile();
		$file2Rev1->setSubmissionLocale('en_US');
		$file2Rev1->setUploaderUserId(1);
		$file2Rev1->setFileStage(SUBMISSION_FILE_PROOF);
		$file2Rev1->setSubmissionId($submissionId);
		$file2Rev1->setFileType('application/pdf');
		$file2Rev1->setFileSize(256);
		$file2Rev1->setDateUploaded('2011-12-05 00:00:00');
		$file2Rev1->setDateModified('2011-12-05 00:00:00');


		//
		// insertObject()
		//
		// Persist the two test files.
		$this->_insertFile($file1Rev1, 'test artwork', SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID);
		self::assertTrue(is_a($file1Rev1, 'SubmissionArtworkFile'));
		$this->_insertFile($file2Rev1, 'test monograph', SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);
		self::assertTrue(is_a($file2Rev1, 'SubmissionFile'));

		// Persist a second revision of the artwork file but this time with a
		// document genre so that it needs to be downcast for insert.
		$downcastFile = clone($file1Rev1);
		$downcastFile->setRevision(2);
		$downcastFile->setDateUploaded('2011-12-05 00:00:00');
		$downcastFile->setDateModified('2011-12-05 00:00:00');
		$file1Rev2 = $this->_insertFile($downcastFile, 'test downcast', SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);

		// Test whether the target type is correct.
		self::assertTrue(is_a($file1Rev2, 'SubmissionFile'));
		// Test that no data on the target interface has been lost.
		$this->_compareFiles($downcastFile, $file1Rev2);

		// Persist a second revision of the monograph file but this time with an
		// artwork genre so that it needs to be upcast for insert.
		$upcastFile = clone($file2Rev1);
		$upcastFile->setRevision(2);
		$file2Rev2 = $this->_insertFile($upcastFile, 'test upcast', SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID);

		// Test whether the target type is correct.
		self::assertTrue(is_a($file2Rev2, 'SubmissionArtworkFile'));
		// Test that no data on the target interface has been lost.
		$this->_compareFiles($upcastFile, $file2Rev2);
		// Make sure that other fields contain default values as
		// they are empty on upcast.
		self::assertNull($file2Rev2->getCaption());


		//
		// getRevision()
		//
		// Retrieve the first revision of the artwork file.
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		self::assertNull($submissionFileDao->getRevision(null, $file1Rev1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($file1Rev1->getFileId(), null));
		self::assertEquals($file1Rev1, $submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision()));
		self::assertEquals($file1Rev1, $submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision(), $file1Rev1->getFileStage()));
		self::assertEquals($file1Rev1, $submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision(), $file1Rev1->getFileStage(), $submissionId));
		self::assertNull($submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision(), SUBMISSION_FILE_PROOF+1));
		self::assertNull($submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision(), null, $submissionId+1));


		//
		// updateObject()
		//
		// Update the artwork file.
		$file1Rev1->setOriginalFileName('updated-file-name');
		$file1Rev1->setCaption('test-caption');
		$updatedFile = $submissionFileDao->updateObject($file1Rev1);

		// Now change the genre so that the canonical file name
		// and the file implementation will have to change.
		$previousFilePath = $file1Rev1->getFilePath();
		$file1Rev1->setGenreId(SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);
		$updatedFile = $submissionFileDao->updateObject($file1Rev1);

		// Test whether the target type is correct.
		self::assertTrue(is_a($updatedFile, 'SubmissionFile'));
		// Test that no data on the target interface has been lost.
		$this->_compareFiles($file1Rev1, $updatedFile);

		// Test the new file path and files.
		$newFilePath = $updatedFile->getFilePath();
		self::assertNotEquals($previousFilePath, $newFilePath);
		self::assertFileNotExists($previousFilePath);
		self::assertFileExists($newFilePath);

		// Now change the genre back so that we can test casting
		// in the other direction.
		$updatedFile->setGenreId(SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID);
		$updatedFile = $submissionFileDao->updateObject($updatedFile);

		// Test whether the target type is correct.
		self::assertTrue(is_a($updatedFile, 'SubmissionArtworkFile'));
		// Test that no data on the target interface has been lost.
		$this->_compareFiles($file1Rev1, $updatedFile);
		$file1Rev1 = $updatedFile;


		//
		// getLatestRevision()
		//
		// Retrieve the latest revision of file 1.
		$file1Rev2->setData('caption', null); // Clear out caption for comparison
		self::assertNull($submissionFileDao->getLatestRevision(null));
		self::assertEquals($file1Rev2, $submissionFileDao->getLatestRevision($file1Rev1->getFileId()));
		self::assertEquals($file1Rev2, $submissionFileDao->getLatestRevision($file1Rev1->getFileId(), $file1Rev1->getFileStage()));
		self::assertEquals($file1Rev2, $submissionFileDao->getLatestRevision($file1Rev1->getFileId(), $file1Rev1->getFileStage(), $submissionId));
		self::assertNull($submissionFileDao->getLatestRevision($file1Rev1->getFileId(), SUBMISSION_FILE_PROOF+1));
		self::assertNull($submissionFileDao->getLatestRevision($file1Rev1->getFileId(), null, $submissionId+1));


		//
		// getLatestRevisions()
		//
		// Calculate the unique ids of the test files.
		$uniqueId1_1 = $file1Rev1->getFileIdAndRevision();
		$uniqueId1_2 = $file1Rev2->getFileIdAndRevision();
		$uniqueId2_1 = $file2Rev1->getFileIdAndRevision();
		$uniqueId2_2 = $file2Rev2->getFileIdAndRevision();

		// Retrieve the latest revisions of both files.
		self::assertNull($submissionFileDao->getLatestRevisions(null));
		self::assertEquals(array($uniqueId1_2 => $file1Rev2, $uniqueId2_2 => $file2Rev2),
				$submissionFileDao->getLatestRevisions($submissionId));
		self::assertEquals(array($uniqueId1_2 => $file1Rev2, $uniqueId2_2 => $file2Rev2),
				$submissionFileDao->getLatestRevisions($submissionId, SUBMISSION_FILE_PROOF));
		self::assertEquals(array(),
				$submissionFileDao->getLatestRevisions($submissionId+1));
		self::assertEquals(array(),
				$submissionFileDao->getLatestRevisions($submissionId, SUBMISSION_FILE_PROOF+1));

		// Test paging.
		$rangeInfo = new DBResultRange(2, 1);
		self::assertEquals(array($uniqueId1_2 => $file1Rev2, $uniqueId2_2 => $file2Rev2),
				$submissionFileDao->getLatestRevisions($submissionId, null, $rangeInfo));
		$rangeInfo = new DBResultRange(1, 1);
		self::assertEquals(array($uniqueId1_2 => $file1Rev2),
				$submissionFileDao->getLatestRevisions($submissionId, null, $rangeInfo));
		$rangeInfo = new DBResultRange(1, 2);
		self::assertEquals(array($uniqueId2_2 => $file2Rev2),
				$submissionFileDao->getLatestRevisions($submissionId, null, $rangeInfo));


		//
		// getAllRevisions()
		//
		// Retrieve all revisions of file 1.
		self::assertNull($submissionFileDao->getAllRevisions(null));
		self::assertEquals(array($uniqueId1_2 => $file1Rev2, $uniqueId1_1 => $file1Rev1),
				$submissionFileDao->getAllRevisions($file1Rev1->getFileId(), SUBMISSION_FILE_PROOF));
		self::assertEquals(array($uniqueId1_2 => $file1Rev2, $uniqueId1_1 => $file1Rev1),
				$submissionFileDao->getAllRevisions($file1Rev1->getFileId(), SUBMISSION_FILE_PROOF, $submissionId));
		self::assertEquals(array(),
				$submissionFileDao->getAllRevisions($file1Rev1->getFileId(), null, $submissionId+1));
		self::assertEquals(array(),
				$submissionFileDao->getAllRevisions($file1Rev1->getFileId(), SUBMISSION_FILE_PROOF+1, null));


		//
		// getLatestRevisionsByAssocId()
		//
		// Retrieve the latest revisions by association.
		self::assertNull($submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, null));
		self::assertNull($submissionFileDao->getLatestRevisionsByAssocId(null, 5));
		self::assertEquals(array($uniqueId1_2 => $file1Rev2),
				$submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5));
		self::assertEquals(array($uniqueId1_2 => $file1Rev2),
				$submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5, null, SUBMISSION_FILE_PROOF));
		self::assertEquals(array(),
				$submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5, null, SUBMISSION_FILE_PROOF+1));

		// Retrieve all revisions by association.
		self::assertNull($submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, null));
		self::assertNull($submissionFileDao->getAllRevisionsByAssocId(null, 5));
		self::assertEquals(array($uniqueId1_2 => $file1Rev2, $uniqueId1_1 => $file1Rev1),
				$submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5));
		self::assertEquals(array($uniqueId1_2 => $file1Rev2, $uniqueId1_1 => $file1Rev1),
				$submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5, SUBMISSION_FILE_PROOF));
		self::assertEquals(array(), $submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5, SUBMISSION_FILE_PROOF+1));


		//
		// assignRevisionToReviewRound()
		//
		// Insert one more revision to test review round file assignments.
		$file1Rev3 = clone($file1Rev2);
		$file1Rev3->setRevision(3);
		self::assertEquals($file1Rev3, $submissionFileDao->insertObject($file1Rev3, $this->testFile));
		$uniqueId1_3 = $file1Rev3->getFileIdAndRevision();

		// Insert review round file assignments.
		$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound = $reviewRoundDao->build($submissionId, WORKFLOW_STAGE_ID_INTERNAL_REVIEW, 1);
		$submissionFileDao->assignRevisionToReviewRound($file1Rev1->getFileId(), $file1Rev1->getRevision(), $reviewRound);
		$submissionFileDao->assignRevisionToReviewRound($file2Rev2->getFileId(), $file2Rev2->getRevision(), $reviewRound);


		//
		// getRevisionsByReviewRound()
		//
		// Retrieve assigned review round files by review stage id and round.
		self::assertEquals(array($uniqueId1_1 => $file1Rev1, $uniqueId2_2 => $file2Rev2),
				$submissionFileDao->getRevisionsByReviewRound($reviewRound));


		//
		// getLatestRevisionsByReviewRound()
		//
		// Retrieve latest revisions of review round files
		self::assertEquals(array($uniqueId1_3 => $file1Rev3, $uniqueId2_2 => $file2Rev2),
				$submissionFileDao->getLatestRevisionsByReviewRound($reviewRound, null));


		//
		// deleteAllRevisionsByReviewRound()
		//
		$submissionFileDao->deleteAllRevisionsByReviewRound($reviewRound->getId());
		self::assertEquals(array(),
				$submissionFileDao->getRevisionsByReviewRound($reviewRound));


		//
		// deleteRevision() and deleteRevisionById()
		//
		// Delete the first revision of file1.
		// NB: This implicitly tests deletion by ID.
		self::assertEquals(1, $submissionFileDao->deleteRevision($file1Rev1));
		self::assertNull($submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision()));
		// Re-insert the file for the next test.
		self::assertEquals($file1Rev1, $submissionFileDao->insertObject($file1Rev1, $this->testFile));


		//
		// deleteLatestRevisionById()
		//
		// Delete the latest revision of file1.
		self::assertEquals(1, $submissionFileDao->deleteLatestRevisionById($file1Rev1->getFileId()));
		self::assertTrue(is_a($submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision()), 'SubmissionArtworkFile'));
		self::assertNull($submissionFileDao->getRevision($file1Rev3->getFileId(), $file1Rev3->getRevision()));


		//
		// deleteAllRevisionsById()
		//
		// Delete all revisions of file1.
		self::assertEquals(2, $submissionFileDao->deleteAllRevisionsById($file1Rev1->getFileId()));
		self::assertTrue(is_a($submissionFileDao->getRevision($file2Rev1->getFileId(), $file2Rev1->getRevision()), 'SubmissionFile'));
		self::assertNull($submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($file1Rev2->getFileId(), $file1Rev2->getRevision()));
		// Re-insert the files for the next test.
		self::assertEquals($file1Rev1, $submissionFileDao->insertObject($file1Rev1, $this->testFile));
		self::assertEquals($file1Rev2, $submissionFileDao->insertObject($file1Rev2, $this->testFile));


		//
		// deleteAllRevisionsByAssocId()
		//
		// Delete all revisions by assoc id.
		self::assertEquals(2, $submissionFileDao->deleteAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5));
		self::assertTrue(is_a($submissionFileDao->getRevision($file2Rev1->getFileId(), $file2Rev1->getRevision()), 'SubmissionFile'));
		self::assertNull($submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($file1Rev2->getFileId(), $file1Rev2->getRevision()));
		// Re-insert the files for the next test.
		self::assertEquals($file1Rev1, $submissionFileDao->insertObject($file1Rev1, $this->testFile));
		self::assertEquals($file1Rev2, $submissionFileDao->insertObject($file1Rev2, $this->testFile));


		//
		// deleteAllRevisionsBySubmissionId()
		//
		// Delete all revisions by submission id.
		self::assertEquals(4, $submissionFileDao->deleteAllRevisionsBySubmissionId($submissionId));
		self::assertNull($submissionFileDao->getRevision($file2Rev1->getFileId(), $file2Rev1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($file1Rev1->getFileId(), $file1Rev1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($file1Rev2->getFileId(), $file1Rev2->getRevision()));


		//
		// insertObject() for new revisions
		//
		// Test insertion of new revisions.
		// Create two files with different file ids.
		$file1Rev1->setFileId(null);
		$file1Rev1->setRevision(null);
		$file1Rev1 = $submissionFileDao->insertObject($file1Rev1, $this->testFile);

		$file1Rev2->setFileId(null);
		$file1Rev2->setRevision(null);
		$file1Rev2->setGenreId(SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);
		$file1Rev2 = $submissionFileDao->insertObject($file1Rev2, $this->testFile);

		// Test the file ids, revisions and identifying fields.
		self::assertNotEquals($file1Rev1->getFileId(), $file1Rev2->getFileId());
		self::assertNotEquals($file1Rev1->getGenreId(), $file1Rev2->getGenreId());
		self::assertEquals(1, $submissionFileDao->getLatestRevisionNumber($file1Rev1->getFileId()));
		self::assertEquals(1, $submissionFileDao->getLatestRevisionNumber($file1Rev2->getFileId()));


		//
		// setAsLatestRevision()
		//
		// Now make the second file a revision of the first.
		$file1Rev2 = $submissionFileDao->setAsLatestRevision($file1Rev1->getFileId(), $file1Rev2->getFileId(),
				$file1Rev1->getSubmissionId(), $file1Rev1->getFileStage());

		// And test the file ids, revisions, identifying fields and types again.
		self::assertEquals($file1Rev1->getFileId(), $file1Rev2->getFileId());
		self::assertEquals($file1Rev1->getGenreId(), $file1Rev2->getGenreId());
		self::assertEquals(1, $file1Rev1->getRevision());
		self::assertEquals(2, $submissionFileDao->getLatestRevisionNumber($file1Rev1->getFileId()));
		$submissionFiles = $submissionFileDao->getAllRevisions($file1Rev1->getFileId());
		self::assertEquals(2, count($submissionFiles));
		foreach($submissionFiles as $submissionFile) {
			self::assertTrue(is_a($submissionFile, 'SubmissionArtworkFile'));
		}

		$this->_cleanFiles($submissionId);

		// Delete the test submission
		$submissionDao = Application::getSubmissionDao();
		$submissionDao->deleteById($submissionId);
	}

	function testNewDataObjectByGenreId() {
		// Instantiate the SUT.
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');

		// Test whether the newDataObjectByGenreId method will return a monograph file.
		$fileObject = $submissionFileDao->newDataObjectByGenreId(SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID);
		self::assertTrue(is_a($fileObject, 'SubmissionFile'));

		// Now set an artwork genre and try again.
		$fileObject = $submissionFileDao->newDataObjectByGenreId(SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID);
		self::assertTrue(is_a($fileObject, 'SubmissionArtworkFile'));
	}

	//
	// Public helper methods
	//
	/**
	 * Return a test genre.
	 * @param $genreId integer
	 * @return Genre the test genre.
	 */
	public function getTestGenre($genreId) {
		// Create a test genre.
		switch($genreId) {
			case SUBMISSION_FILE_DAO_TEST_DOC_GENRE_ID:
				$category = GENRE_CATEGORY_DOCUMENT;
				$name = 'Document Genre';
				break;

			case SUBMISSION_FILE_DAO_TEST_ART_GENRE_ID:
				$category = GENRE_CATEGORY_ARTWORK;
				$name = 'Artwork Genre';
				break;

			default:
				self::fail();
		}
		$genre = new Genre();
		$request = Application::get()->getRequest();
		$press = $request->getContext();
		$genre->setContextId($press->getId());
		$genre->setId($genreId);
		$genre->setName($name, 'en_US');
		$genre->setCategory($category);
		return $genre;
	}


	//
	// Private helper methods
	//
	/**
	 * Compare the common properties of monograph and
	 * artwork files even when the two files do not have the
	 * same implementation.
	 * @param $sourceFile SubmissionFile
	 * @param $targetFile SubmissionFile
	 */
	function _compareFiles($sourceFile, $targetFile) {
		self::assertEquals($sourceFile->getFileStage(), $targetFile->getFileStage());
		self::assertEquals($sourceFile->getSubmissionId(), $targetFile->getSubmissionId());
		self::assertEquals($sourceFile->getFileType(), $targetFile->getFileType());
		self::assertEquals($sourceFile->getFileSize(), $targetFile->getFileSize());
		self::assertEquals($sourceFile->getDateUploaded(), $targetFile->getDateUploaded());
		self::assertEquals($sourceFile->getDateModified(), $targetFile->getDateModified());
		self::assertEquals($sourceFile->getAssocType(), $targetFile->getAssocType());
		self::assertEquals($sourceFile->getAssocId(), $targetFile->getAssocId());
	}

	/**
	 * Prepare and test inserting a file
	 * @param $file SubmissionFile
	 * @param $testContent string
	 * @param $genreCategory integer
	 * @return SubmissionFile
	 */
	private function _insertFile($file, $testContent, $genreId) {
		// Prepare the test.
		file_put_contents($this->testFile, $testContent);
		$file->setGenreId($genreId);

		// Insert the file.
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$file = $submissionFileDao->insertObject($file, $this->testFile);

		// Test the outcome.
		self::assertFileExists($file->getFilePath());
		self::assertEquals($testContent, file_get_contents($file->getFilePath()));

		return $file;
	}

	/**
	 * Remove remnants from the tests.
	 */
	private function _cleanFiles($submissionId = null) {
		// Delete the test submission's files.
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		if (!$submissionId) $submissionId = SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID;
		$submissionFileDao->deleteAllRevisionsBySubmissionId($submissionId);
	}
}

