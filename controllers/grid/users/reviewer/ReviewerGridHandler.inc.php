<?php

/**
 * @file controllers/grid/users/reviewer/ReviewerGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
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
	var $_stageId;


	/**
	 * Constructor
	 */
	function ReviewerGridHandler() {
		parent::GridHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT),
			array_merge($this->_getReviewAssignmentOps(), $this->_getReviewRoundOps())
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
	 * Get the review stage id.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get review round object.
	 * @return ReviewRound
	 */
	function &getReviewRound() {
		$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
		if (is_a($reviewRound, 'ReviewRound')) {
			return $reviewRound;
		} else {
			$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);
			$reviewRoundId = $reviewAssignment->getReviewRoundId();
			$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
			return $reviewRoundDao->getReviewRoundById($reviewRoundId);
		}
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
		$stageId = $request->getUserVar('stageId'); // This is being validated in OmpWorkflowStageAccessPolicy

		// Not all actions need a stageId. Some work off the reviewAssignment which has the type and round.
		$this->_stageId = (int)$stageId;

		// Get the stage access policy
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$ompWorkflowStageAccessPolicy = new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId);

		// Add policy to ensure there is a review round id.
		import('classes.security.authorization.internal.ReviewRoundRequiredPolicy');
		$ompWorkflowStageAccessPolicy->addPolicy(new ReviewRoundRequiredPolicy($request, $args, 'reviewRoundId', $this->_getReviewRoundOps()));

		// Add policy to ensure there is a review assignment for certain operations.
		import('classes.security.authorization.internal.ReviewAssignmentRequiredPolicy');
		$ompWorkflowStageAccessPolicy->addPolicy(new ReviewAssignmentRequiredPolicy($request, $args, 'reviewAssignmentId', $this->_getReviewAssignmentOps()));
		$this->addPolicy($ompWorkflowStageAccessPolicy);

		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_MANAGER,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_EDITOR
		);

		// Basic grid configuration
		$this->setTitle('user.role.reviewers');

		// Grid actions
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$router =& $request->getRouter();
		$actionArgs = array_merge($this->getRequestArgs(), array('selectionType' => REVIEWER_SELECT_SEARCH_BY_NAME));
		$this->addAction(
			new LinkAction(
				'addReviewer',
				new AjaxModal(
					$router->url($request, null, null, 'showReviewerForm', null, $actionArgs),
					__('editor.monograph.addReviewer'),
					'modal_add_user'
					),
				__('editor.monograph.addReviewer'),
				'add_user'
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
				$cellProvider,
				array('width' => 60)
			)
		);

		$this->addColumn(
			new GridColumn(
				'editor',
				'user.role.pressEditor',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider,
				array('myUserGroup' => true)
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
		$reviewRound =& $this->getReviewRound();
		return array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->getStageId(),
			'reviewRoundId' => $reviewRound->getId()
		);
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		// Get the existing review assignments for this monograph
		$monograph =& $this->getMonograph(); /* @var $monograph SeriesEditorSubmission */
		$reviewRound =& $this->getReviewRound();
		return $monograph->getReviewAssignments($this->getStageId(), $reviewRound->getRound());
	}


	//
	// Public actions
	//
	/**
	 * Add a reviewer.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function showReviewerForm($args, &$request) {
		$json = new JSONMessage(true, $this->_fetchReviewerForm($args, $request));
		return $json->getString();
	}

	/**
	 * Load the contents of the reviewer form
	 * @param $args array
	 * @param $request Request
	 * @return string JSON
	*/
	function reloadReviewerForm($args, &$request) {
		$json = new JSONMessage(true);
		$json->setEvent('refreshForm', $this->_fetchReviewerForm($args, $request));
		return $json->getString();
	}

	/**
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editReviewer($args, &$request) {
		// Form handling.
		import('controllers.grid.users.reviewer.form.ReviewerForm');
		$reviewerForm = new ReviewerForm($this->getMonograph());
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
		$selectionType = $request->getUserVar('selectionType');
		$formClassName = $this->_getReviewerFormClassName($selectionType);

		// Form handling
		import('controllers.grid.users.reviewer.form.' . $formClassName );
		$reviewerForm = new $formClassName($this->getMonograph(), $this->getReviewRound());
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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$result = $seriesEditorAction->clearReview($request, $monograph->getId(), $reviewAssignment->getId());

		// Render the result.
		if ($result) {
			return DAO::getDataChangedEvent($reviewAssignment->getId());
		} else {
			$json = new JSONMessage(false, __('editor.review.errorDeletingReviewer'));
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
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$reviewRound =& $this->getReviewRound();
		$round = $reviewRound->getRound();
		$term = $request->getUserVar('term');

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO'); /* @var $seriesEditorSubmissionDao SeriesEditorSubmissionDAO */
		$reviewers =& $seriesEditorSubmissionDao->getReviewersNotAssignedToMonograph($press->getId(), $monograph->getId(), $stageId, $round, $term);

		$reviewerList = array();
		while($reviewer =& $reviewers->next()) {
			$reviewerList[] = array('label' => $reviewer->getFullName(), 'value' => $reviewer->getId());
			unset($reviewer);
		}

		$json = new JSONMessage(true, $reviewerList);
		return $json->getString();
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
		return $json->getString();
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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);
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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);

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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);
		// Retrieve the monograph.
		$monograph =& $monographDao->getById($reviewAssignment->getSubmissionId());
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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);

		// Initialize form.
		import('controllers.grid.users.reviewer.form.ReviewReminderForm');
		$reviewReminderForm = new ReviewReminderForm($reviewAssignment);
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
		$reviewAssignment =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ASSIGNMENT);

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewReminderForm');
		$reviewReminderForm = new ReviewReminderForm($reviewAssignment);
		$reviewReminderForm->readInputData();
		if ($reviewReminderForm->validate()) {
			$reviewReminderForm->execute($args, $request);
			$json = new JSONMessage(true);
		} else {
			$json = new JSONMessage(false, __('editor.review.reminderError'));
		}
		return $json->getString();
	}


	//
	// Private helper methods
	//
	/**
	 * Return a fetched reviewer form data in string.
	 * @param $args Array
	 * @param $request Request
	 * @return String
	 */
	function _fetchReviewerForm($args, &$request) {
		$selectionType = $request->getUserVar('selectionType');
		assert(!empty($selectionType));
		$formClassName = $this->_getReviewerFormClassName($selectionType);

		// Form handling.
		import('controllers.grid.users.reviewer.form.' . $formClassName );
		$reviewerForm = new $formClassName($this->getMonograph(), $this->getReviewRound());
		$reviewerForm->initData($args, $request);

		return $reviewerForm->fetch($request);
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

	/**
	* Get operations that need a review assignment policy.
	* @return array
	*/
	function _getReviewAssignmentOps() {
		// Define operations that need a review assignment policy.
		return array('readReview', 'reviewRead', 'thankReviewer', 'editReminder', 'sendReminder', 'deleteReviewer');

	}

	/**
	 * Get operations that need a review round policy.
	 * @return array
	 */
	function _getReviewRoundOps() {
		// Define operations that need a review round policy.
		return array('fetchGrid', 'fetchRow', 'showReviewerForm', 'reloadReviewerForm', 'editReviewer', 'updateReviewer',
								'getReviewersNotAssignedToMonograph', 'getUsersNotAssignedAsReviewers', 'createReviewer');
	}
}

?>
