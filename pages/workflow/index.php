<?php

/**
 * @defgroup pages_review
 */

/**
 * @file pages/review/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_review
 * @brief Handle requests for review functions.
 *
 */

switch ($op) {
	case 'copyediting':
		define('HANDLER_CLASS', 'CopyeditingHandler');
		import('pages.workflow.CopyeditingHandler');
		break;
	case 'review':
		define('HANDLER_CLASS', 'ReviewHandler');
		import('pages.workflow.ReviewHandler');
		break;
}

?>
