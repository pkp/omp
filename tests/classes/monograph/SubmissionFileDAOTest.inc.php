<?php

/**
 * @file tests/classes/monograph/SubmissionFileDAOTest.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileDAOTest
 * @ingroup tests_classes_monograph
 * @see SubmissionFileDAO
 *
 * @brief Test class for SubmissionFileDAO.
 */

import('lib.pkp.tests.DatabaseTestCase');
import('classes.monograph.SubmissionFileDAO');
import('classes.monograph.MonographFile');
import('classes.monograph.ArtworkFile');
import('classes.monograph.MonographDAO');
import('classes.monograph.Genre');
import('lib.pkp.classes.db.DBResultRange');

// Define a test file stage.
define('SUBMISSION_FILE_DAO_TEST_PRESS_ID', 999);
define('SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID', 9999);
define('SUBMISSION_FILE_DAO_TEST_GENRE_ID', 0);

class SubmissionFileDAOTest extends DatabaseTestCase {
	protected function setUp() {
		// Register a mock monograph DAO.
		$monographDao =& $this->getMock('MonographDAO', array('getMonograph'));
		$monograph = new Monograph();
		$monograph->setId(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID);
		$monograph->setPressId(SUBMISSION_FILE_DAO_TEST_PRESS_ID);
		$monographDao->expects($this->any())
		             ->method('getMonograph')
		             ->will($this->returnValue($monograph));
		DAORegistry::registerDAO('MonographDAO', $monographDao);

		$this->_cleanFiles();
	}

	protected function tearDown() {
		$this->_cleanFiles();
	}

