<?php

/**
 * @file controllers/grid/settings/series/form/SeriesForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	/**
	 * Constructor.
	 * @param $request Request
	 * @param $seriesId int optional
	 */
	function SeriesForm($request, $seriesId = null) {
		parent::PKPSectionForm(
			$request,
			'controllers/grid/settings/series/form/seriesForm.tpl',
			$seriesId
		);

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.setup.form.series.nameRequired'));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, $request) {
		$press = $request->getPress();

		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesId = $this->getSeriesId();
		if ($seriesId) {
			$series = $seriesDao->getById($seriesId, $press->getId());
		}

		if (isset($series) ) {
			$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
			$sortOption = $series->getSortOption() ? $series->getSortOption() : $publishedMonographDao->getDefaultSortOption();
			$this->_data = array(
				'seriesId' => $seriesId,
				'title' => $series->getTitle(null),
				'featured' => $series->getFeatured(),
				'path' => $series->getPath(),
				'description' => $series->getDescription(null),
				'prefix' => $series->getPrefix(null),
				'subtitle' => $series->getSubtitle(null),
				'image' => $series->getImage(),
				'restricted' => $series->getEditorRestricted(),
				'sortOption' => $sortOption,
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
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$seriesEditorCount = $userGroupDao->getContextUsersCount($press->getId(), null, ROLE_ID_SUB_EDITOR);
		$templateMgr->assign('seriesEditorCount', $seriesEditorCount);

		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$categoryCount = $categoryDao->getCountByPressId($press->getId());
		$templateMgr->assign('categoryCount', $categoryCount);

		// Sort options.
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$templateMgr->assign('sortOptions', $publishedMonographDao->getSortSelectOptions());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array('seriesId', 'path', 'featured', 'restricted', 'description', 'categories', 'prefix', 'subtitle', 'temporaryFileId', 'sortOption'));
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
				$pressFileManager->deleteFile($basePath . $oldSetting['thumbnailName']);
				$pressFileManager->deleteFile($basePath . $oldSetting['name']);
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
			$temporaryFileManager->deleteFile($temporaryFileId, $this->_userId);
		}

		// Update series object to store image information.
		$seriesDao->updateObject($series);

		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		// Save the series editor associations.
		ListbuilderHandler::unpack(
			$request,
			$this->getData('subEditors'),
			array(&$this, 'deleteSubEditorEntry'),
			array(&$this, 'insertSubEditorEntry'),
			array(&$this, 'updateSubEditorEntry')
		);

		// Save the category associations.
		ListbuilderHandler::unpack(
			$request,
			$this->getData('categories'),
			array(&$this, 'deleteCategoryEntry'),
			array(&$this, 'insertCategoryEntry'),
			array(&$this, 'updateCategoryEntry')
		);

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

	/**
	 * Persist a category association
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertCategoryEntry($request, $newRowId) {
		$categoryId = array_shift($newRowId);

		$seriesDao = DAORegistry::getDAO('SeriesDAO');

		// Make sure the membership doesn't already exist
		if ($seriesDao->categoryAssociationExists($this->getSeriesId(), $categoryId)) {
			return false;
		}

		// Otherwise, insert the row.
		$seriesDao->addCategory($this->getSeriesId(), $categoryId);
		return true;
	}

	/**
	 * Delete a category association with this series.
	 * @see ListbuilderHandler::deleteEntry
	 * @param $request PKPRequest
	 * @param $rowId int
	 */
	function deleteCategoryEntry($request, $rowId) {
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesDao->removeCategory($this->getSeriesId(), $rowId);
		return true;
	}

	/**
	 * Update a category association with this series.
	 * @see ListbuilderHandler::updateEntry
	 * @param $request PKPRequest
	 * @param $rowId int old category
	 * @param $newRowId array new category
	 */
	function updateCategoryEntry($request, $rowId, $newRowId) {
		$this->deleteCategoryEntry($request, $rowId);
		$this->insertCategoryEntry($request, $newRowId);
		return true;
	}
}

?>
