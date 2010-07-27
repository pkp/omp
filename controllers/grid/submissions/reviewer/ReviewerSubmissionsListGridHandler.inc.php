<?php

/**
 * @file controllers/grid/submissions/ReviewerSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionsListGridHandler
 * @ingroup controllers_grid_submissionContributor
 *
 * @brief Handle reviewer submissions list grid requests.
 */

// import grid base classes
import('controllers.grid.submissions.SubmissionsListGridHandler');
// import reviewer submissions list specific classes
import('controllers.grid.submissions.reviewer.ReviewerSubmissionsListGridCellProvider');

class ReviewerSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function ReviewerSubmissionsListGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(ROLE_ID_REVIEWER,
				array('fetchGrid', 'deleteSubmission'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// Make sure the request complies with the review page policy.
		import('classes.security.authorization.OmpReviewPagePolicy');
		$this->addPolicy(new OmpReviewPagePolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_SUBMISSION));

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();

		$this->setData($this->_getSubmissions($request, $user->getId(), $press->getId()));

		// override the title column's cell provider
		$cellProvider = new ReviewerSubmissionsListGridCellProvider();
		$titleColumn =& $this->getColumn('title');
		$titleColumn->setCellProvider($cellProvider);

		// Add reviewer-specific columns
		$this->addColumn(
			new GridColumn(
				'dateAssigned',
				'common.assigned',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'dateDue',
				'submission.due',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Public SubmissionsList Grid Actions
	//

	/**
	 * Delete a submission
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteSubmission(&$args, &$request) {
		//FIXME: Implement

		return false;
	}

	//
	// Private helper functions
	//
	function _getSubmissions(&$request, $userId, $pressId) {
		//$rangeInfo =& Handler::getRangeInfo('submissions');
		$page = $request->getUserVar('status');
		switch($page) {
			case 'completed':
				$active = false;
				$this->setTitle('common.queue.long.completed');
				break;
			default:
				$page = 'active';
				$this->setTitle('common.queue.long.active');
				$active = true;
		}

		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		$submissions = $reviewerSubmissionDao->getReviewerSubmissionsByReviewerId($userId, $pressId, $active);

		return $submissions;
	}
}