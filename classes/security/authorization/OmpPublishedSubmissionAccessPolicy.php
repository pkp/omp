<?php

/**
 * @file classes/security/authorization/OmpPublishedSubmissionAccessPolicy.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OmpPublishedSubmissionAccessPolicy
 *
 * @ingroup security_authorization
 *
 * @brief Class to control access to published submissions in OMP.
 */

namespace APP\security\authorization;

use APP\core\Request;
use PKP\security\authorization\internal\ContextPolicy;

class OmpPublishedSubmissionAccessPolicy extends ContextPolicy
{
    /**
     * Constructor
     *
     * @param Request $request
     * @param array $args request parameters
     * @param array $roleAssignments
     * @param string $submissionParameterName the request parameter we
     */
    public function __construct($request, $args, $roleAssignments, $submissionParameterName = 'submissionId')
    {
        parent::__construct($request);

        // Require published submissions
        $this->addPolicy(new OmpPublishedSubmissionRequiredPolicy($request, $args, $submissionParameterName));
    }
}
