<?php

/**
 * @file classes/file/PressFileManager.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressFileManager
 * @ingroup file
 *
 * @brief Class defining operations for private press file management.
 */


import('lib.pkp.classes.file.FileManager');

class PressFileManager extends FileManager {

	/** @var string the path to location of the files */
	var $filesDir;

	/** @var int the ID of the associated press */
	var $pressId;

	/** @var Press the associated press */
	var $press;

	/**
	 * Constructor.
	 * Create a manager for handling press file uploads.
	 * @param $press Press
	 */
	function PressFileManager(&$press) {
		$this->pressId = $press->getId();
		$this->press =& $press;
		$this->filesDir = Config::getVar('files', 'files_dir') . '/presses/' . $this->pressId . '/';

		parent::FileManager();
	}

	function uploadFile($fileName, $destFileName) {
		return parent::uploadFile($fileName, $this->filesDir . $destFileName);
	}

	function downloadFile($filePath, $fileType, $inline = false) {
		return parent::downloadFile($this->filesDir . $filePath, $fileType, $inline);
	}

	function deleteFile($fileName) {
		return parent::deleteFile($this->filesDir . $fileName);
	}
}

?>
