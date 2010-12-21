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
define('REVIEWER_SELECT_SEARCH',		0x00000001);
define('REVIEWER_SELECT_ADVANCED',		0x00000002);
define('REVIEWER_SELECT_CREATE',		0x00000003);
define('REVIEWER_SELECT_ENROLL',		0x00000004);

class ReviewerGridHandler extends GridHandler {
	/** @var Monograph */
	var $_submission;

	/**
	 * Constructor
	 */
	function ReviewerGridHandler() {
		parent::GridHandler();

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'addReviewer', 'editReviewer', 'updateReviewer', 'deleteReviewer',
				'getReviewerAutocomplete', 'getReviewerRoleAssignmentAutocomplete', 'readReview', 'createReviewer', 'editReminder', 'sendReminder'));
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
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
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

		// Get the monograph
		$submission =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		assert(is_a($submission, 'SeriesEditorSubmission'));
		$monographId = $submission->getId();

		// Get the review round currently being looked at
		$reviewType = $request->getUserVar('reviewType');
		$round = $request->getUserVar('round');

		// Get the existing review assignments for this monograph
		$reviewAssignments =& $submission->getReviewAssignments($reviewType, $round);

		$this->setData($reviewAssignments);

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = array('monographId' => $monographId,
							'reviewType' => $reviewType,
							'round' => $round);
		$this->addAction(
			new LinkAction(
				'addReviewer',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addReviewer', null, $actionArgs),
				'editor.monograph.addReviewer'
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

		// Add a column for the stage editor.
		// FIXME: We're just adding some placeholder text here until this
		// is correctly implemented, see #6233.
		$this->addColumn(
			new GridColumn(
				'FIXME',
				null,
				'FIXME',
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);

		// Add a column for the assigned reviewer.
		$this->addColumn(
			new GridColumn(
				'reviewer',
				'user.role.reviewer',
				null,
				'controllers/grid/common/cell/roleCell.tpl',
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


	//
	// Public Reviewer Grid Actions
	//
	/**
	 * An action to manually add a new reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addReviewer($args, &$request) {
		// Calling editReviewer() with an empty row id will add
		// a new reviewer.
		return $this->editReviewer($args, $request);
	}

	/**
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editReviewer($args, &$request) {
		// Identify the review assignment being updated
		$reviewAssignmentId = (int) $request->getUserVar('reviewAssignmentId');

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewerForm');
		$reviewerForm = new ReviewerForm($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH), $reviewAssignmentId);
		$reviewerForm->initData($args, $request);

		$json = new JSON('true', $reviewerForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateReviewer($args, &$request) {
		// Identify the review assignment being updated
		$reviewAssignmentId = (int) $request->getUserVar('reviewAssignmentId');

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewerForm');
		$reviewerForm = new ReviewerForm($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH), $reviewAssignmentId);
		$reviewerForm->readInputData();
		if ($reviewerForm->validate()) {
			$reviewAssignment =& $reviewerForm->execute($args, $request);

			// prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($reviewAssignment->getId());
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');

			$row->setData($reviewAssignment);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			// There was an error, redisplay the form
			$json = new JSON('false', $reviewerForm->fetch($request));
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
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Identify the review assignment ID.
		$reviewId = (int) $request->getUserVar('reviewId');

		// Delete the review assignment.
		// NB: SeriesEditorAction::clearReview() will check that this review
		// id is actually attached to the monograph so no need for further
		// validation here.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$result = SeriesEditorAction::clearReview($monograph->getId(), $reviewId);

		// Render the result.
		if ($result) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('submission.submit.errorDeletingReviewer'));
		}
		return $json->getString();
	}


	/**
	* Get potential reviewers for editor's reviewer selection autocomplete.
	* @param $args array
	* @param $request PKPRequest
	* @return string Serialized JSON object
	*/
	function getReviewerAutocomplete($args, &$request) {
		$round = (int)$request->getUserVar('round');
		$press =& $request->getPress();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$monographId = $monograph->getId();

		// Get items to populate possible items list with
		$seriesEditorSubmissionDAO =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$allReviewers = $seriesEditorSubmissionDAO->getAllReviewers($press->getId());
		$currentRoundReviewers =& $seriesEditorSubmissionDAO->getReviewersForMonograph($press->getId(), $monographId, $round);
		$currentRoundReviewerIds = array_keys($currentRoundReviewers->toAssociativeArray('id'));

		$itemList = array();
		foreach ($allReviewers->toAssociativeArray('id') as $i => $reviewer) {
			// Check that the reviewer is not in the current round.  We need to do the comparison here to avoid nested selects.
			if (!in_array($i, $currentRoundReviewerIds)) {
				$itemList[] = array('id' => $reviewer->getId(),
									'name' => $reviewer->getFullName(),
								 	'abbrev' => $reviewer->getUsername());
			}
		}

		import('lib.pkp.classes.core.JSON');
		$sourceJson = new JSON('true', null, 'false', 'local');
		$sourceContent = array();
		foreach ($itemList as $i => $item) {
			// The autocomplete code requires the JSON data to use 'label' as the array key for labels, and 'value' for the id
			$additionalAttributes = array(
				'label' =>  sprintf('%s (%s)', $item['name'], $item['abbrev']),
				'value' => $item['id']
		   );
			$itemJson = new JSON('true', '', 'false', null, $additionalAttributes);
			$sourceContent[] = $itemJson->getString();

			unset($itemJson);
		}
		$sourceJson->setContent('[' . implode(',', $sourceContent) . ']');

		echo $sourceJson->getString();
	}

	/**
	* Get a list of all non-reviewer users in the system to populate the reviewer role assignment autocomplete.
	* @param $args array
	* @param $request PKPRequest
	* @return string Serialized JSON object
	*/
	function getReviewerRoleAssignmentAutocomplete($args, &$request) {
		$press =& $request->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$users =& $userGroupDao->getUsersByContextId($press->getId());

		$itemList = array();
		$roleDao =& DAORegistry::getDAO('RoleDAO'); /* @var $roleDao RoleDAO */
		while ($user =& $users->next()) {
			// Check that the reviewer is not in the current round.  We need to do the comparison here to avoid nested selects.
			if (!$roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_REVIEWER)) {
				$itemList[] = array('id' => $user->getId(),
									'name' => $user->getFullName(),
								 	'abbrev' => $user->getUsername());
			}
			unset($user);
		}

		import('lib.pkp.classes.core.JSON');
		$sourceJson = new JSON('true', null, 'false', 'local');
		$sourceContent = array();
		foreach ($itemList as $i => $item) {
			// The autocomplete code requires the JSON data to use 'label' as the array key for labels, and 'value' for the id
			$additionalAttributes = array(
				'label' =>  sprintf('%s (%s)', $item['name'], $item['abbrev']),
				'value' => $item['id']
			);
			$itemJson = new JSON('true', '', 'false', null, $additionalAttributes);
			$sourceContent[] = $itemJson->getString();

			unset($itemJson);
		}
		$sourceJson->setContent('[' . implode(',', $sourceContent) . ']');

		echo $sourceJson->getString();
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
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH); /* @var $monograph SeriesEditorSubmission */
		$templateMgr->assign_by_ref('monograph', $monograph);

		// Retrieve review assignment.
		$reviewAssignment =& $this->_retrieveReviewAssignment($request, $monograph);
		$templateMgr->assign_by_ref('reviewAssignment', $reviewAssignment);

		// Retrieve reviewer comment.
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$monographComments =& $monographCommentDao->getReviewerCommentsByReviewerId($reviewAssignment->getReviewerId(), $reviewAssignment->getSubmissionId(), $reviewAssignment->getId());
		$templateMgr->assign_by_ref('reviewerComment', $monographComments[0]);

		// Mark the latest read date of the review by the editor.
		$user =& $request->getUser();
		$viewsDao =& DAORegistry::getDAO('ViewsDAO');
		$viewsDao->recordView(ASSOC_TYPE_REVIEW_RESPONSE, $reviewAssignment->getId(), $user->getId());

		// Render the response.
		$json =& new JSON('true', $templateMgr->fetch('controllers/grid/users/reviewer/readReview.tpl'));
		return $json->getString();
	}

	/**
	 * Displays a modal to allow the editor to ender a message to send to the reviewer as a reminder
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editReminder($args, &$request) {
		// Identify the review assignment being updated.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$reviewAssignment =& $this->_retrieveReviewAssignment($request, $monograph);

		// Initialize form.
		import('controllers.grid.users.reviewer.form.ReviewReminderForm');
		$reviewReminderForm = new ReviewReminderForm($reviewAssignment->getId());
		$reviewReminderForm->initData($args, $request);

		// Render form.
		$json = new JSON('true', $reviewReminderForm->fetch($request));
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
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('editor.review.reminderError'));
		}
		return $json->getString();
	}


	//
	// Private helper methods
	//
	/**
	 * Retrieves the review assignment given as a
	 * review id in the request and checks it against
	 * the monograph.
	 * @param $request Request
	 * @param $monograph Monograph
	 * @return ReviewAssignment or null if the review id is invalid.
	 */
	function &_retrieveReviewAssignment(&$request, &$monograph) {
		assert(is_a($monograph, 'Monograph'));

		// Retrieve review assignment.
		$reviewId = (int) $request->getUserVar('reviewId');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		// Assert that the review assignment actually belongs to the
		// authorized monograph.
		if ($reviewAssignment->getMonographId() != $monograph->getId()) fatalError('Invalid review assignment id.');
		return $reviewAssignment;
	}
}