<?php
/**
 * @file classes/components/form/context/AppearanceSetupForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AppearanceSetupForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for general website appearance setup, such as uploading
 *  a logo.
 */
namespace APP\components\forms\context;
use \PKP\components\forms\context\PKPAppearanceSetupForm;
use \PKP\components\forms\FieldOptions;

class AppearanceSetupForm extends PKPAppearanceSetupForm {

	/**
	 * @copydoc PKPAppearanceSetupForm::__construct()
	 */
	public function __construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl) {
		parent::__construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl);

		$catalogSortOptions = \DAORegistry::getDAO('SubmissionDAO')->getSortSelectOptions();
		$catalogSortOptions = array_map(function($key, $label) {
			return ['value' => $key, 'label' => $label];
		}, array_keys($catalogSortOptions), $catalogSortOptions);

		$this->addField(new FieldOptions('displayInSpotlight', [
				'label' => __('manager.setup.displayInSpotlight.label'),
				'value' => (bool) $context->getData('displayInSpotlight'),
				'options' => [
					['value' => 'true', 'label' => __('manager.setup.displayInSpotlight')],
				],
			]))
			->addField(new FieldOptions('displayFeaturedBooks', [
				'label' => __('manager.setup.displayFeaturedBooks.label'),
				'value' => (bool) $context->getData('displayFeaturedBooks'),
				'options' => [
					['value' => 'true', 'label' => __('manager.setup.displayFeaturedBooks')],
				],
			]))
			->addField(new FieldOptions('displayNewReleases', [
				'label' => __('manager.setup.displayNewReleases.label'),
				'value' => (bool) $context->getData('displayNewReleases'),
				'options' => [
					['value' => 'true', 'label' => __('manager.setup.displayNewReleases')],
				],
			]))
			->addField(new FieldOptions('catalogSortOption', [
				'label' => __('catalog.sortBy'),
				'description' => __('catalog.sortBy.catalogDescription'),
				'type' => 'radio',
				'value' => $context->getData('catalogSortOption'),
				'options' => $catalogSortOptions,
			]));
	}
}
