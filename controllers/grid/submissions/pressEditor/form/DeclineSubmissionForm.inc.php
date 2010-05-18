<?php

/**
 * @file controllers/grid/submissions/pressEditor/form/DeclineSubmissionForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DeclineSubmissionForm
 * @ingroup controllers_grid_submit_submissionContributor_form
 *
 * @brief Form for declining a submission outright
 */

import('lib.pkp.classes.form.Form');

class DeclineSubmissionForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function DeclineSubmissionForm($monographId) {
		parent::Form('controllers/grid/submissions/pressEditor/decline.tpl');
		$this->_monographId = (int) $monographId;

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'personalMessage', 'required', 'common.personalMessageRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//

	/**
	 * Get the MonographId
	 * @return int monographId
	 */
	function getMonographId() {
		return $this->_monographId;
	}
	
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
	* Initialize form data
	*/
	function initData(&$args, &$request) {
		$press =& $request->getPress();
		$user =& $request->getUser();
		$monograph =& $this->getMonograph();
		$submitter = $monograph->getUser();
		
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'SUBMISSION_UNSUITABLE');
		$paramArray = array(
			'authorName' => $submitter->getFullName(),
			'pressName' => $press->getLocalizedName(),
			'monographTitle' => $monograph->getLocalizedTitle(),
			'editorialContactSignature' => $user->getContactSignature(),
		);
		$email->assignParams($paramArray);

		$this->_data = array(
			'personalMessage' => $email->getBody()
		);
	}

	/**
	 * Display the form.
	 */
	function display(&$request, $fetch = true) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $this->getMonographId());

		return parent::display($request, $fetch);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('personalMessage'));
	}

	/**
	 * Save submissionContributor
	 */
	function execute() {
		$monograph =& $this->getMonograph();
		$submitter = $monograph->getUser();
		
		// 1. Decline submission FIXME
//		import('classes.submission.seriesEditor.SeriesEditorAction');
//		SeriesEditorAction::recordDecision($monograph, SUBMISSION_EDITOR_DECISION_DECLINE);

		// 2. Send Personal message to author
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'EDITOR_DECISION_ACCEPT');
		$email->setBody($this->getData('personalMessage'));
		$email->addRecipient($submitter->getEmail(), $submitter->getFullName());
		$email->send();
	}
}

?>
