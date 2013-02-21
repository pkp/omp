<?php

/**
 * @file controllers/grid/submissions/mySubmissions/MySubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MySubmissionsListGridHandler
 * @ingroup controllers_grid_submissions_mySubmissions
 *
 * @brief Handle author's submissions list grid requests (submissions the user has made).
 */

// Import grid base classes.
import('controllers.grid.submissions.SubmissionsListGridHandler');
import('controllers.grid.submissions.SubmissionsListGridRow');

// Import 'my submissions' list specific grid classes.
import('controllers.grid.submissions.mySubmissions.MySubmissionsListGridCellProvider');

class MySubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function MySubmissionsListGridHandler() {
		parent::SubmissionsListGridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_AUTHOR),
			array('fetchGrid', 'fetchRow', 'deleteSubmission')
		);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$titleColumn =& $this->getColumn('title');
		$titleColumn->setCellProvider(new MySubmissionsListGridCellProvider());
	}


	//
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId) {
		$this->setTitle('submission.mySubmissions');

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$submissions = $monographDao->getByUserId($userId);
		$data = array();
		while ($submission =& $submissions->next()) {
			if ($submission->getDatePublished() == null) {
				$submissionId = $submission->getId();
				$data[$submissionId] =& $submission;
			}
			unset($submission);
		}

		return $data;
	}
}

?>
