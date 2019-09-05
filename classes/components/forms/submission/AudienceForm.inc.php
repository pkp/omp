<?php
/**
 * @file classes/components/form/submission/AudienceForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AudienceForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for entering a submission's audience details.
 */
namespace APP\components\forms\submission;
use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldSelect;
use \DAORegistry;

define('FORM_AUDIENCE', 'audience');

class AudienceForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_AUDIENCE;

	/** @copydoc FormComponent::$method */
	public $method = 'PUT';

	/**
	 * Constructor
	 *
	 * @param $action string URL to submit the form to
	 * @param $submission Submission The submission to change settings for
	 */
	public function __construct($action, $submission) {
		$this->action = $action;
		$this->successMessage = __('monograph.audience.success');

		$audienceCodes = $this->getOptions(DAORegistry::getDAO('ONIXCodelistItemDAO')->getCodes('List28'));
		$audienceRangeQualifiers = $this->getOptions(DAORegistry::getDAO('ONIXCodelistItemDAO')->getCodes('List30'));
		$audienceRanges = $this->getOptions(DAORegistry::getDAO('ONIXCodelistItemDAO')->getCodes('List77'));

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
	 * @param array the list items
	 * @return array
	 */
	public function getOptions($list) {
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
