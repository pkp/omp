<?php

/**
 * @file controllers/grid/submissions/author/AuthorSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmissionsListGridHandler
 * @ingroup controllers_grid_submissions_author
 *
 * @brief Handle author submissions list grid requests.
 */

// Import grid base classes.
import('controllers.grid.submissions.SubmissionsListGridHandler');

// Import author submissions list specific grid classes.
import('controllers.grid.submissions.author.AuthorSubmissionsListGridRow');

class AuthorSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function AuthorSubmissionsListGridHandler() {
		parent::SubmissionsListGridHandler();
		$this->addRoleAssignment(ROLE_ID_AUTHOR, array('fetchGrid', 'deleteSubmission'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Grid-level actions
		$dispatcher =& Registry::get('dispatcher');
		$this->addAction(
			new LinkAction(
				'newSubmission',
				LINK_ACTION_MODE_LINK,
				LINK_ACTION_TYPE_NOTHING,
				$dispatcher->url($request, ROUTE_PAGE, null, 'submission', 'wizard'),
				'submission.submit',
				null,
				'add'
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
	 * @return string
	 */
	function deleteSubmission(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$this->validate($monographId);

		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$authorSubmission = $authorSubmissionDao->getAuthorSubmission($monographId);

		// If the submission is incomplete, allow the author to delete it.
		if ($authorSubmission->getSubmissionProgress()!=0) {
			import('classes.file.MonographFileManager');
			$monographFileManager = new MonographFileManager($monographId);
			$monographFileManager->deleteMonographTree();

			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monographDao->deleteMonographById($monographId);

			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('settings.setup.errorDeletingItem'));
		}

		return $json->getString();
	}


	//
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId, $pressId) {
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

		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$submissions = $authorSubmissionDao->getAuthorSubmissions($userId, $pressId, $active);
		$data = array();
		while($submission =& $submissions->next()) {
			$submissionId = $submission->getId();
			$data[$submissionId] = $submission;
			unset($submision);
		}

		return $data;
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return SubmissionContributorGridRow
	 */
	function &getRowInstance() {
		// Return an AuthorSubmissionList row
		$row = new AuthorSubmissionsListGridRow();
		return $row;
	}
}