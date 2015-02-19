<?php

/**
 * @file classes/monograph/MonographFile.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFile
 * @ingroup monograph
 * @see SubmissionFileDAO
 *
 * @brief Monograph file class.
 */

import('lib.pkp.classes.submission.SubmissionFile');

class MonographFile extends SubmissionFile {

	/**
	 * Constructor.
	 */
	function MonographFile() {
		parent::SubmissionFile();
	}


	//
	// Get/set methods
	//

	/**
	 * Get ID of monograph.
	 * @return int
	 */
	function getMonographId() {
		return $this->getSubmissionId();
	}

	/**
	 * Set ID of monograph.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setSubmissionId($monographId);
	}
}

?>
