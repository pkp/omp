<?php

/**
 * @file controllers/modals/editorDecision/form/InitiateInternalReviewForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InitiateReviewForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Form for creating the first review round for a submission's internal
 *  review
 */

import('lib.pkp.controllers.modals.editorDecision.form.InitiateReviewForm');

class InitiateInternalReviewForm extends InitiateReviewForm {
	/**
	 * Constructor.
	 * @param $submission Submission
	 * @param $decision int SUBMISSION_EDITOR_DECISION_...
	 * @param $stageId int WORKFLOW_STAGE_ID_...
	 */
	function InitiateInternalReviewForm($submission, $decision, $stageId) {
		parent::InitiateReviewForm($submission, $decision, $stageId, 'controllers/modals/editorDecision/form/initiateInternalReviewForm.tpl');
	}

	/**
	 * Get the stage ID constant for the submission to be moved to.
	 * @return int WORKFLOW_STAGE_ID_...
	 */
	function _getStageId() {
		return WORKFLOW_STAGE_ID_INTERNAL_REVIEW;
	}
}

?>
