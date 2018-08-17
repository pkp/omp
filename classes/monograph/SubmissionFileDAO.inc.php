<?php

/**
 * @file classes/monograph/SubmissionFileDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileDAO
 * @ingroup monograph
 * @see SubmissionFile
 * @see SubmissionArtworkFile
 * @see SubmissionFileDAODelegate
 * @see SubmissionArtworkFileDAODelegate
 *
 * @brief Operations for retrieving and modifying OMP-specific submission
 *  file implementations.
 */

import('lib.pkp.classes.submission.PKPSubmissionFileDAO');

class SubmissionFileDAO extends PKPSubmissionFileDAO {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}


	//
	// Protected helper methods
	//
	/**
	 * @copydoc PKPSubmissionFileDAO::fromRow()
	 */
	function fromRow($row, $fileImplementation = null) {
		if (isset($row['artwork_file_id']) && is_numeric($row['artwork_file_id'])) {
			return parent::fromRow($row, 'SubmissionArtworkFile');
		} elseif (isset($row['supplementary_file_id']) && is_numeric($row['supplementary_file_id'])) {
			return parent::fromRow($row, 'SupplementaryFile');
		} else {
			return parent::fromRow($row, 'SubmissionFile');
		}
	}
}


