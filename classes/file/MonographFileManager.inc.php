<?php

/**
 * @file classes/file/MonographFileManager.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileManager
 * @ingroup file
 *
 * @brief Helper class for database-backed monograph file management tasks.
 *
 * Monograph directory structure:
 * [monograph id]/note
 * [monograph id]/public
 * [monograph id]/submission
 * [monograph id]/submission/original
 * [monograph id]/submission/review
 * [monograph id]/submission/review/attachment
 * [monograph id]/submission/editor
 * [monograph id]/submission/copyedit
 * [monograph id]/submission/layout
 * [monograph id]/attachment
 */

import('file.BaseMonographFileManager');

class MonographFileManager extends BaseMonographFileManager {
	/**
	 * Constructor.
	 * @param $pressId int
	 * @param $monographId int
	 */
	function MonographFileManager($pressId, $monographId) {
		parent::BaseMonographFileManager($pressId, $monographId);
	}


	//
	// Public methods
	//
	/**
	 * Upload a monograph file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileStage int monograph file workflow stage
	 * @param $uploaderUserId int The id of the user that uploaded the file.
	 * @param $uploaderUserGroupId int The id of the user group that the uploader acted in
	 *  when uploading the file.
	 * @param $revisedFileId int
	 * @param $genreId int (e.g. Manusciprt, Appendix, etc.)
	 * @return MonographFile
	 */
	function &uploadMonographFile($fileName, $fileStage, $uploaderUserId,
			$uploaderUserGroupId, $revisedFileId = null, $genreId = null, $assocType = null, $assocId = null) {
		return $this->_handleUpload(
			$fileName, $fileStage, $uploaderUserId,
			$uploaderUserGroupId, $revisedFileId, $genreId, $assocType, $assocId
		);
	}

	/**
	 * Delete a file.
	 * @param $fileId integer
	 * @param $revisionId integer
	 * @return boolean returns true if successful
	 */
	function deleteFile($fileId, $revision = null) {
		$monographFile =& $this->_getFile($fileId, $revision);
		if (isset($monographFile)) {
			return parent::deleteFile($monographFile->getfilePath());
		} else {
			return false;
		}
	}

	/**
	 * Download a file.
	 * @param $fileId int the file id of the file to download
	 * @param $revision int the revision of the file to download
	 * @param $inline print file as inline instead of attachment, optional
	 * @return boolean
	 */
	function downloadFile($fileId, $revision = null, $inline = false) {
		$returner = false;
		$monographFile =& $this->_getFile($fileId, $revision);
		if (isset($monographFile)) {
			// Make sure that the file belongs to the monograph.
			if ($monographFile->getMonographId() != $this->getMonographId()) fatalError('Invalid file id!');

			MonographFileManager::recordView($monographFile);

			// Send the file to the user.
			$filePath = $monographFile->getFilePath();
			$mediaType = $monographFile->getFileType();
			$returner = parent::downloadFile($filePath, $mediaType, $inline);
		}

		return $returner;
	}

	/**
	 * Record a file view in database.
	 * @param $monographFile MonographFile
	 */
	function recordView(&$monographFile) {
		// Mark the file as viewed by this user.
		$sessionManager =& SessionManager::getManager();
		$session =& $sessionManager->getUserSession();
		$user =& $session->getUser();
		if (is_a($user, 'User')) {
			$viewsDao =& DAORegistry::getDAO('ViewsDAO');
			$viewsDao->recordView(
			ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileIdAndRevision(),
			$user->getId()
			);
		}
	}

	/**
	 * Copy a temporary file to a monograph file.
	 * @param $temporaryFile MonographFile
	 * @param $fileStage integer
	 * @param $assocId integer
	 * @param $assocType integer
	 * @return integer the file ID (false if upload failed)
	 */
	function temporaryFileToMonographFile(&$temporaryFile, $fileStage, $uploaderUserId, $uploaderUserGroupId, $revisedFileId, $genreId, $assocType, $assocId) {
		// Instantiate and pre-populate the new target monograph file.
		$sourceFile = $temporaryFile->getFilePath();
		$monographFile =& $this->_instantiateMonographFile($sourceFile, $fileStage, $revisedFileId, $genreId, $assocType, $assocId);

		// Transfer data from the temporary file to the monograph file.
		$monographFile->setFileType($temporaryFile->getFileType());
		$monographFile->setOriginalFileName($temporaryFile->getOriginalFileName());

		// Set the user and user group ids
		$monographFile->setUploaderUserId($uploaderUserId);
		$monographFile->setUserGroupId($uploaderUserGroupId);

		// Copy the temporary file to its final destination and persist
		// its metadata to the database.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		if (!$submissionFileDao->insertObject($monographFile, $sourceFile)) return false;

		// Return the new file id.
		return $monographFile->getFileId();
	}

