<?php

/**
 * @file controllers/modals/editorDecision/form/NewReviewRoundForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ResubmitForReviewForm
 * @ingroup controllers_modal_editorDecision_form
 *
 * @brief Form for creating a new review round
 */

import('lib.pkp.classes.form.Form');

class InitiateReviewForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function InitiateReviewForm($monographId) {
		parent::Form('controllers/modals/editorDecision/form/initiateReviewForm.tpl');
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

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->_monographId);
		$templateMgr->assign_by_ref('monograph', $monograph);
//		$this->setData('reviewType', $reviewType);
		$this->setData('round', $monograph->getCurrentRound());
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles', 'monographId'));
	}

	/**
	 * Save review assignment
	 */
	function execute(&$args, &$request) {
		import('classes.submission.seriesEditor.SeriesEditorAction');
		import('submission.editor.EditorAction');

		$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');

		// 1. Increment the monograph's workflow stage
		$monograph =& $this->getMonograph();
		$monograph->setCurrentStageId(WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographDao->updateMonograph($monograph);

		// 2. Create a new internal review round
		// FIXME: what do do about reviewRevision? being set to 1 for now.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRoundDao->createReviewRound($this->_monographId, REVIEW_TYPE_INTERNAL, 1, 1, REVIEW_ROUND_STATUS_PENDING_REVIEWERS);

		// 3. Assign the editor
		// FIXME: bug # 5546: this assignment should be done elsewhere, prior to this point.
		$user =& $request->getUser();
		EditorAction::assignEditor($this->_monographId, $user->getId(), true);

		// 4. Add the selected files to the new round
		$selectedFiles = $this->getData('selectedFiles');
		$fileForReview = array();
		foreach ($selectedFiles as $selectedFile) {
			$fileForReview[] = explode("-", $selectedFile);
		}
		$reviewAssignmentDAO->setFilesForReview($this->_monographId, REVIEW_TYPE_INTERNAL, 1, $fileForReview);

		return 1;
	}
}

?>
