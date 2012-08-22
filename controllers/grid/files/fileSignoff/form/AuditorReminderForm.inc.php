<?php

/**
 * @file controllers/grid/users/reviewer/form/AuditorReminderForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuditorReminderForm
 * @ingroup controllers_grid_files_fileSignoff_form
 *
 * @brief Form for sending a singoff reminder to an auditor.
 */

import('lib.pkp.classes.form.Form');

class AuditorReminderForm extends Form {
	/** The signoff associated with the auditor */
	var $_signoff;

	/** The monograph id */
	var $_monographId;

	/** The current stage id */
	var $_stageId;

	/** The publication format id, if any */
	var $_publicationFormatId;

	/**
	 * Constructor.
	 */
	function AuditorReminderForm(&$signoff, $monographId, $stageId, $publicationFormatId = null) {
		parent::Form('controllers/grid/files/fileSignoff/form/auditorReminderForm.tpl');
		$this->_signoff =& $signoff;
		$this->_monographId = $monographId;
		$this->_stageId = $stageId;
		$this->_publicationFormatId = $publicationFormatId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the signoff
	 * @return Signoff
	 */
	function &getSignoff() {
		return $this->_signoff;
	}

	/**
	 * Get the monograph id.
	 * @return int
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	/**
	 * Get the stage id.
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the publication format id.
	 * @return int
	 */
	function getPublicationFormatId() {
		return $this->_publicationFormatId;
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated author.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $request->getUser();
		$press =& $request->getPress();

		$signoff =& $this->getSignoff();
		$auditorId = $signoff->getUserId();
		$auditor =& $userDao->getById($auditorId);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getById($this->getMonographId());

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'REVIEW_REMIND');

		// Format the review due date
		$signoffDueDate = strtotime($signoff->getDateUnderway());
		$dateFormatShort = Config::getVar('general', 'date_format_short');
		if ($signoffDueDate == -1) $signoffDueDate = $dateFormatShort; // Default to something human-readable if no date specified
		else $signoffDueDate = strftime($dateFormatShort, $signoffDueDate);

		import('controllers.grid.submissions.SubmissionsListGridCellProvider');
		list($page, $operation) = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $monograph, $auditor->getId());

		$dispatcher =& $request->getDispatcher();
		$auditUrl = $dispatcher->url($request, ROUTE_PAGE, null, $page, $operation, array('monographId' => $monograph->getId()));

		$paramArray = array(
			'auditorName' => $auditor->getFullName(),
			'signoffDueDate' => $signoffDueDate,
			'editorialContactSignature' => $user->getContactSignature(),
			'auditorUserName' => $auditor->getUsername(),
			'passwordResetUrl' => $dispatcher->url($request, ROUTE_PAGE, null, 'login', 'resetPassword', $auditor->getUsername(), array('confirm' => Validation::generatePasswordResetHash($auditor->getId()))),
			'submissionAuditUrl' => $auditUrl
		);
		$email->assignParams($paramArray);

		$this->setData('monographId', $monograph->getId());
		$this->setData('stageId', $this->getStageId());
		$this->setData('signoffId', $signoff->getId());
		$this->setData('publicationFormatId', $this->getPublicationFormatId());
		$this->setData('signoff', $signoff);
		$this->setData('auditorName', $auditor->getFullName());
		$this->setData('message', $email->getBody() . "\n" . $press->getSetting('emailSignature'));
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('message'));

	}

	/**
	 * Save review assignment
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		$signoff =& $this->getSignoff();
		$auditorId = $signoff->getUserId();
		$auditor =& $userDao->getById($auditorId);
		$monograph =& $monographDao->getById($this->getMonographId());

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'REVIEW_REMIND', null, null, null, false);

		$email->addRecipient($auditor->getEmail(), $auditor->getFullName());
		$email->setBody($this->getData('message'));
		$email->send($request);
	}
}

?>