	/**
	 * Copies an existing ArticleFile and renames it.
	 * @param $sourceFileId int
	 * @param $sourceRevision int
	 * @param $fileStage int
	 * @param $destFileId int (optional)
	 * @param $viewable boolean (optional)
	 */
	function copyFileToFileStage($sourceFileId, $sourceRevision, $newFileStage, $destFileId = null, $viewable = false) {
		if (HookRegistry::call('MonographFileManager::copyFileToFileStage', array(&$sourceFileId, &$sourceRevision, &$newFileStage, &$destFileId, &$result))) return $result;

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$sourceFile =& $submissionFileDao->getRevision($sourceFileId, $sourceRevision); /* @var $sourceFile MonographFile */
		if (!$sourceFile) return false;

		// Rename the variable just so that we don't get confused.
		$destFile =& $sourceFile;

		// Find out where the source file lives.
		$sourcePath = $sourceFile->getFilePath();

		// Update the ID (or clear if making a new file) and get new revision number.
		if ($destFileId != null) {
			$currentRevision = $submissionFileDao->getLatestRevisionNumber($destFileId);
			$revision = $currentRevision + 1;
			$destFile->setFileId($destFileId);
		} else {
			$destFile->setFileId(null);
			$revision = 1;
		}

		// Update the necessary fields of the destination file.
		$destFile->setRevision($revision);
		$destFile->setFileStage($newFileStage);
		$destFile->setDateModified(Core::getCurrentDate());
		$destFile->setViewable($viewable);
		// Set the old file as the source
		$destFile->setSourceFileId($sourceFileId);
		$destFile->setSourceRevision($sourceRevision);

		// Find out where the file should go.
		$destPath = $destFile->getFilePath();

		// Copy the file to the new location.
		$this->copyFile($sourcePath, $destPath);

		// Now insert the row into the DB and get the inserted file id.
		$insertedFile =& $submissionFileDao->insertObject($destFile, $destPath);

		return array($insertedFile->getFileId(), $insertedFile->getRevision());
	}

	//
	// Private helper methods
	//
	/**
	 * Upload the file and add it to the database.
	 * @param $fileName string index into the $_FILES array
	 * @param $fileStage int monograph file stage (one of the MONOGRAPH_FILE_* constants)
	 * @param $uploaderUserId int The id of the user that uploaded the file.
	 * @param $uploaderUserGroupId int The id of the user group that the uploader acted in
	 *  when uploading the file.
	 * @param $revisedFileId int ID of an existing file to revise
	 * @param $genreId int foreign key into genres table (e.g. manuscript, etc.)
	 * @param $assocType int
	 * @param $assocId int
	 * @return MonographFile the uploaded monograph file or null if an error occured.
	 */
	function &_handleUpload($fileName, $fileStage, $uploaderUserId, $uploaderUserGroupId,
			$revisedFileId = null, $genreId = null, $assocType = null, $assocId = null) {

		$nullVar = null;

		// Ensure that the file has been correctly uploaded to the server.
		if (!$this->uploadedFileExists($fileName)) return $nullVar;

		// Retrieve the location of the uploaded file.
		$sourceFile = $this->getUploadedFilePath($fileName);

		// Instantiate and pre-populate a new monograph file object.
		$monographFile = $this->_instantiateMonographFile($sourceFile, $fileStage, $revisedFileId, $genreId, $assocType, $assocId);
		if (is_null($monographFile)) return $nullVar;

		// Retrieve and copy the file type of the uploaded file.
		$fileType = $this->getUploadedFileType($fileName);
		assert($fileType !== false);
		$monographFile->setFileType($fileType);

		// Retrieve and copy the file name of the uploaded file.
		$originalFileName = $this->getUploadedFileName($fileName);
		assert($originalFileName !== false);
		$monographFile->setOriginalFileName($this->truncateFileName($originalFileName));

		// Set the uploader's user and user group id.
		$monographFile->setUploaderUserId($uploaderUserId);
		$monographFile->setUserGroupId($uploaderUserGroupId);

		// Copy the uploaded file to its final destination and
		// persist its meta-data to the database.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		return $submissionFileDao->insertObject($monographFile, $fileName, true);
	}

