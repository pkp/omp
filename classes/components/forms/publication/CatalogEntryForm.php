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
use PKP\components\forms\FieldAutosuggestPreset;
use PKP\components\forms\FieldRichTextarea;
use PKP\components\forms\FieldSelect;
use PKP\components\forms\FieldText;
use PKP\components\forms\FieldUploadImage;
use PKP\components\forms\FormComponent;
use PKP\publication\enums\UpdateType;

class CatalogEntryForm extends FormComponent
{
    public const FORM_CATALOG_ENTRY = 'catalogEntry';

    public const GROUP_PLACEMENT = 'placement';
    public const GROUP_PUBLICATION_TIMING = 'publicationTiming';
    public const GROUP_VERSION_AND_UPDATES = 'versionAndUpdates';
    public const GROUP_DISPLAY = 'display';
    public const GROUP_ACCESS = 'access';

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

        $this
            ->addGroup(['id' => self::GROUP_PLACEMENT, 'label' => __('publication.placement')])
            ->addGroup(['id' => self::GROUP_PUBLICATION_TIMING, 'label' => __('publication.publicationTiming')])
            ->addGroup(['id' => self::GROUP_VERSION_AND_UPDATES, 'label' => __('publication.versionAndUpdates')])
            ->addGroup(['id' => self::GROUP_DISPLAY, 'label' => __('publication.display')])
            ->addGroup(['id' => self::GROUP_ACCESS, 'label' => __('publication.access')]);

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

        $this
            ->addField(new FieldSelect('seriesId', [
                'groupId' => self::GROUP_PLACEMENT,
                'label' => __('series.series'),
                'value' => $publication->getData('seriesId'),
                'options' => $seriesOptions,
            ]))
            ->addField(new FieldText('seriesPosition', [
                'groupId' => self::GROUP_PLACEMENT,
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

            $vocabulary = Repo::category()->getCategoryVocabularyStructure($categories);

            $this->addField(new FieldAutosuggestPreset('categoryIds', [
                'groupId' => self::GROUP_PLACEMENT,
                'label' => __('submission.submit.placement.categories'),
                'value' => (array) $publication->getData('categoryIds'),
                'description' => $hasAllBreadcrumbs ? '' : __('submission.categories.circularReferenceWarning'),
                'options' => $categoryOptions,
                'vocabularies' => [
                    [
                        'addButtonLabel' => __('manager.selectCategories'),
                        'modalTitleLabel' => __('manager.selectCategories'),
                        'items' => $vocabulary
                    ]
                ]
            ]));
        }

        $this
            ->addField(new FieldText('datePublished', [
                'groupId' => self::GROUP_PUBLICATION_TIMING,
                'label' => __('publication.datePublished'),
                'value' => $publication->getData('datePublished'),
            ]))
            ->addField(new FieldSelect('updateType', [
                'groupId' => self::GROUP_VERSION_AND_UPDATES,
                'label' => __('publication.updateType.label'),
                'description' => __('publication.updateType.description'),
                'options' => array_map(
                    fn (UpdateType $case) => ['value' => $case->value, 'label' => $case->label()],
                    UpdateType::cases()
                ),
                'value' => $publication->getData('updateType') ?? UpdateType::NEW_VERSION->value,
                'size' => 'large',
            ]))
            ->addField(new FieldRichTextarea('summaryOfChanges', [
                'groupId' => self::GROUP_VERSION_AND_UPDATES,
                'label' => __('submission.form.summaryOfChanges'),
                'description' => __('publication.summaryOfChanges.description'),
                'isMultilingual' => true,
                'value' => $publication->getData('summaryOfChanges'),
            ]))
            ->addField(new FieldUploadImage('coverImage', [
                'groupId' => self::GROUP_DISPLAY,
                'label' => __('monograph.coverImage'),
                'value' => $publication->getData('coverImage'),
                'isMultilingual' => true,
                'baseUrl' => $baseUrl,
                'options' => [
                    'url' => $temporaryFileApiUrl,
                ],
            ]))
            ->addField(new FieldText('urlPath', [
                'groupId' => self::GROUP_ACCESS,
                'label' => __('publication.urlPath'),
                'description' => __('publication.urlPath.description'),
                'value' => $publication->getData('urlPath'),
            ]));
    }
}
