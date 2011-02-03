<?php

/**
 * @file controllers/modals/editorDecision/form/SendReviewsForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SendReviewsForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Form to request additional work from the author (Request revisions or
 *         resubmit for review), or to decline the submission.
 */

import('controllers.modals.editorDecision.form.EditorDecisionForm');

import('classes.submission.common.Action');

class SendReviewsForm extends EditorDecisionForm {
	/** The decision being taken **/
	var $_decision;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $decision int
	 */
	function SendReviewsForm($monograph, $decision) {
		parent::EditorDecisionForm($monograph, 'controllers/modals/editorDecision/form/sendReviewsForm.tpl');

		assert(in_array($decision, array(SUBMISSION_EDITOR_DECISION_ACCEPT, SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW,
										 SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS, SUBMISSION_EDITOR_DECISION_RESUBMIT, SUBMISSION_EDITOR_DECISION_DECLINE)));
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

		$actionLabels = array(SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.monograph.decision.requestRevisions',
							  SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.monograph.decision.resubmit',
							  SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline');

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
		$this->readUserVars(array('personalMessage', 'selectedFiles'));
	}

	/**
	 * Save review assignment
	 * @param $args array
	 * @param $request PKPRequest
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		$decision = $this->getDecision();
		$monograph =& $this->getMonograph();
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($monograph->getId());

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction =& new SeriesEditorAction();
		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
				// 1. Record the decision
				$seriesEditorAction->recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_DECLINE);

				// 2. select email key
				$emailKey = 'SUBMISSION_UNSUITABLE';

				// 3. Set status of round
				$status = REVIEW_ROUND_STATUS_REVISIONS_REQUESTED;
				break;

			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
				// 1. Record the decision
				$seriesEditorAction->recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_RESUBMIT);

				// 2.  Set status of round
				$status = REVIEW_ROUND_STATUS_RESUBMITTED;

				// 3.  Select email key
				$emailKey = 'EDITOR_DECISION_RESUBMIT';
				break;

			case SUBMISSION_EDITOR_DECISION_DECLINE:
				// 1. Record the decision
				$seriesEditorAction->recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_DECLINE);

				// 2. select email key
				$emailKey = 'SUBMISSION_UNSUITABLE';

				// 3. Set status of round
				$status = REVIEW_ROUND_STATUS_DECLINED;
				break;

			default:
				// only support the three decisions above
				assert(false);
		}

		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$currentReviewRound =& $reviewRoundDao->build($monograph->getId(), $seriesEditorSubmission->getCurrentReviewType(), $seriesEditorSubmission->getCurrentRound());
		$currentReviewRound->setStatus($status);
		$reviewRoundDao->updateObject($currentReviewRound);

		// n. Send Personal message to author
		$submitter = $seriesEditorSubmission->getUser();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, $emailKey);
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());
		$email->setAssoc(MONOGRAPH_EMAIL_EDITOR_NOTIFY_AUTHOR, MONOGRAPH_EMAIL_TYPE_EDITOR, $currentReviewRound->getRound());

		// Attach the selected reviewer attachments
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$selectedAttachments = $this->getData('selectedFiles') ? $this->getData('selectedFiles') : array();
 		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($seriesEditorSubmission->getId(), $seriesEditorSubmission->getCurrentRound());
		assert(is_array($reviewIndexes));
		if(is_array($selectedAttachments)) {
			foreach ($selectedAttachments as $attachmentId) {
				$attachment = explode('-', $attachmentId);

				$monographFile =& $submissionFileDao->getRevision($attachment[0], $attachment[1]);
				assert(is_a($monographFile, 'MonographFile'));

				$reviewAssignmentId = $monographFile->getAssocId();
				assert($monographFile->getAssocType() == ASSOC_TYPE_REVIEW_ASSIGNMENT);
				assert(is_numeric($reviewAssignmentId));

				$reviewIndex = $reviewIndexes[$reviewAssignmentId];
				assert(!is_null($reviewIndex));

				$email->addAttachment($monographFile->getFilePath(), String::enumerateAlphabetically($reviewIndex) . '-' . $monographFile->getOriginalFileName());

				// Update monograph to set viewable as true, so author can view the file on their submission summary page
				$monographFile->setViewable(true);
				$submissionFileDao->updateObject($monographFile);
			}
		}
		$email->send();
	}
}

?>
