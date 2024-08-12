<?php

/**
 * @file controllers/grid/settings/series/form/SeriesForm.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SeriesForm
 *
 * @ingroup controllers_grid_settings_series_form
 *
 * @brief Form for adding/editing a series
 * stores/retrieves from an associative array
 */

namespace APP\controllers\grid\settings\series\form;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\section\Section;
use APP\template\TemplateManager;
use PKP\controllers\grid\settings\sections\form\PKPSectionForm;
use PKP\core\Core;
use PKP\db\DAORegistry;
use PKP\file\ContextFileManager;
use PKP\file\TemporaryFileDAO;
use PKP\file\TemporaryFileManager;

class SeriesForm extends PKPSectionForm
{
    public int $_pressId;

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
                return !Repo::section()->getByPath($path, $pressId) || ($form->getData('oldPath') != null && $form->getData('oldPath') == $path);
            }
        ));
    }

    public function getSeries(): ?Section
    {
        return $this->section;
    }

    public function setSeries(Section $series): void
    {
        $this->section = $series;
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData()
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();

        $seriesId = $this->getSeriesId();
        if ($seriesId) {
            $series = Repo::section()->get($seriesId, $context->getId());
            if ($series) {
                $this->setSeries($series);
                $categoryIds = Repo::section()->getAssignedCategoryIds($series->getId());
            }
        }

        if ($this->getSeries() !== null) {
            $sortOption = $this->getSeries()->getSortOption() ? $this->getSeries()->getSortOption() : Repo::submission()->getDefaultSortOption();
            $this->_data = [
                'seriesId' => $this->getSeries()->getId(),
                'title' => $this->getSeries()->getTitle(null, false),
                'featured' => $this->getSeries()->getFeatured(),
                'path' => $this->getSeries()->getPath(),
                'description' => $this->getSeries()->getDescription(null),
                'prefix' => $this->getSeries()->getPrefix(null),
                'subtitle' => $this->getSeries()->getSubtitle(null),
                'image' => $this->getSeries()->getImage(),
                'editorRestricted' => $this->getSeries()->getEditorRestricted(),
                'isInactive' => $this->getSeries()->getIsInactive(),
                'onlineIssn' => $this->getSeries()->getOnlineISSN(),
                'printIssn' => $this->getSeries()->getPrintISSN(),
                'sortOption' => $sortOption,
                'categories' => $categoryIds ?? []
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
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            /** @var TemporaryFileDAO $temporaryFileDao */
            $temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $this->_userId);
            if (
                !$temporaryFile ||
                !($this->_imageExtension = $temporaryFileManager->getImageExtension($temporaryFile->getFileType())) ||
                !($this->_sizeArray = getimagesize($temporaryFile->getFilePath())) ||
                $this->_sizeArray[0] <= 0 || $this->_sizeArray[1] <= 0
            ) {
                $this->addError('temporaryFileId', __('form.invalidImage'));
                return false;
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

        // Sort options.
        $templateMgr->assign('sortOptions', Repo::submission()->getSortSelectOptions());

        // Categories list
        $allCategories = [];
        $categories = Repo::category()->getCollector()
            ->filterByContextIds([$context->getId()])
            ->getMany()
            ->toArray();

        foreach ($categories as $category) {
            $title = $category->getLocalizedTitle();
            if ($category->getParentId()) {
                $title = $categories[$category->getParentId()]->getLocalizedTitle() . ' > ' . $title;
            }
            $allCategories[(int) $category->getId()] = $title;
        }

        $templateMgr->assign([
            'allCategories' => $allCategories,
            'selectedCategories' => $this->getData('categories')?->values()?->all() ?? [],
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
        $this->readUserVars(['seriesId', 'path', 'featured', 'editorRestricted', 'description', 'categories', 'prefix', 'subtitle', 'temporaryFileId', 'onlineIssn', 'printIssn', 'sortOption', 'isInactive']);
        // For path duplicate checking; excuse the current path.
        if ($seriesId = $this->getSeriesId()) {
            $series = Repo::section()->get($seriesId, $this->_pressId);
            $this->setData('oldPath', $series->getPath());
        }
    }

    /**
     * Save series.
     */
    public function execute(...$functionParams)
    {
        $request = Application::get()->getRequest();
        $press = $request->getPress();

        // Get or create the series object
        if ($this->getSeriesId()) {
            $series = Repo::section()->get($this->getSeriesId(), $press->getId());
        } else {
            $series = Repo::section()->newDataObject();
            $series->setContextId($press->getId());
            $series->setImage([]);
        }

        // Populate/update the series object from the form
        $series->setPath($this->getData('path'));
        $series->setFeatured($this->getData('featured') ? 1 : 0);
        $series->setTitle($this->getData('title'), null); // Localized
        $series->setDescription($this->getData('description'), null); // Localized
        $series->setPrefix($this->getData('prefix'), null); // Localized
        $series->setSubtitle($this->getData('subtitle'), null); // Localized
        $series->setEditorRestricted($this->getData('editorRestricted') ? 1 : 0);
        $series->setIsInactive($this->getData('isInactive') ? 1 : 0);
        $series->setOnlineISSN($this->getData('onlineIssn'));
        $series->setPrintISSN($this->getData('printIssn'));
        $series->setSortOption($this->getData('sortOption'));

        // Insert or update the series in the DB
        if ($this->getSeriesId()) {
            Repo::section()->edit($series, []);
        } else {
            $this->setSeriesId(Repo::section()->add($series));
        }

        // Handle the image upload if there was one.
        if ($temporaryFileId = $this->getData('temporaryFileId')) {
            // Fetch the temporary file storing the uploaded library file
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            /** @var TemporaryFileDAO $temporaryFileDao */

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
                case '.jpg':
                    $image = imagecreatefromjpeg($temporaryFilePath);
                    break;
                case '.png':
                    $image = imagecreatefrompng($temporaryFilePath);
                    break;
                case '.gif':
                    $image = imagecreatefromgif($temporaryFilePath);
                    break;
                default:
                    $image = null; // Suppress warn
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
                case '.jpg':
                    imagejpeg($thumbnail, $basePath . $thumbnailFilename);
                    break;
                case '.png':
                    imagepng($thumbnail, $basePath . $thumbnailFilename);
                    break;
                case '.gif':
                    imagegif($thumbnail, $basePath . $thumbnailFilename);
                    break;
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
        Repo::section()->edit($series, []);

        // Save the category associations.
        Repo::section()->removeFromCategory($this->getSeriesId());
        $categoryIds = $this->getData('categories');
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                Repo::section()->addToCategory($this->getSeriesId(), $categoryId);
            }
        }

        // The parent class depends on an existing seriesId/sectionId
        parent::execute(...$functionParams);

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
