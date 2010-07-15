<?php

/**
 * @file controllers/modals/editorDecision/form/ResubmitForReviewForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ResubmitForReviewForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Form for adding resubmitting a submission to a new round of reviews
 */

import('lib.pkp.classes.form.Form');

class ResubmitForReviewForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function ResubmitForReviewForm($monographId) {
		parent::Form('controllers/modals/editorDecision/form/resubmitForReviewForm.tpl');
		$this->_monographId = (int) $monographId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph
	 * @return object monograph
	 */
	function getMonograph() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		return $monographDao->getMonograph($this->_monographId);
	}

	//
	// Template methods from Form
	//
	/**
	* Initialize form data with the author name and the monograph id.
	* @param $args array
	* @param $request PKPRequest
	*/
	function initData(&$args, &$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));

		$this->_data = array(
			'monographId' => $this->_monographId,
		);
	}

	function fetch(&$request) {
		$monograph =& $this->getMonograph();
		$reviewType = (int) $request->getUserVar('reviewType');
		$round = (int) $request->getUserVar('round');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->_monographId);
		$templateMgr->assign_by_ref('monograph', $monograph);
		$this->setData('reviewType', $reviewType);
		$this->setData('round', $round);
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles', 'monographId', 'selected-listbuilder-users-reselectreviewerslistbuilder'));
	}

	/**
	 * Save review assignment
	 */
	function execute(&$args, &$request) {
		import('classes.submission.seriesEditor.SeriesEditorAction');
		import('submission.editor.EditorAction');

		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($this->_monographId);

		// 1. Record the decision
		SeriesEditorAction::recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_RESUBMIT);

		// 2. Create a new internal review round
		// FIXME: what do do about reviewRevision? being set to 1 for now.
		$currentRound = $reviewRoundDao->build($this->_monographId, REVIEW_TYPE_INTERNAL, $seriesEditorSubmission->getCurrentRound());
		$currentRound->setStatus(REVIEW_ROUND_STATUS_RESUBMITTED);
		$reviewRoundDao->update($currentRound);

		$newRound = $seriesEditorSubmission->getCurrentRound() ? 1 : ($seriesEditorSubmission->getCurrentRound() + 1);
		$reviewRoundDao->createReviewRound($this->_monographId, REVIEW_TYPE_INTERNAL, $newRound, 1);

		$seriesEditorSubmission->setCurrentRound($newRound);
		$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

		// 3. Assign the editor
		// FIXME: bug # 5546: this assignment should be done elsewhere, prior to this point.
		$user =& $request->getUser();
		EditorAction::assignEditor($this->_monographId, $user->getId(), true);

		// 4. Add the selected files to the new round
		$selectedFiles = $this->getData('selectedFiles');
		$reviewAssignmentDAO->setFilesForReview($this->_monographId, REVIEW_TYPE_INTERNAL, $newRound, $selectedFiles);

		// 5. Add the selected reviewers to the
		$selectedReviewers = $this->getData('selected-listbuilder-users-reselectreviewerslistbuilder');
		foreach ($selectedReviewers as $reviewerId) {
			// FIXME: Last two parameters (review due dates) not set--Should these be defaults, or set in the modal?
			SeriesEditorAction::addReviewer($seriesEditorSubmission, $reviewerId, REVIEW_TYPE_INTERNAL, $newRound);
		}
	}
}

?>
