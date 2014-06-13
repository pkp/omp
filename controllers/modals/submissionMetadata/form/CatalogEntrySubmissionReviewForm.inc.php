<?php

/**
 * @file controllers/modals/submissionMetadata/form/CatalogEntrySubmissionReviewForm.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
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
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_APP_SUBMISSION);
		if (array_key_exists('expeditedSubmission', $formParams)) {
			// If we are expediting, make the confirmation checkbox mandatory.
			$request = Application::getRequest();
			$this->addCheck(new FormValidator($this, 'confirm', 'required', 'submission.catalogEntry.confirm.required'));
			$this->addCheck(new FormValidatorRegExp($this, 'price', 'optional', 'grid.catalogEntry.validPriceRequired', '/^(([1-9]\d{0,2}(,\d{3})*|[1-9]\d*|0|)(.\d{2})?|([1-9]\d{0,2}(,\d{3})*|[1-9]\d*|0|)(.\d{2})?)$/'));
		}
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
	 * @see SubmissionMetadataViewForm::fetch()
	 */
	function fetch($request) {

		$templateMgr = TemplateManager::getManager($request);

		// Make this available for expedited submissions.
		$salesTypes = array(
			'openAccess' => 'payment.directSales.openAccess',
			'directSales' => 'payment.directSales.directSales',
			'notAvailable' => 'payment.directSales.notAvailable',
		);

		$templateMgr->assign('salesTypes', $salesTypes);
		return parent::fetch($request);
	}

	/**
	 * Save the metadata and create a catalog entry.
	 */
	function execute($request) {
		parent::execute($request);

		$monograph = $this->getSubmission();
		$monographDao = DAORegistry::getDAO('MonographDAO');
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($monograph->getId(), null, false);
		$isExistingEntry = $publishedMonograph?true:false;

		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
		$press = $request->getPress();
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormatFactory = $publicationFormatDao->getBySubmissionId($monograph->getId());
		$publicationFormats = $publicationFormatFactory->toAssociativeArray();
		$notificationMgr = new NotificationManager();

		if ($this->getData('confirm')) {
			// Update the monograph status.
			$monograph->setStatus(STATUS_PUBLISHED);
			$monographDao->updateObject($monograph);

			if (!$isExistingEntry) {
				unset($publishedMonograph);
				$publishedMonograph = $publishedMonographDao->newDataObject();
				$publishedMonograph->setId($monograph->getId());
				$publishedMonographDao->insertObject($publishedMonograph);
			}
			$publishedMonograph->setDatePublished(Core::getCurrentDate());
			$publishedMonographDao->updateObject($publishedMonograph);

			$notificationMgr->updateNotification(
				$request,
				array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
				null,
				ASSOC_TYPE_MONOGRAPH,
				$publishedMonograph->getId()
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

				// Unpublish monograph.
				$publishedMonograph->setDatePublished(null);
				$publishedMonographDao->updateObject($publishedMonograph);

				$notificationMgr->updateNotification(
					$request,
					array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
					null,
					ASSOC_TYPE_MONOGRAPH,
					$publishedMonograph->getId()
				);

				// Create tombstones for each publication format.
				$publicationFormatTombstoneMgr->insertTombstonesByPublicationFormats($publicationFormats, $press);

				// Log the unpublication event.
				import('lib.pkp.classes.log.SubmissionLog');
				SubmissionLog::logEvent($request, $monograph, SUBMISSION_LOG_METADATA_UNPUBLISH, 'submission.event.metadataUnpublished');
			}

			// regular submission without publish in catalog.
			$monographDao->updateObject($monograph);
		}
	}
}

?>
