<?php

/**
 * @file controllers/grid/users/reviewer/ReviewerGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGridHandler
 * @ingroup controllers_grid_reviewer
 *
 * @brief Handle reviewer grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import reviewer grid specific classes
import('controllers.grid.users.reviewer.ReviewerGridCellProvider');
import('controllers.grid.users.reviewer.ReviewerGridRow');

class ReviewerGridHandler extends GridHandler {
	/** @var Monograph */
	var $_submission;

	/**
	 * Constructor
	 */
	function ReviewerGridHandler() {
		parent::GridHandler();
		// FIXME: Please correctly distribute the operations among roles.
		$this->addRoleAssignment(ROLE_ID_AUTHOR,
				$authorOperations = array());
		$this->addRoleAssignment(ROLE_ID_PRESS_ASSISTANT,
				$pressAssistantOperations = array_merge($authorOperations, array()));
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array_merge($pressAssistantOperations,
				array('fetchGrid', 'addReviewer', 'editReviewer', 'updateReviewer', 'deleteReviewer',
				'getReviewerAutocomplete', 'readReview', 'createReviewer', 'editReminder', 'sendReminder')));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this reviewer grid.
	 * @return Monograph
	 */
	function &getSubmission() {
		return $this->_submission;
	}


	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStagePolicy');
		$this->addPolicy(new OmpWorkflowStagePolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the authorized submission.
		$this->_submission =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_EDITOR));

		// Basic grid configuration
		$this->setTitle('user.role.reviewers');

		// Get the monograph
		$submission =& $this->getSubmission();
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

		$session =& $request->getSession();
		$actingAsUserGroupId = $session->getActingAsUserGroupId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$actingAsUserGroup =& $userGroupDao->getById($actingAsUserGroupId);

		// add a column for the role the user is acting as
		$this->addColumn(
			new GridColumn(
				$actingAsUserGroupId,
				null,
				$actingAsUserGroup->getLocalizedName(),
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);

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
		// Return a reviewer row
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
	function addReviewer(&$args, &$request) {
		// Calling editReviewer() with an empty row id will add
		// a new reviewer.
		return $this->editReviewer($args, $request);
	}

	/**
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editReviewer(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the review assignment being updated
		$reviewAssignmentId = $request->getUserVar('reviewAssignmentId');

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewerForm');
		$reviewerForm = new ReviewerForm($monographId, $reviewAssignmentId);
		$reviewerForm->initData($args, $request);

		$json = new JSON('true', $reviewerForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateReviewer(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the review assignment being updated
		$reviewAssignmentId = $request->getUserVar('reviewAssignmentId');

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewerForm');
		$reviewerForm = new ReviewerForm($monographId, $reviewAssignmentId);
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
			$json = new JSON('false', Locale::translate('editor.review.errorAddingReviewer'));
		}
		return $json->getString();
	}

	/**
	 * Delete a reviewer
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function deleteReviewer(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the reviewer to be deleted
		$reviewerId = $request->getUserVar('reviewerId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$result = $authorDao->deleteAuthorById($reviewerId, $monographId);

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
	* @return JSON
	*/
	function getReviewerAutocomplete(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$press =& $request->getPress();
		$seriesEditorSubmissionDAO =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');

		// Get items to populate possible items list with
		$reviewers =& $seriesEditorSubmissionDAO->getReviewersNotAssignedToMonograph($press->getId(), $monographId);
		$reviewers =& $reviewers->toArray();

		$itemList = array();
		foreach ($reviewers as $i => $reviewer) {
			$itemList[] = array('id' => $reviewer->getId(),
							 'name' => $reviewer->getFullName(),
							 'abbrev' => $reviewer->getUsername()
							);
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
	 * Open a modal to read the reviewer's review and download any files they may have uploaded
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function readReview(&$args, &$request) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($request->getUserVar('reviewId'));
		$monograph =& $monographDao->getMonograph($reviewAssignment->getSubmissionId());
		$monographComments =& $monographCommentDao->getReviewerCommentsByReviewerId($reviewAssignment->getReviewerId(), $reviewAssignment->getSubmissionId(), $reviewAssignment->getReviewId());

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('reviewAssignment', $reviewAssignment);
		$templateMgr->assign_by_ref('monograph', $monograph);
		$templateMgr->assign_by_ref('reviewerComment', $monographComments[0]);
		$json =& new JSON('true', $templateMgr->fetch('controllers/grid/users/reviewer/readReview.tpl'));
		return $json->getString();
	}

	/**
	 * Displays a modal to allow the editor to ender a message to send to the reviewer as a reminder
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function editReminder(&$args, &$request) {
		// Identify the review assignment being updated
		$reviewAssignmentId = $request->getUserVar('reviewId');

		// Form handling
		import('controllers.grid.users.reviewer.form.ReviewReminderForm');
		$reviewReminderForm = new ReviewReminderForm($reviewAssignmentId);
		$reviewReminderForm->initData($args, $request);

		$json = new JSON('true', $reviewReminderForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Send the reviewer reminder and close the modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function sendReminder(&$args, &$request) {
		// Identify the review assignment being updated
 		$reviewAssignmentId = $request->getUserVar('reviewAssignmentId');

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
}