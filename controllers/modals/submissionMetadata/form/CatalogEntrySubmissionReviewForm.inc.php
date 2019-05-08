<?php

/**
 * @file controllers/modals/submissionMetadata/form/CatalogEntrySubmissionReviewForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
	function __construct($monographId, $stageId = null, $formParams = null) {
		parent::__construct($monographId, $stageId, $formParams, 'controllers/modals/submissionMetadata/form/catalogEntrySubmissionReviewForm.tpl');
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_APP_SUBMISSION);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();

		// Read in the additional fields price data.
		$this->readUserVars(array('price'));
	}
}


