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


import('lib.pkp.classes.file.PrivateFileManager');

class PressFileManager extends PrivateFileManager {
	/** @var int the ID of the associated press */
	var $pressId;

	/**
	 * Constructor.
	 * Create a manager for handling press file uploads.
	 * @param $press Press
	 */
	function PressFileManager($pressId) {
		parent::PrivateFileManager();
		$this->pressId = $pressId;
	}

	/**
	 * Get the base path for file storage
	 * @return string
	 */
	function getBasePath() {
		return parent::getBasePath() . '/presses/' . $this->pressId . '/';
	}
}

?>
