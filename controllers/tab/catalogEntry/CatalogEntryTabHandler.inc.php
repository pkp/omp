<?php

/**
 * @file controllers/tab/catalogEntry/CatalogEntryTabHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryTabHandler
 * @ingroup controllers_tab_catalogEntry
 *
 * @brief Handle AJAX operations for tabs on the New Catalog Entry management page.
 */

// Import the base Handler.
import('lib.pkp.controllers.tab.publicationEntry.PublicationEntryTabHandler');

class CatalogEntryTabHandler extends PublicationEntryTabHandler {
	/**
	 * Constructor
	 */
	function CatalogEntryTabHandler() {
		parent::PublicationEntryTabHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
			array(
				'catalogMetadata',
				'publicationMetadata',
				'uploadCoverImage',
			)
		);
	}


	//
	// Public handler methods
	//

	/**
	 * Show the catalog metadata form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function catalogMetadata($args, $request) {
		import('controllers.tab.catalogEntry.form.CatalogEntryCatalogMetadataForm');

		$submission = $this->getSubmission();
		$stageId = $this->getStageId();
		$user = $request->getUser();

		$catalogEntryCatalogMetadataForm = new CatalogEntryCatalogMetadataForm($submission->getId(), $user->getId(), $stageId, array('displayedInContainer' => true));

		$catalogEntryCatalogMetadataForm->initData($args, $request);
		$json = new JSONMessage(true, $catalogEntryCatalogMetadataForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Show the publication metadata form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function publicationMetadata($args, $request) {

		$publicationFormatId = (int) $request->getUserVar('publicationFormatId');
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');

		$submission = $this->getSubmission();
		$stageId = $this->getStageId();

		$publicationFormat = $publicationFormatDao->getById($publicationFormatId, $submission->getId());

		if (!$publicationFormat) {
			$json = new JSONMessage(false, __('monograph.publicationFormat.formatDoesNotExist'));
			return $json->getString();
		}

		import('controllers.tab.catalogEntry.form.CatalogEntryFormatMetadataForm');
		$catalogEntryPublicationMetadataForm = new CatalogEntryFormatMetadataForm($submission->getId(), $publicationFormatId, $publicationFormat->getPhysicalFormat(), $stageId, array('displayedInContainer' => true, 'tabPos' => $this->getTabPosition()));
		$catalogEntryPublicationMetadataForm->initData($args, $request);
		$json = new JSONMessage(true, $catalogEntryPublicationMetadataForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a new cover image file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadCoverImage($args, $request) {
		$user = $request->getUser();

		import('lib.pkp.classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
		if ($temporaryFile) {
			$json = new JSONMessage(true);
			$json->setAdditionalAttributes(array(
				'temporaryFileId' => $temporaryFile->getId()
			));
		} else {
			$json = new JSONMessage(false, __('common.uploadFailed'));
		}

		return $json->getString();
	}

	/**
	 * Get the form for a particular tab.
	 */
	function _getFormFromCurrentTab(&$form, &$notificationKey, $request) {
		parent::_getFormFromCurrentTab($form, $notificationKey, $request); // give PKP-lib a chance to set the form and key.

		if (!$form) { // nothing applicable in parent.
			$submission = $this->getSubmission();
			switch ($this->getCurrentTab()) {
				case 'catalog':
					import('controllers.tab.catalogEntry.form.CatalogEntryCatalogMetadataForm');
					$user = $request->getUser();
					$form = new CatalogEntryCatalogMetadataForm($submission->getId(), $user->getId(), $this->getStageId(), array('displayedInContainer' => true, 'tabPos' => $this->getTabPosition()));
					$notificationKey = 'notification.savedCatalogMetadata';
					SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_CATALOG_METADATA_UPDATE, 'submission.event.catalogMetadataUpdated');
					break;
				default: // publication format tabs
					import('controllers.tab.catalogEntry.form.CatalogEntryFormatMetadataForm');
				$publicationFormatId = $request->getUserVar('publicationFormatId');

				// perform some validation to make sure this format is enabled and assigned to this monograph
				$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
				$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
				$formats = $publicationFormatDao->getBySubmissionId($submission->getId());
				$form = null;
				while ($format = $formats->next()) {
					if ($format->getId() == $publicationFormatId) {
						$form = new CatalogEntryFormatMetadataForm($submission->getId(), $publicationFormatId, $format->getId(), $this->getStageId(), array('displayedInContainer' => true, 'tabPos' => $this->getTabPosition()));
						$notificationKey = 'notification.savedPublicationFormatMetadata';
						SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_PUBLICATION_FORMAT_METADATA_UPDATE, 'submission.event.publicationMetadataUpdated', array('formatName' => $format->getLocalizedName()));
						break;
					}
				}
				break;
			}
		}
	}

	/**
	 * Returns an instance of the form used for reviewing a submission's 'submission' metadata.
	 * @see PublicationEntryTabHandler::_getPublicationEntrySubmissionReviewForm()
	 * @return PKPForm
	 */
	function _getPublicationEntrySubmissionReviewForm() {
		$submission = $this->getSubmission();
		import('controllers.modals.submissionMetadata.form.CatalogEntrySubmissionReviewForm');
		return new CatalogEntrySubmissionReviewForm($submission->getId(), $this->getStageId(), array('displayedInContainer' => true));
	}

	/**
	 * return a string to the Handler for this modal.
	 * @return String
	 */
	function _getHandlerClassPath() {
		return 'modals.submissionMetadata.CatalogEntryHandler';
	}
}

?>
