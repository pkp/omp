<?php

/**
 * @file controllers/modals/editorDecision/form/EditorDecisionWithEmailForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
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

	/**
	 * Constructor.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $decision integer
	 * @param $template string The template to display
	 */
	function EditorDecisionWithEmailForm($seriesEditorSubmission, $decision, $template) {
		parent::EditorDecisionForm($seriesEditorSubmission, $template);
		$this->setDecision($decision);
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
	 * Set the decision
	 * @param $decision integer
	 */
	function setDecision($decision) {
		$this->_decision = (int) $decision;
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

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'EDITOR_DECISION_ACCEPT');
		$paramArray = array(
			'authorName' => $submitter->getFullName(),
			'pressName' => $press->getLocalizedName(),
			'monographTitle' => $seriesEditorSubmission->getLocalizedTitle(),
			'editorialContactSignature' => $submitter->getContactSignature(),
		);
		$email->assignParams($paramArray);

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
		$this->readUserVars(array('personalMessage', 'selectedAttachments'));
		parent::readInputData();
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		// Review type.
		//FIXME #6409: What to do with reviewType?
		$reviewType = (int)$request->getUserVar('reviewType');
		$this->setData('reviewType', $reviewType);

		// Review round.
		$seriesEditorSubmission =& $this->getSeriesEditorSubmission();
		$round = (int) $request->getUserVar('round');
		if($round > $seriesEditorSubmission->getCurrentRound() || $round < 0) {
			fatalError('Invalid review round!');
		}

		// URL to retrieve peer reviews:
		$router =& $request->getRouter();
		$submission =& $this->getSeriesEditorSubmission();
		$this->setData(
			'peerReviewUrl',
			$router->url($request, null, null, 'importPeerReviews', null, array('monographId' => $submission->getId()))
		);

		return parent::fetch($request, $round);
	}


	//
	// Private helper methods
	//
	/**
	 * Sends an email with a personal message and the selected
	 * review attachements to the author. Also updates the status
	 * of the current review round and marks review attachments
	 * selected by the editor as "viewable" for the author.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $status integer One of the REVIEW_ROUND_STATUS_* constants.
	 * @param $emailKey string An email template.
	 */
	function _sendReviewMailToAuthor(&$seriesEditorSubmission, $status, $emailKey) {
		// Retrieve the current review round and update it with the new status.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$currentReviewRound =& $reviewRoundDao->build($seriesEditorSubmission->getId(), $seriesEditorSubmission->getCurrentReviewType(), $seriesEditorSubmission->getCurrentRound());
		$currentReviewRound->setStatus($status);
		$reviewRoundDao->updateObject($currentReviewRound);

		// Send personal message to author.
		$submitter =& $seriesEditorSubmission->getUser();
		import('classes.mail.MonographMailTemplate');
		$email =& new MonographMailTemplate($seriesEditorSubmission, $emailKey);
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());
		$email->setAssoc(MONOGRAPH_EMAIL_EDITOR_NOTIFY_AUTHOR, MONOGRAPH_EMAIL_TYPE_EDITOR, $currentReviewRound->getRound());

		// Retrieve review indexes.
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($seriesEditorSubmission->getId(), $seriesEditorSubmission->getCurrentRound());
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

		// Send the email.
		$email->send();
	}
}

?>
