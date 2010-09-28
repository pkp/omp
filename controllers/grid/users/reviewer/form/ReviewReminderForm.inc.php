<?php

/**
 * @file controllers/grid/users/reviewer/form/ReviewReminderForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewReminderForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Form for sending a review reminder to a reviewer
 */

import('lib.pkp.classes.form.Form');

class ReviewReminderForm extends Form {
	/** The review assignment associated with the reviewer **/
	var $_reviewAssignmentId;

	/**
	 * Constructor.
	 */
	function ReviewReminderForm($reviewAssignmentId) {
		parent::Form('controllers/grid/users/reviewer/form/reviewReminderForm.tpl');
		$this->_reviewAssignmentId = (int) $reviewAssignmentId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Review assignment's Id
	 * @return int reviewerId
	 */
	function getReviewAssignmentId() {
		return $this->_reviewAssignmentId;
	}

	/**
	 * Get the Monograph
	 * @return object monograph
	 */
	function getReviewAssignment() {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		return $reviewAssignmentDao->getById($this->_reviewAssignmentId);
	}


	//
	// Template methods from Form
	//
	/**
	 * Initialize form data from the associated submissionContributor.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $request->getUser();

		$reviewAssignment =& $this->getReviewAssignment();
		$reviewerId = $reviewAssignment->getReviewerId();
		$reviewer =& $userDao->getUser($reviewerId);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($reviewAssignment->getMonographId());

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'REVIEW_REMIND');

		// Format the review due date
		$reviewDueDate = strtotime($reviewAssignment->getDateDue());
		$dateFormatShort = Config::getVar('general', 'date_format_short');
		if ($reviewDueDate == -1) $reviewDueDate = $dateFormatShort; // Default to something human-readable if no date specified
		else $reviewDueDate = strftime($dateFormatShort, $reviewDueDate);

		$paramArray = array(
			'reviewerName' => $reviewer->getFullName(),
			'reviewDueDate' => $reviewDueDate,
			'editorialContactSignature' => $user->getContactSignature(),
			'passwordResetUrl' => Request::url(null, 'login', 'resetPassword', $reviewer->getUsername(), array('confirm' => Validation::generatePasswordResetHash($reviewer->getId()))),
			'submissionReviewUrl' => Request::url(null, 'reviewer', 'submission', $reviewAssignment->getId())
		);
		$email->assignParams($paramArray);

		$this->_data = array(
			'monographId' => $monograph->getId(),
			'reviewAssignmentId' => $this->getReviewAssignmentId(),
			'reviewAssignment' => $reviewAssignment,
			'reviewerName' => $reviewer->getFullName(),
			'message' => $email->getBody()
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('message'));

	}

	/**
	 * Save review assignment
	 */
	function execute($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		$reviewAssignment =& $this->getReviewAssignment();
		$reviewerId = $reviewAssignment->getReviewerId();
		$reviewer =& $userDao->getUser($reviewerId);
		$monograph =& $monographDao->getMonograph($reviewAssignment->getMonographId());

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'REVIEW_REMIND');

		$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
		$email->setBody($this->getData('message'));
		$email->send();
	}
}

?>
