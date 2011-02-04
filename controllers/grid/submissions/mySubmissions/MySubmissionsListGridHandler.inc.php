<?php

/**
 * @file controllers/grid/submissions/mySubmissions/MySubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
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

		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR),
				array('fetchGrid'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$cellProvider = new MySubmissionsListGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'title',
				'monograph.title',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		$cellProvider = new SubmissionsListGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'status',
				'common.status',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Public Handler Actions
	//
	/**
	 * Delete a submission
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteSubmission($args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$this->validate($monographId);

		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$authorSubmission = $authorSubmissionDao->getAuthorSubmission($monographId);

		// If the submission is incomplete, allow the author to delete it.
		if ($authorSubmission->getSubmissionProgress() != 0) {
			$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
			$monographDao->deleteMonographById($monographId);

			$json = new JSON(true);
		} else {
			$json = new JSON(false, Locale::translate('settings.setup.errorDeletingItem'));
		}

		return $json->getString();
	}


	//
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId) {
		$this->setTitle('submission.mySubmissions');
		$active = true;

		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$submissions = $authorSubmissionDao->getAuthorSubmissions($userId);
		$data = array();
		while($submission =& $submissions->next()) {
			$submissionId = $submission->getId();
			$data[$submissionId] =& $submission;
			unset($submision);
		}

		return $data;
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return SubmissionsListGridRow
	 */
	function &getRowInstance() {
		$row = new SubmissionsListGridRow();
		return $row;
	}
}
