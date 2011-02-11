<?php

/**
 * @file controllers/grid/submissions/pressEditor/AssignedSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
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

// Import assigned submissions list specific grid classes.
import('controllers.grid.submissions.assignedSubmissions.AssignedSubmissionsListGridCellProvider');

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

		// Add editor specific locale component.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR));

		$cellProvider = new AssignedSubmissionsListGridCellProvider();
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
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		// Get submissions the user is a stage participant for
		$signoffs =& $signoffDao->getByUserId($userId);

		$authorUserGroupIds = $userGroupDao->getUserGroupIdsByRoleId(ROLE_ID_AUTHOR);

		$data = array();
		while($signoff =& $signoffs->next()) {
			// If this is an author signoff, do not include (it will be in the 'my submissions' grid)
			if(!in_array($signoff->getUserGroupId(), $authorUserGroupIds)) {
				$monograph =& $monographDao->getMonograph($signoff->getAssocId());
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