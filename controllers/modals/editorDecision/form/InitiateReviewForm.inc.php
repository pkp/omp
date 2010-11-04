<?php

/**
 * @file controllers/modals/editorDecision/form/InitiateReviewRoundForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InitiateReviewForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Form for creating the first review round for a submission
 */

import('controllers.modals.editorDecision.form.EditorDecisionForm');

class InitiateReviewForm extends EditorDecisionForm {

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 */
	function InitiateReviewForm($monograph) {
		parent::EditorDecisionForm($monograph, 'controllers/modals/editorDecision/form/initiateReviewForm.tpl');

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Overridden template methods
	//

	/**
	 * Fetch the modal content
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getMonograph();
		$this->setData('round', $monograph->getCurrentRound());
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles'));
	}

	/**
	 * Start review stage
	 * @param $args array
	 * @param $request PKPRequest
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		// 1. Increment the monograph's workflow stage
		$monograph =& $this->getMonograph();
		$monograph->setCurrentStageId(WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographDao->updateMonograph($monograph);

		// 2. Create a new internal review round
		// FIXME #6102: What to do with reviewType?
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRoundDao->build($monograph->getId(), REVIEW_TYPE_INTERNAL, 1, 1, REVIEW_ROUND_STATUS_PENDING_REVIEWERS);

		// 3. Assign the default users
		import('classes.submission.common.Action');
		Action::assignDefaultStageParticipants($monograph->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW);

		// 4. Add the selected files to the new round
		$selectedFiles = $this->getData('selectedFiles');
		if(is_array($selectedFiles)) {
			$filesForReview = array();
			foreach ($selectedFiles as $selectedFile) {
				$filesForReview[] = explode("-", $selectedFile);
			}
			$reviewRoundDAO =& DAORegistry::getDAO('ReviewRoundDAO');
			$reviewRoundDAO->setFilesForReview($monograph->getId(), REVIEW_TYPE_INTERNAL, 1, $filesForReview);
		}
	}
}

?>
