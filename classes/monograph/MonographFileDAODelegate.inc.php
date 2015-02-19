<?php

/**
 * @file classes/monograph/MonographFileDAODelegate.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileDAODelegate
 * @ingroup monograph
 * @see MonographFile
 * @see SubmissionFileDAO
 *
 * @brief Operations for retrieving and modifying MonographFile objects.
 *
 * The SubmissionFileDAO will delegate to this class if it wishes
 * to access SubmissionFile classes.
 */


import('classes.monograph.MonographFile');
import('lib.pkp.classes.submission.SubmissionFileDAODelegate');

class MonographFileDAODelegate extends SubmissionFileDAODelegate {
	/**
	 * Constructor
	 */
	function MonographFileDAODelegate() {
		parent::SubmissionFileDAODelegate();
	}

	/**
	 * @see SubmissionFileDAODelegate::newDataObject()
	 * @return SubmissionFile
	 */
	function newDataObject() {
		return new MonographFile();
	}
}

?>
