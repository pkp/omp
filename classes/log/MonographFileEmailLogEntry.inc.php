<?php

/**
 * @file classes/log/MonographFileEmailLogEntry.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileEmailLogEntry
 * @ingroup log
 * @see MonographFileEmailLogDAO
 *
 * @brief Describes an entry in the monograph file email log.
 */

import('lib.pkp.classes.log.EmailLogEntry');

class MonographFileEmailLogEntry extends EmailLogEntry {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	function setFileId($fileId) {
		return $this->setAssocId($fileId);
	}

	function getFileId() {
		return $this->getAssocId();
	}

}

?>
