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

	/**
	 * Upload a monograph file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $typeId int monograph file type
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadMonographFile($fileName, $typeId, $fileId = null) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_SUBMISSION, $fileId, false, $typeId);
	}

	/**
	 * Upload an artwork file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $typeId int monograph file type
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadArtworkFile($fileName, $typeId, $fileId = null) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_ARTWORK, $fileId, false, $typeId);
	}

	/**
	 * Upload a file to the review file folder.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadReviewFile($fileName, $fileId = null, $reviewId = null) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_REVIEW, $fileId, null, null, $reviewId);
	}

	/**
	 * Upload a file to the editor decision file folder.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadEditorDecisionFile($fileName, $fileId = null) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_EDITOR, $fileId);
	}

	/**
	 * Upload a file to the copyedit file folder.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @return int file ID, is false if failure
	 */
	function uploadCopyeditFile($fileName, $fileId = null) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_COPYEDIT, $fileId);
	}

	/**
	 * Upload an series editor's layout editing file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @param $overwrite boolean
	 * @return int file ID, is null if failure
	 */
	function uploadLayoutFile($fileName, $fileId = null, $overwrite = true) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_LAYOUT, $fileId, $overwrite);
	}

	/**
	 * Upload a layout file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @param $overwrite boolean
	 * @return int file ID, is null if failure
	 */
	function uploadGalleyFile($fileName, $fileId = null, $overwrite = true) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_GALLEY, $fileId, $overwrite);
	}

	/**
	 * Upload a public file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @param $overwrite boolean
	 * @return int file ID, is false if failure
	 */
	function uploadPublicFile($fileName, $fileId = null, $overwrite = true) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_PUBLIC, $fileId, $overwrite);
	}

	/**
	 * Upload a note file.
	 * @param $fileName string the name of the file used in the POST form
	 * @param $fileId int
	 * @param $overwrite boolean
	 * @return int file ID, is false if failure
	 */
	function uploadSubmissionNoteFile($fileName, $fileId = null, $overwrite = true) {
		return $this->handleUpload($fileName, MONOGRAPH_FILE_NOTE, $fileId, $overwrite);
	}

	/**
	 * Write a public file.
	 * @param $fileName string The original filename
	 * @param $contents string The contents to be written to the file
	 * @param $mimeType string The mime type of the original file
	 * @param $fileId int
	 * @param $overwrite boolean
	 */
	function writePublicFile($fileName, &$contents, $mimeType, $fileId = null, $overwrite = true) {
		return $this->handleWrite($fileName, $contents, $mimeType, MONOGRAPH_FILE_PUBLIC, $fileId, $overwrite);
	}

	/**
	 * Copy a public file.
	 * @param $url string The source URL/filename
	 * @param $mimeType string The mime type of the original file
	 * @param $fileId int
	 * @param $overwrite boolean
	 */
	function copyPublicFile($url, $mimeType, $fileId = null, $overwrite = true) {
		return $this->handleCopy($url, $mimeType, MONOGRAPH_FILE_PUBLIC, $fileId, $overwrite);
	}

	/**
	 * Copy an attachment file.
	 * @param $url string The source URL/filename
	 * @param $mimeType string The mime type of the original file
	 * @param $fileId int
	 * @param $overwrite boolean
	 */
	function copyAttachmentFile($url, $mimeType, $fileId = null, $overwrite = true, $assocId = null) {
		return $this->handleCopy($url, $mimeType, MONOGRAPH_FILE_ATTACHMENT, $fileId, $overwrite, $assocId);
	}

	/**
	 * Retrieve file information by file ID.
	 * @return MonographFile
	 */
	function &getFile($fileId, $revision = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($fileId, $revision, $this->monographId);
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
			$filePath = $this->filesDir . $monographFile->getType() . '/' . $monographFile->getFileName();

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
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

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
			parent::deleteFile($this->filesDir . $f->getType() . '/' . $f->getFileName());
		}

		$monographFileDao->deleteMonographFileById($fileId, $revision);

		return count($files);
	}

	/**
	 * Delete the entire tree of files belonging to a monograph.
	 */
	function deleteMonographTree() {
		parent::rmtree($this->filesDir);
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
	function downloadFilesArchive(&$monographFiles) {
		if(!isset($monographFiles)) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFiles =& $monographFileDao->getByMonographId($this->monographId);
		}

		$filePaths = array();
		while ($monographFile =& $monographFiles->next()) {
			// Remove absolute path so the archive doesn't include it (otherwise all files are organized by absolute path)
			$filePath = str_replace($this->filesDir, '', $monographFile->getFilePath());
			// Add files to be archived to array
			$filePaths[] = escapeshellarg($filePath);
		}

		// Create the archive and download the file
		$archivePath = $this->filesDir . "monograph_" . $this->monographId . "_files.tar.gz";
		$tarCommand = "tar czf ". $archivePath . " -C \"" . $this->filesDir . "\" " . implode(" ", $filePaths);
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
	 * Copies an existing file to create a review file.
	 * @param $originalFileId int the file id of the original file.
	 * @param $originalRevision int the revision of the original file.
	 * @param $destFileId int the file id of the current review file
	 * @return int the file id of the new file.
	 */
	function copyToReviewFile($fileId, $revision = null, $destFileId = null) {
		return $this->copyAndRenameFile($fileId, $revision, MONOGRAPH_FILE_REVIEW, $destFileId);
	}

	/**
	 * Copies an existing file to create an editor decision file.
	 * @param $fileId int the file id of the review file.
	 * @param $revision int the revision of the review file.
	 * @param $destFileId int file ID to copy to
	 * @return int the file id of the new file.
	 */
	function copyToEditorFile($fileId, $revision = null, $destFileId = null) {
		return $this->copyAndRenameFile($fileId, $revision, MONOGRAPH_FILE_EDITOR, $destFileId);
	}

	/**
	 * Copies an existing file to create a copyedit file.
	 * @param $fileId int the file id of the editor file.
	 * @param $revision int the revision of the editor file.
	 * @return int the file id of the new file.
	 */
	function copyToCopyeditFile($fileId, $revision = null) {
		return $this->copyAndRenameFile($fileId, $revision, MONOGRAPH_FILE_COPYEDIT);
	}

	/**
	 * Copies an existing file to create a layout file.
	 * @param $fileId int the file id of the copyedit file.
	 * @param $revision int the revision of the copyedit file.
	 * @return int the file id of the new file.
	 */
	function copyToLayoutFile($fileId, $revision = null) {
		return $this->copyAndRenameFile($fileId, $revision, MONOGRAPH_FILE_LAYOUT);
	}

	/**
	 * Copies an existing file to create a production file.
	 * @param $fileId int the file id of the production file.
	 * @param $revision int the revision of the production file.
	 * @return int the file id of the new file.
	 */
	function copyToProductionFile($fileId, $revision = null) {
		return $this->copyAndRenameFile($fileId, $revision, MONOGRAPH_FILE_PRODUCTION);
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
			case MONOGRAPH_FILE_ARTWORK: return 'artwork';
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
	function copyAndRenameFile($sourceFileId, $sourceRevision, $destType, $destFileId = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile = $monographFileDao->newDataObject();

		$destTypePath = $this->typeToPath($destType);
		$destDir = $this->filesDir . $destTypePath . '/';

		if ($destFileId != null) {
			$currentRevision = $monographFileDao->getRevisionNumber($destFileId);
			$revision = $currentRevision + 1;
		} else {
			$revision = 1;
		}

		$sourceMonographFile = $monographFileDao->getMonographFile($sourceFileId, $sourceRevision, $this->monographId);

		if (!isset($sourceMonographFile)) {
			return false;
		}

		$sourceDir = $this->filesDir . $sourceMonographFile->getType() . '/';

		if ($destFileId != null) {
			$monographFile->setFileId($destFileId);
		}
		$monographFile->setMonographId($this->monographId);
		$monographFile->setSourceFileId($sourceFileId);
		$monographFile->setSourceRevision($sourceRevision);
		$monographFile->setFileName($sourceMonographFile->getFileName());
		$monographFile->setFileType($sourceMonographFile->getFileType());
		$monographFile->setFileSize($sourceMonographFile->getFileSize());
		$monographFile->setOriginalFileName($sourceMonographFile->getFileName());
		$monographFile->setType($destType);
		$monographFile->setDateUploaded(Core::getCurrentDate());
		$monographFile->setDateModified(Core::getCurrentDate());
		$monographFile->setRevision($revision);

		$fileId = $monographFileDao->insertMonographFile($monographFile);

		// Rename the file.
		$fileExtension = $this->parseFileExtension($sourceMonographFile->getFileName());
		$newFileName = $this->monographId.'-'.$fileId.'-'.$revision.'-'.$destType.'.'.$fileExtension;

		if (!$this->fileExists($destDir, 'dir')) {
			// Try to create destination directory
			$this->mkdirtree($destDir);
		}

		copy($sourceDir.$sourceMonographFile->getFileName(), $destDir.$newFileName);

		$monographFile->setFileName($newFileName);
		$monographFileDao->updateMonographFile($monographFile);

		return $fileId;
	}

	/**
	 * PRIVATE routine to generate a dummy file. Used in handleUpload.
	 * @param $monograph object
	 * @return MonographFile
	 */
	function &generateDummyFile(&$monograph, $type) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$monographFile = new MonographFile();
		$monographFile->setMonographId($monograph->getId());
		$monographFile->setFileName('temp');
		$monographFile->setOriginalFileName('temp');
		$monographFile->setFileType('temp');
		$monographFile->setFileSize(0);
		$monographFile->setType('temp');
		$monographFile->setDateUploaded(Core::getCurrentDate());
		$monographFile->setDateModified(Core::getCurrentDate());
		$monographFile->setRevision(1);
		$monographFile->setFileId($monographFileDao->insertMonographFile($monographFile));

		return $monographFile;
	}

	/**
	 * PRIVATE routine to remove all prior revisions of a file.
	 */
	function removePriorRevisions($fileId, $revision) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$revisions = $monographFileDao->getMonographFileRevisions($fileId, null, false);
		foreach ($revisions as $revisionFile) {
			if ($revisionFile->getRevision() != $revision) {
				$this->deleteFile($fileId, $revisionFile->getRevision());
			}
		}
	}

	/**
	 * PRIVATE routine to generate a filename for a monograph file. Sets the filename
	 * field in the monographFile to the generated value.
	 * @param $monographFile The monograph to generate a filename for
	 * @param $type The type of the monograph (e.g. as supplied to handleUpload)
	 * @param $originalName The name of the original file
	 */
	function generateFilename(&$monographFile, $type, $originalName) {
		$extension = $this->parseFileExtension($originalName);
		$newFileName = $monographFile->getMonographId().'-'.$monographFile->getFileId().'-'.$monographFile->getRevision().'-'.$type.'.'.$extension;
		$monographFile->setFileName($newFileName);
		return $newFileName;
	}

	/**
	 * PRIVATE routine to generate a filename for a monograph file. Sets the filename
	 * field in the monographFile to the generated value.
	 * @param $monographFile The monograph to generate a filename for
	 * @param $originalName The name of the original file
	 * @param $typeId int monograph file type id
	 */
	function generateMonographFileName(&$monographFile, $originalName, $typeId) {
		$extension = $this->parseFileExtension($originalName);
		$primaryLocale = Locale::getPrimaryLocale();

		$monographFileTypeDao =& DAORegistry::getDAO('MonographFileTypeDAO');
		$monographFileType =& $monographFileTypeDao->getById($typeId);

		$newFileName = $monographFileType->getDesignation($primaryLocale).'_'.date('Y', time()).'-'.$monographFileType->getName($primaryLocale).'-'.$monographFile->getFileId().'-'.$monographFile->getRevision().'.'.$extension;
		$monographFile->setFileName($newFileName);
		return $newFileName;
	}

	/**
	 * PRIVATE routine to upload the file and add it to the database.
	 * @param $fileName string index into the $_FILES array
	 * @param $type int identifying type
	 * @param $fileId int ID of an existing file to update
	 * @param $overwrite boolean overwrite all previous revisions of the file (revision number is still incremented)
	 * @return int the file ID (false if upload failed)
	 */
	function handleUpload($fileName, $type, $fileId = null, $overwrite = false, $monographFileTypeId = null, $assocId = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$typePath = $this->typeToPath($type);
		$dir = $this->filesDir . $typePath . '/';

		if (!$fileId) {
			// Insert dummy file to generate file id FIXME?
			$dummyFile = true;
			$monographFile =& $this->generateDummyFile($this->monograph, $type);
		} else {
			$dummyFile = false;
			$monographFile = $monographFileDao->newDataObject();
			$monographFile->setRevision($monographFileDao->getRevisionNumber($fileId)+1);
			$monographFile->setMonographId($this->monographId);
			$monographFile->setFileId($fileId);
			$monographFile->setDateUploaded(Core::getCurrentDate());
			$monographFile->setDateModified(Core::getCurrentDate());
		}

		if(isset($assocId)) {
			$monographFile->setAssocId($assocId);
		}

		$monographFile->setFileType($_FILES[$fileName]['type']);
		$monographFile->setFileSize($_FILES[$fileName]['size']);
		$monographFile->setOriginalFileName(MonographFileManager::truncateFileName($_FILES[$fileName]['name'], 127));
		$monographFile->setType($type);

		// Set the uploader's userGroupId
		$sessionMgr =& SessionManager::getManager();
		$session =& $sessionMgr->getUserSession();
		$monographFile->setUserGroupId($session->getActingAsUserGroupId());

		if (isset($monographFileTypeId)) {
			$newFileName = $this->generateMonographFileName(
					$monographFile,
					$this->getUploadedFileName($fileName),
					$monographFileTypeId
				);
			$monographFile->setAssocId($monographFileTypeId);
		} else {
			$newFileName = $this->generateFilename($monographFile, $type, $this->getUploadedFileName($fileName));
		}

		if (!$this->uploadFile($fileName, $dir.$newFileName)) {
			// Delete the dummy file we inserted
			$monographFileDao->deleteMonographFileById($monographFile->getFileId());
			return false;
		}

		if ($dummyFile) $monographFileDao->updateMonographFile($monographFile);
		else $monographFileDao->insertMonographFile($monographFile);

		if ($overwrite) $this->removePriorRevisions($monographFile->getFileId(), $monographFile->getRevision());

		return $monographFile->getFileId();
	}

	/**
	 * PRIVATE routine to write a monograph file and add it to the database.
	 * @param $fileName original filename of the file
	 * @param $contents string contents of the file to write
	 * @param $mimeType string the mime type of the file
	 * @param $type int identifying type
	 * @param $fileId int ID of an existing file to update
	 * @param $overwrite boolean overwrite all previous revisions of the file (revision number is still incremented)
	 * @return int the file ID (false if upload failed)
	 */
	function handleWrite($fileName, &$contents, $mimeType, $type, $fileId = null, $overwrite = false) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$typePath = $this->typeToPath($type);
		$dir = $this->filesDir . $typePath . '/';

		if (!$fileId) {
			// Insert dummy file to generate file id FIXME?
			$dummyFile = true;
			$monographFile =& $this->generateDummyFile($this->monograph, $type);
		} else {
			$dummyFile = false;
			$monographFile = $monographFileDao->newDataObject();
			$monographFile->setRevision($monographFileDao->getRevisionNumber($fileId)+1);
			$monographFile->setMonographId($this->monographId);
			$monographFile->setFileId($fileId);
			$monographFile->setDateUploaded(Core::getCurrentDate());
			$monographFile->setDateModified(Core::getCurrentDate());
		}

		$monographFile->setFileType($mimeType);
		$monographFile->setFileSize(strlen($contents));
		$monographFile->setOriginalFileName(MonographFileManager::truncateFileName($fileName, 127));
		$monographFile->setType($type);

		$newFileName = $this->generateFilename($monographFile, $type, $fileName);

		if (!$this->writeFile($dir.$newFileName, $contents)) {
			// Delete the dummy file we inserted
			$monographFileDao->deleteMonographFileById($monographFile->getFileId());

			return false;
		}

		if ($dummyFile) $monographFileDao->updateMonographFile($monographFile);
		else $monographFileDao->insertMonographFile($monographFile);

		if ($overwrite) $this->removePriorRevisions($monographFile->getFileId(), $monographFile->getRevision());

		return $monographFile->getFileId();
	}

	/**
	 * PRIVATE routine to copy a monograph file and add it to the database.
	 * @param $url original filename/url of the file
	 * @param $mimeType string the mime type of the file
	 * @param $type int identifying type
	 * @param $fileId int ID of an existing file to update
	 * @param $overwrite boolean overwrite all previous revisions of the file (revision number is still incremented)
	 * @return int the file ID (false if upload failed)
	 */
	function handleCopy($url, $mimeType, $type, $fileId = null, $overwrite = false) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$typePath = $this->typeToPath($type);
		$dir = $this->filesDir . $typePath . '/';

		if (!$fileId) {
			// Insert dummy file to generate file id FIXME?
			$dummyFile = true;
			$monographFile =& $this->generateDummyFile($this->monograph, $type);
		} else {
			$dummyFile = false;
			$monographFile = $monographFileDao->newDataObject();
			$monographFile->setRevision($monographFileDao->getRevisionNumber($fileId)+1);
			$monographFile->setMonographId($this->monographId);
			$monographFile->setFileId($fileId);
			$monographFile->setDateUploaded(Core::getCurrentDate());
			$monographFile->setDateModified(Core::getCurrentDate());
		}

		$monographFile->setFileType($mimeType);
		$monographFile->setOriginalFileName(MonographFileManager::truncateFileName(basename($url), 127));
		$monographFile->setType($type);

		$newFileName = $this->generateFilename($monographFile, $type, $monographFile->getOriginalFileName());

		if (!$this->copyFile($url, $dir.$newFileName)) {
			// Delete the dummy file we inserted
			$monographFileDao->deleteMonographFileById($monographFile->getFileId());

			return false;
		}

		$monographFile->setFileSize(filesize($dir.$newFileName));

		if ($dummyFile) $monographFileDao->updateMonographFile($monographFile);
		else $monographFileDao->insertMonographFile($monographFile);

		if ($overwrite) $this->removePriorRevisions($monographFile->getFileId(), $monographFile->getRevision());

		return $monographFile->getFileId();
	}

	/**
	 * Copy a temporary file to a monograph file.
	 * @param $temporaryFile
	 * @return int the file ID (false if upload failed)
	 */
	function temporaryFileToMonographFile(&$temporaryFile, $type, $assocId = null) {
		if (HookRegistry::call('MonographFileManager::temporaryFileToMonographFile', array(&$temporaryFile, &$type, &$assocId, &$result))) return $result;

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$typePath = $this->typeToPath($type);
		$dir = $this->filesDir . $typePath . '/';

		$monographFile =& $this->generateDummyFile($this->monograph, $type);
		$monographFile->setFileType($temporaryFile->getFileType());
		$monographFile->setOriginalFileName($temporaryFile->getOriginalFileName());
		$monographFile->setType($type);
		$monographFile->setAssocId($assocId);

		$newFileName = $this->generateFilename($monographFile, $type, $monographFile->getOriginalFileName());

		if (!$this->copyFile($temporaryFile->getFilePath(), $dir.$newFileName)) {
			// Delete the dummy file we inserted
			$monographFileDao->deleteMonographFileById($monographFile->getFileId());

			return false;
		}

		$monographFile->setFileSize(filesize($dir.$newFileName));
		$monographFileDao->updateMonographFile($monographFile);
		$this->removePriorRevisions($monographFile->getFileId(), $monographFile->getRevision());

		return $monographFile->getFileId();
	}
}

?>