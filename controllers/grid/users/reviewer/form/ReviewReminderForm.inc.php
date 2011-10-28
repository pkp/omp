<?php

/**
 * @file controllers/grid/users/reviewer/form/ReviewReminderForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	var $_reviewAssignment;

	/**
	 * Constructor.
	 */
	function ReviewReminderForm(&$reviewAssignment) {
		parent::Form('controllers/grid/users/reviewer/form/reviewReminderForm.tpl');
		$this->_reviewAssignment =& $reviewAssignment;

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph
	 * @return ReviewAssignment
	 */
	function &getReviewAssignment() {
		return $this->_reviewAssignment;
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

		$reviewAssignment =& $this->getReviewAssignment();
		$reviewerId = $reviewAssignment->getReviewerId();
		$reviewer =& $userDao->getUser($reviewerId);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($reviewAssignment->getSubmissionId());

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'REVIEW_REMIND');

		// Format the review due date
		$reviewDueDate = strtotime($reviewAssignment->getDateDue());
		$dateFormatShort = Config::getVar('general', 'date_format_short');
		if ($reviewDueDate == -1) $reviewDueDate = $dateFormatShort; // Default to something human-readable if no date specified
		else $reviewDueDate = strftime($dateFormatShort, $reviewDueDate);

		$dispatcher =& $request->getDispatcher();
		$paramArray = array(
			'reviewerName' => $reviewer->getFullName(),
			'reviewDueDate' => $reviewDueDate,
			'editorialContactSignature' => $user->getContactSignature(),
			'passwordResetUrl' => $dispatcher->url($request, ROUTER_PAGE, null, 'login', 'resetPassword', $reviewer->getUsername(), array('confirm' => Validation::generatePasswordResetHash($reviewer->getId()))),
			'submissionReviewUrl' => $dispatcher->url($request, ROUTER_PAGE, null, 'reviewer', 'submission', null, array('monographId' => $reviewAssignment->getSubmissionId()))
		);
		$email->assignParams($paramArray);

		$this->setData('monographId', $monograph->getId());
		$this->setData('stageId', $reviewAssignment->getStageId());
		$this->setData('reviewAssignmentId', $reviewAssignment->getId());
		$this->setData('reviewAssignment', $reviewAssignment);
		$this->setData('reviewerName', $reviewer->getFullName());
		$this->setData('message', $email->getBody());
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

		$reviewAssignment =& $this->getReviewAssignment();
		$reviewerId = $reviewAssignment->getReviewerId();
		$reviewer =& $userDao->getUser($reviewerId);
		$monograph =& $monographDao->getMonograph($reviewAssignment->getSubmissionId());

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'REVIEW_REMIND');

		$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
		$email->setBody($this->getData('message'));
		$email->send($request);
	}
}

?>
