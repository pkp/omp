<?php

/**
 * @file controllers/grid/users/reviewer/form/ReviewerForm.inc.php
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

class ReviewerForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monographId;

	/** The reviewer associated with the review assignment **/
	var $_reviewAssignmentId;

	/**
	 * Constructor.
	 */
	function ReviewerForm($monographId, $reviewAssignmentId) {
		parent::Form('controllers/grid/users/reviewer/form/reviewerForm.tpl');
		$this->_monographId = (int) $monographId;
		$this->_reviewAssignmentId = (int) $reviewAssignmentId;

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'reviewerId', 'required', 'author.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidator($this, 'responseDueDate', 'required', 'author.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidator($this, 'reviewDueDate', 'required', 'author.submit.form.authorRequiredFields'));

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

	/**
	 * Get the Review assignment's Id
	 * @return int reviewerId
	 */
	function getReviewAssignmentId() {
		return $this->_reviewAssignmentId;
	}

	//
	// Template methods from Form
	//
	/**
	* Initialize form data from the associated submissionContributor.
	* @param $submissionContributor Reviewer
	*/
	function initData(&$args, &$request) {
		$reviewerId = $request->getUserVar('reviewerId');
		$press =& $request->getContext();
		// The reviewer id has been set
		if ( is_numeric($reviewerId) ) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			$roleDao =& DAORegistry::getDAO('RoleDAO');

			$user =& $userDao->getUser($reviewerId);
			if ($user && $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_REVIEWER) ) {
				$this->setData('userNameString', sprintf('%s (%s)', $user->getFullname(), $user->getUsername()));
			}
		}
		
		// Get the review method (open, blind, or double-blind)
		$round = (int) $request->getUserVar('round');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($this->getMonographId(), $reviewerId, $round);
		if(isset($reviewAssignment)) {
			$reviewMethod = $reviewAssignment->getReviewMethod();
		} else $reviewMethod = SUBMISSION_REVIEW_METHOD_BLIND;

		/* Load in the email to be used as the personal message
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($this->getMonograph(), 'REVIEW_REQUEST');
		$user =& $request->getUser();
		$paramArray = array(
			'editorialContactSignature' => $user->getContactSignature(),
		);
		$email->assignParams($paramArray);
		*/
		
		// Get the response/review due dates or else set defaults
		if (isset($reviewAssignment) && $reviewAssignment->getDueDate() != null) {
			$reviewDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime($reviewAssignment->getDueDate()));
		} else {
			$numWeeks = max((int) $press->getSetting('numWeeksPerReview'), 2);
			$reviewDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime('+' . $numWeeks . ' week'));
		}
		if (isset($reviewAssignment) && $reviewAssignment->getResponseDueDate() != null) {
			$responseDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime($reviewAssignment->getResponseDueDate()));
		} else {
			$numWeeks = max((int) $press->getSetting('numWeeksPerResponse'), 2);
			$responseDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime('+' . $numWeeks . ' week'));
		}
		

		$this->_data = array(
			'monographId' => $this->getMonographId(),
			'reviewAssignmentId' => $this->getReviewAssignmentId(),
			'reviewType' => (int) $request->getUserVar('reviewType'),
			'reviewMethod' => $reviewMethod,
			'round' => (int) $request->getUserVar('round'),
			'reviewerId' => $reviewerId,
			'personalMessage' => Locale::translate('reviewer.step1.requestBoilerplate'),
			'responseDueDate' => $responseDueDate,
			'reviewDueDate' => $reviewDueDate
		);

	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('monographId',
								'reviewType',
								'round',
								'reviewerId',
								'personalMessage',
								'responseDueDate',
								'reviewDueDate'));
	}

	/**
	 * Save review assignment
	 */
	function execute(&$args, &$request) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$submission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($this->getMonographId());
		$reviewerId = $this->getData('reviewerId');
		$reviewType = $this->getData('reviewType');
		$round = $this->getData('round');
		$reviewDueDate = $this->getData('reviewDueDate');
		$responseDueDate = $this->getData('responseDueDate');

		import('classes.submission.seriesEditor.SeriesEditorAction');
		SeriesEditorAction::addReviewer($submission, $reviewerId, $reviewType, $round, $reviewDueDate, $responseDueDate);

		// Get the reviewAssignment object now that it has been added
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($submission->getId(), $reviewerId, $round, $reviewType);
		$reviewAssignment->setDateNotified(Core::getCurrentDate());
		$reviewAssignment->setCancelled(0);
		$reviewAssignment->stampModified();
		$reviewAssignmentDao->updateObject($reviewAssignment);
		
		return $reviewAssignment;
	}
}

?>
