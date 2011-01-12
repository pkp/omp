<?php

/**
 * @file classes/file/TemporaryFileManager.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemporaryFileManager
 * @ingroup file
 * @see TemporaryFileDAO
 *
 * @brief Class defining operations for temporary file management.
 */


import('lib.pkp.classes.file.PKPTemporaryFileManager');

class TemporaryFileManager extends PKPTemporaryFileManager {
	/**
	 * Constructor.
	 * Create a manager for handling temporary file uploads.
	 */
	function TemporaryFileManager() {
		parent::PKPTemporaryFileManager();
	}

	/**
	 * Create a new temporary file from a monogrpah file.
	 * @param $monographFile object
	 * @param $userId int
	 * @return object The new TemporaryFile or false on failure
	 */
	function monographToTemporaryFile($monographFile, $userId) {
		// Get the file extension, then rename the file.
		$fileExtension = $this->parseFileExtension($monographFile->getFileName());

		if (!$this->fileExists($this->filesDir, 'dir')) {
			// Try to create destination directory
			$this->mkdirtree($this->filesDir);
		}

		$newFileName = basename(tempnam($this->filesDir, $fileExtension));
		if (!$newFileName) return false;

		if (copy($monographFile->getFilePath(), $this->filesDir . $newFileName)) {
			$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile = new TemporaryFile();

			$temporaryFile->setUserId($userId);
			$temporaryFile->setFileName($newFileName);
			$temporaryFile->setFileType($monographFile->getFileType());
			$temporaryFile->setFileSize($monographFile->getFileSize());
			$temporaryFile->setOriginalFileName($monographFile->getOriginalFileName());
			$temporaryFile->setDateUploaded(Core::getCurrentDate());

			$temporaryFileDao->insertTemporaryFile($temporaryFile);

			return $temporaryFile;

		} else {
			return false;
		}
	}
}

?>
