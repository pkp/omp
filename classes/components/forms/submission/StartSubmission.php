<?php
/**
 * @file classes/components/form/submission/StartSubmission.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StartSubmission
 *
 * @ingroup classes_controllers_form
 *
 * @brief The form to begin the submission wizard
 */

namespace APP\components\forms\submission;

use APP\submission\Submission;
use Illuminate\Support\Enumerable;
use PKP\components\forms\FieldOptions;
use PKP\context\Context;

class StartSubmission extends \PKP\components\forms\submission\StartSubmission
{
    public function __construct(string $action, Context $context, Enumerable $userGroups)
    {
        parent::__construct($action, $context, $userGroups);

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
            'value' => Submission::WORK_TYPE_AUTHORED_WORK,
            'isRequired' => true,

        ]), [FIELD_POSITION_AFTER, 'title']);
    }
}
