<?php

/**
 * @defgroup pages_submission
 */

/**
 * @file pages/submission/index.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_submission
 * @brief Handle requests for submission wizard
 *
 */


switch ($op) {
	//
	// Monograph Submission
	//
	case 'wizard':
	case 'saveStep':
	case 'index':
		import('pages.submission.SubmitHandler');
		define('HANDLER_CLASS', 'SubmitHandler');
		break;
	// FIXME: Move the following operations to their specified handlers - see #6091.
	case 'authorDetails':  // move to workflow/submission?
	case 'reviewRoundInfo': // move to workflow/review?
		import('pages.submission.SubmissionHandler');
		define('HANDLER_CLASS', 'SubmissionHandler');
}

?>
