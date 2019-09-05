<?php
/**
 * @file classes/components/form/publication/CatalogEntryForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for entering a publication's catalog entry details,
 *   such as series, category and cover image.
 */
namespace APP\components\forms\publication;
use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldOptions;
use \PKP\components\forms\FieldSelect;
use \PKP\components\forms\FieldText;
use \PKP\components\forms\FieldUploadImage;
use \DAORegistry;

define('FORM_CATALOG_ENTRY', 'catalogEntry');

class CatalogEntryForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_CATALOG_ENTRY;

	/** @copydoc FormComponent::$method */
	public $method = 'PUT';

	/**
	 * Constructor
	 *
	 * @param $action string URL to submit the form to
	 * @param $locales array Supported locales
	 * @param $publication Publication The publication to change settings for
	 * @param $submission Submission The submission of this publication
	 * @param $baseUrl string Site's base URL. Used for image previews.
	 * @param $temporaryFileApiUrl string The url to upload the cover image
	 */
	public function __construct($action, $locales, $publication, $submission, $baseUrl, $temporaryFileApiUrl) {
		$this->action = $action;
		$this->successMessage = __('publication.catalogEntry.success');
		$this->locales = $locales;

		// Series options
		$seriesOptions = [['value' => '', 'label' => '']];
		$result = DAORegistry::getDAO('SeriesDAO')->getByContextId($submission->getData('contextId'));
		while (!$result->eof()) {
			$series = $result->next();
			$seriesOptions[] = [
				'value' => (int) $series->getId(),
				'label' => $series->getLocalizedTitle(),
			];
		}

		// Category options
		$categoryOptions = [];
		$result = DAORegistry::getDAO('CategoryDAO')->getByContextId($submission->getData('contextId'));
		while (!$result->eof()) {
			$category = $result->next();
			$categoryOptions[] = [
				'value' => (int) $category->getId(),
				'label' => $category->getLocalizedTitle(),
			];
		}

		$this->addField(new FieldText('datePublished', [
				'label' => __('publication.datePublished'),
				'value' => $publication->getData('datePublished'),
			]))
			->addField(new FieldSelect('seriesId', [
				'label' => __('series.series'),
				'value' => $publication->getData('seriesId'),
				'options' => $seriesOptions,
			]))
			->addField(new FieldText('seriesPosition', [
				'label' => __('submission.submit.seriesPosition'),
				'description' => __('submission.submit.seriesPosition.description'),
				'value' => $publication->getData('seriesPosition'),
			]))
			->addField(new FieldOptions('categoryIds', [
				'label' => __('submission.submit.placement.categories'),
				'value' => (array) $publication->getData('categoryIds'),
				'options' => $categoryOptions,
			]))
			->addField(new FieldUploadImage('coverImage', [
				'label' => __('monograph.coverImage'),
				'value' => $publication->getData('coverImage'),
				'isMultilingual' => true,
				'baseUrl' => $baseUrl,
				'options' => [
					'url' => $temporaryFileApiUrl,
				],
			]));
	}
}
