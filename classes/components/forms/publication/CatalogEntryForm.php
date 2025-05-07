<?php

/**
 * @file classes/components/form/publication/CatalogEntryForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for entering a publication's catalog entry details,
 *   such as series, category and cover image.
 */

namespace APP\components\forms\publication;

use APP\facades\Repo;
use APP\publication\Publication;
use APP\submission\Submission;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldSelect;
use PKP\components\forms\FieldText;
use PKP\components\forms\FieldUploadImage;
use PKP\components\forms\FormComponent;

class CatalogEntryForm extends FormComponent
{
    public const FORM_CATALOG_ENTRY = 'catalogEntry';
    public $id = self::FORM_CATALOG_ENTRY;
    public $method = 'PUT';

    /** @var string */
    public $successMessage;

    /**
     * Constructor
     *
     * @param string $action URL to submit the form to
     * @param array $locales Supported locales
     * @param Publication $publication The publication to change settings for
     * @param Submission $submission The submission of this publication
     * @param string $baseUrl Site's base URL. Used for image previews.
     * @param string $temporaryFileApiUrl The url to upload the cover image
     */
    public function __construct($action, $locales, $publication, $submission, $baseUrl, $temporaryFileApiUrl)
    {
        $this->action = $action;
        $this->successMessage = __('publication.catalogEntry.success');
        $this->locales = $locales;

        // Series options
        $seriesOptions = [['value' => '', 'label' => '']];
        $result = Repo::section()
            ->getCollector()
            ->filterByContextIds([$submission->getData('contextId')])
            ->getMany();
        foreach ($result as $series) {
            $seriesOptions[] = [
                'value' => (int) $series->getId(),
                'label' => (($series->getIsInactive()) ? __('publication.inactiveSeries', ['series' => $series->getLocalizedTitle()]) : $series->getLocalizedTitle()),
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
            ]));

        // Categories
        $categoryOptions = [];
        $categories = Repo::category()->getCollector()
            ->filterByContextIds([$submission->getData('contextId')])
            ->getMany();

        $categoriesBreadcrumb = Repo::category()->getBreadcrumbs($categories);
        foreach ($categoriesBreadcrumb as $categoryId => $breadcrumb) {
            $categoryOptions[] = [
                'value' => $categoryId,
                'label' => $breadcrumb,
            ];
        }

        $hasAllBreadcrumbs = count($categories) === $categoriesBreadcrumb->count();
        if (!empty($categoryOptions)) {
            $this->addField(new FieldOptions('categoryIds', [
                'label' => __('submission.submit.placement.categories'),
                'value' => (array) $publication->getData('categoryIds'),
                'description' => $hasAllBreadcrumbs ? '' : __('submission.categories.circularReferenceWarning'),
                'options' => $categoryOptions,
            ]));
        }

        $this->addField(new FieldText('urlPath', [
            'label' => __('publication.urlPath'),
            'description' => __('publication.urlPath.description'),
            'value' => $publication->getData('urlPath'),
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
