<?php

/**
 * @file controllers/tab/catalogEntry/form/CatalogEntryCatalogMetadataForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryCatalogMetadataForm
 * @ingroup controllers_tab_catalogEntry_form_CatalogEntryCatalogMetadataForm
 *
 * @brief Displays a submission's catalog metadata entry form.
 */

// Image tpye.
define('SUBMISSION_IMAGE_TYPE_THUMBNAIL', 1);

import('lib.pkp.classes.form.Form');

class CatalogEntryCatalogMetadataForm extends Form {

	/** @var $_monograph Monograph The monograph used to show metadata information */
	var $_monograph;

	/** @var $_publishedSubmission PublishedSubmission The published submission associated with this monograph */
	var $_publishedSubmission;

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
	function __construct($monographId, $userId, $stageId = null, $formParams = null) {
		parent::__construct('controllers/tab/catalogEntry/form/catalogMetadataFormFields.tpl');

		$submissionVersion = null;
		if (key_exists('submissionVersion', $formParams)) {
			$submissionVersion = $formParams['submissionVersion'];
		}

		$monographDao = DAORegistry::getDAO('MonographDAO');
		$this->_monograph = $monographDao->getById($monographId, null, false, $submissionVersion);

		$this->_stageId = $stageId;
		$this->_formParams = $formParams;
		$this->_userId = $userId;

		$this->addCheck(new FormValidatorURL($this, 'licenseURL', 'optional', 'form.url.invalid'));
	}

	/**
	 * @copydoc Form::fetch
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('submissionId', $this->getMonograph()->getId());
		$templateMgr->assign('submissionVersion', $this->getMonograph()->getSubmissionVersion());
		$templateMgr->assign('stageId', $this->getStageId());
		$templateMgr->assign('datePublished', $this->getMonograph()->getDatePublished());

		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');

		// get the lists associated with the select elements on this form
		$audienceCodes = $onixCodelistItemDao->getCodes('List28');
		$audienceRangeQualifiers = $onixCodelistItemDao->getCodes('List30');
		$audienceRanges = $onixCodelistItemDao->getCodes('List77');

		// assign these lists to the form for select options
		$templateMgr->assign('audienceCodes', $audienceCodes);
		$templateMgr->assign('audienceRangeQualifiers', $audienceRangeQualifiers);
		$templateMgr->assign('audienceRanges', $audienceRanges);

		// Workflow type
		$templateMgr->assign(array(
			'workTypeOptions' => array(
				WORK_TYPE_EDITED_VOLUME => __('submission.workflowType.editedVolume'),
				WORK_TYPE_AUTHORED_WORK => __('submission.workflowType.authoredWork'),
			),
			'workType' => $this->getMonograph()->getWorkType(),
		));

		// SelectListPanel for volume editors
		$authorDao = DAORegistry::getDAO('AuthorDAO');
		$authors = $authorDao->getBySubmissionId($this->getMonograph()->getId(), true, false, $this->getMonograph()->getSubmissionVersion());
		$volumeEditorsListItems = array();
		foreach ($authors as $author) {
			$volumeEditorsListItems[] = array(
				'id' => $author->getId(),
				'title' => $author->getFullName() . ', ' . $author->getLocalizedUserGroupName(),
			);
		}
		$volumeEditorsListData = array(
			'items' => $volumeEditorsListItems,
			'inputName' => 'volumeEditors[]',
			'selected' => $this->getData('volumeEditors') ? $this->getData('volumeEditors') : [],
			'i18n' => array(
				'title' => __('submission.workflowType.editedVolume.selectEditors'),
				'notice' => __('submission.workflowType.editedVolume.selectEditors.description'),
			),
		);
		$templateMgr->assign('volumeEditorsListData', $volumeEditorsListData);

		$submission = $this->getMonograph();
		$publishedSubmission = $this->getPublishedSubmission();
		if ($publishedSubmission) {
			if ($submission->getCurrentSubmissionVersion() != $submission->getSubmissionVersion()) {
				if (!isset($this->_formParams)) {
					$this->_formParams = array();
				}

				$this->_formParams["readOnly"] = true;
				$this->_formParams["hideSubmit"] = true;
			}

			// pre-select the existing values on the form.
			$templateMgr->assign('audience', $publishedSubmission->getAudience());
			$templateMgr->assign('audienceRangeQualifier', $publishedSubmission->getAudienceRangeQualifier());
			$templateMgr->assign('audienceRangeFrom', $publishedSubmission->getAudienceRangeFrom());
			$templateMgr->assign('audienceRangeTo', $publishedSubmission->getAudienceRangeTo());
			$templateMgr->assign('audienceRangeExact', $publishedSubmission->getAudienceRangeExact());
			$templateMgr->assign('coverImage', $publishedSubmission->getCoverImage());
		}

		$templateMgr->assign('formParams', $this->getFormParams());

		return parent::fetch($request, $template, $display);
	}

	function initData() {
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_COMMON,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_APP_SUBMISSION
		);

		$submission = $this->getMonograph();
		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
		$this->_publishedSubmission = $publishedSubmissionDao->getBySubmissionId($submission->getId(), null, false, $submission->getSubmissionVersion());

		$copyrightHolder = $submission->getCopyrightHolder(null);
		$copyrightYear = $submission->getCopyrightYear();
		$licenseURL = $submission->getLicenseURL();

		$authorDao = DAORegistry::getDAO('AuthorDAO');
		$authors = $authorDao->getBySubmissionId($submission->getId(), true, false, $submission->getSubmissionVersion());
		$volumeEditors = [];
		foreach ($authors as $author) {
			if ($author->getIsVolumeEditor()) {
				$volumeEditors[] = $author->getId();
			}
		}

		$this->_data = array(
			'copyrightHolder' => $submission->getDefaultCopyrightHolder(null), // Localized
			'copyrightYear' => $submission->getDefaultCopyrightYear(),
			'licenseURL' => $submission->getDefaultLicenseURL(),
			'arePermissionsAttached' => !empty($copyrightHolder) || !empty($copyrightYear) || !empty($licenseURL),
			'confirm' => ($this->_publishedSubmission && $this->_publishedSubmission->getDatePublished())?true:false,
			'volumeEditors' => $volumeEditors,
		);
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
	 * Get the PublishedSubmission
	 * @return PublishedSubmission
	 */
	function getPublishedSubmission() {
		return $this->_publishedSubmission;
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
			'copyrightYear', 'copyrightHolder', 'licenseURL', 'attachPermissions',
			'temporaryFileId', // Cover image
			'confirm', 'datePublished',
			'workType', 'volumeEditors',
		);

