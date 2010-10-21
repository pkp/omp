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

import('lib.pkp.classes.form.Form');

class PromoteForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monographId;

	/** The decision being taken **/
	var $_decision;

	/**
	 * Constructor.
	 */
	function PromoteForm($monographId, $decision) {
		parent::Form('controllers/modals/editorDecision/form/promoteForm.tpl');
		$this->_monographId = (int) $monographId;
		$this->_decision = (int) $decision;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
// FIXME: implement this check
//		$this->addCheck(new FormValidatorCustom($this, 'decision', 'required', 'invalid decision',
//														'in_array($decision, array(\'EDITOR_DECISION_ACCEPT\',
//																					\'EDITOR_DECISION_DECLINE\',
//																					\'EDITOR_DECISION_EXTERNAL_REVIEW\'))'));
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
	function initData($args, &$request) {
		$press =& $request->getPress();
		$monograph =& $this->getMonograph();
		$submitter = $monograph->getUser();

		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION));

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'EDITOR_DECISION_ACCEPT');
		$paramArray = array(
			'authorName' => $submitter->getFullName(),
			'pressName' => $press->getLocalizedName(),
			'monographTitle' => $monograph->getLocalizedTitle(),
			'editorialContactSignature' => $submitter->getContactSignature(),
		);
		$email->assignParams($paramArray);

		import('classes.submission.common.Action');
		$actionLabels = array(SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => 'editor.monograph.decision.externalReview',
							  SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.monograph.decision.accept');

		$this->_data = array(
			'monographId' => $this->_monographId,
			'decision' => $this->_decision,
			'authorName' => $monograph->getAuthorString(),
			'personalMessage' => $email->getBody(),
			'actionLabel' => $actionLabels[$this->_decision]
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
		$this->readUserVars(array('monographId', 'decision', 'personalMessage', 'selectedFiles', 'selectedAttachments'));
	}

	/**
	 * Save review assignment
	 */
	function execute($args, &$request) {
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$decision = $this->getData('decision');
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($this->_monographId);
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$currentReviewRound = $reviewRoundDao->build($this->_monographId, $seriesEditorSubmission->getCurrentReviewType(), $seriesEditorSubmission->getCurrentRound());

		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_ACCEPT);

				// 2. select email key
				$emailKey = 'EDITOR_DECISION_ACCEPT';

				// 3. Set status of round
				$status = REVIEW_ROUND_STATUS_ACCEPTED;

				// 3. Assign the default users to the next workflow stage
				import('classes.submission.common.Action');
				Action::assignDefaultStageParticipants($this->_monographId, WORKFLOW_STAGE_ID_EDITING);

				break;
			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW);

				// Create a new review round
				// FIXME: what do do about reviewRevision? being set to 1 for now.
				// 2. Create a new external review round if it doesn't exist
				if ( !$reviewRoundDao->reviewRoundExists($this->_monographId, REVIEW_TYPE_EXTERNAL, 1)) {
					$reviewRoundDao->createReviewRound($this->_monographId, REVIEW_TYPE_EXTERNAL, 1, 1);
				}

				// 3. Get selected files and put in DB somehow
				// FIXME: this is probably not right now.  Need to review Exteral Review vs. New Review Round.
				$selectedFiles = $this->getData('selectedFiles');
				$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');

				$reviewAssignmentDAO->setFilesForReview($this->_monographId, $reviewType, $round, $selectedFiles);

				// 4. select email key
				// FIXME: will we have an email key for this decision?
				$emailKey = 'EDITOR_DECISION_ACCEPT';

				// 5. Set status of round
				$status = REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL;
				break;
			default:
				// only support the three decisions above
				assert(false);
		}

		$currentReviewRound->setStatus($status);
		$reviewRoundDao->updateObject($currentReviewRound);

		// n. Send Personal message to author
		$submitter = $seriesEditorSubmission->getUser();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, $emailKey, null, true);
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());

		// Attach the selected reviewer attachments
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($this->_monographId);
		$selectedAttachments = $this->getData('selectedAttachments') ? $this->getData('selectedAttachments') : array();
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($seriesEditorSubmission->getId(), $seriesEditorSubmission->getCurrentRound());
		foreach ($selectedAttachments as $attachmentId) {
			$monographFile =& $monographFileManager->getFile($attachmentId);
			$fileName = $monographFile->getOriginalFileName();
			$reviewAssignmentId = $monographFile->getAssocId();
			$reviewerPrefix = chr(ord('A') + $reviewIndexes[$reviewAssignmentId]);
			$email->addAttachment($monographFile->getFilePath(), $reviewerPrefix . '-' . $monographFile->getOriginalFileName());
		}

		$email->send();
	}
}

?>
