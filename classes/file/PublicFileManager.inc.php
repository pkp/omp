<?php

/**
 * @file classes/file/PublicFileManager.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicFileManager
 * @ingroup file
 *
 * @brief Wrapper class for uploading files to a site/press' public directory.
 */

// $Id$


import('file.FileManager');

class PublicFileManager extends FileManager {

	/**
	 * Get the path to the site public files directory.
	 * @return string
	 */
	function getSiteFilesPath() {
		return Config::getVar('files', 'public_files_dir') . '/site';
	}

	/**
	 * Upload a file to the site's public directory.
	 * @param $fileName string the name of the file in the upload form
	 * @param $destFileName string the destination file name
	 * @return boolean
	 */
 	function uploadSiteFile($fileName, $destFileName) {
 		return $this->uploadFile($fileName, $this->getSiteFilesPath() . '/' . $destFileName);
 	}

 	/**
	 * Delete a file from the site's public directory.
 	 * @param $fileName string the target file name
	 * @return boolean
 	 */
 	function removeSiteFile($fileName) {
 		return $this->deleteFile($this->getSiteFilesPath() . '/' . $fileName);
 	}

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
