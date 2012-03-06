<?php

/**
 * @file controllers/modals/editorDecision/form/EditorDecisionWithEmailForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionWithEmailForm
 * @ingroup controllers_modals_editorDecision_form
 *
 * @brief Base class for the editor decision forms.
 */

import('controllers.modals.editorDecision.form.EditorDecisionForm');

class EditorDecisionWithEmailForm extends EditorDecisionForm {

	/** @var integer The decision being taken **/
	var $_decision;

	/** @var String */
	var $_saveFormOperation;

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $decision integer
	 * @param $stageId integer
	 * @param $template string The template to display
	 * @param $reviewRound ReviewRound
	 */
	function EditorDecisionWithEmailForm(&$seriesEditorSubmission, $decision, $stageId, $template, &$reviewRound = null) {
		parent::EditorDecisionForm($seriesEditorSubmission, $stageId, $template, $reviewRound);
		$this->_decision = $decision;
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the decision
	 * @return integer
	 */
	function getDecision() {
		return $this->_decision;
	}

	/**
	 * Get the operation to save this form.
	 * @return string
	 */
	function getSaveFormOperation() {
		return $this->_saveFormOperation;
	}

	/**
	 * Set the operation to save this form.
	 * @param $saveFormOperation string
	 */
	function setSaveFormOperation($saveFormOperation) {
		$this->_saveFormOperation = $saveFormOperation;
	}

	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::initData()
	 * @param $actionLabels array
	 */
	function initData($args, &$request, $actionLabels) {
		$press =& $request->getPress();
		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();
		$submitter = $seriesEditorSubmission->getUser();
		$user =& $request->getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'EDITOR_DECISION_ACCEPT');
		$paramArray = array(
			'authorName' => $submitter->getFullName(),
			'pressName' => $press->getLocalizedName(),
			'monographTitle' => $seriesEditorSubmission->getLocalizedTitle(),
			'editorialContactSignature' => $user->getContactSignature(),
		);
		$email->assignParams($paramArray);

		// If we are in review stage we need a review round.
		$reviewRound =& $this->getReviewRound();
		if (is_a($reviewRound, 'ReviewRound')) {
			$this->setData('reviewRoundId', $reviewRound->getId());
		}

		$data = array(
			'monographId' => $seriesEditorSubmission->getId(),
			'decision' => $this->getDecision(),
			'authorName' => $seriesEditorSubmission->getAuthorString(),
			'personalMessage' => $email->getBody(),
			'actionLabel' => $actionLabels[$this->getDecision()]
		);
		foreach($data as $key => $value) {
			$this->setData($key, $value);
		}

		return parent::initData($args, $request);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('personalMessage', 'selectedAttachments', 'skipEmail'));
		parent::readInputData();
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		// No all decision forms need a review round.
		// Try to get a review round.
		$reviewRound =& $this->getReviewRound();

		// If we have a review round, then we are in a review stage.
		if (is_a($reviewRound, 'ReviewRound')) {
			// URL to retrieve peer reviews:
			$router =& $request->getRouter();
			$submission =& $this->getSeriesEditorSubmission();
			$stageId = $reviewRound->getStageId();
			$this->setData(
				'peerReviewUrl',
				$router->url(
					$request, null, null,
					'importPeerReviews', null,
					array(
						'monographId' => $submission->getId(),
						'stageId' => $stageId,
						'reviewRoundId' => $reviewRound->getId()
					)
				)
			);
		}

		// When this form is being used in review stages, we need a different
		// save operation to allow the EditorDecisionHandler authorize the review
		// round object.
		if ($this->getSaveFormOperation()) {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('saveFormOperation', $this->getSaveFormOperation());
		}

