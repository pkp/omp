<?php

/**
 * @file controllers/modals/submissionMetadata/form/CatalogEntrySubmissionReviewForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	function execute() {
		parent::execute();

		$monograph =& $this->getMonograph();
		if ($this->getData('confirm') != '') {
			$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
			$publishedMonograph =& $publishedMonographDao->getById($monograph->getId());
			$isExistingEntry = $publishedMonograph?true:false;
			if (!$isExistingEntry) {
				unset($publishedMonograph);
				$publishedMonograph = $publishedMonographDao->newDataObject();
				$publishedMonograph->setId($monograph->getId());
			}
			if ($isExistingEntry) {
				$publishedMonographDao->updateObject($publishedMonograph);
			} else {
				$publishedMonograph->setDatePublished(Core::getCurrentDate());
				$publishedMonographDao->insertObject($publishedMonograph);
				// Remove "need to approve submission" note
				$notificationDao =& DAORegistry::getDAO('NotificationDAO');
				$notificationDao->deleteByAssoc(
                                        ASSOC_TYPE_MONOGRAPH,
                                        $monograph->getId(),
                                        null,
                                        NOTIFICATION_TYPE_APPROVE_SUBMISSION,
                                        $monograph->getPressId()
                                );
			}
		} else { // regular submission without publish in catalog
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monographDao->updateMonograph($monograph);
		}
	}
}

?>
