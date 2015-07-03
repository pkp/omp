<?php

/**
 * @file controllers/modals/submissionMetadata/ReviewerSubmissionMetadataHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionMetadataHandler
 * @ingroup controllers_modals_submissionMetadata
 *
 * @brief Display submission metadata to reviewers.
 */

import('lib.pkp.classes.controllers.modals.submissionMetadata.PKPReviewerSubmissionMetadataHandler');

class ReviewerSubmissionMetadataHandler extends PKPReviewerSubmissionMetadataHandler {
	/**
	 * Constructor.
	 */
	function ReviewerSubmissionMetadataHandler() {
		parent::PKPReviewerSubmissionMetadataHandler();
	}

	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('classes.security.authorization.SubmissionAccessPolicy');
		$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Get an instance of the metadata form to be used by this handler.
	 * @param $submissionId int
	 * @return Form
	 */
	function getFormInstance($submissionId, $stageId = null, $params = null) {
		import('controllers.modals.submissionMetadata.form.CatalogEntrySubmissionReviewForm');
		return new CatalogEntrySubmissionReviewForm($submissionId, $stageId, $params);
	}
}

?>
