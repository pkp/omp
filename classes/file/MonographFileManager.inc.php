<?php

/**
 * @file classes/file/MonographFileManager.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileManager
 * @ingroup file
 *
 * @brief Class defining operations for monograph file management.
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

	/** @var string the path to location of the files */
	var $filesDir;

	/** @var int the ID of the associated monograph */
	var $monographId;

	/** @var Monograph the associated monograph */
	var $monograph;

	/**
	 * Constructor.
	 * Create a manager for handling monograph file uploads.
	 * @param $monographId int
	 */
	function MonographFileManager($monographId) {
		$this->monographId = $monographId;
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);
		$this->monograph =& $monograph;
		$pressId = $monograph->getPressId();
		$this->filesDir = Config::getVar('files', 'files_dir') . '/presses/' . $pressId .
		'/monographs/' . $monographId . '/';
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the monograph id.
	 * @return integer
	 */
	function getMonographId() {
		return $this->monographId;
	}

	/**
	 * Get the files directory.
	 * @return string
	 */
	function getFilesDir() {
		return $this->filesDir;
	}

	/**
	 * Get the monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->monograph;
	}


	//
	// Public methods
	//
	/**
	 * Upload a monograph file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $typeId int monograph file type (e.g. Manusciprt, Appendix, etc.)
	 * @param $fileId int
	 * @param $monographFileTypeId int
	 * @return int file ID, is false if failure
	 */
	function uploadMonographFile($fileName, $typeId = MONOGRAPH_FILE_SUBMISSION, $fileId = null, $monographFileTypeId = null) {
		return $this->_handleUpload($fileName, $typeId, $fileId, $monographFileTypeId, null, null);
	}

	/**
	 * Upload a file to the review file folder.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadReviewFile($fileName, $fileId = null, $reviewId = null) {
		$assocType = $reviewId ? ASSOC_TYPE_REVIEW_ASSIGNMENT : null;
		return $this->_handleUpload($fileName, MONOGRAPH_FILE_REVIEW, $fileId, null, $reviewId, $assocType);
	}

	/**
	 * Upload a copyedited file to the copyedit file folder.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadCopyeditResponseFile($fileName, $fileId = null) {
		return $this->_handleUpload($fileName, MONOGRAPH_FILE_COPYEDIT_RESPONSE, $fileId);
	}

	/**
	 * Retrieve file information by file ID.
	 * @return MonographFile
	 */
	function &getFile($fileId, $revision = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId, $revision, $this->getMonographId());
		return $monographFile;
	}

	/**
	 * Read a file's contents.
	 * @param $output boolean output the file's contents instead of returning a string
	 * @return boolean
	 */
	function readFile($fileId, $revision = null, $output = false) {
		$monographFile =& $this->getFile($fileId, $revision);

		if (isset($monographFile)) {
			$fileType = $monographFile->getFileType();
			$filePath = $this->getFilesDir() . $monographFile->getType() . '/' . $monographFile->getFileName();

			return parent::readFile($filePath, $output);

		} else {
			return false;
		}
	}

	/**
	 * Delete a file by ID.
	 * If no revision is specified, all revisions of the file are deleted.
	 * @param $fileId int
	 * @param $revision int (optional)
	 * @return int number of files removed
	 */
	function deleteFile($fileId, $revision = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */

		$files = array();
		if (isset($revision)) {
			$file =& $monographFileDao->getMonographFile($fileId, $revision);
			if (isset($file)) {
				$files[] = $file;
			}

		} else {
			$files =& $monographFileDao->getMonographFileRevisions($fileId, null, false);
		}

		foreach ($files as $f) {
			parent::deleteFile($this->getFilesDir() . $f->getType() . '/' . $f->getFileName());
		}

		$monographFileDao->deleteMonographFileById($fileId, $revision);

		return count($files);
	}

	/**
	 * Delete the entire tree of files belonging to a monograph.
	 */
	function deleteMonographTree() {
		parent::rmtree($this->getFilesDir());
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
		$monographFile =& $this->getFile($fileId, $revision);
		if (isset($monographFile)) {
			// Make sure that the file belongs to the monograph.
			if ($monographFile->getMonographId() != $this->getMonographId()) fatalError('Invalid file id!');

			// Retrieve file information.
			$fileType = $monographFile->getFileType();
			$filePath = $monographFile->getFilePath();

			// Mark the file as viewed by this user
			$sessionManager =& SessionManager::getManager();
			$session =& $sessionManager->getUserSession();
			$user =& $session->getUser();
			$viewsDao =& DAORegistry::getDAO('ViewsDAO');
			$viewsDao->recordView(ASSOC_TYPE_MONOGRAPH_FILE, $fileId, $user->getId());

			// Send the file to the user
			$returner = parent::downloadFile($filePath, $fileType, $inline);
		}

		return $returner;
	}

	/**
	 * Download all monograph files as an archive
	 * @param $monographFiles ArrayItemIterator
	 * @return boolean
	 */
	function downloadFilesArchive(&$monographFiles = null) {
		if(!isset($monographFiles)) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFiles =& $monographFileDao->getByMonographId($this->getMonographId());
		}

		$filePaths = array();
		while ($monographFile =& $monographFiles->next()) {
			// Remove absolute path so the archive doesn't include it (otherwise all files are organized by absolute path)
			$filePath = str_replace($this->getFilesDir(), '', $monographFile->getFilePath());
			// Add files to be archived to array
			$filePaths[] = escapeshellarg($filePath);
		}

		// Create the archive and download the file
		$archivePath = $this->getFilesDir() . "monograph_" . $this->getMonographId() . "_files.tar.gz";
		$tarCommand = "tar czf ". $archivePath . " -C \"" . $this->getFilesDir() . "\" " . implode(" ", $filePaths);
		exec($tarCommand);
		if (file_exists($archivePath)) {
			parent::downloadFile($archivePath);
			return true;
		} else return false;
	}

	/**
	 * View a file inline (variant of downloadFile).
	 * @see MonographFileManager::downloadFile
	 */
	function viewFile($fileId, $revision = null) {
		$this->downloadFile($fileId, $revision, true);
	}

	/**
	 * Return type path associated with a type code.
	 * @param $type string
	 * @return string
	 */
	function typeToPath($type) {
		switch ($type) {
			case MONOGRAPH_FILE_PUBLIC: return 'public';
			case MONOGRAPH_FILE_SUBMISSION: return 'submission';
			case MONOGRAPH_FILE_NOTE: return 'note';
			case MONOGRAPH_FILE_REVIEW: return 'submission/review';
			case MONOGRAPH_FILE_FINAL: return 'submission/final';
			case MONOGRAPH_FILE_FAIR_COPY: return 'submission/fairCopy';
			case MONOGRAPH_FILE_EDITOR: return 'submission/editor';
			case MONOGRAPH_FILE_COPYEDIT: return 'submission/copyedit';
			case MONOGRAPH_FILE_PRODUCTION: return 'submission/production';
			case MONOGRAPH_FILE_GALLEY: return 'submission/galleys';
			case MONOGRAPH_FILE_LAYOUT: return 'submission/layout';
			case MONOGRAPH_FILE_ATTACHMENT: default: return 'attachment';
		}
	}

	/**
	 * Copies an existing MonographFile and renames it.
	 * @param $sourceFileId int
	 * @param $sourceRevision int
	 * @param $destType string
	 * @param $destFileId int (optional)
	 */
	/**
	 * Copy a temporary file to a monograph file.
	 * @param $temporaryFile
	 * @return int the file ID (false if upload failed)
	 */
	function temporaryFileToMonographFile(&$temporaryFile, $type, $assocId = null) {
		if (HookRegistry::call('MonographFileManager::temporaryFileToMonographFile', array(&$temporaryFile, &$type, &$assocId, &$result))) return $result;

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$typePath = $this->typeToPath($type);
		$dir = $this->getFilesDir() . $typePath . '/';

		$monographFile =& $this->_generateDummyFile($this->getMonograph());
		$monographFile->setType($type);
		$monographFile->setFileType($temporaryFile->getFileType());
		$monographFile->setOriginalFileName($temporaryFile->getOriginalFileName());
		$monographFile->setAssocId($assocId);

		$newFileName = $this->_generateFilename($monographFile, $type, $monographFile->getOriginalFileName());

		if (!$this->copyFile($temporaryFile->getFilePath(), $dir.$newFileName)) {
			// Delete the dummy file we inserted
			$monographFileDao->deleteMonographFileById($monographFile->getFileId());

			return false;
		}

		$monographFile->setFileSize(filesize($dir.$newFileName));
		$monographFileDao->updateMonographFile($monographFile);
		$this->_removePriorRevisions($monographFile->getFileId(), $monographFile->getRevision());

		return $monographFile->getFileId();
	}


	//
	// Private helper methods
	//
	/**
	 * Upload the file and add it to the database.
	 * @param $fileName string index into the $_FILES array
	 * @param $type int identifying type (i.e. MONOGRAPH_FILE_*)
	 * @param $fileId int ID of an existing file to update
	 * @param $monographFileTypeId int foreign key into monograph_file_types table (e.g. manuscript, etc.)
	 * @param $assocType int
	 * @param $assocId int
	 * @return int the file ID (false if upload failed)
	 */
	function _handleUpload($fileName, $type, $fileId = null, $monographFileTypeId = null, $assocId = null, $assocType = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$typePath = $this->typeToPath($type);
		$dir = $this->getFilesDir() . $typePath . '/';

		if (!$fileId) {
			// Insert dummy file to generate file id FIXME?
			$dummyFile = true;
			$monographFile =& $this->_generateDummyFile($this->getMonograph());
		} else {
			$dummyFile = false;
			$monographFile = $monographFileDao->newDataObject();
			$monographFile->setRevision($monographFileDao->getRevisionNumber($fileId)+1);
			$monographFile->setMonographId($this->getMonographId());
			$monographFile->setFileId($fileId);
			$monographFile->setDateUploaded(Core::getCurrentDate());
			$monographFile->setDateModified(Core::getCurrentDate());
		}

		if(isset($assocId)) {
			assert(isset($assocType));
			$monographFile->setAssocType($assocType);
			$monographFile->setAssocId($assocId);
		}

		if(isset($monographFileTypeId)) {
			$monographFile->setMonographFileType($monographFileTypeId);
		}

		$monographFile->setType($type);
		$monographFile->setFileType($_FILES[$fileName]['type']);
		$monographFile->setFileSize($_FILES[$fileName]['size']);
		$monographFile->setOriginalFileName(MonographFileManager::truncateFileName($_FILES[$fileName]['name'], 127));

		// Set the uploader's userGroupId
		$sessionMgr =& SessionManager::getManager();
		$session =& $sessionMgr->getUserSession();
		$monographFile->setUserGroupId($session->getActingAsUserGroupId());

		if (isset($monographFileTypeId)) {
			$newFileName = $this->_generateMonographFileName($monographFile, $this->getUploadedFileName($fileName), $monographFileTypeId);
		} else {
			$newFileName = $this->_generateFilename($monographFile, $type, $this->getUploadedFileName($fileName));
		}

		if (!$this->uploadFile($fileName, $dir.$newFileName)) {
			// Delete the dummy file we inserted
			$monographFileDao->deleteMonographFileById($monographFile->getFileId());
			return false;
		}

		if ($dummyFile) $monographFileDao->updateMonographFile($monographFile);
		else $monographFileDao->insertMonographFile($monographFile);

		return $monographFile->getFileId();
	}

	/**
	 * Routine to generate a dummy file. Used in _handleUpload.
	 * @param $monograph Monograph
	 * @return MonographFile
	 */
	function &_generateDummyFile(&$monograph) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$monographFile = new MonographFile();
		$monographFile->setMonographId($monograph->getId());
		$monographFile->setFileName('temp');
		$monographFile->setOriginalFileName('temp');
		$monographFile->setFileType('temp');
		$monographFile->setFileSize(0);
		$monographFile->setType(0);
		$monographFile->setDateUploaded(Core::getCurrentDate());
		$monographFile->setDateModified(Core::getCurrentDate());
		$monographFile->setRevision(1);
		$monographFile->setFileId($monographFileDao->insertMonographFile($monographFile));

		return $monographFile;
	}

	/**
	 * Generate a unique filename for a monograph file. Sets the filename
	 * field in the monographFile to the generated value.
	 * @param $monographFile MonographFile the monograph to generate a filename for
	 * @param $type integer one of the MONOGRAPH_FILE_* constants
	 * @param $originalName string the name of the original file
	 */
	function _generateFilename(&$monographFile, $type, $originalName) {
		$extension = $this->parseFileExtension($originalName);
		$newFileName = $monographFile->getMonographId().'-'.$monographFile->getFileId().'-'.$monographFile->getRevision().'-'.$type.'.'.$extension;
		$monographFile->setFileName($newFileName);
		return $newFileName;
	}

	/**
	 * Generate a unique filename for a monograph file that has a genre type set.
	 * This will use the localized name of the file genre and a time stamp in the
	 * file name.
	 * @param $monographFile MonographFile the monograph to generate a filename for
	 * @param $originalName string the name of the original file
	 * @param $typeId integer monograph file genre type id
	 */
	function _generateMonographFileName(&$monographFile, $originalName, $typeId) {
		$extension = $this->parseFileExtension($originalName);
		$primaryLocale = Locale::getPrimaryLocale();

		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO'); /* @var $monographFileTypeDao MonographFileTypeDAO */
		$monographFileType =& $monographFileTypeDao->getById($typeId);

		$newFileName = $monographFileType->getDesignation($primaryLocale).'_'.date('Y', time()).'-'.$monographFileType->getName($primaryLocale).'-'.$monographFile->getFileId().'-'.$monographFile->getRevision().'.'.$extension;
		$monographFile->setFileName($newFileName);
		return $newFileName;
	}

	/**
	 * Remove all prior revisions of a file.
	 * @param $fileId integer
	 * @param $revision integer
	 */
	function _removePriorRevisions($fileId, $revision) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$revisions = $monographFileDao->getMonographFileRevisions($fileId, null, false);
		foreach ($revisions as $revisionFile) {
			if ($revisionFile->getRevision() != $revision) {
				$this->deleteFile($fileId, $revisionFile->getRevision());
			}
		}
	}
}

?>