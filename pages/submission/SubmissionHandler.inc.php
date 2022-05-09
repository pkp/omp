<?php

/**
 * @file pages/submission/SubmissionHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHandler
 * @ingroup pages_submission
 *
 * @brief Handle requests for the submission wizard.
 */

import('lib.pkp.pages.submission.PKPSubmissionHandler');

use PKP\security\Role;

class SubmissionHandler extends PKPSubmissionHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_AUTHOR, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            ['index', 'wizard', 'step', 'saveStep']
        );
    }


    //
    // Protected helper methods
    //
    /**
     * Get the step numbers and their corresponding title locale keys.
     *
     * @return array
     */
    public function getStepsNumberAndLocaleKeys()
    {
        return [
            1 => 'submission.submit.prepare',
            2 => 'submission.submit.upload',
            3 => 'submission.submit.catalog',
            4 => 'submission.submit.confirmation',
            5 => 'submission.submit.nextSteps',
        ];
    }

    /**
     * Get the number of submission steps.
     *
     * @return int
     */
    public function getStepCount()
    {
        return 5;
    }
}
