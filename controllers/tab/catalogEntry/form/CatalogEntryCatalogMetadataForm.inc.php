<?php

/**
 * @file controllers/tab/catalogEntry/form/CatalogEntryCatalogMetadataForm.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
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

	/** @var $_publishedSubmission PublishedSubmission The published monograph associated with this monograph */
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
	 * @param $submissionId integer
	 * @param $userId integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function __construct($submissionId, $userId, $stageId = null, $formParams = null) {
		parent::__construct('controllers/tab/catalogEntry/form/catalogMetadataFormFields.tpl');
		$monographDao = DAORegistry::getDAO('SubmissionDAO');
		$this->_monograph = $monographDao->getById($submissionId);

		$this->_stageId = $stageId;
		$this->_formParams = $formParams;
		$this->_userId = $userId;

		$this->addCheck(new FormValidatorURL($this, 'licenseURL', 'optional', 'form.url.invalid'));
	}

	/**
	 * Fetch the HTML contents of the form.
	 * @param $request PKPRequest
	 * return string
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('submissionId', $this->getMonograph()->getId());
		$templateMgr->assign('stageId', $this->getStageId());
		$templateMgr->assign('formParams', $this->getFormParams());

		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');

		// get the lists associated with the select elements on this form
		$audienceCodes = $onixCodelistItemDao->getCodes('List28');
		$audienceRangeQualifiers = $onixCodelistItemDao->getCodes('List30');
		$audienceRanges = $onixCodelistItemDao->getCodes('List77');

		// assign these lists to the form for select options
		$templateMgr->assign('audienceCodes', $audienceCodes);
		$templateMgr->assign('audienceRangeQualifiers', $audienceRangeQualifiers);
		$templateMgr->assign('audienceRanges', $audienceRanges);

		$publishedSubmission = $this->getPublishedSubmission();
		if ($publishedSubmission) {

			// pre-select the existing values on the form.
			$templateMgr->assign('audience', $publishedSubmission->getAudience());
			$templateMgr->assign('audienceRangeQualifier', $publishedSubmission->getAudienceRangeQualifier());
			$templateMgr->assign('audienceRangeFrom', $publishedSubmission->getAudienceRangeFrom());
			$templateMgr->assign('audienceRangeTo', $publishedSubmission->getAudienceRangeTo());
			$templateMgr->assign('audienceRangeExact', $publishedSubmission->getAudienceRangeExact());
			$templateMgr->assign('coverImage', $publishedSubmission->getCoverImage());
		}

		return parent::fetch($request);
	}

	function initData() {
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_COMMON,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_APP_SUBMISSION
		);

		$submission = $this->getMonograph();
		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
		$this->_publishedSubmission = $publishedSubmissionDao->getById($submission->getId(), null, false);

		$copyrightHolder = $submission->getCopyrightHolder(null);
		$copyrightYear = $submission->getCopyrightYear();
		$licenseURL = $submission->getLicenseURL();

		$this->_data = array(
			'copyrightHolder' => $submission->getDefaultCopyrightHolder(null), // Localized
			'copyrightYear' => $submission->getDefaultCopyrightYear(),
			'licenseURL' => $submission->getDefaultLicenseURL(),
			'arePermissionsAttached' => !empty($copyrightHolder) || !empty($copyrightYear) || !empty($licenseURL),
			'confirm' => ($this->_publishedSubmission && $this->_publishedSubmission->getDatePublished())?true:false,
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
			'confirm',
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
	 * Save the metadata and store the catalog data for this published
	 * monograph.
	 */
	function execute($request) {
		parent::execute();

		$monograph = $this->getMonograph();
		$monographDao = DAORegistry::getDAO('SubmissionDAO');
		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
		$publishedSubmission = $publishedSubmissionDao->getById($monograph->getId(), null, false); /* @var $publishedSubmission PublishedSubmission */
		$isExistingEntry = $publishedSubmission?true:false;
		if (!$publishedSubmission) {
			$publishedSubmission = $publishedSubmissionDao->newDataObject();
			$publishedSubmission->setId($monograph->getId());
		}

		// Populate the published monograph with the cataloging metadata
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
				$simpleMonographFileManager->deleteFile($basePath . $oldSetting['thumbnailName']);
				$simpleMonographFileManager->deleteFile($basePath . $oldSetting['name']);
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
			$press = $request->getPress();
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
			$temporaryFileManager->deleteFile($temporaryFileId, $this->_userId);
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
		$monographDao->updateObject($monograph);

		// Update the modified fields or insert new.
		if ($isExistingEntry) {
			$publishedSubmissionDao->updateObject($publishedSubmission);
		} else {
			$publishedSubmissionDao->insertObject($publishedSubmission);
		}

		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormatFactory = $publicationFormatDao->getBySubmissionId($monograph->getId());
		$publicationFormats = $publicationFormatFactory->toAssociativeArray();
		$notificationMgr = new NotificationManager();
		if ($this->getData('confirm')) {
			// Update the monograph status.
			$monograph->setStatus(STATUS_PUBLISHED);
			$monographDao->updateObject($monograph);

			$publishedSubmission->setDatePublished(Core::getCurrentDate());
			$publishedSubmissionDao->updateObject($publishedSubmission);

			$notificationMgr->updateNotification(
				$request,
				array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
				null,
				ASSOC_TYPE_MONOGRAPH,
				$publishedSubmission->getId()
			);

			// Remove publication format tombstones.
			$publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats($publicationFormats);

			// Update the search index for this published monograph.
			import('classes.search.MonographSearchIndex');
			MonographSearchIndex::indexMonographMetadata($monograph);

			// Log the publication event.
			import('lib.pkp.classes.log.SubmissionLog');
			SubmissionLog::logEvent($request, $monograph, SUBMISSION_LOG_METADATA_PUBLISH, 'submission.event.metadataPublished');
		} else {
			if ($isExistingEntry) {
				// Update the monograph status.
				$monograph->setStatus(STATUS_QUEUED);
				$monographDao->updateObject($monograph);

				// Unpublish monograph.
				$publishedSubmission->setDatePublished(null);
				$publishedSubmissionDao->updateObject($publishedSubmission);

				$notificationMgr->updateNotification(
					$request,
					array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
					null,
					ASSOC_TYPE_MONOGRAPH,
					$publishedSubmission->getId()
				);

				// Create tombstones for each publication format.
				$publicationFormatTombstoneMgr->insertTombstonesByPublicationFormats($publicationFormats, $request->getContext());

				// Log the unpublication event.
				import('lib.pkp.classes.log.SubmissionLog');
				SubmissionLog::logEvent($request, $monograph, SUBMISSION_LOG_METADATA_UNPUBLISH, 'submission.event.metadataUnpublished');
			}
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

?>
