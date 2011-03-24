<?php

/**
 * @file classes/file/LibraryFileManager.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileManager
 * @ingroup file
 *
 * @brief Wrapper class for uploading files to a site/press' library directory.
 */


import('classes.press.LibraryFile');
import('lib.pkp.classes.file.FileManager');

class LibraryFileManager extends FileManager {
	/* @var $pressId Press id for the current press */
	var $pressId;

	/* @var $fileDir Directory where library files live */
	var $filesDir;

	/**
	 * Constructor
	 * @param $pressId int
	 */
	function LibraryFileManager($pressId) {
		parent::FileManager();
		$this->filesDir = Config::getVar('files', 'files_dir') . '/presses/' . $pressId . '/library/';
		$this->pressId = $pressId;
	}

 	/**
	 * Delete a file by ID.
	 * @param $fileId int
	 * @return int number of files removed
	 */
	function deleteFile($fileId) {
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$libraryFile =& $libraryFileDao->getById($fileId);

		parent::deleteFile($this->filesDir . $libraryFile->getFileName());

		$libraryFileDao->deleteById($fileId);
	}

	/**
	 * Generate a filename for a library file.
	 * @param $type int LIBRARY_FILE_TYPE_...
	 * @param $originalFileName string
	 * @return string
	 */
	function generateFileName($type, $originalFileName) {
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$suffix = $this->_getFileSuffixFromType($type);
		$ext = $this->getExtension($originalFileName);
		$truncated = $this->truncateFileName($originalFileName, 127 - String::strlen($suffix) - 1);
		$baseName = String::substr($truncated, 0, String::strpos($originalFileName, $ext) - 1);

		// Try a simple syntax first
		$fileName = $baseName . '-' . $suffix . '.' . $ext;
		if (!$libraryFileDao->filenameExists($this->pressId, $fileName)) return $fileName;

		for ($i = 1; ; $i++) {
			$fullSuffix = $suffix . '-' . $i;
			//truncate more if necessary
			$truncated = $this->truncateFileName($originalFileName, 127 - String::strlen($fullSuffix) - 1);
			// get the base name and append the suffix
			$baseName = String::substr($truncated, 0, String::strpos($originalFileName, $ext) - 1);

			//try the following
			$fileName = $baseName . '-' . $fullSuffix . '.' . $ext;
			if (!$libraryFileDao->filenameExists($this->pressId, $fileName)) {
				return $fileName;
			}
		}
	}

	/**
	 * PRIVATE routine to upload the file and add it to the database.
	 * @param $pressId int The id of the press
	 * @param $fileName string index into the $_FILES array
	 * @param $type int LIBRARY_FILE_TYPE_... identifying type
	 * @param $fileId int ID of file being replaced (null for new file)
	 * @return int the file ID (false if upload failed)
	 */
	function handleUpload($type, $fileName, $fileId = null) {
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$newFileName = $this->generateFilename($type, $this->getUploadedFileName($fileName));

		$libraryFile = $libraryFileDao->newDataObject();
		$libraryFile->setPressId($this->pressId);
		$libraryFile->setType($type);
		$libraryFile->setDateUploaded(Core::getCurrentDate());
		$libraryFile->setFileType($_FILES[$fileName]['type']);
		$libraryFile->setFileSize($_FILES[$fileName]['size']);
		$libraryFile->setFileName($newFileName);

		// remove the previous file
 		if ($fileId) {
			$libraryFile->setId($fileId);
			$this->deleteById($fileId);
		}

		if (!$this->uploadFile($fileName, $this->filesDir.$newFileName)) {
			return false;
		} else {
			// file upload was successful
			$libraryFileDao->insertObject($libraryFile);
		}

		return $libraryFile->getId();
	}

	function _getFileSuffixFromType($type) {
		static $map = array(
			LIBRARY_FILE_SUFFIX_REVIEW => 'LRV',
			LIBRARY_FILE_SUFFIX_SUBMISSION => 'LSB',
			LIBRARY_FILE_SUFFIX_PRODUCTION => 'LPR',
			LIBRARY_FILE_SUFFIX_EDITORIAL => 'LED',
			LIBRARY_FILE_SUFFIX_PRODUCTION_TEMPLATES => 'LPT'
		);
		assert(isset($map[$type]));
		return $map[$type];
	}
}

?>
