<?php

/**
 * @file classes/file/MonographFileManager.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileManager
 * @ingroup file
 *
 * @brief Static helper class for monograph file management tasks.
 *
 * Monograph directory structure:
 * [monograph id]/note
 * [monograph id]/public
 * [monograph id]/submission
 * [monograph id]/submission/original
 * [monograph id]/submission/review
 * [monograph id]/submission/editor
 * [monograph id]/submission/copyedit
 * [monograph id]/submission/layout
 * [monograph id]/attachment
 */


import('lib.pkp.classes.file.FileManager');

class MonographFileManager extends FileManager {
	/**
	 * Constructor.
	 */
	function MonographFileManager() {
		parent::FileManager();
	}


	//
	// Public methods
	//
	/**
	 * Upload a monograph file.
	 * @param $monographId integer
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileStage int monograph file workflow stage
	 * @param $uploaderUserGroupId int The id of the user group that the uploader acted in
	 *  when uploading the file.
	 * @param $revisedFileId int
	 * @param $genreId int (e.g. Manusciprt, Appendix, etc.)
	 * @return MonographFile
	 */
	function &uploadMonographFile($monographId, $fileName, $fileStage, $uploaderUserGroupId, $revisedFileId = null, $genreId = null) {
		return MonographFileManager::_handleUpload($monographId, $fileName, $fileStage, $uploaderUserGroupId, $revisedFileId, $genreId);
	}

	/**
	 * Upload a copyedited file to the copyedit file folder.
	 * @param $monographId integer
	 * @param $fileName string the name of the file used in the POST form
	 * @param $uploaderUserGroupId int The id of the user group that the uploader acted in
	 *  when uploading the file.
	 * @param $revisedFileId int
	 * @return MonographFile
	 */
	function &uploadCopyeditResponseFile($monographId, $fileName, $uploaderUserGroupId, $revisedFileId = null) {
		return MonographFileManager::_handleUpload($monographId, $fileName, MONOGRAPH_FILE_COPYEDIT_RESPONSE, $uploaderUserGroupId, $revisedFileId);
	}

