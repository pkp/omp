<?php

/**
 * @file controllers/tab/catalogEntry/form/CatalogEntryCatalogMetadataForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryCatalogMetadataForm
 * @ingroup controllers_tab_catalogEntry_form_CatalogEntryCatalogMetadataForm
 *
 * @brief Displays a submission's catalog metadata entry form.
 */

// Thumbnails will be scaled down to fall within these dimensions, preserving
// aspect ratio, and not scaling up beyond the present resolution.
define('THUMBNAIL_MAX_WIDTH', 110);
define('THUMBNAIL_MAX_HEIGHT', 110);

import('lib.pkp.classes.form.Form');

class CatalogEntryCatalogMetadataForm extends Form {

	/** @var $_monograph Monograph The monograph used to show metadata information */
	var $_monograph;

	/** @var $_publishedMonograph PublishedMonograph The published monograph associated with this monograph */
	var $_publishedMonograph;

	/** @var $_stageId int The current stage id */
	var $_stageId;

	/** @var $_userId int The current user ID */
	var $_userId;

	/** @var $_imageExtension string Cover image extension */
	var $_imageExtension;

	/** @var $_sizeArray array Cover image information from getimagesize */
	var $_sizeArray;

	/**
	 * Parameters to configure the form template.
	 */
	var $_formParams;

	/**
	 * Constructor.
	 * @param $monographId integer
	 * @param $userId integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function CatalogEntryCatalogMetadataForm($monographId, $userId, $stageId = null, $formParams = null) {
		parent::Form('catalog/form/catalogMetadataFormFields.tpl');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getById((int) $monographId);
		if ($monograph) {
			$this->_monograph = $monograph;
		}

		$this->_stageId = $stageId;
		$this->_formParams = $formParams;
		$this->_userId = $userId;
	}

	/**
	 * Fetch the HTML contents of the form.
	 * @param $request PKPRequest
	 * return string
	 */
	function fetch(&$request) {
		$monograph =& $this->getMonograph();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->getMonograph()->getId());
		$templateMgr->assign('stageId', $this->getStageId());
		$templateMgr->assign('formParams', $this->getFormParams());

		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');

		// get the lists associated with the select elements on this form
		$audienceCodes =& $onixCodelistItemDao->getCodes('List28');
		$audienceRangeQualifiers =& $onixCodelistItemDao->getCodes('List30');
		$audienceRanges =& $onixCodelistItemDao->getCodes('List77');

		// assign these lists to the form for select options
		$templateMgr->assign('audienceCodes', $audienceCodes);
		$templateMgr->assign('audienceRangeQualifiers', $audienceRangeQualifiers);
		$templateMgr->assign('audienceRanges', $audienceRanges);

		$publishedMonograph =& $this->getPublishedMonograph();
		if ($publishedMonograph) {
			// pre-select the existing values on the form.
			$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
			foreach ($publishedMonographDao->getAdditionalFieldNames() as $fieldName) {
				$templateMgr->assign($fieldName, $publishedMonograph->getData($fieldName));
			}
		}

		return parent::fetch($request);
	}

	function initData() {
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APPLICATION_COMMON,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_OMP_SUBMISSION
		);

		$monograph =& $this->getMonograph();
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$this->_publishedMonograph =& $publishedMonographDao->getById($monograph->getId());
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the PublishedMonograph
	 * @return PublishedMonograph
	 */
	function getPublishedMonograph() {
		return $this->_publishedMonograph;
	}

	/**
	 * Get the stage id
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the extra form parameters.
	 */
	function getFormParams() {
		return $this->_formParams;
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$vars = array(
			'audience', 'audienceRangeQualifier', 'audienceRangeFrom', 'audienceRangeTo', 'audienceRangeExact',
			'temporaryFileId', // Cover image
		);

		$this->readUserVars($vars);
	}

	/**
	 * Validate the form.
	 * @return boolean
	 */
	function validate() {
		// If a cover image was uploaded, make sure it's valid
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			import('classes.file.TemporaryFileManager');
			$temporaryFileManager = new TemporaryFileManager();
			$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile =& $temporaryFileDao->getTemporaryFile($temporaryFileId, $this->_userId);
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
	 * Save the metadata and store the catalog data for this published
	 * monograph.
	 */
	function execute() {
		parent::execute();

		$monograph =& $this->getMonograph();
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getById($monograph->getId());
		if (!$publishedMonograph) {
			fatalError('Updating catalog metadata with no published monograph!');
		}

		// Populate the published monograph with the cataloging metadata
		foreach ($publishedMonographDao->getAdditionalFieldNames() as $fieldName) {
			$publishedMonograph->setData($fieldName, $this->getData($fieldName));
		}

		// If a cover image was uploaded, deal with it.
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			// Fetch the temporary file storing the uploaded library file
			$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile =& $temporaryFileDao->getTemporaryFile($temporaryFileId, $this->_userId);
			$temporaryFilePath = $temporaryFile->getFilePath();
			import('classes.file.SimpleMonographFileManager');
			$simpleMonographFileManager = new SimpleMonographFileManager($monograph->getPressId(), $publishedMonograph->getId());
			$basePath = $simpleMonographFileManager->getBasePath();

			// Delete the old file if it exists
			$oldSetting = $publishedMonograph->getCoverImage();
			if ($oldSetting) {
				$simpleMonographFileManager->deleteFile($basePath . $oldSetting['thumbnailName']);
				$simpleMonographFileManager->deleteFile($basePath . $oldSetting['name']);
			}

			// The following variables were fetched in validation
			assert($this->_sizeArray && $this->_imageExtension);

			// Generate the thumbnail
			switch ($this->_imageExtension) {
				case '.jpg': $cover = imagecreatefromjpeg($temporaryFilePath); break;
				case '.png': $cover = imagecreatefrompng($temporaryFilePath); break;
				case '.gif': $cover = imagecreatefromgif($temporaryFilePath); break;
			}
			assert($cover);
			// Calculate the scaling ratio for each dimension.
			$xRatio = min(1, THUMBNAIL_MAX_WIDTH / $this->_sizeArray[0]);
			$yRatio = min(1, THUMBNAIL_MAX_HEIGHT / $this->_sizeArray[1]);
			// Choose the smallest ratio and create the target.
			$ratio = min($xRatio, $yRatio);
			$thumbnailWidth = $ratio * $this->_sizeArray[0];
			$thumbnailHeight = $ratio * $this->_sizeArray[1];
			$thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
			imagecopyresized($thumbnail, $cover, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $this->_sizeArray[0], $this->_sizeArray[1]);
			imagedestroy($cover);
			$thumbnailFilename = 'thumbnail' . $this->_imageExtension;
			switch ($this->_imageExtension) {
				case '.jpg': imagejpeg($thumbnail, $basePath . $thumbnailFilename); break;
				case '.jpg': imagepng($thumbnail, $basePath . $thumbnailFilename); break;
				case '.jpg': imagegif($thumbnail, $basePath . $thumbnailFilename); break;
			}
			imagedestroy($thumbnail);

			// Copy the new file over
			$filename = 'cover' . $this->_imageExtension;
			$simpleMonographFileManager->copyFile($temporaryFile->getFilePath(), $basePath . $filename);

			$publishedMonograph->setCoverImage(array(
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
			import('classes.file.TemporaryFileManager');
			$temporaryFileManager = new TemporaryFileManager();
			$temporaryFileManager->deleteFile($temporaryFileId, $this->_userId);
		}

		// Update the modified fields
		$publishedMonographDao->updateLocaleFields($publishedMonograph);
	}
}

?>
