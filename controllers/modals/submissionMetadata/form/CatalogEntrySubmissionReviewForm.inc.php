<?php

/**
 * @file controllers/modals/submissionMetadata/form/CatalogEntrySubmissionReviewForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntrySubmissionReviewForm
 * @ingroup controllers_modals_submissionMetadata_form_CatalogEntrySubmissionReviewForm
 *
 * @brief Displays a submission's metadata view.
 */

import('lib.pkp.classes.form.Form');

// Use this class to handle the submission metadata.
import('controllers.modals.submissionMetadata.form.SubmissionMetadataViewForm');

class CatalogEntrySubmissionReviewForm extends SubmissionMetadataViewForm {

	/**
	 * Constructor.
	 * @param $monographId integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function CatalogEntrySubmissionReviewForm($monographId, $stageId = null, $formParams = null) {
		parent::SubmissionMetadataViewForm($monographId, $stageId, $formParams, 'controllers/modals/submissionMetadata/form/catalogEntrySubmissionReviewForm.tpl');
		AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_SUBMISSION);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();

		// Read in the additional confirmation checkbox
		$this->readUserVars(array('confirm'));
	}

	/**
	 * Save the metadata and create a catalog entry.
	 */
	function execute(&$request) {
		parent::execute($request);

		$monograph =& $this->getMonograph();
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getById($monograph->getId(), null, false);
		$isExistingEntry = $publishedMonograph?true:false;

		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
		$press =& $request->getPress();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormatFactory =& $publicationFormatDao->getByMonographId($monograph->getId());
		$publicationFormats =& $publicationFormatFactory->toAssociativeArray();
		$notificationMgr = new NotificationManager();

		if ($this->getData('confirm')) {
			// Update the monograph status.
			$monograph->setStatus(STATUS_PUBLISHED);
			$monographDao->updateMonograph($monograph);

			if (!$isExistingEntry) {
				unset($publishedMonograph);
				$publishedMonograph = $publishedMonographDao->newDataObject();
				$publishedMonograph->setId($monograph->getId());
				$publishedMonographDao->insertObject($publishedMonograph);
			}
			$publishedMonograph->setDatePublished(Core::getCurrentDate());
			$publishedMonographDao->updateObject($publishedMonograph);

			// Remove "need to approve submission" notifications.
			$notificationMgr->updateApproveSubmissionNotificationTypes($request, $publishedMonograph);

			// Remove publication format tombstones.
			$publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats($publicationFormats);

			// Update the search index for this published monograph.
			import('classes.search.MonographSearchIndex');
			MonographSearchIndex::indexMonographMetadata($monograph);

			// Log the publication event.
			import('classes.log.MonographLog');
			MonographLog::logEvent($request, $monograph, MONOGRAPH_LOG_METADATA_PUBLISH, 'submission.event.metadataPublished');
		} else {
			if ($isExistingEntry) {
				// Update the monograph status.
				$monograph->setStatus(STATUS_QUEUED);

				// Unpublish monograph.
				$publishedMonograph->setDatePublished(null);
				$publishedMonographDao->updateObject($publishedMonograph);

				// Create "need to approve submission" notification.
				$notificationMgr->updateApproveSubmissionNotificationTypes($request, $publishedMonograph);

				// Create tombstones for each publication format.
				$publicationFormatTombstoneMgr->insertTombstonesByPublicationFormats($publicationFormats, $press);

				// Log the unpublication event.
				import('classes.log.MonographLog');
				MonographLog::logEvent($request, $monograph, MONOGRAPH_LOG_METADATA_UNPUBLISH, 'submission.event.metadataUnpublished');
			}

			// regular submission without publish in catalog.
			$monographDao->updateMonograph($monograph);
		}
	}
}

?>
