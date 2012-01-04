<?php

/**
 * @file controllers/grid/submissions/assignedSubmissions/AssignedSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AssignedSubmissionsListGridHandler
 * @ingroup controllers_grid_submissions_pressEditor
 *
 * @brief Handle press submissions list grid requests (submissions the user is assigned to).
 */

// Import grid base classes.
import('controllers.grid.submissions.SubmissionsListGridHandler');
import('controllers.grid.submissions.SubmissionsListGridRow');

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

class AssignedSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function AssignedSubmissionsListGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_SITE_ADMIN, ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_PRESS_ASSISTANT),
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

		// Set title.
		$this->setTitle('common.queue.long.myAssigned');
	}


	//
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');

		// Get submissions the user is a stage participant for
		$signoffs =& $signoffDao->getByUserId($userId);

		$authorUserGroupIds = $userGroupDao->getUserGroupIdsByRoleId(ROLE_ID_AUTHOR);

		$data = array();

		// get signoffs and stage assignments
		$stageAssignments =& $stageAssignmentDao->getByUserId($userId);
		while($stageAssignment =& $stageAssignments->next()) {
			$monograph =& $monographDao->getById($stageAssignment->getSubmissionId());
			if ($monograph->getDateSubmitted() == null) { continue; }; // Still incomplete, don't add to assigned submissions grid.

			// Check if user is a submitter of this monograph.
			if ($userId == $monograph->getUserId()) { continue; }; // It will be in the 'my submissions' grid.

			$monographId = $monograph->getId();
			$data[$monographId] = $monograph;
			unset($monograph, $stageAssignment, $authors);
		}

		while($signoff =& $signoffs->next()) {
			// If it is a monograph signoff (and not, say, a file signoff) and
			// If this is an author signoff, do not include (it will be in the 'my submissions' grid)
			if( $signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH &&
				!in_array($signoff->getUserGroupId(), $authorUserGroupIds)) {
				$monograph =& $monographDao->getById($signoff->getAssocId());
				$monographId = $monograph->getId();
				$data[$monographId] = $monograph;
				unset($monograph, $signoff);
			}
		}

		// Get submissions the user is reviewing
		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO'); /* @var $reviewerSubmissionDao ReviewerSubmissionDAO */
		$reviewerSubmissions = $reviewerSubmissionDao->getReviewerSubmissionsByReviewerId($userId);
		while($reviewerSubmission =& $reviewerSubmissions->next()) {
			$monographId = $reviewerSubmission->getId();
			$data[$monographId] = $reviewerSubmission;
			unset($reviewerSubmission);
		}

		return $data;
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	* Get the row handler - override the default row handler
	* @return SubmissionsListGridRow
	*/
	function &getRowInstance() {
		$row = new SubmissionsListGridRow();
		return $row;
	}
}

?>
