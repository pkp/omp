<?php

/**
 * @file controllers/modals/editorDecision/form/NewReviewRoundForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewReviewRoundForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Form for creating a new review round (after the first)
 */

import('controllers.modals.editorDecision.form.EditorDecisionForm');

class NewReviewRoundForm extends EditorDecisionForm {

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 */
	function NewReviewRoundForm($monograph) {
		parent::EditorDecisionForm($monograph, 'controllers/modals/editorDecision/form/newReviewRoundForm.tpl');

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
	 * Get the new round tab button HTML/JS, that will be added to the tab bar on new tab creation
	 * @param $request PKPRequest
	 * @param $round int The new review round number
	 */
	function getNewTab(&$request, $round) {
		$monograph =& $this->getMonograph();

		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();
		$url = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'review', array($monograph->getId(), $round));

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('newRoundUrl', $url);
		$templateMgr->assign('round', $round);

		return $templateMgr->fetch('controllers/modals/editorDecision/form/reviewRoundTab.tpl');
	}

	/**
	 * Start new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int The new review round number
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		import('classes.submission.seriesEditor.SeriesEditorAction');

		$monograph =& $this->getMonograph();
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($monograph->getId());

		// 1. Record the decision
		SeriesEditorAction::recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_RESUBMIT);

		// 2. Create a new internal review round
		// FIXME #6102: What to do with reviewType?
		$newRound = $seriesEditorSubmission->getCurrentRound() ? ($seriesEditorSubmission->getCurrentRound() + 1): 1;
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRoundDao->build($monograph->getId(), REVIEW_TYPE_INTERNAL, $newRound, 1, REVIEW_ROUND_STATUS_PENDING_REVIEWERS);

		$seriesEditorSubmission->setCurrentRound($newRound);
		$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

		// 3. Add the selected files to the new round
		$selectedFiles = $this->getData('selectedFiles');
		$filesWithRevisions = array();
		if(is_array($selectedFiles)) {
			foreach ($selectedFiles as $selectedFile) {
				$filesWithRevisions[] = explode("-", $selectedFile);
			}
			$reviewRoundDAO =& DAORegistry::getDAO('ReviewRoundDAO');
			$reviewRoundDAO->setFilesForReview($monograph->getId(), REVIEW_TYPE_INTERNAL, $newRound, $filesWithRevisions);
		}
		return $newRound;
	}
}

?>
