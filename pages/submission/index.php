<?php

/**
 * @defgroup pages_submission
 */

/**
 * @file pages/submission/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
	case 'expediteSubmission':
		import('pages.submission.SubmitHandler');
		define('HANDLER_CLASS', 'SubmitHandler');
		break;
	default:
		import('pages.submission.SubmissionHandler');
		define('HANDLER_CLASS', 'SubmissionHandler');
}

?>
