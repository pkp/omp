<?php

/**
 * @file controllers/tab/catalogEntry/CatalogEntryTabHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
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
	 * @return JSONMessage JSON object
	 */
	function catalogMetadata($args, $request) {
		import('controllers.tab.catalogEntry.form.CatalogEntryCatalogMetadataForm');

		$submission = $this->getSubmission();
		$stageId = $this->getStageId();
		$user = $request->getUser();

		$catalogEntryCatalogMetadataForm = new CatalogEntryCatalogMetadataForm($submission->getId(), $user->getId(), $stageId, array('displayedInContainer' => true));

		$catalogEntryCatalogMetadataForm->initData($args, $request);
		return new JSONMessage(true, $catalogEntryCatalogMetadataForm->fetch($request));
	}

	/**
	 * Show the publication metadata form.
	 * @param $request Request
	 * @param $args array
	 * @return JSONMessage JSON object
	 */
	function publicationMetadata($args, $request) {

		$representationId = (int) $request->getUserVar('representationId');
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');

		$submission = $this->getSubmission();
		$stageId = $this->getStageId();

		$publicationFormat = $publicationFormatDao->getById($representationId, $submission->getId());

		if (!$publicationFormat) {
			return new JSONMessage(false, __('monograph.publicationFormat.formatDoesNotExist'));
		}

		import('controllers.tab.catalogEntry.form.CatalogEntryFormatMetadataForm');
		$catalogEntryPublicationMetadataForm = new CatalogEntryFormatMetadataForm($submission->getId(), $representationId, $publicationFormat->getPhysicalFormat(), $publicationFormat->getRemoteURL(), $stageId, array('displayedInContainer' => true, 'tabPos' => $this->getTabPosition()));
		$catalogEntryPublicationMetadataForm->initData($args, $request);
		return new JSONMessage(true, $catalogEntryPublicationMetadataForm->fetch($request));
	}

	/**
	 * Upload a new cover image file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
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
			return $json;
		} else {
			return new JSONMessage(false, __('common.uploadFailed'));
		}
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
				$representationId = $request->getUserVar('representationId');

				// perform some validation to make sure this format is enabled and assigned to this monograph
				$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
				$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
				$formats = $publicationFormatDao->getBySubmissionId($submission->getId());
				$form = null;
				while ($format = $formats->next()) {
					if ($format->getId() == $representationId) {
						$form = new CatalogEntryFormatMetadataForm($submission->getId(), $representationId, $format->getPhysicalFormat(), $format->getRemoteURL(), $this->getStageId(), array('displayedInContainer' => true, 'tabPos' => $this->getTabPosition()));
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
