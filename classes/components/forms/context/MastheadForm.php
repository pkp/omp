<?php
/**
 * @file classes/components/form/context/MastheadForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MastheadForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief Add OJS-specific fields to the context add/edit form.
 */

namespace APP\components\forms\context;

use APP\codelist\ONIXCodelistItemDAO;
use PKP\components\forms\context\PKPMastheadForm;
use PKP\components\forms\FieldSelect;
use PKP\components\forms\FieldText;
use PKP\db\DAORegistry;

class MastheadForm extends PKPMastheadForm
{
    /**
     * @copydoc PKPMastheadForm::__construct()
     */
    public function __construct($action, $locales, $context, $imageUploadUrl)
    {
        parent::__construct($action, $locales, $context, $imageUploadUrl);

        /** @var ONIXCodelistItemDAO */
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
        $codeTypes = $onixCodelistItemDao->getCodes('44');
        $codeTypeOptions = array_map(function ($code, $name) {
            return ['value' => $code, 'label' => $name];
        }, array_keys($codeTypes), $codeTypes);

        $this->addGroup([
            'id' => 'onix',
            'label' => __('manager.settings.publisher.identity'),
            'description' => __('manager.settings.publisher.identity.description'),
        ], [FIELD_POSITION_AFTER, 'identity'])
            ->addField(new FieldText('publisher', [
                'label' => __('manager.settings.publisher'),
                'value' => $context->getData('publisher'),
                'size' => 'large',
                'groupId' => 'onix',
            ]))
            ->addField(new FieldText('location', [
                'label' => __('manager.settings.location'),
                'value' => $context->getData('location'),
                'groupId' => 'onix',
            ]))
            ->addField(new FieldSelect('codeType', [
                'label' => __('manager.settings.publisherCodeType'),
                'value' => $context->getData('codeType'),
                'options' => $codeTypeOptions,
                'groupId' => 'onix',
            ]))
            ->addField(new FieldText('codeValue', [
                'label' => __('manager.settings.publisherCode'),
                'value' => $context->getData('codeValue'),
                'groupId' => 'onix',
            ]));
    }
}
