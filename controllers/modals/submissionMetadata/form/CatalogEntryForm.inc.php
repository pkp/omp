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
		parent::SubmissionMetadataViewForm($monographId, $stageId, $formParams);
	}
}

?>
