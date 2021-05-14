<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep3Form.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep3Form
 * @ingroup submission_form
 *
 * @brief Form for Step 3 of author submission.
 */

namespace APP\submission\form;

use PKP\submission\form\PKPSubmissionSubmitStep3Form;

use APP\submission\SubmissionMetadataFormImplementation;

class SubmissionSubmitStep3Form extends PKPSubmissionSubmitStep3Form
{
    /**
     * Constructor.
     */
    public function __construct($context, $submission)
    {
        parent::__construct(
            $context,
            $submission,
            new SubmissionMetadataFormImplementation($this)
        );
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\submission\form\SubmissionSubmitStep3Form', '\SubmissionSubmitStep3Form');
}
