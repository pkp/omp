<?php
/**
 * @file classes/components/form/context/DoiSetupSettingsForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DoiSetupSettingsForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for enabling and configuring DOI settings for a given context
 */

namespace APP\components\forms\context;

use APP\facades\Repo;
use PKP\components\forms\context\PKPDoiSetupSettingsForm;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldText;
use PKP\context\Context;
use PKP\plugins\Hook;

class DoiSetupSettingsForm extends PKPDoiSetupSettingsForm
{
    public function __construct(string $action, array $locales, Context $context)
    {
        parent::__construct($action, $locales, $context);

        $this->objectTypeOptions = [
            [
                'value' => Repo::doi()::TYPE_PUBLICATION,
                'label' => __('common.publications'),
                'allowedBy' => [],
            ],
            [
                'value' => Repo::doi()::TYPE_CHAPTER,
                'label' => __('submission.chapters'),
                'allowedBy' => [],
            ],
            [
                'value' => Repo::doi()::TYPE_REPRESENTATION,
                'label' => __('monograph.publicationFormats'),
                'allowedBy' => [],
            ],
            [
                'value' => Repo::doi()::TYPE_SUBMISSION_FILE,
                'label' => __('doi.manager.settings.enableSubmissionFileDoi'),
                'allowedBy' => [],
            ],
        ];
        Hook::call('DoiSetupSettingsForm::getObjectTypes', [&$this->objectTypeOptions]);
        if ($this->enabledRegistrationAgency === null) {
            $filteredOptions = $this->objectTypeOptions;
        } else {
            $filteredOptions = array_filter($this->objectTypeOptions, function ($option) {
                return in_array($this->enabledRegistrationAgency, $option['allowedBy']);
            });
        }

        // Added in PKPDoiSetupSettingsForm, but not currently applicable to OMP
        $this->removeField(Context::SETTING_DOI_AUTOMATIC_DEPOSIT);

        $this->addField(new FieldOptions(Context::SETTING_ENABLED_DOI_TYPES, [
            'label' => __('doi.manager.settings.doiObjects'),
            'description' => __('doi.manager.settings.doiObjectsRequired'),
            'groupId' => self::DOI_SETTINGS_GROUP,
            'options' => $filteredOptions,
            'value' => $context->getData(Context::SETTING_ENABLED_DOI_TYPES) ? $context->getData(Context::SETTING_ENABLED_DOI_TYPES) : [],
        ]), [FIELD_POSITION_BEFORE, Context::SETTING_DOI_PREFIX])
            ->addField(new FieldText(Repo::doi()::CUSTOM_CHAPTER_PATTERN, [
                'label' => __('submission.chapters'),
                'groupId' => self::DOI_CUSTOM_SUFFIX_GROUP,
                'value' => $context->getData(Repo::doi()::CUSTOM_CHAPTER_PATTERN),
            ]), [FIELD_POSITION_BEFORE, Repo::doi()::CUSTOM_REPRESENTATION_PATTERN])
            ->addField(new FieldText(Repo::doi()::CUSTOM_FILE_PATTERN, [
                'label' => __('doi.manager.settings.enableSubmissionFileDoi'),
                'groupId' => self::DOI_CUSTOM_SUFFIX_GROUP,
                'value' => $context->getData(Repo::doi()::CUSTOM_FILE_PATTERN),
            ]));
    }
}
