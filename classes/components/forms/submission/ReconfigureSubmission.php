<?php
/**
 * @file classes/components/form/submission/ReconfigureSubmission.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ReconfigureSubmission
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for configuring the submission wizard, such as the
 *   submission's series or language, after the submission was started.
 */

namespace APP\components\forms\submission;

use APP\publication\Publication;
use APP\submission\Submission;
use PKP\components\forms\FieldOptions;
use PKP\context\Context;

class ReconfigureSubmission extends \PKP\components\forms\submission\ReconfigureSubmission
{
    public function __construct(string $action, Submission $submission, Publication $publication, Context $context)
    {
        parent::__construct($action, $submission, $publication, $context);

        $this->addField(new FieldOptions('workType', [
            'type' => 'radio',
            'label' => __('submission.workflowType'),
            'description' => __('submission.workflowType.description'),
            'options' => [
                [
                    'value' => Submission::WORK_TYPE_AUTHORED_WORK,
                    'label' => __('submission.workflowType.authoredWork'),
                ],
                [
                    'value' => Submission::WORK_TYPE_EDITED_VOLUME,
                    'label' => __('submission.workflowType.editedVolume'),
                ],
            ],
            'value' => $submission->getData('workType'),
            'isRequired' => true,
        ]));
    }
}
