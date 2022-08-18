<?php

/**
 * @file controllers/grid/settings/series/form/SeriesForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesForm
 * @ingroup controllers_grid_settings_series_form
 *
 * @brief Form for adding/edditing a series
 * stores/retrieves from an associative array
 */

namespace APP\controllers\grid\settings\series\form;

use APP\core\Application;
use PKP\controllers\grid\settings\sections\form\PKPSectionForm;
use APP\facades\Repo;
use APP\template\TemplateManager;
use PKP\file\ContextFileManager;
use PKP\file\TemporaryFileManager;
use PKP\db\DAORegistry;
use PKP\security\Role;

class SeriesForm extends PKPSectionForm
{
    /** @var int $_pressId */
    public $_pressId;

    /**
     * Constructor.
     *
     * @param Request $request
     * @param int $seriesId optional
     */
    public function __construct($request, $seriesId = null)
    {
        parent::__construct(
            $request,
            'controllers/grid/settings/series/form/seriesForm.tpl',
            $seriesId
        );

        $this->_pressId = $pressId = $request->getContext()->getId();

        // Validation checks for this form
        $form = $this;
        $this->addCheck(new \PKP\form\validation\FormValidatorLocale($this, 'title', 'required', 'manager.setup.form.series.nameRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorISSN($this, 'onlineIssn', 'optional', 'catalog.manage.series.issn.validation'));
        $this->addCheck(new \PKP\form\validation\FormValidatorISSN($this, 'printIssn', 'optional', 'catalog.manage.series.issn.validation'));
        $this->addCheck(new \PKP\form\validation\FormValidatorCustom($this, 'printIssn', 'optional', 'catalog.manage.series.issn.equalValidation', function ($printIssn) use ($form) {
            return !($form->getData('onlineIssn') != '' && $form->getData('onlineIssn') == $printIssn);
        }));
        $this->addCheck(new \PKP\form\validation\FormValidatorRegExp($this, 'path', 'required', 'grid.series.pathAlphaNumeric', '/^[a-zA-Z0-9\/._-]+$/'));
        $this->addCheck(new \PKP\form\validation\FormValidatorCustom(
            $this,
            'path',
            'required',
            'grid.series.pathExists',
            function ($path) use ($form, $pressId) {
                $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
                return !$seriesDao->getByPath($path, $pressId) || ($form->getData('oldPath') != null && $form->getData('oldPath') == $path);
            }
        ));
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData()
    {
        $request = Application::get()->getRequest();
        $press = $request->getPress();

        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $seriesId = $this->getSeriesId();
        $categoryIds = [];
        if ($seriesId) {
            $series = $seriesDao->getById($seriesId, $press->getId());
            $categoryIds = array_map(fn($category) => $category->getId(), $seriesDao->getCategories($seriesId, $press->getId()));
        }

        if (isset($series)) {
            $sortOption = $series->getSortOption() ? $series->getSortOption() : Repo::submission()->getDefaultSortOption();
            $this->_data = [
                'seriesId' => $seriesId,
                'title' => $series->getTitle(null, false),
                'featured' => $series->getFeatured(),
                'path' => $series->getPath(),
                'description' => $series->getDescription(null),
                'prefix' => $series->getPrefix(null),
                'subtitle' => $series->getSubtitle(null),
                'image' => $series->getImage(),
                'restricted' => $series->getEditorRestricted(),
                'isInactive' => $series->getIsInactive(),
                'onlineIssn' => $series->getOnlineISSN(),
                'printIssn' => $series->getPrintISSN(),
                'sortOption' => $sortOption,
                'categories' => $categoryIds,
            ];
        }

        return parent::initData();
    }

    /**
     * @see Form::validate()
     */
    public function validate($callHooks = true)
    {
        if ($temporaryFileId = $this->getData('temporaryFileId')) {
            $temporaryFileManager = new TemporaryFileManager();
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO'); /** @var TemporaryFileDAO $temporaryFileDao */
            $temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $this->_userId);
            if (!$temporaryFile ||
                    !($this->_imageExtension = $temporaryFileManager->getImageExtension($temporaryFile->getFileType())) ||
                    !($this->_sizeArray = getimagesize($temporaryFile->getFilePath())) ||
                    $this->_sizeArray[0] <= 0 || $this->_sizeArray[1] <= 0
            ) {
                $this->addError('temporaryFileId', __('form.invalidImage'));
                return false;
            }
        }

        // Validate if it can be inactive
        if ($this->getData('isInactive')) {
            $request = Application::get()->getRequest();
            $context = $request->getContext();
            $seriesId = $this->getSeriesId();

            $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
            $seriesIterator = $seriesDao->getByContextId($context->getId());
            $activeSeriesCount = 0;
            while ($series = $seriesIterator->next()) {
                if (!$series->getIsInactive() && ($seriesId != $series->getId())) {
                    $activeSeriesCount++;
                }
            }
            if ($activeSeriesCount < 1 && $this->getData('isInactive')) {
                $this->addError('isInactive', __('manager.series.confirmDeactivateSeries.error'));
            }
        }

        return parent::validate($callHooks);
    }

    /**
     * @copydoc PKPSectionForm::fetch()
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('seriesId', $this->getSeriesId());

        $context = $request->getContext();

        $categoryCount = Repo::category()->getCollector()
            ->filterByContextIds([$context->getId()])
            ->getCount();

        $templateMgr->assign('categoryCount', $categoryCount);

        // Sort options.
        $templateMgr->assign('sortOptions', Repo::submission()->getSortSelectOptions());

        // Series Editors
        $usersIterator = Repo::user()->getMany(
            Repo::user()->getCollector()
                ->filterByContextIds([$context->getId()])
                ->filterByRoleIds([Role::ROLE_ID_SUB_EDITOR])
        );
        $availableSubeditors = [];
        foreach ($usersIterator as $user) {
            $availableSubeditors[(int) $user->getId()] = $user->getFullName();
        }
        $assignedToSeries = [];
        if ($this->getSeriesId()) {
            $assignedToSeries = iterator_to_array(Repo::user()->getIds(
                Repo::user()->getCollector()
                    ->filterByContextIds([$context->getId()])
                    ->filterByRoleIds([Role::ROLE_ID_SUB_EDITOR])
                    ->assignedToSectionIds([(int) $this->getSeriesId()])
            ));
        }

        // Categories list
        $allCategories = [];
        $categoriesResult = Repo::category()->getCollector()
            ->filterByContextIds([$context->getId()])
            ->getMany()
            ->toArray();

        foreach ($categoriesResult as $category) {
            $title = $category->getLocalizedTitle();
            if ($category->getParentId()) {
                $title = $categoriesResult[$category->getParentId()]->getLocalizedTitle() . ' > ' . $title;
            }
            $allCategories[(int) $category->getId()] = $title;
        }

        $templateMgr->assign([
            'availableSubeditors' => $availableSubeditors,
            'assignedToSeries' => $assignedToSeries,
            'allCategories' => $allCategories,
            'selectedCategories' => (array) $this->getData('categories'),
        ]);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Assign form data to user-submitted data.
     *
     * @see Form::readInputData()
     */
    public function readInputData()
    {
        parent::readInputData();
        $this->readUserVars(['seriesId', 'path', 'featured', 'restricted', 'description', 'categories', 'prefix', 'subtitle', 'temporaryFileId', 'onlineIssn', 'printIssn', 'sortOption', 'isInactive']);
        // For path duplicate checking; excuse the current path.
        if ($seriesId = $this->getSeriesId()) {
            $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
            $series = $seriesDao->getById($seriesId, $this->_pressId);
            $this->setData('oldPath', $series->getPath());
        }
    }

    /**
     * Save series.
     */
    public function execute(...$functionParams)
    {
        parent::execute(...$functionParams);

        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $request = Application::get()->getRequest();
        $press = $request->getPress();

        // Get or create the series object
        if ($this->getSeriesId()) {
            $series = $seriesDao->getById($this->getSeriesId(), $press->getId());
        } else {
            $series = $seriesDao->newDataObject();
            $series->setPressId($press->getId());
        }

        // Populate/update the series object from the form
        $series->setPath($this->getData('path'));
        $series->setFeatured($this->getData('featured'));
        $series->setTitle($this->getData('title'), null); // Localized
        $series->setDescription($this->getData('description'), null); // Localized
        $series->setPrefix($this->getData('prefix'), null); // Localized
        $series->setSubtitle($this->getData('subtitle'), null); // Localized
        $series->setEditorRestricted($this->getData('restricted'));
        $series->setIsInactive($this->getData('isInactive') ? 1 : 0);
        $series->setOnlineISSN($this->getData('onlineIssn'));
        $series->setPrintISSN($this->getData('printIssn'));
        $series->setSortOption($this->getData('sortOption'));

        // Insert or update the series in the DB
        if ($this->getSeriesId()) {
            $seriesDao->updateObject($series);
        } else {
            $this->setSeriesId($seriesDao->insertObject($series));
        }

        // Handle the image upload if there was one.
        if ($temporaryFileId = $this->getData('temporaryFileId')) {
            // Fetch the temporary file storing the uploaded library file
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO'); /** @var TemporaryFileDAO $temporaryFileDao */

            $temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $this->_userId);
            $temporaryFilePath = $temporaryFile->getFilePath();
            $pressFileManager = new ContextFileManager($press->getId());
            $basePath = $pressFileManager->getBasePath() . '/series/';

            // Delete the old file if it exists
            $oldSetting = $series->getImage();
            if ($oldSetting) {
                $pressFileManager->deleteByPath($basePath . $oldSetting['thumbnailName']);
                $pressFileManager->deleteByPath($basePath . $oldSetting['name']);
            }

            // The following variables were fetched in validation
            assert($this->_sizeArray && $this->_imageExtension);

            // Generate the surrogate image.
            switch ($this->_imageExtension) {
                case '.jpg': $image = imagecreatefromjpeg($temporaryFilePath); break;
                case '.png': $image = imagecreatefrompng($temporaryFilePath); break;
                case '.gif': $image = imagecreatefromgif($temporaryFilePath); break;
                default: $image = null; // Suppress warn
            }
            assert($image);

            $coverThumbnailsMaxWidth = $press->getSetting('coverThumbnailsMaxWidth');
            $coverThumbnailsMaxHeight = $press->getSetting('coverThumbnailsMaxHeight');

            $thumbnailFilename = $series->getId() . '-series-thumbnail' . $this->_imageExtension;
            $xRatio = min(1, $coverThumbnailsMaxWidth / $this->_sizeArray[0]);
            $yRatio = min(1, $coverThumbnailsMaxHeight / $this->_sizeArray[1]);

            $ratio = min($xRatio, $yRatio);

            $thumbnailWidth = round($ratio * $this->_sizeArray[0]);
            $thumbnailHeight = round($ratio * $this->_sizeArray[1]);
            $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $this->_sizeArray[0], $this->_sizeArray[1]);

            // Copy the new file over
            $filename = $series->getId() . '-series' . $this->_imageExtension;
            $pressFileManager->copyFile($temporaryFile->getFilePath(), $basePath . $filename);

            switch ($this->_imageExtension) {
                case '.jpg': imagejpeg($thumbnail, $basePath . $thumbnailFilename); break;
                case '.png': imagepng($thumbnail, $basePath . $thumbnailFilename); break;
                case '.gif': imagegif($thumbnail, $basePath . $thumbnailFilename); break;
            }
            imagedestroy($thumbnail);
            imagedestroy($image);

            $series->setImage([
                'name' => $filename,
                'width' => $this->_sizeArray[0],
                'height' => $this->_sizeArray[1],
                'thumbnailName' => $thumbnailFilename,
                'thumbnailWidth' => $thumbnailWidth,
                'thumbnailHeight' => $thumbnailHeight,
                'uploadName' => $temporaryFile->getOriginalFileName(),
                'dateUploaded' => Core::getCurrentDate(),
            ]);

            // Clean up the temporary file
            $temporaryFileManager = new TemporaryFileManager();
            $temporaryFileManager->deleteById($temporaryFileId, $this->_userId);
        }

        // Update series object to store image information.
        $seriesDao->updateObject($series);

        // Update series editors
        $subEditorsDao = DAORegistry::getDAO('SubEditorsDAO'); /** @var SubEditorsDAO $subEditorsDao */
        $subEditorsDao->deleteBySubmissionGroupId($series->getId(), ASSOC_TYPE_SERIES, $series->getContextId());
        $subEditors = $this->getData('subEditors');
        if (!empty($subEditors)) {
            $roleDao = DAORegistry::getDAO('RoleDAO'); /** @var RoleDAO $roleDao */
            foreach ($subEditors as $subEditor) {
                if ($roleDao->userHasRole($series->getContextId(), $subEditor, Role::ROLE_ID_SUB_EDITOR)) {
                    $subEditorsDao->insertEditor($series->getContextId(), $series->getId(), $subEditor, ASSOC_TYPE_SERIES);
                }
            }
        }

        // Save the category associations.
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        $seriesDao->removeCategories($this->getSeriesId());
        $categoryIds = $this->getData('categories');
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $seriesDao->addCategory($this->getSeriesId(), $categoryId);
            }
        }

        return true;
    }

    /**
     * Get the series ID for this series.
     *
     * @return int
     */
    public function getSeriesId()
    {
        return $this->getSectionId();
    }

    /**
     * Set the series ID for this series.
     *
     * @param int $seriesId
     */
    public function setSeriesId($seriesId)
    {
        $this->setSectionId($seriesId);
    }
}