	/**
	 * @covers SubmissionFileDAO
	 * @covers PKPSubmissionFileDAO
	 * @covers MonographFileDAODelegate
	 * @covers ArtworkFileDAODelegate
	 * @covers SubmissionFileDAODelegate
	 */
	public function testSubmissionFileCrud() {
		// Create two test files, one monograph file one artwork file.
		$artworkFile1 = new ArtworkFile();
		$artworkFile1->setName('test-artwork', 'en_US');
		$artworkFile1->setFileName('test-artwork.jpg');
		$artworkFile1->setCaption('test-caption');
		$artworkFile1->setFileStage(MONOGRAPH_FILE_PRODUCTION);
		$artworkFile1->setSubmissionId(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID);
		$artworkFile1->setFileType('image/jpeg');
		$artworkFile1->setFileSize(512);
		$artworkFile1->setDateUploaded('2011-12-04 00:00:00');
		$artworkFile1->setDateModified('2011-12-04 00:00:00');
		$artworkFile1->setGenreId(2);
		$artworkFile1->setAssocType(ASSOC_TYPE_REVIEW_ASSIGNMENT);
		$artworkFile1->setAssocId(5);

		$monographFile = new MonographFile();
		$monographFile->setName('test-document', 'en_US');
		$monographFile->setFileName('test-file.doc');
		$monographFile->setFileStage(MONOGRAPH_FILE_PRODUCTION);
		$monographFile->setSubmissionId(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID);
		$monographFile->setFileType('application/pdf');
		$monographFile->setFileSize(256);
		$monographFile->setDateUploaded('2011-12-05 00:00:00');
		$monographFile->setDateModified('2011-12-05 00:00:00');
		$monographFile->setGenreId(1);

		// Test the isInlineable method.
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		self::assertFalse($submissionFileDao->isInlineable($monographFile));
		self::assertTrue($submissionFileDao->isInlineable($artworkFile1));

		// Persist the two test files.
		$artworkFile1 =& $submissionFileDao->insertObject($artworkFile1); /* @var $artworkFile1 ArtworkFile */
		$monographFile =& $submissionFileDao->insertObject($monographFile);

		// Persist a second revision of the artwork file.
		$artworkFile2 =& cloneObject($artworkFile1); /* @var $artworkFile2 ArtworkFile */
		$artworkFile2->setRevision(2);
		$artworkFile2->setDateUploaded('2011-12-05 00:00:00');
		$artworkFile2->setDateModified('2011-12-05 00:00:00');
		$artworkFile2 =& $submissionFileDao->insertObject($artworkFile2);

		// Retrieve the first revision of the artwork file.
		self::assertNull($submissionFileDao->getRevision(null, $artworkFile1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($artworkFile1->getFileId(), null));
		self::assertEquals($artworkFile1, $submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision()));
		self::assertEquals($artworkFile1, $submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision(), $artworkFile1->getFileStage()));
		self::assertEquals($artworkFile1, $submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision(), $artworkFile1->getFileStage(), SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID));
		self::assertNull($submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision(), MONOGRAPH_FILE_PRODUCTION+1));
		self::assertNull($submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision(), null, SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID+1));

		// Update the latest revision of the artwork file.
		$artworkFile2->setOriginalFileName('updated-file-name');
		$artworkFile2->setCaption('test-caption');
		self::assertTrue($submissionFileDao->updateObject($artworkFile2));

		// Retrieve the latest revision of the artwork file.
		self::assertNull($submissionFileDao->getLatestRevision(null));
		self::assertEquals($artworkFile2, $submissionFileDao->getLatestRevision($artworkFile2->getFileId()));
		self::assertEquals($artworkFile2, $submissionFileDao->getLatestRevision($artworkFile2->getFileId(), $artworkFile2->getFileStage()));
		self::assertEquals($artworkFile2, $submissionFileDao->getLatestRevision($artworkFile2->getFileId(), $artworkFile2->getFileStage(), SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID));
		self::assertNull($submissionFileDao->getLatestRevision($artworkFile2->getFileId(), MONOGRAPH_FILE_PRODUCTION+1));
		self::assertNull($submissionFileDao->getLatestRevision($artworkFile2->getFileId(), null, SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID+1));

		// Retrieve the latest revisions of both files.
		self::assertNull($submissionFileDao->getLatestRevisions(null));
		self::assertEquals(array($artworkFile2, $monographFile), $submissionFileDao->getLatestRevisions(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID));
		self::assertEquals(array($artworkFile2, $monographFile), $submissionFileDao->getLatestRevisions(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID, MONOGRAPH_FILE_PRODUCTION));
		self::assertEquals(array(), $submissionFileDao->getLatestRevisions(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID+1));
		self::assertEquals(array(), $submissionFileDao->getLatestRevisions(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID, MONOGRAPH_FILE_PRODUCTION+1));

		// Test paging.
		$rangeInfo = new DBResultRange(2, 1);
		self::assertEquals(array($artworkFile2, $monographFile), $submissionFileDao->getLatestRevisions(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID, null, $rangeInfo));
		$rangeInfo = new DBResultRange(1, 1);
		self::assertEquals(array($artworkFile2), $submissionFileDao->getLatestRevisions(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID, null, $rangeInfo));
		$rangeInfo = new DBResultRange(1, 2);
		self::assertEquals(array($monographFile), $submissionFileDao->getLatestRevisions(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID, null, $rangeInfo));

		// Retrieve all revisions of the artwork file.
		self::assertNull($submissionFileDao->getAllRevisions(null));
		self::assertEquals(array($artworkFile2, $artworkFile1), $submissionFileDao->getAllRevisions($artworkFile1->getFileId(), MONOGRAPH_FILE_PRODUCTION));
		self::assertEquals(array($artworkFile2, $artworkFile1), $submissionFileDao->getAllRevisions($artworkFile1->getFileId(), MONOGRAPH_FILE_PRODUCTION, SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID));
		self::assertEquals(array(), $submissionFileDao->getAllRevisions($artworkFile1->getFileId(), null, SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID+1));
		self::assertEquals(array(), $submissionFileDao->getAllRevisions($artworkFile1->getFileId(), MONOGRAPH_FILE_PRODUCTION+1, null));

		// Retrieve the latest revisions by association.
		self::assertNull($submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, null));
		self::assertNull($submissionFileDao->getLatestRevisionsByAssocId(null, 5));
		self::assertEquals(array($artworkFile2), $submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5));
		self::assertEquals(array($artworkFile2), $submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5, MONOGRAPH_FILE_PRODUCTION));
		self::assertEquals(array(), $submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5, MONOGRAPH_FILE_PRODUCTION+1));

		// Retrieve all revisions by association.
		self::assertNull($submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, null));
		self::assertNull($submissionFileDao->getAllRevisionsByAssocId(null, 5));
		self::assertEquals(array($artworkFile2, $artworkFile1), $submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5));
		self::assertEquals(array($artworkFile2, $artworkFile1), $submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5, MONOGRAPH_FILE_PRODUCTION));
		self::assertEquals(array(), $submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5, MONOGRAPH_FILE_PRODUCTION+1));

		// Delete the first revision of the artwork file.
		// NB: This implicitly tests deletion by ID.
		self::assertEquals(1, $submissionFileDao->deleteRevision($artworkFile1));
		self::assertNull($submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision()));
		// Re-insert the file for the next test.
		self::assertEquals($artworkFile1, $submissionFileDao->insertObject($artworkFile1));

		// Delete the latest revision of the artwork file.
		self::assertEquals(1, $submissionFileDao->deleteLatestRevisionById($artworkFile1->getFileId()));
		self::assertType('ArtworkFile', $submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($artworkFile2->getFileId(), $artworkFile2->getRevision()));
		// Re-insert the file for the next test.
		self::assertEquals($artworkFile2, $submissionFileDao->insertObject($artworkFile2));

		// Delete all revisions of the artwork file.
		self::assertEquals(2, $submissionFileDao->deleteAllRevisionsById($artworkFile1->getFileId()));
		self::assertType('MonographFile', $submissionFileDao->getRevision($monographFile->getFileId(), $monographFile->getRevision()));
		self::assertNull($submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($artworkFile2->getFileId(), $artworkFile2->getRevision()));
		// Re-insert the files for the next test.
		self::assertEquals($artworkFile1, $submissionFileDao->insertObject($artworkFile1));
		self::assertEquals($artworkFile2, $submissionFileDao->insertObject($artworkFile2));

		// Delete all revisions by assoc id.
		self::assertEquals(2, $submissionFileDao->deleteAllRevisionsByAssocId(ASSOC_TYPE_REVIEW_ASSIGNMENT, 5));
		self::assertType('MonographFile', $submissionFileDao->getRevision($monographFile->getFileId(), $monographFile->getRevision()));
		self::assertNull($submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($artworkFile2->getFileId(), $artworkFile2->getRevision()));
		// Re-insert the files for the next test.
		self::assertEquals($artworkFile1, $submissionFileDao->insertObject($artworkFile1));
		self::assertEquals($artworkFile2, $submissionFileDao->insertObject($artworkFile2));

		// Delete all revisions by submission id.
		self::assertEquals(3, $submissionFileDao->deleteAllRevisionsBySubmissionId(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID));
		self::assertNull($submissionFileDao->getRevision($monographFile->getFileId(), $monographFile->getRevision()));
		self::assertNull($submissionFileDao->getRevision($artworkFile1->getFileId(), $artworkFile1->getRevision()));
		self::assertNull($submissionFileDao->getRevision($artworkFile2->getFileId(), $artworkFile2->getRevision()));

		// Test insertion of new revisions.
		// Create two artwork files with different file ids.
		$artworkFile1->setFileId(null);
		$artworkFile1->setRevision(null);
		$artworkFile1 =& $submissionFileDao->insertObject($artworkFile1);
		$artworkFile2->setFileId(null);
		$artworkFile2->setRevision(null);
		$artworkFile2->setGenreId($artworkFile1->getGenreId()+1);
		$artworkFile2 =& $submissionFileDao->insertObject($artworkFile2);

		// Test the file ids, revisions and identifying fields.
		self::assertNotEquals($artworkFile1->getFileId(), $artworkFile2->getFileId());
		self::assertNotEquals($artworkFile1->getGenreId(), $artworkFile2->getGenreId());
		self::assertEquals(1, $submissionFileDao->getLatestRevisionNumber($artworkFile1->getFileId()));
		self::assertEquals(1, $submissionFileDao->getLatestRevisionNumber($artworkFile2->getFileId()));

		// Now make the second file a revision of the first.
		$artworkFile2 =& $submissionFileDao->setAsLatestRevision($artworkFile1->getFileId(), $artworkFile2->getFileId(),
				$artworkFile1->getSubmissionId(), $artworkFile1->getFileStage());

		// And test the file ids, revisions, identifying fields and types again.
		self::assertEquals($artworkFile1->getFileId(), $artworkFile2->getFileId());
		self::assertEquals($artworkFile1->getGenreId(), $artworkFile2->getGenreId());
		self::assertEquals(1, $artworkFile1->getRevision());
		self::assertEquals(2, $submissionFileDao->getLatestRevisionNumber($artworkFile1->getFileId()));
		$submissionFiles =& $submissionFileDao->getAllRevisions($artworkFile1->getFileId());
		self::assertEquals(2, count($submissionFiles));
		foreach($submissionFiles as $submissionFile) { /* @var $submissionFile SubmissionFile */
			self::assertType('ArtworkFile', $submissionFile);
		}

		// Test type casting.
		$fileId = $artworkFile1->getFileId();
		$sourceFiles = array($artworkFile2, $artworkFile1);

		// Downcast the all revisions of the artwork file to monograph files.
		$submissionFileDao->cast($fileId, 'MonographFile');

		// Test whether the target type is correct.
		$downgradedFiles =& $submissionFileDao->getAllRevisions($fileId);
		self::assertEquals(2, count($downgradedFiles));

		// Test that no data on the target interface has been lost.
		foreach($sourceFiles as $index => $sourceFile) {
			$targetFile =& $downgradedFiles[$index];
			self::assertType('MonographFile', $targetFile);
			$this->_compareFiles($sourceFile, $targetFile);
			unset($targetFile);
		}

		// Upcast the first revision of the monograph file back to an artwork file.
		$submissionFileDao->cast($fileId, 'ArtworkFile', 1);

		// Check that the first revision has been upcast.
		$targetFile =& $submissionFileDao->getRevision($fileId, 1);
		self::assertType('ArtworkFile', $targetFile);

		// Compare the basic fields belonging to monograph files.
		$this->_compareFiles($downgradedFiles[1], $targetFile);

		// Make sure that other fields contain default values as
		// they got lost on downgrade.
		self::assertNull($targetFile->getCaption());

		// Check that the second revision has not changed.
		self::assertEquals($downgradedFiles[0], $submissionFileDao->getRevision($fileId, 2));
	}

	function testNewDataObjectByGenreId() {
		// Register a mock genre DAO.
		$genreDao =& $this->getMock('GenreDAO', array('getById'));
		DAORegistry::registerDAO('GenreDAO', $genreDao);

		// Instantiate the SUT.
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */

		// Let the mock genre DAO return a monograph file genre first.
		$genre = new Genre();
		$genre->setCategory(GENRE_CATEGORY_DOCUMENT);
		$genreDao->expects($this->any())
		         ->method('getById')
		         ->will($this->returnValue($genre));

		// Test whether the newDataObjectByGenreId method will return a monograph file.
		$fileObject =& $submissionFileDao->newDataObjectByGenreId(SUBMISSION_FILE_DAO_TEST_GENRE_ID);
		self::assertType('MonographFile', $fileObject);

		// Now set an artwork genre and try again.
		$genre->setCategory(GENRE_CATEGORY_ARTWORK);
		$fileObject =& $submissionFileDao->newDataObjectByGenreId(SUBMISSION_FILE_DAO_TEST_GENRE_ID);
		self::assertType('ArtworkFile', $fileObject);
	}


	//
	// Private helper methods
	//
	/**
	 * Compare the common properties of monograph and
	 * artwork files even when the two files do not have the
	 * same implementation.
	 * @param $sourceFile MonographFile
	 * @param $targetFile MonographFile
	 */
	function _compareFiles($sourceFile, $targetFile) {
		self::assertEquals($sourceFile->getName('en_US'), $targetFile->getName('en_US'));
		self::assertEquals($sourceFile->getFileName(), $targetFile->getFileName());
		self::assertEquals($sourceFile->getFileStage(), $targetFile->getFileStage());
		self::assertEquals($sourceFile->getSubmissionId(), $targetFile->getSubmissionId());
		self::assertEquals($sourceFile->getFileType(), $targetFile->getFileType());
		self::assertEquals($sourceFile->getFileSize(), $targetFile->getFileSize());
		self::assertEquals($sourceFile->getDateUploaded(), $targetFile->getDateUploaded());
		self::assertEquals($sourceFile->getDateModified(), $targetFile->getDateModified());
		self::assertEquals($sourceFile->getGenreId(), $targetFile->getGenreId());
		self::assertEquals($sourceFile->getAssocType(), $targetFile->getAssocType());
		self::assertEquals($sourceFile->getAssocId(), $targetFile->getAssocId());
	}

	/**
	 * Remove remnants from the tests.
	 */
	private function _cleanFiles() {
		// Delete the test submission's files.
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFileDao->deleteAllRevisionsBySubmissionId(SUBMISSION_FILE_DAO_TEST_SUBMISSION_ID);
	}
}
?>