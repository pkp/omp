<?php

/**
 * @file pages/submission/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_submission
 * @brief Handle requests for the submission wizard.
 *
 */

switch ($op) {
    //
    // Monograph Submission
    //
    case 'wizard':
    case 'step':
    case 'saveStep':
    case 'index':
        import('pages.submission.SubmissionHandler');
        define('HANDLER_CLASS', 'SubmissionHandler');
        break;
}
