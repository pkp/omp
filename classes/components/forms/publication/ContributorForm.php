<?php
/**
 * @file classes/components/forms/publication/ContributorForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ContributorForm
 *
 * @ingroup classes_components_forms_publication
 *
 * @brief A preset form for adding and editing a contributor for a publication.
 */

namespace APP\components\forms\publication;

use APP\submission\Submission;
use PKP\components\forms\FieldOptions;
use PKP\context\Context;

class ContributorForm extends \PKP\components\forms\publication\ContributorForm
{
    public function __construct(string $action, array $locales, Submission $submission, Context $context)
    {
        parent::__construct($action, $locales, $submission, $context);

        if ($submission->getData('workType') === Submission::WORK_TYPE_EDITED_VOLUME) {
            $this->addField(new FieldOptions('isVolumeEditor', [
                'label' => __('author.volumeEditor'),
                'value' => false,
                'options' => [
                    [
                        'value' => true,
                        'label' => __('author.isVolumeEditor'),
                    ],
                ],
            ]));
        }
    }
}
