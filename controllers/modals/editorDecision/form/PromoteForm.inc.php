<?php

/**
 * @file controllers/modals/editorDecision/form/PromoteForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PromoteForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Form for promoting a submission (to external review or production)
 */

import('controllers.modals.editorDecision.form.EditorDecisionForm');

import('classes.submission.common.Action');

class PromoteForm extends EditorDecisionForm {

	/** The decision being taken **/
	var $_decision;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $decision int
	 */
	function PromoteForm($monograph, $decision) {
		parent::EditorDecisionForm($monograph, 'controllers/modals/editorDecision/form/promoteForm.tpl');

		assert(in_array($decision,
				array(
					SUBMISSION_EDITOR_DECISION_ACCEPT, SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW,
					SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS, SUBMISSION_EDITOR_DECISION_RESUBMIT,
					SUBMISSION_EDITOR_DECISION_DECLINE
				)));
		$this->setDecision($decision);

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the decision
	 * @return int
	 */
	function getDecision() {
		return $this->_decision;
	}

	/**
	 * Set the decision
	 * @param $decision int
	 */
	function setDecision($decision) {
		$this->_decision = (int) $decision;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize form data with the author name and the monograph id.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$press =& $request->getPress();
		$monograph =& $this->getMonograph();
		$submitter = $monograph->getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'EDITOR_DECISION_ACCEPT');
		$paramArray = array(
			'authorName' => $submitter->getFullName(),
			'pressName' => $press->getLocalizedName(),
			'monographTitle' => $monograph->getLocalizedTitle(),
			'editorialContactSignature' => $submitter->getContactSignature(),
		);
		$email->assignParams($paramArray);

		$actionLabels = array(SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => 'editor.monograph.decision.externalReview',
							  SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.monograph.decision.accept');

		$this->_data = array(
			'monographId' => $monograph->getId(),
			'decision' => $this->getDecision(),
			'authorName' => $monograph->getAuthorString(),
			'personalMessage' => $email->getBody(),
			'actionLabel' => $actionLabels[$this->getDecision()]
		);

		return parent::initData($args, $request);
	}

	/**
	 * Fetch the modal content
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$monograph =& $this->getMonograph();
		$reviewType = (int) $request->getUserVar('reviewType'); //FIXME #6102: What to do with reviewType?
		$round = (int) $request->getUserVar('round');
		assert($round <= $monograph->getCurrentRound() && $round >= 0);

		$templateMgr =& TemplateManager::getManager();
		$this->setData('reviewType', $reviewType);
		$this->setData('round', $round);
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('personalMessage', 'selectedFiles', 'selectedAttachments'));
	}

	/**
	 * Save editor decision
	 * @param $args array
	 * @param $request PKPRequest
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$monograph =& $this->getMonograph();

		$decision = $this->getDecision();
		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($monograph, SUBMISSION_EDITOR_DECISION_ACCEPT);

				// 2. select email key
				$emailKey = 'EDITOR_DECISION_ACCEPT';

				// 3. Set status of round
				$status = REVIEW_ROUND_STATUS_ACCEPTED;

				// 4. Assign the default users to the next workflow stage
				Action::assignDefaultStageParticipants($monograph->getId(), WORKFLOW_STAGE_ID_EDITING);

				$monograph->setCurrentStageId(WORKFLOW_STAGE_ID_EDITING);
				$monographDao->updateMonograph($monograph);

				break;
			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($monograph, SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW);

				// 2. Create a new external review round
				$reviewRoundDao->build($monograph->getId(), REVIEW_TYPE_EXTERNAL, 1, 1);


				// 3. Get selected files and put in review_round_files table
				$selectedFiles = $this->getData('selectedFiles');
				$filesForReview = array();
				foreach ($selectedFiles as $selectedFile) {
					$filesForReview[] = explode("-", $selectedFile);
				}
				$reviewRoundDao->setFilesForReview($monograph->getId(), REVIEW_TYPE_EXTERNAL, 1, $filesForReview);

				// 4. select email key
				// FIXME #6123: will we have an email key for this decision?
				$emailKey = 'EDITOR_DECISION_ACCEPT';

				// 5. Set status of round
				$status = REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL;

				// 6. Assign the default users to the next workflow stage
				Action::assignDefaultStageParticipants($monograph->getId(), WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);

				$monograph->setCurrentStageId(WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);
				$monograph->setCurrentRound(1);
				$monographDao->updateMonograph($monograph);
				break;
			default:
				// only support the three decisions above
				assert(false);
		}

		$currentReviewRound =& $reviewRoundDao->build($monograph->getId(), $monograph->getCurrentReviewType(), $monograph->getCurrentRound());
		$currentReviewRound->setStatus($status);
		$reviewRoundDao->updateObject($currentReviewRound);

		// n. Send Personal message to author
		$submitter =& $monograph->getUser();
		import('classes.mail.MonographMailTemplate');
		$email =& new MonographMailTemplate($monograph, $emailKey, null, true);
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());
		$email->setAssoc(MONOGRAPH_EMAIL_EDITOR_NOTIFY_AUTHOR, MONOGRAPH_EMAIL_TYPE_EDITOR, $currentReviewRound->getRound());

		// Attach the selected reviewer attachments
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$selectedAttachments = $this->getData('selectedAttachments') ? $this->getData('selectedAttachments') : array();
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($monograph->getId(), $monograph->getCurrentRound());
		assert(is_array($reviewIndexes));
		if(is_array($selectedAttachments)) {
			foreach ($selectedAttachments as $attachmentId) {
				$monographFile =& $monographFileDao->getMonographFile($attachmentId);
				$fileName = $monographFile->getOriginalFileName();
				$reviewAssignmentId = $monographFile->getAssocId();
				assert($monographFile->getAssocType == ASSOC_TYPE_REVIEW_ASSIGNMENT);
				assert(is_numeric($reviewAssignmentId));
				$email->addAttachment($monographFile->getFilePath(), String::enumerateAlphabetically($reviewIndexes[$reviewAssignmentId]) . '-' . $monographFile->getOriginalFileName());

				// Update monograph to set viewable as true, so author can view the file on their submission summary page
				$monographFile->setViewable(true);
				$monographFileDao->updateMonographFile($monographFile);
			}
		}
		$email->send();
	}
}

?>
