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
		$actionLabels = array(SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.monograph.decision.requestRevisions',
							  SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.monograph.decision.resubmit',
							  SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline');

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
		$this->readUserVars(array('monographId', 'decision', 'personalMessage'));
	}

	/**
	 * Save review assignment
	 */
	function execute(&$args, &$request) {
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$decision = $this->getData('decision');
		$monograph =& $this->getMonograph();
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$currentRound = $reviewRoundDao->build($this->_monographId, $monograph->getCurrentReviewType(), $monograph->getCurrentRound());

		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($monograph, SUBMISSION_EDITOR_DECISION_DECLINE);

				// 2. select email key
				$emailKey = 'SUBMISSION_UNSUITABLE';

				// 3. Set status of round
				$status = REVIEW_ROUND_STATUS_REVISIONS_REQUESTED;
				break;

			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($seriesEditorSubmission, SUBMISSION_EDITOR_DECISION_RESUBMIT);

				// 2.  Set status of round
				$status = REVIEW_ROUND_STATUS_RESUBMITTED;

				// 3.  Select email key
				$emailKey = 'EDITOR_DECISION_RESUBMIT';
				break;

			case SUBMISSION_EDITOR_DECISION_DECLINE:
				// 1. Record the decision
				SeriesEditorAction::recordDecision($monograph, SUBMISSION_EDITOR_DECISION_DECLINE);

				// 2. select email key
				$emailKey = 'SUBMISSION_UNSUITABLE';

				// 3. Set status of round
				$status = REVIEW_ROUND_STATUS_DECLINED;
				break;

			default:
				// only support the three decisions above
				assert(false);
		}

		$currentReviewRound->setStatus($status);
		$reviewRoundDao->update($currentReviewRound);

		// n. Send Personal message to author
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, $emailKey);
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());
		$email->send();
	}
}

?>
