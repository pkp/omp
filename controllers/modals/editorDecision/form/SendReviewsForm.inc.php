<?php

/**
 * @file controllers/modals/editorDecision/form/ReviewerForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerForm
 * @ingroup controllers_grid_reviewer__form
 *
 * @brief Form for adding a reviewer to a submission
 */

import('lib.pkp.classes.form.Form');

class SendReviewsForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monographId;

	/** The decision being taken **/
	var $_decision;

	/**
	 * Constructor.
	 */
	function SendReviewsForm($monographId, $decision) {
		parent::Form('controllers/modals/editorDecision/form/sendReviewsForm.tpl');
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
	function initData(&$args, &$request) {
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

		$this->_data = array(
			'monographId' => $this->_monographId,
			'decision' => $this->_decision,
			'authorName' => $monograph->getAuthorString(),
			'personalMessage' => $email->getBody()
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
		$this->readUserVars(array('monographId', 'decision', 'personalMessage'));
	}

	/**
	 * Save review assignment
	 */
	function execute(&$args, &$request) {
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$decision = $this->getData('decision');

		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($monograph, SUBMISSION_EDITOR_DECISION_ACCEPT);

				// 2. select email key
				$emailKey = 'EDITOR_DECISION_ACCEPT';
				break;
			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($monograph, SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW);

				// Create a new review round
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				// FIXME: what do do about reviewRevision? being set to 1 for now.
				// 2. Create a new external review round if it doesn't exist
				if ( !$reviewRoundDao->reviewRoundExists($this->_monographId, REVIEW_TYPE_EXTERNAL, 1)) {
					$reviewRoundDao->createReviewRound($this->_monographId, REVIEW_TYPE_EXTERNAL, 1, 1);

					import('submission.editor.EditorAction');
					// FIXME: bug # 5546: this assignment should be done elsewhere, prior to this point.
					$user =& $request->getUser();
					EditorAction::assignEditor($this->_monographId, $user->getId(), true);
				}

				// 3. Get selected files and put in DB somehow
				// FIXME: this is probably not right now.  Need to review Exteral Review vs. New Review Round.
				$selectedFiles = $this->getData('selectedFiles');
				$reviewAssignmentDAO =& DAORegistry::getDAO('ReviewAssignmentDAO');

				$reviewAssignmentDAO->setFilesForReview($this->_monographId, $reviewType, $round, $selectedFiles);

				// 4. select email key
				// FIXME: will we have an email key for this decision?
				$emailKey = 'EDITOR_DECISION_ACCEPT';
				break;
			case SUBMISSION_EDITOR_DECISION_DECLINE:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($monograph, SUBMISSION_EDITOR_DECISION_DECLINE);

				// 2. select email key
				$emailKey = 'SUBMISSION_UNSUITABLE';
				break;
			default:
				// only support the three decisions above
				assert(false);
		}




		// n. Send Personal message to author
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, $emailKey);
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());
		$email->send();
	}
}

?>
