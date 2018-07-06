<?php

/**
 * @file controllers/grid/settings/series/form/SeriesForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesForm
 * @ingroup controllers_grid_settings_series_form
 *
 * @brief Form for adding/edditing a series
 * stores/retrieves from an associative array
 */

import('lib.pkp.controllers.grid.settings.sections.form.PKPSectionForm');

class SeriesForm extends PKPSectionForm {
	/** @var $_pressId int */
	var $_pressId;

	/**
	 * Constructor.
	 * @param $request Request
	 * @param $seriesId int optional
	 */
	function __construct($request, $seriesId = null) {
		parent::__construct(
			$request,
			'controllers/grid/settings/series/form/seriesForm.tpl',
			$seriesId
		);

		$this->_pressId = $pressId = $request->getContext()->getId();

		// Validation checks for this form
		$form = $this;
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.setup.form.series.nameRequired'));
		$this->addCheck(new FormValidatorISSN($this, 'onlineIssn', 'optional', 'catalog.manage.series.issn.validation'));
		$this->addCheck(new FormValidatorISSN($this, 'printIssn', 'optional', 'catalog.manage.series.issn.validation'));
		$this->addCheck(new FormValidatorCustom($this, 'printIssn', 'optional', 'catalog.manage.series.issn.equalValidation', function($printIssn) use ($form) {
			return !($form->getData('onlineIssn') != '' && $form->getData('onlineIssn') == $printIssn);
		}));
		$this->addCheck(new FormValidatorRegExp($this, 'path', 'required', 'grid.series.pathAlphaNumeric', '/^[a-zA-Z0-9\/._-]+$/'));
		$this->addCheck(new FormValidatorCustom(
			$this, 'path', 'required', 'grid.series.pathExists',
			function($path) use ($form, $pressId) {
				$seriesDao = DAORegistry::getDAO('SeriesDAO');
				return !$seriesDao->getByPath($path,$pressId) || ($form->getData('oldPath') != null && $form->getData('oldPath') == $path);
		}));
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$request = Application::getRequest();
		$press = $request->getPress();

		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesId = $this->getSeriesId();
		if ($seriesId) {
			$series = $seriesDao->getById($seriesId, $press->getId());
		}

		$categories = $seriesDao->getCategories($seriesId, $press->getId());
		$categoryIds = array();
		while ($category = $categories->next()) {
			$categoryIds[] = $category->getId();
		}

		if (isset($series) ) {
			$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
			$sortOption = $series->getSortOption() ? $series->getSortOption() : $publishedMonographDao->getDefaultSortOption();
			$this->_data = array(
				'seriesId' => $seriesId,
				'title' => $series->getTitle(null, false),
				'featured' => $series->getFeatured(),
				'path' => $series->getPath(),
				'description' => $series->getDescription(null),
				'prefix' => $series->getPrefix(null),
				'subtitle' => $series->getSubtitle(null),
				'image' => $series->getImage(),
				'restricted' => $series->getEditorRestricted(),
				'onlineIssn' => $series->getOnlineISSN(),
				'printIssn' => $series->getPrintISSN(),
				'sortOption' => $sortOption,
				'subEditors' => $this->_getAssignedSubEditorIds($seriesId, $press->getId()),
				'categories' => $categoryIds,
			);
		}
	}

	/**
	 * @see Form::validate()
	 */
	function validate() {
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			import('lib.pkp.classes.file.TemporaryFileManager');
			$temporaryFileManager = new TemporaryFileManager();
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $this->_userId);
			if (	!$temporaryFile ||
					!($this->_imageExtension = $temporaryFileManager->getImageExtension($temporaryFile->getFileType())) ||
					!($this->_sizeArray = getimagesize($temporaryFile->getFilePath())) ||
					$this->_sizeArray[0] <= 0 || $this->_sizeArray[1] <= 0
			) {
				$this->addError('temporaryFileId', __('form.invalidImage'));
				return false;
			}
		}
		return parent::validate();
	}

	/**
	 * Fetch form contents
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('seriesId', $this->getSeriesId());

		$press = $request->getPress();

		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$categoryCount = $categoryDao->getCountByPressId($press->getId());
		$templateMgr->assign('categoryCount', $categoryCount);

		// Sort options.
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$templateMgr->assign('sortOptions', $publishedMonographDao->getSortSelectOptions());

		// Series Editors
		$seriesEditorsListData = $this->_getSubEditorsListPanelData($press->getId(), $request);
		$templateMgr->assign(array(
			'hasSubEditors' => !empty($seriesEditorsListData['items']),
			'subEditorsListData' => json_encode($seriesEditorsListData),
		));

		// Get SelectCategoryListHandler data
		import('controllers.list.SelectCategoryListHandler');
		$categoriesList = new SelectCategoryListHandler(array(
			'title' => 'grid.category.categories',
			'inputName' => 'categories[]',
			'selected' => $this->getData('categories'),
		));

		$categoriesListData = $categoriesList->getConfig();

		$templateMgr->assign(array(
			'hasCategories' => !empty($categoriesListData['items']),
			'categoriesListData' => json_encode($categoriesListData),
		));

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array('seriesId', 'path', 'featured', 'restricted', 'description', 'categories', 'prefix', 'subtitle', 'temporaryFileId', 'onlineIssn', 'printIssn', 'sortOption'));
		// For path duplicate checking; excuse the current path.
		if ($seriesId = $this->getSeriesId()) {
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$series = $seriesDao->getById($seriesId, $this->_pressId);
			$this->setData('oldPath', $series->getPath());
		}
	}

	/**
	 * Save series.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, $request) {
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
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
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');

			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $this->_userId);
			$temporaryFilePath = $temporaryFile->getFilePath();
			import('lib.pkp.classes.file.ContextFileManager');
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

			$series->setImage(array(
					'name' => $filename,
					'width' => $this->_sizeArray[0],
					'height' => $this->_sizeArray[1],
					'thumbnailName' => $thumbnailFilename,
					'thumbnailWidth' => $thumbnailWidth,
					'thumbnailHeight' => $thumbnailHeight,
					'uploadName' => $temporaryFile->getOriginalFileName(),
					'dateUploaded' => Core::getCurrentDate(),
			));

			// Clean up the temporary file
			import('lib.pkp.classes.file.TemporaryFileManager');
			$temporaryFileManager = new TemporaryFileManager();
			$temporaryFileManager->deleteById($temporaryFileId, $this->_userId);
		}

		// Update series object to store image information.
		$seriesDao->updateObject($series);

		// Save the series editor associations.
		$this->_saveSubEditors($press->getId());

		// Save the category associations.
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
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
	 * @return int
	 */
	function getSeriesId() {
		return $this->getSectionId();
	}

	/**
	 * Set the series ID for this series.
	 * @param $seriesId int
	 */
	function setSeriesId($seriesId) {
		$this->setSectionId($seriesId);
	}
}

?>
