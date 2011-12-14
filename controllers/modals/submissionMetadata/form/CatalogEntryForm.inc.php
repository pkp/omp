<?php

/**
 * @file controllers/modals/submissionMetadata/form/CatalogEntryForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryForm
 * @ingroup controllers_modals_submissionMetadata_form_CatalogEntryForm
 *
 * @brief Displays a submission's metadata view.
 */

import('lib.pkp.classes.form.Form');

// Use this class to handle the submission metadata.
import('controllers.modals.submissionMetadata.form.SubmissionMetadataViewForm');

class CatalogEntryForm extends SubmissionMetadataViewForm {
	/**
	 * Constructor.
	 * @param $monographId integer
	 * @param $stageId integer
	 * @param $formParams array
	 */
	function CatalogEntryForm($monographId, $stageId = null, $formParams = null) {
		parent::SubmissionMetadataViewForm($monographId, $stageId, $formParams, 'controllers/modals/submissionMetadata/form/catalogEntryForm.tpl');

		$this->addCheck(new FormValidator($this, 'confirm', 'required', 'submission.catalogEntry.confirm.required'));

		// submission.catalogEntry.confirm.required on validation
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION);
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
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getById($monograph->getId());
		$isExistingEntry = $publishedMonograph?true:false;
		if (!$isExistingEntry) {
			unset($publishedMonograph);
			$publishedMonograph = $publishedMonographDao->newDataObject();
			$publishedMonograph->setId($monograph->getId());
		}
		$publishedMonograph->setDatePublished(Core::getCurrentDate());
		if ($isExistingEntry) {
			$publishedMonographDao->updateObject($publishedMonograph);
		} else {
			$publishedMonographDao->insertObject($publishedMonograph);
		}
	}
}

?>
