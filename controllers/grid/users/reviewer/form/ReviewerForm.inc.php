<?php

/**
 * @file controllers/grid/users/reviewer/form/ReviewerForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

	/** An array of actions for the other reviewer forms */
	var $_reviewerFormActions;

	/**
	 * Constructor.
	 */
	function ReviewerForm($monograph) {
		parent::Form('controllers/grid/users/reviewer/form/defaultReviewerForm.tpl');
		$this->setMonograph($monograph);

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
	 * Set a reviewer form action
	 * @param $action LinkAction
	 */
	function setReviewerFormAction($action) {
		$this->_reviewerFormActions[$action->getId()] =& $action;
	}

	/**
	 * Get all of the reviewer form actions
	 * @return array
	 */
	function getReviewerFormActions() {
		return $this->_reviewerFormActions;
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
		$stageId = (int) $request->getUserVar('stageId');

		// Get the review method (open, blind, or double-blind)
		// FIXME: Bug #6403, Need to be able to specify the review method
		$reviewMethod = SUBMISSION_REVIEW_METHOD_DOUBLEBLIND;

		// Get the response/review due dates or else set defaults
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($this->getMonographId(), $reviewerId, $round, $stageId);
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

		$this->setData('monographId', $this->getMonographId());
		$this->setData('stageId', $stageId);
		$this->setData('reviewMethod', $reviewMethod);
		$this->setData('round', (int) $request->getUserVar('round'));
		$this->setData('reviewerId', $reviewerId);
		$this->setData('personalMessage', Locale::translate('reviewer.step1.requestBoilerplate'));
		$this->setData('responseDueDate', $responseDueDate);
		$this->setData('reviewDueDate', $reviewDueDate);
		$this->setData('existingInterests', $interestDao->getAllUniqueInterests());
		$this->setData('selectionType', $selectionType);
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewerActions', $this->getReviewerFormActions());

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
		$this->readUserVars(array(
			'selectionType',
			'monographId',
			'stageId',
			'round',
			'personalMessage',
			'responseDueDate',
			'reviewDueDate'
		));

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
		$stageId = $this->getData('stageId');
		$round = $this->getData('round');
		$reviewDueDate = $this->getData('reviewDueDate');
		$responseDueDate = $this->getData('responseDueDate');
		$reviewerId = (int) $this->getData('reviewerId');

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->addReviewer($request, $submission, $reviewerId, $stageId, $round, $reviewDueDate, $responseDueDate);

		// Get the reviewAssignment object now that it has been added
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($submission->getId(), $reviewerId, $round, $stageId);
		$reviewAssignment->setDateNotified(Core::getCurrentDate());
		$reviewAssignment->setCancelled(0);
		$reviewAssignment->stampModified();
		$reviewAssignmentDao->updateObject($reviewAssignment);

		// Update the review round status if this is the first reviewer added
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$currentReviewRound =& $reviewRoundDao->build($this->getMonographId(), $submission->getStageId(), $submission->getCurrentRound());
		if ($currentReviewRound->getStatus() == REVIEW_ROUND_STATUS_PENDING_REVIEWERS) {
			$currentReviewRound->setStatus(REVIEW_ROUND_STATUS_PENDING_REVIEWS);
			$reviewRoundDao->updateObject($currentReviewRound);
		}

		// Notify the reviewer via email
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($submission, 'REVIEW_REQUEST', null, null, null, false);

		if ($mail->isEnabled()) {
			$userDao = & DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
			$reviewer =& $userDao->getUser($reviewerId);
			$user = $submission->getUser();
			$mail->addRecipient($reviewer->getEmail(), $reviewer->getFullName());

			$paramArray = array(
				'reviewerName' => $reviewer->getFullName(),
				'messageToReviewer' => $this->getData('personalMessage'),
				'responseDueDate' => $responseDueDate,
				'reviewDueDate' => $reviewDueDate,
				'editorialContactSignature' => $user->getContactSignature()
			);
			$mail->assignParams($paramArray);
			$mail->send($request);
		}

		return $reviewAssignment;
	}
}

?>
