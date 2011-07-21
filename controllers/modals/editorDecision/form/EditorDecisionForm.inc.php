<?php

/**
 * @file controllers/modals/editorDecision/form/EditorDecisionForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Base class for the editor decision forms.
 */

import('lib.pkp.classes.form.Form');

// Define review round and review stage id constants.
import('classes.monograph.reviewRound.ReviewRound');

class EditorDecisionForm extends Form {
	/** @var SeriesEditorSubmission The submission associated with the editor decision **/
	var $_seriesEditorSubmission;

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $template string The template to display
	 */
	function EditorDecisionForm($seriesEditorSubmission, $template) {
		parent::Form($template);
		$this->setSeriesEditorSubmission($seriesEditorSubmission);

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the submission
	 * @return SeriesEditorSubmission
	 */
	function getSeriesEditorSubmission() {
		return $this->_seriesEditorSubmission;
	}

	/**
	 * Set the submission
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 */
	function setSeriesEditorSubmission($seriesEditorSubmission) {
		$this->_seriesEditorSubmission = $seriesEditorSubmission;
	}


	//
	// Overridden template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		Locale::requireComponents(
			array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION)
		);
		parent::initData();
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles'));
		parent::initData();
	}


	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request, $round = null) {
		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();

		// Set the reviewer round.
		if (is_null($round)) {
			$round = $seriesEditorSubmission->getCurrentRound();
		}
		// N.B. The current round and stage are loaded under the assumption that
		// decisions are only made for the current stage.
		$this->setData('stageId', $seriesEditorSubmission->getStageId());
		$this->setData('round', $round);

		// Set the monograph.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $seriesEditorSubmission->getId());
		$templateMgr->assign_by_ref('monograph', $seriesEditorSubmission);

		return parent::fetch($request);
	}


	//
	// Private helper methods
	//
	/**
	 * Initiate a new review round and add selected files
	 * to it. Also saves the new round to the submission.
	 * @param $monograph Monograph
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $newRound integer
	 * @param $status integer One of the REVIEW_ROUND_STATUS_* constants.
	 */
	function _initiateReviewRound(&$monograph, $stageId, $newRound, $status = null) {
		assert(is_int($newRound));

		// Create a new review round.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$reviewRoundDao->build($monograph->getId(), $stageId, $newRound, $status);

		// Add the selected files to the new round.
		$selectedFiles = $this->getData('selectedFiles');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		if(is_array($selectedFiles)) {
			$filesWithRevisions = array();
			foreach ($selectedFiles as $selectedFile) {
				// Split the file into file id and file revision.
				list($fileId, $revision) = explode('-', $selectedFile);
				// Bring in the MONOGRAPH_FILE_* constants.
				import('classes.monograph.MonographFile');
				// Bring in the Manager (we need it).
				import('classes.file.MonographFileManager');
				list($newFileId, $newRevision) = MonographFileManager::copyFileToFileStage($fileId, $revision, MONOGRAPH_FILE_REVIEW);
				$submissionFileDao->assignRevisionToReviewRound($newFileId, $newRevision, $stageId, $newRound, $monograph->getId());
			}
		}

		// Change the monograph's review round state.
		$monograph->setCurrentRound($newRound);
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$monographDao->updateMonograph($monograph);
	}
}

?>
