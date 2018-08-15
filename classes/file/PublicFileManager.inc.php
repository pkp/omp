<?php

/**
 * @file classes/file/PublicFileManager.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Get the path to a press' public files directory.
	 * @param $pressId int Press ID
	 * @return string
	 */
	function getPressFilesPath($pressId) {
		return $this->getContextFilesPath(ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Get the path to a press' public files directory.
	 * @param $assocType int Assoc type for context
	 * @param $contextId int Press ID
	 * @return string
	 */
	function getContextFilesPath($assocType, $contextId) {
		assert($assocType == ASSOC_TYPE_PRESS);
		return Config::getVar('files', 'public_files_dir') . '/presses/' . (int) $contextId;
	}

	/**
	 * Upload a file to a press' public directory.
	 * @param $assocType int The assoc type of the context
	 * @param $contextId int The context ID
	 * @param $fileName string the name of the file in the upload form
	 * @param $destFileName string the destination file name
	 * @return boolean
	 */
	function uploadPressFile($pressId, $fileName, $destFileName) {
		return $this->uploadContextFile(ASSOC_TYPE_PRESS, $pressId, $fileName, $destFileName);
	}

	/**
	 * Write a file to a press' public directory.
	 * @param $pressId int
	 * @param $destFileName string the destination file name
	 * @param $contents string the contents to write to the file
	 * @return boolean
	 */
	function writePressFile($pressId, $destFileName, $contents) {
		return $this->writeContextFile(ASSOC_TYPE_PRESS, $pressId, $destFileName, $contents);
	}

	/**
	 * Copy a file to a press' public directory.
	 * @param $pressId int
	 * @param $sourceFile string the source of the file to copy
	 * @param $destFileName string the destination file name
	 * @return boolean
	 */
	function copyPressFile($pressId, $sourceFile, $destFileName) {
		return $this->copyContextFile(ASSOC_TYPE_PRESS, $pressId, $sourceFile, $destFileName);
	}

	/**
	 * Delete a file from a press' public directory.
	 * @param $pressId int
	 * @param $fileName string the target file name
	 * @return boolean
	 */
	function removePressFile($pressId, $fileName) {
		return $this->removeContextFile(ASSOC_TYPE_PRESS, $pressId, $fileName);
	}
}


