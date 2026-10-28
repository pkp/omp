<?php

/**
 * @file classes/components/form/context/MetadataSettingsForm.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2000-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MetadataSettingsForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief Add OMP-specific fields to the metadata settings form.
 */

namespace APP\components\forms\context;

use APP\codelist\Thema;
use PKP\components\forms\context\PKPMetadataSettingsForm;
use PKP\components\forms\FieldMetadataSetting;
use PKP\components\forms\FieldOptions;
use PKP\context\Context;

class MetadataSettingsForm extends PKPMetadataSettingsForm
{
    /**
     * @copydoc PKPMetadataSettingsForm::__construct()
     */
    public function __construct($action, $context)
    {
        parent::__construct($action, $context);

        $this->addField(new FieldMetadataSetting(Thema::SETTING, [
            'label' => __('manager.setup.metadata.subjects.thema'),
            'description' => __('manager.setup.metadata.subjects.thema.description'),
            'options' => [
                ['value' => Context::METADATA_ENABLE, 'label' => __('manager.setup.metadata.subjects.thema.enable')],
            ],
            'submissionOptions' => [
                ['value' => Context::METADATA_ENABLE, 'label' => __('manager.setup.metadata.subjects.thema.allowFreeText')],
                ['value' => Context::METADATA_REQUIRE, 'label' => __('manager.setup.metadata.subjects.thema.require')],
            ],
            'value' => $context->getData(Thema::SETTING) ?: Context::METADATA_DISABLE,
        ]), [FIELD_POSITION_AFTER, 'subjects']);

        $this->addField(new FieldOptions('enablePublisherId', [
            'label' => __('submission.publisherId'),
            'description' => __('submission.publisherId.description'),
            'options' => [
                [
                    'value' => 'publication',
                    'label' => __('submission.publisherId.enable', ['objects' => __('common.publications')]),
                ],
                [
                    'value' => 'chapter',
                    'label' => __('submission.publisherId.enable', ['objects' => __('submission.chapters')]),
                ],
                [
                    'value' => 'representation',
                    'label' => __('submission.publisherId.enable', ['objects' => __('monograph.publicationFormats')]),
                ],
                [
                    'value' => 'file',
                    'label' => __('submission.publisherId.enable', ['objects' => __('submission.files')]),
                ],
            ],
            'value' => $context->getData('enablePublisherId') ? $context->getData('enablePublisherId') : [],
        ]));
    }
}
