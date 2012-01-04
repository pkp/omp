<?php

/**
 * @file classes/file/PublicFileManager.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicFileManager
 * @ingroup file
 *
 * @brief Wrapper class for uploading files to a site/press' public directory.
 */


import('lib.pkp.classes.file.PKPPublicFileManager');

class PublicFileManager extends PKPPublicFileManager {
	/**
	 * Get the path to a press' public files directory.
	 * @param $pressId int
	 * @return string
	 */
	function getPressFilesPath($pressId) {
		return Config::getVar('files', 'public_files_dir') . '/presses/' . $pressId;
	}

	/**
	 * Upload a file to a press' public directory.
	 * @param $pressId int
	 * @param $fileName string the name of the file in the upload form
	 * @param $destFileName string the destination file name
	 * @return boolean
	 */
	function uploadPressFile($pressId, $fileName, $destFileName) {
		return $this->uploadFile($fileName, $this->getPressFilesPath($pressId) . '/' . $destFileName);
	}

	/**
	 * Write a file to a press' public directory.
	 * @param $pressId int
	 * @param $destFileName string the destination file name
	 * @param $contents string the contents to write to the file
	 * @return boolean
	 */
	function writePressFile($pressId, $destFileName, &$contents) {
		return $this->writeFile($this->getPressFilesPath($pressId) . '/' . $destFileName, $contents);
	}

	/**
	 * Copy a file to a press' public directory.
	 * @param $pressId int
	 * @param $sourceFile string the source of the file to copy
	 * @param $destFileName string the destination file name
	 * @return boolean
	 */
	function copyPressFile($pressId, $sourceFile, $destFileName) {
		return $this->copyFile($sourceFile, $this->getPressFilesPath($pressId) . '/' . $destFileName);
	}

	/**
	 * Delete a file from a press' public directory.
	 * @param $pressId int
	 * @param $fileName string the target file name
	 * @return boolean
	 */
	function removePressFile($pressId, $fileName) {
		return $this->deleteFile($this->getPressFilesPath($pressId) . '/' . $fileName);
	}
}

?>
