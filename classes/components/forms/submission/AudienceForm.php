<?php
/**
 * @file classes/components/form/submission/AudienceForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AudienceForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for entering a submission's audience details.
 */

namespace APP\components\forms\submission;

use APP\codelist\ONIXCodelistItemDAO;
use APP\submission\Submission;
use PKP\components\forms\FieldSelect;
use PKP\components\forms\FormComponent;
use PKP\db\DAORegistry;

class AudienceForm extends FormComponent
{
    public const FORM_AUDIENCE = 'audience';
    public $id = self::FORM_AUDIENCE;
    public $method = 'PUT';

    /** @var string */
    public $successMessage;

    /**
     * Constructor
     *
     * @param string $action URL to submit the form to
     * @param Submission $submission The submission to change settings for
     */
    public function __construct($action, $submission)
    {
        $this->action = $action;
        $this->successMessage = __('monograph.audience.success');

        /** @var ONIXCodelistItemDAO */
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
        $audienceCodes = $this->getOptions($onixCodelistItemDao->getCodes('List28'));
        $audienceRangeQualifiers = $this->getOptions($onixCodelistItemDao->getCodes('List30'));
        $audienceRanges = $this->getOptions($onixCodelistItemDao->getCodes('List77'));

        $this->addField(new FieldSelect('audience', [
            'label' => __('monograph.audience'),
            'value' => $submission->getData('audience'),
            'options' => $audienceCodes,
        ]))
            ->addField(new FieldSelect('audienceRangeQualifier', [
                'label' => __('monograph.audience.rangeQualifier'),
                'value' => $submission->getData('audienceRangeQualifier'),
                'options' => $audienceRangeQualifiers,
            ]))
            ->addField(new FieldSelect('audienceRangeFrom', [
                'label' => __('monograph.audience.rangeFrom'),
                'value' => $submission->getData('audienceRangeFrom'),
                'options' => $audienceRanges,
            ]))
            ->addField(new FieldSelect('audienceRangeTo', [
                'label' => __('monograph.audience.rangeTo'),
                'value' => $submission->getData('audienceRangeTo'),
                'options' => $audienceRanges,
            ]))
            ->addField(new FieldSelect('audienceRangeExact', [
                'label' => __('monograph.audience.rangeExact'),
                'value' => $submission->getData('audienceRangeExact'),
                'options' => $audienceRanges,
            ]));
    }

    /**
     * Convert Onix code list to select field options
     *
     * @param array $list the list items
     *
     * @return array
     */
    public function getOptions($list)
    {
        $options = [];
        foreach ($list as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }
        return $options;
    }
}
