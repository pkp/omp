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

	/** @var int The StageId where the decision is being made **/
	var $_stageId;

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $template string The template to display
	 */
	function EditorDecisionForm($seriesEditorSubmission, $stageId, $template) {
		parent::Form($template);
		$this->_seriesEditorSubmission = $seriesEditorSubmission;
		$this->_stageId = $stageId;

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
	 * Get the stage Id
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	//
	// Overridden template methods from Form
	//
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
		$this->setData('stageId', $this->getStageId());
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
	 * @param $request Request
	 * @param $status integer One of the REVIEW_ROUND_STATUS_* constants.
	 */
	function _initiateReviewRound(&$monograph, $stageId, $newRound, &$request, $status = null) {
		assert(is_int($newRound));

		// Create a new review round.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$reviewRound =& $reviewRoundDao->build($monograph->getId(), $stageId, $newRound, $status);

		// Check for a notification already in place for the current review round.
		$press =& $request->getPress();
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(
			ASSOC_TYPE_REVIEW_ROUND, 
			$reviewRound->getId(), 
			null, 
			NOTIFICATION_TYPE_REVIEW_ROUND_STATUS,
			$press->getId()
		);
		
		// Create round status notification if there is no notification already.
		if ($notificationFactory->wasEmpty()) {
			$notificationMgr = new NotificationManager();
			$notificationMgr->createNotification(
				$request,
				null,
				NOTIFICATION_TYPE_REVIEW_ROUND_STATUS,
				$press->getId(),
				ASSOC_TYPE_REVIEW_ROUND,
				$reviewRound->getId(),
				NOTIFICATION_LEVEL_NORMAL
			);
		}
		
		// Add the selected files to the new round.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */

		// Bring in the MONOGRAPH_FILE_* constants.
		import('classes.monograph.MonographFile');
		// Bring in the Manager (we need it).
		import('classes.file.MonographFileManager');
		foreach (array('selectedFiles', 'selectedAttachments') as $userVar) {
			$selectedFiles = $this->getData($userVar);
			if(is_array($selectedFiles)) {
				foreach ($selectedFiles as $selectedFile) {
					// Split the file into file id and file revision.
					list($fileId, $revision) = explode('-', $selectedFile);
					list($newFileId, $newRevision) = MonographFileManager::copyFileToFileStage($fileId, $revision, MONOGRAPH_FILE_REVIEW_FILE, null, true);
					$submissionFileDao->assignRevisionToReviewRound($newFileId, $newRevision, $stageId, $newRound, $monograph->getId());
				}
			}
		}

		// Change the monograph's review round state.
		$monograph->setCurrentRound($newRound);
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$monographDao->updateMonograph($monograph);
	}
}

?>
