<?php

/**
 * @file controllers/grid/submissions/reviewer/ReviewerSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionsListGridHandler
 * @ingroup controllers_grid_submissions_reviewer
 *
 * @brief Handle reviewer submissions list grid requests.
 */

// Import grid base classes.
import('controllers.grid.submissions.SubmissionsListGridHandler');

// Import reviewer submissions list specific grid classes.
import('controllers.grid.submissions.reviewer.ReviewerSubmissionsListGridCellProvider');

class ReviewerSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function ReviewerSubmissionsListGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(ROLE_ID_REVIEWER, array('fetchGrid', 'deleteSubmission'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Override the title column's cell provider.
		$cellProvider = new ReviewerSubmissionsListGridCellProvider();
		$titleColumn =& $this->getColumn('title');
		$titleColumn->setCellProvider($cellProvider);

		// Add reviewer-specific columns.
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
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId, $pressId) {
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