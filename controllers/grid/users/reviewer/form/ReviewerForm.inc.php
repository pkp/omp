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
 * @brief Form for adding a reviewer to a submission
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
		parent::Form('controllers/grid/users/reviewer/form/reviewerForm.tpl');
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
		$monographDao =& DAORegistry::getDAO('MonographDAO');
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

		// Get the review method (open, blind, or double-blind)
		$round = (int) $request->getUserVar('round');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($this->getMonographId(), $reviewerId, $round);
		if(isset($reviewAssignment)) {
			$reviewMethod = $reviewAssignment->getReviewMethod();
		} else $reviewMethod = SUBMISSION_REVIEW_METHOD_BLIND;

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
		$interestDao =& DAORegistry::getDAO('InterestDAO');

		// Get the currently selected reviewer selection type to show the correct tab if we're re-displaying the form
		$selectionType = (int) $request->getUserVar('selectionType');

		$this->_data = array(
			'monographId' => $this->getMonographId(),
			'reviewAssignmentId' => $this->getReviewAssignmentId(),
			'reviewType' => (int) $request->getUserVar('reviewType'),
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
								'reviewerId',
								'personalMessage',
								'responseDueDate',
								'reviewDueDate'));

		// Read different data depending on what tab we're in
		$selectionType = (int) $this->getData('selectionType');
		if($selectionType == REVIEWER_SELECT_CREATE) {
			$this->readUserVars(array('firstname',
								'middlename',
								'lastname',
								'affiliation',
								'interestsKeywords',
								'username',
								'email',
								'sendNotify',
								'userGroupId'));
		} elseif($selectionType == REVIEWER_SELECT_ENROLL) {
			$this->readUserVars(array('userId', 'userGroupId'));
		}
	}

	/**
	 * Need to override validate function -- Implementing FormValidators in constructor won't work because hidden
	 *  form elements (i.e. in other accordion tabs) would throw errors when not filled in
	 * @see lib/pkp/classes/form/Form::validate()
	 */
	function validate() {
		// Enact different validation rules depending on what tab we're in
		$selectionType = $this->getData('selectionType');
		if($selectionType == REVIEWER_SELECT_CREATE) {
			$this->addCheck(new FormValidator($this, 'firstname', 'required', 'user.profile.form.firstNameRequired'));
			$this->addCheck(new FormValidator($this, 'lastname', 'required', 'user.profile.form.lastNameRequired'));
			$this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'user.register.form.usernameExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByUsername'), array(), true));
			$this->addCheck(new FormValidatorAlphaNum($this, 'username', 'required', 'user.register.form.usernameAlphaNumeric'));
			$this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
			$this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.register.form.emailExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'), array(), true));
			$this->addCheck(new FormValidator($this, 'userGroupId', 'required', 'user.profile.form.usergroupRequired'));
		} elseif($selectionType == REVIEWER_SELECT_ENROLL) {
			$this->addCheck(new FormValidator($this, 'userGroupId', 'required', 'user.profile.form.usergroupRequired'));
		} else {
			$this->addCheck(new FormValidator($this, 'reviewerId', 'required', 'editor.review.mustSelect'));
		}
		return parent::validate();
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

		$reviewType = $this->getData('reviewType');
		$round = $this->getData('round');
		$reviewDueDate = $this->getData('reviewDueDate');
		$responseDueDate = $this->getData('responseDueDate');

		$selectionType = (int) $this->getData('selectionType');
		if($selectionType == REVIEWER_SELECT_CREATE) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user = new User();

			$user->setFirstName($this->getData('firstname'));
			$user->setMiddleName($this->getData('middlename'));
			$user->setLastName($this->getData('lastname'));
			$user->setEmail($this->getData('email'));

			$authDao =& DAORegistry::getDAO('AuthSourceDAO');
			$auth =& $authDao->getDefaultPlugin();
			$user->setAuthId($auth?$auth->getAuthId():0);

			$user->setUsername($this->getData('username'));
			$password = Validation::generatePassword();

			if (isset($auth)) {
				$user->setPassword($password);
				// FIXME Check result and handle failures
				$auth->doCreateUser($user);
				$user->setAuthId($auth->authId);
				$user->setPassword(Validation::encryptCredentials($user->getId(), Validation::generatePassword())); // Used for PW reset hash only
			} else {
				$user->setPassword(Validation::encryptCredentials($this->getData('username'), $password));
			}

			$user->setDateRegistered(Core::getCurrentDate());
			$reviewerId = $userDao->insertUser($user);

			// Add reviewer interests to interests table
			$interestDao =& DAORegistry::getDAO('InterestDAO');
			$interests = Request::getUserVar('interestsKeywords');
			$interests = array_map('urldecode', $interests); // The interests are coming in encoded -- Decode them for DB storage
			if (empty($interests)) $interests = array();
			elseif (!is_array($interests)) $interests = array($interests);
			$interestDao->insertInterests($interests, $reviewerId, true);

			// Assign the selected user group ID to the user
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
			$userGroupId = (int) $this->getData('userGroupId');
			$userGroupDao->assignUserToGroup($reviewerId, $userGroupId);

			if ($this->getData('sendNotify')) {
				// Send welcome email to user
				import('classes.mail.MailTemplate');
				$mail = new MailTemplate('REVIEWER_REGISTER');
				$mail->setFrom($press->getSetting('contactEmail'), $press->getSetting('contactName'));
				$mail->assignParams(array('username' => $this->getData('username'), 'password' => $password, 'userFullName' => $user->getFullName()));
				$mail->addRecipient($user->getEmail(), $user->getFullName());
				$mail->send();
			}
		} elseif($selectionType == REVIEWER_SELECT_ENROLL) {
			// Assign a reviewer user group to an existing non-reviewer
			$userId = $this->getData('userId');
			$userGroupId = $this->getData('userGroupId');

			$userGroupId = $this->getData('userGroupId');
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
			$userGroupDao->assignUserToGroup($userId, $userGroupId);
			// Set the reviewerId to the userId to return to the grid
			$reviewerId = $userId;
		} else {
			$reviewerId = $this->getData('reviewerId');
		}

		import('classes.submission.seriesEditor.SeriesEditorAction');
		SeriesEditorAction::addReviewer($submission, $reviewerId, $reviewType, $round, $reviewDueDate, $responseDueDate);

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
