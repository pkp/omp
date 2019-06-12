<?php

/**
 * @file classes/log/SubmissionEmailLogEntry.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionEmailLogEntry
 * @ingroup log
 * @see SubmissionEmailLogDAO
 *
 * @brief Describes an entry in the submission email log.
 */

import('lib.pkp.classes.log.PKPSubmissionEmailLogEntry');

class SubmissionEmailLogEntry extends PKPSubmissionEmailLogEntry {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	function setMonographId($submissionId) {
		return $this->setAssocId($submissionId);
	}

	function getMonographId() {
		return $this->getAssocId();
	}

}

?>
