<?php

/**
 * @file controllers/tab/settings/appearance/form/AppearanceForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AppearanceForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form to edit appearance settings.
 */

import('lib.pkp.controllers.tab.settings.appearance.form.PKPAppearanceForm');

class AppearanceForm extends PKPAppearanceForm {
	/**
	 * Constructor.
	 */
	function __construct($wizardMode = false) {
		parent::__construct($wizardMode, array(
			'displayNewReleases' => 'bool',
			'displayFeaturedBooks' => 'bool',
			'displayInSpotlight' => 'bool',
			'coverThumbnailsMaxWidth' => 'int',
			'coverThumbnailsMaxHeight' => 'int',
			'coverThumbnailsResize' => 'bool',
			'catalogSortOption' => 'string',
		));

		$this->addCheck(new FormValidator($this, 'coverThumbnailsMaxWidth', 'required', 'manager.setup.coverThumbnailsMaxWidthRequired'));
		$this->addCheck(new FormValidator($this, 'coverThumbnailsMaxHeight', 'required', 'manager.setup.coverThumbnailsMaxHeightRequired'));

	}

	/**
	 * @copydoc PKPAppearanceForm::initData()
	 */
	function initData() {
		parent::initData();
		$request = Application::getRequest();
		$context = $request->getContext();
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$sortOption = $context->getSetting('catalogSortOption') ? $context->getSetting('catalogSortOption') : $publishedMonographDao->getDefaultSortOption();
		$this->setData('catalogSortOption', $sortOption);
	}

	/**
	 * @copydoc PKPAppearanceForm::fetch()
	 */
	function fetch($request, $template = null, $display = false, $params = null) {
		// Sort options.
		$templateMgr = TemplateManager::getManager($request);
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$templateMgr->assign('sortOptions', $publishedMonographDao->getSortSelectOptions());
		return parent::fetch($request, $template, $display, $params);
	}

	/**
	 * @copydoc ContextSettingsForm::execute()
	 */
	function execute() {
		parent::execute();

		$coverThumbnailsResize = $this->getData('coverThumbnailsResize');
		if ($coverThumbnailsResize) {
			$context = Application::getRequest()->getContext();
			// new thumbnails max width and max height
			$coverThumbnailsMaxWidth = $this->getData('coverThumbnailsMaxWidth');
			$coverThumbnailsMaxHeight = $this->getData('coverThumbnailsMaxHeight');

			// resize cover thumbainls for all press categories
			import('lib.pkp.classes.file.ContextFileManager');
			$pressFileManager = new ContextFileManager($context->getId());
			$categoryBasePath = $pressFileManager->getBasePath() . 'categories/';
			$categoryDao = DAORegistry::getDAO('CategoryDAO');
			$this->_resizeCoverThumbnails($context, $categoryDao, $coverThumbnailsMaxWidth, $coverThumbnailsMaxHeight, $categoryBasePath);

			// resize cover thumbainls for all press series
			$seriesBasePath = $pressFileManager->getBasePath() . 'series/';
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$this->_resizeCoverThumbnails($context, $seriesDao, $coverThumbnailsMaxWidth, $coverThumbnailsMaxHeight, $seriesBasePath);

			// resize cover thumbnails for all press published monographs
			$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
			$this->_resizeCoverThumbnails($context, $publishedMonographDao, $coverThumbnailsMaxWidth, $coverThumbnailsMaxHeight, '');
		}
	}

	/**
	 * Resize cover thumnails for all given press objects (categories, series and published monographs).
	 * @param $context Context
	 * @param $objectDao CategoriesDAO, SeriesDAO or PublishedMonographsDAO
	 * @param $coverThumbnailsMaxWidth int
	 * @param $coverThumbnailsMaxHeight int
	 * @param $basePath string Base path for the given object
	 */
	function _resizeCoverThumbnails($context, $objectDao, $coverThumbnailsMaxWidth, $coverThumbnailsMaxHeight, $basePath) {
		import('classes.file.SimpleMonographFileManager');
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();

		$objects = $objectDao->getByPressId($context->getId());
		while ($object = $objects->next()) {
			if (is_a($object, 'PublishedMonograph')) {
				$cover = $object->getCoverImage();
				$simpleMonographFileManager = new SimpleMonographFileManager($context->getId(), $object->getId());
				$basePath = $simpleMonographFileManager->getBasePath();
			} else {
				$cover = $object->getImage();
			}
			if ($cover) {
				// delete old cover thumbnail
				$fileManager->deleteByPath($basePath . $cover['thumbnailName']);

				// get settings necessary for the new thumbnail
				$coverExtension = $fileManager->getExtension($cover['name']);
				$xRatio = min(1, $coverThumbnailsMaxWidth / $cover['width']);
				$yRatio = min(1, $coverThumbnailsMaxHeight / $cover['height']);
				$ratio = min($xRatio, $yRatio);
				$thumbnailWidth = round($ratio * $cover['width']);
				$thumbnailHeight = round($ratio * $cover['height']);

				// create a thumbnail image of the defined size
				$thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

				// generate the image of the original cover
				switch ($coverExtension) {
					case 'jpg': $coverImage = imagecreatefromjpeg($basePath . $cover['name']); break;
					case 'png': $coverImage = imagecreatefrompng($basePath . $cover['name']); break;
					case 'gif': $coverImage = imagecreatefromgif($basePath . $cover['name']); break;
					default: $coverImage = null; // Suppress warn
				}
				assert($coverImage);

				// copy the cover image to the thumbnail
				imagecopyresampled($thumbnail, $coverImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $cover['width'], $cover['height']);

				// create the thumbnail file
				switch ($coverExtension) {
					case 'jpg': imagejpeg($thumbnail, $basePath . $cover['thumbnailName']); break;
					case 'png': imagepng($thumbnail, $basePath . $cover['thumbnailName']); break;
					case 'gif': imagegif($thumbnail, $basePath . $cover['thumbnailName']); break;
				}

				imagedestroy($thumbnail);
				if (is_a($object, 'PublishedMonograph')) {
					$object->setCoverImage(array(
						'name' => $cover['name'],
						'width' => $cover['width'],
						'height' => $cover['height'],
						'thumbnailName' => $cover['thumbnailName'],
						'thumbnailWidth' => $thumbnailWidth,
						'thumbnailHeight' => $thumbnailHeight,
						'uploadName' => $cover['uploadName'],
						'dateUploaded' => $cover['dateUploaded'],
					));
				} else {
					$object->setImage(array(
						'name' => $cover['name'],
						'width' => $cover['width'],
						'height' => $cover['height'],
						'thumbnailName' => $cover['thumbnailName'],
						'thumbnailWidth' => $thumbnailWidth,
						'thumbnailHeight' => $thumbnailHeight,
						'uploadName' => $cover['uploadName'],
						'dateUploaded' => $cover['dateUploaded'],
					));
				}
				// Update category object to store new thumbnail information.
				$objectDao->updateObject($object);
			}
			unset($object);
		}
	}

}

?>
