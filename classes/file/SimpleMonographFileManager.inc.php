<?php

/**
 * @file classes/file/SimpleMonographFileManager.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SimpleMonographFileManager
 * @ingroup file
 *
 * @brief Helper class for simple monograph file management tasks. Simple
 * 	monograph files are not backed in the usual submission_files manner
 * 	and are not versioned.
 *
 * Monograph directory structure:
 * [monograph id]/simple
 */

import('lib.pkp.classes.file.BaseSubmissionFileManager');

class SimpleMonographFileManager extends BaseSubmissionFileManager {
	/**
	 * Constructor.
	 */
	function __construct($pressId, $monographId) {
		parent::__construct($pressId, $monographId);
	}

	/**
	 * Get the storage directory for simple monograph files.
	 */
	function getBasePath() {
		return parent::getBasePath() . 'simple/';
	}
}


