<?php

/**
 * @defgroup pages_workflow Workflow page
 */

/**
 * @file pages/workflow/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_workflow
 * @brief Handle requests for workflow functions.
 *
 */

switch ($op) {
    case 'access':
    case 'index':
    case 'submission':
    case 'internalReview':
    case 'externalReview':
    case 'editorial':
    case 'production':
    case 'editorDecisionActions':
    case 'submissionProgressBar':
        define('HANDLER_CLASS', 'APP\pages\workflow\WorkflowHandler');
        break;
}
