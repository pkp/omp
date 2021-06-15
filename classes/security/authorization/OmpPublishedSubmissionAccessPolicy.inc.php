<?php
/**
 * @file classes/security/authorization/OmpPublishedSubmissionAccessPolicy.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OmpPublishedSubmissionAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to published submissions in OMP.
 */

namespace APP\security\authorization;

use PKP\security\authorization\internal\ContextPolicy;

class OmpPublishedSubmissionAccessPolicy extends ContextPolicy
{
    /**
     * Constructor
     *
     * @param $request PKPRequest
     * @param $args array request parameters
     * @param $roleAssignments array
     * @param $submissionParameterName string the request parameter we
     */
    public function __construct($request, $args, $roleAssignments, $submissionParameterName = 'submissionId')
    {
        parent::__construct($request);

        // Require published submissions
        $this->addPolicy(new OmpPublishedSubmissionRequiredPolicy($request, $args, $submissionParameterName));
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\security\authorization\OmpPublishedSubmissionAccessPolicy', '\OmpPublishedSubmissionAccessPolicy');
}