	/**
	 * Read a file's contents.
	 * @param $fileId integer
	 * @param $revision integer
	 * @param $output boolean output the file's contents instead of returning a string
	 * @return boolean
	 */
	function readFile($fileId, $revision = null, $output = false) {
		$monographFile =& MonographFileManager::_getFile($fileId, $revision);
		if (isset($monographFile)) {
			return parent::readFile($monographFile->getFilePath(), $output);
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
	function downloadFile($monographId, $fileId, $revision = null, $inline = false) {
		$returner = false;
		$monographFile =& MonographFileManager::_getFile($fileId, $revision);
		if (isset($monographFile)) {
			// Make sure that the file belongs to the monograph.
			if ($monographFile->getMonographId() != $monographId) fatalError('Invalid file id!');

			// Mark the file as viewed by this user.
			$sessionManager =& SessionManager::getManager();
			$session =& $sessionManager->getUserSession();
			$user =& $session->getUser();
			if (is_a($user, 'User')) {
				$viewsDao =& DAORegistry::getDAO('ViewsDAO');
				$viewsDao->recordView(ASSOC_TYPE_MONOGRAPH_FILE, $fileId, $user->getId());
			}

			// Send the file to the user.
			$filePath = $monographFile->getFilePath();
			$mediaType = $monographFile->getFileType();
			$returner = parent::downloadFile($filePath, $mediaType, $inline);
		}

		return $returner;
	}

	/**
	 * Download all monograph files as an archive
	 * @param $monographId integer
	 * @param $monographFiles ArrayItemIterator
	 * @return boolean
	 */
	function downloadFilesArchive($monographId, &$monographFiles) {
		$filesDir = MonographFileManager::_getFilesDir($monographId);
		$filePaths = array();
		while ($monographFile =& $monographFiles->next()) { /* @var $monographFile MonographFile */
			// Remove absolute path so the archive doesn't include it (otherwise all files are organized by absolute path)
			$filePath = str_replace($filesDir, '', $monographFile->getFilePath());
			// Add files to be archived to array
			$filePaths[] = escapeshellarg($filePath);
		}

		// Create the archive and download the file
		$archivePath = $filesDir . "monograph_" . $monographId . "_files.tar.gz";
		$tarCommand = "tar czf ". $archivePath . " -C \"" . $filesDir . "\" " . implode(" ", $filePaths);
		exec($tarCommand);
		if (file_exists($archivePath)) {
			parent::downloadFile($archivePath);
			return true;
		} else return false;
	}

	/**
	 * View a file inline (variant of downloadFile).
	 * @param $monographId integer
	 * @param $fileId integer
	 * @param $revision integer
	 * @see MonographFileManager::downloadFile
	 */
	function viewFile($monographId, $fileId, $revision = null) {
		MonographFileManager::downloadFile($monographId, $fileId, $revision, true);
	}

	/**
	 * Copy a temporary file to a monograph file.
	 * @param $monographId integer
	 * @param $temporaryFile MonographFile
	 * @param $fileStage integer
	 * @param $assocId integer
	 * @param $assocType integer
	 * @return integer the file ID (false if upload failed)
	 */
	function temporaryFileToMonographFile($monographId, &$temporaryFile, $fileStage, $assocId, $assocType) {
		// Instantiate and pre-populate the new target monograph file.
		$sourceFile = $temporaryFile->getFilePath();
		$monographFile =& MonographFileManager::_instantiateMonographFile($sourceFile, $monographId, $fileStage, null, null, $assocId, $assocType);

		// Transfer data from the temporary file to the monograph file.
		$monographFile->setFileType($temporaryFile->getFileType());
		$monographFile->setOriginalFileName($temporaryFile->getOriginalFileName());

		// Copy the temporary file to it's final destination and persist
		// its metadata to the database.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		if (!$submissionFileDao->insertObject($monographFile, $sourceFile)) return false;

		// Return the new file id.
		return $monographFile->getFileId();
	}


	//
	// Private helper methods
	//
	/**
	 * Get the files directory.
	 * @param $monographId integer
	 * @return string
	 */
	function _getFilesDir($monographId) {
		static $filesDir;
		if (empty($filesDir)) {
			$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
			$monograph =& $monographDao->getMonograph($monographId);
			assert(is_a($monograph, 'Monograph'));
			$filesDir = $monograph->getFilePath();
		}
		return $filesDir;
	}

	/**
	 * Upload the file and add it to the database.
	 * @param $monographId integer
	 * @param $fileName string index into the $_FILES array
	 * @param $fileStage int monograph file stage (one of the MONOGRAPH_FILE_* constants)
	 * @param $uploaderUserGroupId int The id of the user group that the uploader acted in
	 *  when uploading the file.
	 * @param $revisedFileId int ID of an existing file to revise
	 * @param $genreId int foreign key into genres table (e.g. manuscript, etc.)
	 * @param $assocType int
	 * @param $assocId int
	 * @return MonographFile the uploaded monograph file or null if an error occured.
	 */
	function &_handleUpload($monographId, $fileName, $fileStage, $uploaderUserGroupId, $revisedFileId = null, $genreId = null, $assocId = null, $assocType = null) {
		$nullVar = null;

		// Ensure that the file has been correctly uploaded to the server.
		if (!MonographFileManager::uploadedFileExists($fileName)) return $nullVar;

		// Retrieve the location of the uploaded file.
		$sourceFile = MonographFileManager::getUploadedFilePath($fileName);

		// Instantiate and pre-populate a new monograph file object.
		$monographFile = MonographFileManager::_instantiateMonographFile($sourceFile, $monographId, $fileStage, $revisedFileId, $genreId, $assocId, $assocType);
		if (is_null($monographFile)) return $nullVar;

		// Retrieve and copy the file type of the uploaded file.
		$fileType = MonographFileManager::getUploadedFileType($fileName);
		assert($fileType !== false);
		$monographFile->setFileType($fileType);

		// Retrieve and copy the file name of the uploaded file.
		$originalFileName = MonographFileManager::getUploadedFileName($fileName);
		assert($originalFileName !== false);
		$monographFile->setOriginalFileName(MonographFileManager::truncateFileName($originalFileName));

		// Set the uploader's user group id.
		$monographFile->setUserGroupId($uploaderUserGroupId);

		// Copy the uploaded file to its final destination and
		// persist its meta-data to the database.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		return $submissionFileDao->insertObject($monographFile, $fileName, true);
	}

	/**
	 * Routine to instantiate and pre-populate a new monograph file.
	 * @param $sourceFilePath string
	 * @param $monographId integer
	 * @param $fileStage integer
	 * @param $revisedFileId integer
	 * @param $genreId integer
	 * @param $assocId integer
	 * @param $assocType integer
	 * @return MonographFile returns the instantiated monograph file or null if an error occurs.
	 */
	function &_instantiateMonographFile($sourceFilePath, $monographId, $fileStage, $revisedFileId, $genreId, $assocId, $assocType) {
		// Retrieve the submission file DAO.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */

		// We either need a genre id or a revised file, otherwise
		// we cannot identify the target file implementation.
		assert($genreId || $revisedFileId);
		if (!$genreId || $revisedFileId) {
			// Retrieve the revised file.
			$revisedFile =& $submissionFileDao->getLatestRevision($revisedFileId, $fileStage, $monographId);
			if (!is_a($revisedFile, 'MonographFile')) return false;
		}

		// If we don't have a genre then use the genre from the
		// existing file.
		if (!$genreId) {
			$genreId = $revisedFile->getGenreId();
		}

		// Instantiate a new monograph file implementation.
		$monographFile =& $submissionFileDao->newDataObjectByGenreId($genreId);
		$monographFile->setMonographId($monographId);

		// Do we create a new file or a new revision of an existing file?
		if ($revisedFileId) {
			// Create a new revision of the file with the existing file id.
			$monographFile->setFileId($revisedFileId);
			$monographFile->setRevision($revisedFile->getRevision()+1);

			// Make sure that the monograph of the revised file is
			// the same as that of the uploaded file.
			assert($revisedFile->getMonographId() == $monographId);
			$nullVar = null;
			if ($revisedFile->getMonographId() != $monographId) return $nullVar;

			// Copy the file workflow stage.
			assert(is_null($fileStage) || $fileStage == $revisedFile->getFileStage());
			$fileStage = (int)$revisedFile->getFileStage();

			// Copy the assoc type.
			assert(is_null($assocType) || $assocType == $revisedFile->getAssocType());
			$assocType = (int)$revisedFile->getAssocType();

			// Copy the assoc id.
			assert(is_null($assocId) || $assocId == $revisedFile->getAssocId());
			$assocId = (int)$revisedFile->getAssocId();
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

		// Set modification dates to the current system date.
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