		return parent::fetch($request);
	}


	//
	// Private helper methods
	//
	/**
	 * Retrieve the last review round and update it with the new status.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $status integer One of the REVIEW_ROUND_STATUS_* constants.
	 */
	function _updateReviewRoundStatus($seriesEditorSubmission, $status, &$reviewRound = null) {
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		if (!$reviewRound) {
			$reviewRound =& $reviewRoundDao->getLastReviewRoundByMonographId($seriesEditorSubmission->getId());
		}

		// If we don't have a review round, it's because the monograph is being
		// accepted without starting any of the review stages. In that case we
		// do nothing.
		if (is_a($reviewRound, 'ReviewRound')) {
			$reviewRoundDao->updateStatus($reviewRound, null, $status);
		}
	}

	/**
	 * Sends an email with a personal message and the selected
	 * review attachements to the author. Also marks review attachments
	 * selected by the editor as "viewable" for the author.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $emailKey string An email template.
	 * @param $request PKPRequest
	 */
	function _sendReviewMailToAuthor(&$seriesEditorSubmission, $emailKey, $request) {
		// Send personal message to author.
		$submitter =& $seriesEditorSubmission->getUser();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, $emailKey);
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());
		$email->setEventType(MONOGRAPH_EMAIL_EDITOR_NOTIFY_AUTHOR);

		$userStageAssignmentDao =& DAORegistry::getDAO('UserStageAssignmentDAO');
		$authorStageParticipants = $userStageAssignmentDao->getUsersBySubmissionAndStageId($seriesEditorSubmission->getId(), $seriesEditorSubmission->getStageId(), null, ROLE_ID_AUTHOR);
		while ($author =& $authorStageParticipants->next()) {
			if (preg_match('{^' . quotemeta($submitter->getEmail()) . '$}', $author->getEmail())) {
				$email->addRecipient($author->getEmail(), $author->getFullName());
			} else {
				$email->addCc($author->getEmail(), $author->getFullName());
			}
		}

		// Get review round.
		$reviewRound =& $this->getReviewRound();

		if(is_a($reviewRound, 'ReviewRound')) {
			// Retrieve review indexes.
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
			$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($seriesEditorSubmission->getId(), $reviewRound->getId());
			assert(is_array($reviewIndexes));

			// Add a review index for review attachments not associated with
			// a review assignment (i.e. attachments uploaded by the editor).
			$lastIndex = end($reviewIndexes);
			$reviewIndexes[-1] = $lastIndex + 1;

			// Attach the selected reviewer attachments to the email.
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$selectedAttachments = $this->getData('selectedAttachments');
			if(is_array($selectedAttachments)) {
				foreach ($selectedAttachments as $attachmentId) {
					// Split the attachment into file id and file revision.
					$attachment = explode('-', $attachmentId);
					assert(count($attachment) == 2);

					// Retrieve the monograph file.
					$monographFile =& $submissionFileDao->getRevision($attachment[0], $attachment[1]);
					assert(is_a($monographFile, 'MonographFile'));

					// Check the association information.
					if($monographFile->getAssocType() == ASSOC_TYPE_REVIEW_ASSIGNMENT) {
						// The review attachment has been uploaded by a reviewer.
						$reviewAssignmentId = $monographFile->getAssocId();
						assert(is_numeric($reviewAssignmentId));
					} else {
						// The review attachment has been uploaded by the editor.
						$reviewAssignmentId = -1;
					}

					// Identify the corresponding review index.
					assert(isset($reviewIndexes[$reviewAssignmentId]));
					$reviewIndex = $reviewIndexes[$reviewAssignmentId];
					assert(!is_null($reviewIndex));

					// Add the attachment to the email.
					$email->addAttachment(
						$monographFile->getFilePath(),
						String::enumerateAlphabetically($reviewIndex).'-'.$monographFile->getOriginalFileName()
					);

					// Update monograph file to set viewable as true, so author
					// can view the file on their submission summary page.
					$monographFile->setViewable(true);
					$submissionFileDao->updateObject($monographFile);
				}
			}
		}

		// Send the email.
		if (!$this->getData('skipEmail')) {
			$email->send($request);
		}
	}
}

?>