		$this->readUserVars($vars);
	}

	/**
	 * Validate the form.
	 * @return boolean
	 */
	function validate($callHooks = true) {
		// If a cover image was uploaded, make sure it's valid
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
		return parent::validate($callHooks);
	}

	/**
	 * Save the metadata and store the catalog data for this published
	 * monograph.
	 */
	function execute() {
		parent::execute();

		$monograph = $this->getMonograph();
		$monographDao = DAORegistry::getDAO('MonographDAO');
		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO'); /** @var $publishedSubmissionDao PublishedSubmissionDAO */
		$publishedSubmission = $publishedSubmissionDao->getBySubmissionId($monograph->getId(), null, false, $monograph->getSubmissionVersion()); /** @var $publishedSubmission PublishedSubmission */
		$previousPublishedSubmission = $publishedSubmissionDao->getBySubmissionId($monograph->getId(), null, false, $monograph->getSubmissionVersion() - 1);
		$isExistingEntry = $publishedSubmission?true:false;
		if (!$publishedSubmission) {
			$publishedSubmission = $publishedSubmissionDao->newDataObject();
			$publishedSubmission->setId($monograph->getId());
			$publishedSubmission->setSubmissionVersion($monograph->getSubmissionVersion());
			$publishedSubmission->setIsCurrentSubmissionVersion(true);
		}

		$monograph->setDatePublished($this->getData('datePublished'));

		if ($this->getData('workType') == WORK_TYPE_EDITED_VOLUME) {
			$volumeEditors = $this->getData('volumeEditors') ? $this->getData('volumeEditors') : [];
			$authorDao = DAORegistry::getDAO('AuthorDAO');
			$authors = $authorDao->getBySubmissionId($monograph->getId(), true);
			foreach ($authors as $author) {
				$author->setIsVolumeEditor(in_array($author->getId(), $volumeEditors));
				$authorDao->updateObject($author);
			}
		}

		// Populate the published submission with the cataloging metadata
		$publishedSubmission->setAudience($this->getData('audience'));
		$publishedSubmission->setAudienceRangeQualifier($this->getData('audienceRangeQualifier'));
		$publishedSubmission->setAudienceRangeFrom($this->getData('audienceRangeFrom'));
		$publishedSubmission->setAudienceRangeTo($this->getData('audienceRangeTo'));
		$publishedSubmission->setAudienceRangeExact($this->getData('audienceRangeExact'));

		// If a cover image was uploaded, deal with it.
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			// Fetch the temporary file storing the uploaded library file
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $this->_userId);
			$temporaryFilePath = $temporaryFile->getFilePath();
			import('classes.file.SimpleMonographFileManager');
			$simpleMonographFileManager = new SimpleMonographFileManager($monograph->getPressId(), $publishedSubmission->getId());
			$basePath = $simpleMonographFileManager->getBasePath();

			// Delete the old file if it exists
			$oldSetting = $publishedSubmission->getCoverImage();
			if ($oldSetting) {
				$simpleMonographFileManager->deleteByPath($basePath . $oldSetting['thumbnailName']);
				$simpleMonographFileManager->deleteByPath($basePath . $oldSetting['name']);
			}

			// The following variables were fetched in validation
			assert($this->_sizeArray && $this->_imageExtension);

			// Load the cover image for surrogate production
			$cover = null; // Scrutinizer
			switch ($this->_imageExtension) {
				case '.jpg': $cover = imagecreatefromjpeg($temporaryFilePath); break;
				case '.png': $cover = imagecreatefrompng($temporaryFilePath); break;
				case '.gif': $cover = imagecreatefromgif($temporaryFilePath); break;
			}
			assert(isset($cover));

			// Copy the new file over (involves creating the appropriate subdirectory too)
			$filename = 'cover' . $this->_imageExtension;
			$simpleMonographFileManager->copyFile($temporaryFile->getFilePath(), $basePath . $filename);

			// Generate thumbnail image
			$press = Application::get()->getRequest()->getPress();
			$coverThumbnailsMaxWidth = $press->getSetting('coverThumbnailsMaxWidth');
			$coverThumbnailsMaxHeight = $press->getSetting('coverThumbnailsMaxHeight');

			$thumbnailImageInfo = $this->_buildSurrogateImage($cover, $basePath, SUBMISSION_IMAGE_TYPE_THUMBNAIL, $coverThumbnailsMaxWidth, $coverThumbnailsMaxHeight);

			// Clean up
			imagedestroy($cover);

			$publishedSubmission->setCoverImage(array(
				'name' => $filename,
				'width' => $this->_sizeArray[0],
				'height' => $this->_sizeArray[1],
				'thumbnailName' => $thumbnailImageInfo['filename'],
				'thumbnailWidth' => $thumbnailImageInfo['width'],
				'thumbnailHeight' => $thumbnailImageInfo['height'],
				'uploadName' => $temporaryFile->getOriginalFileName(),
				'dateUploaded' => Core::getCurrentDate(),
			));

			// Clean up the temporary file
			import('lib.pkp.classes.file.TemporaryFileManager');
			$temporaryFileManager = new TemporaryFileManager();
			$temporaryFileManager->deleteById($temporaryFileId, $this->_userId);
		}

		if ($this->getData('attachPermissions')) {
			$monograph->setCopyrightYear($this->getData('copyrightYear'));
			$monograph->setCopyrightHolder($this->getData('copyrightHolder'), null); // Localized
			$monograph->setLicenseURL($this->getData('licenseURL'));
		} else {
			$monograph->setCopyrightYear(null);
			$monograph->setCopyrightHolder(null, null);
			$monograph->setLicenseURL(null);
		}
		$monograph->setWorkType($this->getData('workType'));

		$monographDao->updateObject($monograph);

		// Update the modified fields or insert new.
		if ($isExistingEntry) {
			$publishedSubmissionDao->updateObject($publishedSubmission);
		} else {
			if ($previousPublishedSubmission) {
				$previousPublishedSubmission->setIsCurrentSubmissionVersion(false);

				$publishedSubmissionDao->updateObject($previousPublishedSubmission);
			}

			$publishedSubmissionDao->insertObject($publishedSubmission);
		}

		import('classes.core.Services');
		$submissionService = Services::get('submission');
		if ($this->getData('confirm')) {
			$submissionService->addToCatalog($monograph);
		} elseif ($isExistingEntry) {
			$submissionService->removeFromCatalog($monograph);
		}
	}

	/**
	 * Generates a surrogate image used as a thumbnail.
	 * @param resource $cover the cover image uploaded.
	 * @param string $basePath base file path.
	 * @param int $type the type of image to create.
	 * @return array the details for the image (dimensions, file name, etc).
	 */
	function _buildSurrogateImage($cover, $basePath, $type, $coverThumbnailsMaxWidth, $coverThumbnailsMaxHeight) {
		// Calculate the scaling ratio for each dimension.
		$maxWidth = 0;
		$maxHeight = 0;
		$surrogateFilename = null;

		switch ($type) {
			case SUBMISSION_IMAGE_TYPE_THUMBNAIL:
				$maxWidth = $coverThumbnailsMaxWidth;
				$maxHeight = $coverThumbnailsMaxHeight;
				$surrogateFilename = 'thumbnail' . $this->_imageExtension;
				break;
		}

		$xRatio = min(1, $maxWidth / $this->_sizeArray[0]);
		$yRatio = min(1, $maxHeight / $this->_sizeArray[1]);

		// Choose the smallest ratio and create the target.
		$ratio = min($xRatio, $yRatio);

		$surrogateWidth = round($ratio * $this->_sizeArray[0]);
		$surrogateHeight = round($ratio * $this->_sizeArray[1]);
		$surrogate = imagecreatetruecolor($surrogateWidth, $surrogateHeight);
		imagecopyresampled($surrogate, $cover, 0, 0, 0, 0, $surrogateWidth, $surrogateHeight, $this->_sizeArray[0], $this->_sizeArray[1]);

		switch ($this->_imageExtension) {
			case '.jpg': imagejpeg($surrogate, $basePath . $surrogateFilename); break;
			case '.png': imagepng($surrogate, $basePath . $surrogateFilename); break;
			case '.gif': imagegif($surrogate, $basePath . $surrogateFilename); break;
		}
		imagedestroy($surrogate);
		return array('filename' => $surrogateFilename, 'width' => $surrogateWidth, 'height' => $surrogateHeight);
	}
}
