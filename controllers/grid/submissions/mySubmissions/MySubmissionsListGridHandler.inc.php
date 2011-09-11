<?php

/**
 * @file controllers/grid/submissions/mySubmissions/MySubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
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

		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_AUTHOR),
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

		$titleColumn =& $this->getColumn('title');
		$titleColumn->setCellProvider(new MySubmissionsListGridCellProvider());
	}


	//
	// Public Handler Actions
	//
	/**
	 * Delete a submission
	 * FIXME: Either delete this operation or add it as a row action, see #6396.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteSubmission($args, &$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getMonograph(
			(int) $request->getUserVar('monographId')
		);

		$user =& $request->getUser();

		// If the submission is incomplete, allow the author to delete it.
		if ($monograph->getSubmissionProgress() != 0 && $monograph->getUserId() == $user->getId()) {
			$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
			$monographDao->deleteMonographById($monographId);

			$json = new JSONMessage(true);
		} else {
			$json = new JSONMessage(false, Locale::translate('settings.setup.errorDeletingItem'));
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

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$submissions = $monographDao->getByUserId($userId);
		$data = array();
		while ($submission =& $submissions->next()) {
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

?>
