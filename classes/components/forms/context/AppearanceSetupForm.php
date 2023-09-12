<?php
/**
 * @file classes/components/form/context/AppearanceSetupForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AppearanceSetupForm
 *
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for general website appearance setup, such as uploading
 *  a logo.
 */

namespace APP\components\forms\context;

use APP\facades\Repo;
use PKP\components\forms\context\PKPAppearanceSetupForm;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldUploadImage;

class AppearanceSetupForm extends PKPAppearanceSetupForm
{
    /**
     * @copydoc PKPAppearanceSetupForm::__construct()
     */
    public function __construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl, $imageUploadUrl)
    {
        parent::__construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl, $imageUploadUrl);

        $catalogSortOptions = Repo::submission()->getSortSelectOptions();
        $catalogSortOptions = array_map(function ($key, $label) {
            return ['value' => $key, 'label' => $label];
        }, array_keys($catalogSortOptions), $catalogSortOptions);

        $this->addField(new FieldOptions('displayFeaturedBooks', [
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
            ]))
            ->addField(new FieldUploadImage('pressThumbnail', [
                'label' => __('manager.setup.pressThumbnail'),
                'tooltip' => __('manager.setup.pressThumbnail.description'),
                'isMultilingual' => true,
                'value' => $context->getData('pressThumbnail'),
                'baseUrl' => $baseUrl,
                'options' => [
                    'url' => $temporaryFileApiUrl,
                ],
            ]), [FIELD_POSITION_AFTER, 'pageHeaderLogoImage']);
    }
}