	/**
	 * Routine to instantiate and pre-populate a new monograph file.
	 * @param $sourceFilePath string
	 * @param $fileStage integer MONOGRAPH_FILE_...
	 * @param $revisedFileId integer optional
	 * @param $genreId integer optional
	 * @param $assocId integer optional
	 * @param $assocType integer optional
	 * @return MonographFile returns the instantiated monograph file or null if an error occurs.
	 */
	function &_instantiateMonographFile($sourceFilePath, $fileStage, $revisedFileId = null, $genreId = null, $assocType = null, $assocId = null) {
		$nullVar = null;

		// Retrieve the submission file DAO.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		// We either need a genre id or a revised file, otherwise
		// we cannot identify the target file implementation.
		assert($genreId || $revisedFileId);
		if (!$genreId || $revisedFileId) {
			// Retrieve the revised file. (null $fileStage in case the revision is from a previous stage).
			$revisedFile =& $submissionFileDao->getLatestRevision($revisedFileId, null, $this->getMonographId());
			if (!is_a($revisedFile, 'MonographFile')) return $nullVar;
		}

		// If we don't have a genre then use the genre from the
		// existing file.
		if (!$genreId) {
			$genreId = $revisedFile->getGenreId();
		}

		// Instantiate a new monograph file implementation.
		$monographFile =& $submissionFileDao->newDataObjectByGenreId($genreId); /* @var $monographFile MonographFile */
		$monographFile->setMonographId($this->getMonographId());

		// Do we create a new file or a new revision of an existing file?
		if ($revisedFileId) {
			// Make sure that the monograph of the revised file is
			// the same as that of the uploaded file.
			if ($revisedFile->getMonographId() != $this->getMonographId()) return $nullVar;

			// If file stages are different we reference with the sourceFileId
			// Otherwise, we keep the file id, update the revision, and copy other fields.
			if(!is_null($fileStage) && $fileStage !== $revisedFile->getFileStage()) {
				$monographFile->setSourceFileId($revisedFileId);
				$monographFile->setSourceRevision($revisedFile->getRevision());
				$monographFile->setRevision(1);
			} else {
				// Create a new revision of the file with the existing file id.
				$monographFile->setFileId($revisedFileId);
				$monographFile->setRevision($revisedFile->getRevision()+1);

				// Copy the file stage (in case of null passed in).
				$fileStage = (int)$revisedFile->getFileStage();

				// Copy the assoc type.
				if(!is_null($assocType) && $assocType !== $revisedFile->getAssocType()) fatalError('Invalid monograph file assoc type!');
				$assocType = (int)$revisedFile->getAssocType();

				// Copy the assoc id.
				if (!is_null($assocId) && $assocId !== $revisedFile->getAssocId()) fatalError('Invalid monograph file assoc ID!');
				$assocId = (int)$revisedFile->getAssocId();
			}
		} else {
			// Create the first revision of a new file.
			$monographFile->setRevision(1);
		}

		// Determine and set the file size of the file.
		$monographFile->setFileSize(filesize($sourceFilePath));

		// Set the file file stage.
		$monographFile->setFileStage($fileStage);

		// Set the file genre.
		$monographFile->setGenreId($genreId);

		// Set dates to the current system date.
		$monographFile->setDateUploaded(Core::getCurrentDate());
		$monographFile->setDateModified(Core::getCurrentDate());

		// Is the monograph file associated to another entity?
		if(isset($assocId)) {
			assert(isset($assocType));
			$monographFile->setAssocType($assocType);
			$monographFile->setAssocId($assocId);
		}

		// Return the pre-populated monograph file.
		return $monographFile;
	}

	/**
	 * Internal helper method to retrieve file
	 * information by file ID.
	 * @param $fileId integer
	 * @param $revision integer
	 * @return MonographFile
	 */
	function &_getFile($fileId, $revision = null) {
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		if ($revision) {
			$monographFile =& $submissionFileDao->getRevision($fileId, $revision);
		} else {
			$monographFile =& $submissionFileDao->getLatestRevision($fileId);
		}
		return $monographFile;
	}
}

?>
