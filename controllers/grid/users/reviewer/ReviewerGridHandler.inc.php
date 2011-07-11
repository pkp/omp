<?php

/**
 * @file controllers/grid/users/reviewer/ReviewerGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGridHandler
 * @ingroup controllers_grid_users_reviewer
 *
 * @brief Handle reviewer grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import reviewer grid specific classes
import('controllers.grid.users.reviewer.ReviewerGridCellProvider');
import('controllers.grid.users.reviewer.ReviewerGridRow');

// Reviewer selection types
define('REVIEWER_SELECT_SEARCH_BY_NAME',		0x00000001);
define('REVIEWER_SELECT_ADVANCED_SEARCH',		0x00000002);
define('REVIEWER_SELECT_CREATE',				0x00000003);
define('REVIEWER_SELECT_ENROLL_EXISTING',		0x00000004);

class ReviewerGridHandler extends GridHandler {

	/** @var Monograph */
	var $_monograph;

	/** @var integer */
	var $_reviewType;

	/** @var integer */
	var $_round;


	/**
	 * Constructor
	 */
	function ReviewerGridHandler() {
		parent::GridHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array(
				'fetchGrid', 'fetchRow', 'addReviewer', 'showReviewerForm', 'editReviewer', 'updateReviewer', 'deleteReviewer',
				'getReviewersNotAssignedToMonograph', 'getUsersNotAssignedAsReviewers', 'readReview', 'reviewRead', 'thankReviewer',
				'createReviewer', 'editReminder', 'sendReminder'
			)
		);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * Get the review type.
	 * @return integer
	 */
	function getReviewType() {
		return $this->_reviewType;
	}

	/**
	 * Get the review round number.
	 * @return integer
	 */
	function getRound() {
		return $this->_round;
	}


	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		// FIXME: Need to authorize review type/round. This is just a temporary
		// workaround until we get those variables in the authorized context, see #6200.
		$reviewType = $request->getUserVar('reviewType');
		$round = $request->getUserVar('round');
		// Not all actions need a reviewType and round. Some work off the reviewAssignment which has the type and round.
		//assert(!empty($reviewType) && !empty($round));
		$this->_reviewType = (int)$reviewType;
		$this->_round = (int)$round;

		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		// FIXME: #6244# HARDCODED INTERNAL_REVIEW
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_INTERNAL_REVIEW));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_EDITOR));

		// Basic grid configuration
		$this->setTitle('user.role.reviewers');

		// Grid actions
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'addReviewer',
				new AjaxModal(
					$router->url($request, null, null, 'addReviewer', null, $this->getRequestArgs()),
					__('editor.monograph.addReviewer')
					),
				__('editor.monograph.addReviewer')
				)
			);

		// Columns
		$cellProvider = new ReviewerGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'user.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		$this->addColumn(
			new GridColumn(
				'editor',
				'user.role.editor',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);

		// Add a column for the assigned reviewer.
		$this->addColumn(
			new GridColumn(
				'reviewer',
				'user.role.reviewer',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return ReviewerGridRow
	 */
	function &getRowInstance() {
		$row = new ReviewerGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
			'reviewType' => $this->getReviewType(),
			'round' => $this->getRound()
		);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		// Get the existing review assignments for this monograph
		$monograph =& $this->getMonograph(); /* @var $monograph SeriesEditorSubmission */
		return $monograph->getReviewAssignments($this->getReviewType(), $this->getRound());
	}


	//
	// Public actions
	//
	/**
	 * An action to manually add a new reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addReviewer($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		//FIXME: #6200. see other methods in this file.
		$templateMgr->assign('reviewType', $this->getReviewType());
		$templateMgr->assign('round', $this->getRound());
		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());
		return $templateMgr->fetchJson('controllers/grid/users/reviewer/form/addReviewerForm.tpl');
	}

	/**
	 * Add a reviewer that already exists
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function showReviewerForm($args, &$request) {
		// Identify the review assignment being updated.
		$reviewAssignmentId = (int)$request->getUserVar('reviewAssignmentId');

		$selectionType = $request->getUserVar('selectionType');
		assert(!empty($selectionType));
		$formClassName = $this->_getReviewerFormClassName($selectionType);

		// Form handling.
		import('controllers.grid.users.reviewer.form.' . $formClassName );
		$reviewerForm = new $formClassName($this->getMonograph(), $reviewAssignmentId);
		$reviewerForm->initData($args, $request);

		$json = new JSONMessage(true, $reviewerForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editReviewer($args, &$request) {
		// Identify the review assignment being updated.
		$reviewAssignmentId = (int)$request->getUserVar('reviewAssignmentId');

		// Form handling.
		import('controllers.grid.users.reviewer.form.ReviewerForm');
		$reviewerForm = new ReviewerForm($this->getMonograph(), $reviewAssignmentId);
		$reviewerForm->initData($args, $request);

		$json = new JSONMessage(true, $reviewerForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateReviewer($args, &$request) {
		// Identify the review assignment being updated.
		$reviewAssignmentId = (int) $request->getUserVar('reviewAssignmentId');

		$selectionType = $request->getUserVar('selectionType');
		$formClassName = $this->_getReviewerFormClassName($selectionType);

		// Form handling
		import('controllers.grid.users.reviewer.form.' . $formClassName );
		$reviewerForm = new $formClassName($this->getMonograph(), $reviewAssignmentId);
		$reviewerForm->readInputData();
		if ($reviewerForm->validate()) {
			$reviewAssignment =& $reviewerForm->execute($args, $request);

			return DAO::getDataChangedEvent($reviewAssignment->getId());
		} else {
			// There was an error, redisplay the form
			$json = new JSONMessage(false, $reviewerForm->fetch($request));
		}
		return $json->getString();
	}

	/**
	 * Delete a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteReviewer($args, &$request) {
		// Delete the review assignment.
		// NB: SeriesEditorAction::clearReview() will check that this review
		// id is actually attached to the monograph so no need for further
		// validation here.
		$monograph =& $this->getMonograph();
		$reviewId = (int) $request->getUserVar('reviewId');
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$result = $seriesEditorAction->clearReview($request, $monograph->getId(), $reviewId);

		// Render the result.
		if ($result) {
			return DAO::getDataChangedEvent($reviewId);
		} else {
			$json = new JSONMessage(false, Locale::translate('editor.review.errorDeletingReviewer'));
			return $json->getString();
		}
	}


	/**
	 * Get potential reviewers for editor's reviewer selection autocomplete.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function getReviewersNotAssignedToMonograph($args, &$request) {
		$press =& $request->getPress();
		$monograph =& $this->getMonograph();
		$round = (int) $request->getUserVar('round');
		$term = $request->getUserVar('term');

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO'); /* @var $seriesEditorSubmissionDao SeriesEditorSubmissionDAO */
		$reviewers =& $seriesEditorSubmissionDao->getReviewersNotAssignedToMonograph($press->getId(), $monograph->getId(), $round, $term);

		$reviewerList = array();
		while($reviewer =& $reviewers->next()) {
			$reviewerList[] = array('label' => $reviewer->getFullName(), 'value' => $reviewer->getId());
			unset($reviewer);
		}

		$json = new JSONMessage(true, $reviewerList);
		echo $json->getString();
	}

	/**
	 * Get a list of all non-reviewer users in the system to populate the reviewer role assignment autocomplete.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function getUsersNotAssignedAsReviewers($args, &$request) {
		$press =& $request->getPress();
		$term = $request->getUserVar('term');

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$users =& $userGroupDao->getUsersNotInRole($press->getId(), ROLE_ID_REVIEWER, $term);

		$userList = array();
		while ($user =& $users->next()) {
			$userList[] = array('label' => $user->getFullName(), 'value' => $user->getId());
			unset($user);
		}

		$json = new JSONMessage(true, $userList);
		echo $json->getString();
	}

	/**
	 * Open a modal to read the reviewer's review and
	 * download any files they may have uploaded
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string serialized JSON object
	 */
	function readReview($args, &$request) {
		$templateMgr =& TemplateManager::getManager();

		// Retrieve monograph.
		$templateMgr->assign_by_ref('monograph', $this->getMonograph());

		// Retrieve review assignment.
		$reviewAssignment =& $this->_retrieveReviewAssignment($request);
		$templateMgr->assign_by_ref('reviewAssignment', $reviewAssignment);

		// Retrieve reviewer comment.
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$monographComments =& $monographCommentDao->getReviewerCommentsByReviewerId($reviewAssignment->getReviewerId(), $reviewAssignment->getSubmissionId(), $reviewAssignment->getId());
		$templateMgr->assign_by_ref('reviewerComment', $monographComments[0]);

		// Render the response.
		return $templateMgr->fetchJson('controllers/grid/users/reviewer/readReview.tpl');
	}

	/**
	 * Mark the review as read and trigger a rewrite of the row.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string serialized JSON object
	 */
	function reviewRead($args, &$request) {
		// Retrieve review assignment.
		$reviewAssignment =& $this->_retrieveReviewAssignment($request);

		// Mark the latest read date of the review by the editor.
		$user =& $request->getUser();
		$viewsDao =& DAORegistry::getDAO('ViewsDAO');
		$viewsDao->recordView(ASSOC_TYPE_REVIEW_RESPONSE, $reviewAssignment->getId(), $user->getId());
		return DAO::getDataChangedEvent($reviewAssignment->getId());
	}


	/**
	 * Send the acknowledgement email and trigger a row refresh action.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string serialized JSON object
	 */
	function thankReviewer($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');

		// Retrieve review assignment.
		$reviewAssignment =& $this->_retrieveReviewAssignment($request);
		// Retrieve the monograph.
		$monograph =& $monographDao->getMonograph($reviewAssignment->getSubmissionId());
		// Retrieve the reviewer user.
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		// Retrieve the current user.
		$user =& $request->getUser();

		assert(isset($monograph) && isset($reviewer));

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'REVIEW_ACK');

		if (!$email->isEnabled()) {
			HookRegistry::call('SeriesEditorAction::thankReviewer', array(&$monograph, &$reviewAssignment, &$email));

			// Personalize the email.
			$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
			$paramArray = array(
				'reviewerName' => $reviewer->getFullName(),
				'editorialContactSignature' => $user->getContactSignature()
			);
			$email->assignParams($paramArray);

			// Send the email.
			$email->send($request);
		}
		// Mark the review assignment as acknowledged.
		$reviewAssignment->setDateAcknowledged(Core::getCurrentDate());
		$reviewAssignment->stampModified();
		$reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

		return DAO::getDataChangedEvent($reviewAssignment->getId());
	}

	/**
	 * Displays a modal to allow the editor to enter a message to send to the reviewer as a reminder
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editReminder($args, &$request) {
		// Identify the review assignment being updated.
		$reviewAssignment =& $this->_retrieveReviewAssignment($request);

		// Initialize form.
		import('controllers.grid.users.reviewer.form.ReviewReminderForm');
		$reviewReminderForm = new ReviewReminderForm($reviewAssignment->getId());
		$reviewReminderForm->initData($args, $request);

		// Render form.
		$json = new JSONMessage(true, $reviewReminderForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Send the reviewer reminder and close the modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendReminder($args, &$request) {
		// Identify the review assignment being updated
 		$reviewAssignmentId = (int) $request->getUserVar('reviewAssignmentId');

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewReminderForm');
		$reviewReminderForm = new ReviewReminderForm($reviewAssignmentId);
		$reviewReminderForm->readInputData();
		if ($reviewReminderForm->validate()) {
			$reviewReminderForm->execute($args, $request);
			$json = new JSONMessage(true);
		} else {
			$json = new JSONMessage(false, Locale::translate('editor.review.reminderError'));
		}
		return $json->getString();
	}


	//
	// Private helper methods
	//
	/**
	 * Retrieves the review assignment given as a
	 * review id in the request and checks it against
	 * the authorized monograph.
	 * @param $request Request
	 * @return ReviewAssignment or null if the review id is invalid.
	 */
	function &_retrieveReviewAssignment(&$request) {
		// Retrieve review assignment.
		$reviewId = (int)$request->getUserVar('reviewId');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		if (!is_a($reviewAssignment, 'ReviewAssignment')) fatalError('Invalid review assignment id!');

		// Assert that the review assignment actually belongs to the
		// authorized monograph.
		$monograph =& $this->getMonograph();
		if ($reviewAssignment->getSubmissionId() != $monograph->getId()) fatalError('Invalid review assignment!');
		return $reviewAssignment;
	}

	/**
	 * Get the name of ReviewerForm class for the current selection type.
	 * @param $selectionType String (const)
	 * @return FormClassName String
	 */
	function _getReviewerFormClassName($selectionType) {
		switch ($selectionType) {
			case REVIEWER_SELECT_SEARCH_BY_NAME:
				return 'SearchByNameReviewerForm';
			case REVIEWER_SELECT_ADVANCED_SEARCH:
				return 'AdvancedSearchReviewerForm';
			case REVIEWER_SELECT_CREATE:
				return 'CreateReviewerForm';
			case REVIEWER_SELECT_ENROLL_EXISTING:
				return 'EnrollExistingReviewerForm';
		}
	}
}

?>
