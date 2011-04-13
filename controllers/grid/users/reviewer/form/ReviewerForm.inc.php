<?php

/**
 * @file controllers/grid/users/reviewer/form/ReviewerForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Base Form for adding a reviewer to a submission.
 * N.B. Requires a subclass to implement the "reviewerId" to be added.
 */

import('lib.pkp.classes.form.Form');

class ReviewerForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monograph;

	/** The reviewer associated with the review assignment **/
	var $_reviewAssignmentId;

	/**
	 * Constructor.
	 */
	function ReviewerForm($monograph, $reviewAssignmentId) {
		parent::Form('controllers/grid/users/reviewer/form/defaultReviewerForm.tpl');
		$this->setMonograph($monograph);
		$this->setReviewAssignmentId(empty($reviewAssignmentId)? null: (int) $reviewAssignmentId);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'responseDueDate', 'required', 'editor.review.errorAddingReviewer'));
		$this->addCheck(new FormValidator($this, 'reviewDueDate', 'required', 'editor.review.errorAddingReviewer'));

		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph Id
	 * @return int monographId
	 */
	function getMonographId() {
		$monograph =& $this->getMonograph();
		return $monograph->getId();
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the Monograph
	 * @param $monograph Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}

	/**
	 * Get the Review assignment's Id
	 * @return int reviewerId
	 */
	function getReviewAssignmentId() {
		return $this->_reviewAssignmentId;
	}

	/**
	 * Get the Review assignment's Id
	 * @return int reviewerId
	 */
	function setReviewAssignmentId($reviewAssignmentId) {
		$this->_reviewAssignmentId = $reviewAssignmentId;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated submissionContributor.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$reviewerId = (int) $request->getUserVar('reviewerId');
		$press =& $request->getContext();
		// The reviewer id has been set
		if (!empty($reviewerId)) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			$roleDao =& DAORegistry::getDAO('RoleDAO');

			$user =& $userDao->getUser($reviewerId);
			if ($user && $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_REVIEWER) ) {
				$this->setData('userNameString', sprintf('%s (%s)', $user->getFullname(), $user->getUsername()));
			}
		}

		$round = (int) $request->getUserVar('round');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		// FIXME: Bug #6199
		$reviewType = (int) $request->getUserVar('reviewType');

		// Get the review method (open, blind, or double-blind)
		// FIXME: Bug #6403, Need to be able to specify the review method
		$reviewMethod = SUBMISSION_REVIEW_METHOD_BLIND;

		// Get the response/review due dates or else set defaults
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($this->getMonographId(), $reviewerId, $round, $reviewType);
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
		$interestDao =& DAORegistry::getDAO('InterestDAO');

		// Get the currently selected reviewer selection type to show the correct tab if we're re-displaying the form
		$selectionType = (int) $request->getUserVar('selectionType');

		$this->_data = array(
			'monographId' => $this->getMonographId(),
			'reviewAssignmentId' => $this->getReviewAssignmentId(),
			'reviewType' => $reviewType,
			'reviewMethod' => $reviewMethod,
			'round' => (int) $request->getUserVar('round'),
			'reviewerId' => $reviewerId,
			'personalMessage' => Locale::translate('reviewer.step1.requestBoilerplate'),
			'responseDueDate' => $responseDueDate,
			'reviewDueDate' => $reviewDueDate,
			'existingInterests' => $interestDao->getAllUniqueInterests(),
			'selectionType' => empty($selectionType) ? REVIEWER_SELECT_SEARCH : $selectionType
		);

	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		// Get the reviewer user groups for the create new reviewer/enroll existing user tabs
		$press =& $request->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$reviewerUserGroups =& $userGroupDao->getByRoleId($press->getId(), ROLE_ID_REVIEWER);
		$userGroups = array();
		while($userGroup =& $reviewerUserGroups->next()) {
			$userGroups[$userGroup->getId()] = $userGroup->getLocalizedName();
			unset($userGroup);
		}

		$this->setData('userGroups', $userGroups);
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('selectionType',
								'monographId',
								'reviewType',
								'round',
								'personalMessage',
								'responseDueDate',
								'reviewDueDate'));

		$interests = $this->getData('interestsKeywords');
		if ($interests != null && is_array($interests)) {
			// The interests are coming in encoded -- Decode them for DB storage
			$this->setData('interestsKeywords', array_map('urldecode', $interests));
		}
	}

	/**
	 * Save review assignment
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$submission =& $seriesEditorSubmissionDao->getSeriesEditorSubmission($this->getMonographId());
		$press =& $request->getPress();

		// FIXME: Bug #6199
		$reviewType = $this->getData('reviewType');
		$round = $this->getData('round');
		$reviewDueDate = $this->getData('reviewDueDate');
		$responseDueDate = $this->getData('responseDueDate');
		$reviewerId = (int) $this->getData('reviewerId');

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->addReviewer($request, $submission, $reviewerId, $reviewType, $round, $reviewDueDate, $responseDueDate);

		// Get the reviewAssignment object now that it has been added
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($submission->getId(), $reviewerId, $round, $reviewType);
		$reviewAssignment->setDateNotified(Core::getCurrentDate());
		$reviewAssignment->setCancelled(0);
		$reviewAssignment->stampModified();
		$reviewAssignmentDao->updateObject($reviewAssignment);

		// Update the review round status if this is the first reviewer added
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$currentReviewRound =& $reviewRoundDao->build($this->getMonographId(), $submission->getCurrentReviewType(), $submission->getCurrentRound());
		if ($currentReviewRound->getStatus() == REVIEW_ROUND_STATUS_PENDING_REVIEWERS) {
			$currentReviewRound->setStatus(REVIEW_ROUND_STATUS_PENDING_REVIEWS);
			$reviewRoundDao->updateObject($currentReviewRound);
		}

		return $reviewAssignment;
	}
}

?>
