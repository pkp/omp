<?php

/**
 * @defgroup pages_review
 */

/**
 * @file pages/workflow/index.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_review
 * @brief Handle requests for review functions.
 *
 */


switch ($op) {
	case 'submission':
	case 'internalReview':
	case 'externalReview':
	case 'copyediting':
	case 'production':
		define('HANDLER_CLASS', 'WorkflowHandler');
		import('pages.workflow.WorkflowHandler');
		break;
	case 'fetchPublicationFormat':
		define('HANDLER_CLASS', 'PublicationFormatHandler');
		import('pages.workflow.PublicationFormatHandler');
		break;
}

?>
