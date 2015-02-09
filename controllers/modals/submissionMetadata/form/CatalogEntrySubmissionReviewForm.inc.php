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
			// If we are expediting, add field requirements.
			$this->addCheck(new FormValidator($this, 'salesType', 'required', 'submission.catalogEntry.salesType.required'));
			$this->addCheck(new FormValidatorCustom($this, 'price', 'required', 'grid.catalogEntry.validPriceRequired',
				create_function('$price, $form', '
					switch ($form->getData(\'salesType\')) {
						case \'directSales\':
							return preg_match(\'/^(([1-9]\d{0,2}(,\d{3})*|[1-9]\d*|0|)(.\d{2})?|([1-9]\d{0,2}(,\d{3})*|[1-9]\d*|0|)(.\d{2})?)$/\', $price);
						default:
							return true; // set to zero in the handler for the other two possibilities.
					}'), array($this))
				);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();

		// Read in the additional fields price data.
		$this->readUserVars(array('salesType', 'price'));
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
}

?>
