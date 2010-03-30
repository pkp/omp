<?php

/**
 * @file controllers/grid/submit/submissionsList/SubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionContributorGridHandler
 * @ingroup controllers_grid_submissionContributor
 *
 * @brief Handle author submissions list grid requests.
 */

// import grid base classes
import('controllers.grid.submit.submissionsList.SubmissionsListGridHandler');

class AuthorSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function AuthorSubmissionsListGridHandler() {
		parent::GridHandler();
		$this->roleId = ROLE_ID_AUTHOR;
	}

	//
	// Getters/Setters
	//
	/**
	 * @see PKPHandler::getRemoteOperations()
	 * @return array
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('deleteSubmission'));
	}

	//
	// Overridden methods from PKPHandler
	//

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_AUTHOR));

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();

		$this->setData($this->_getSubmissions($request, $user->getId(), $press->getId()));

		// Grid-level actions
		$dispatcher =& Registry::get('dispatcher');
		$this->addAction(
			new GridAction(
				'newSubmission',
				GRID_ACTION_MODE_LINK,
				GRID_ACTION_TYPE_NOTHING,
				$dispatcher->url($request, 'page', null, 'author', 'submit'),
				'author.submit',
				'add'
			)
		);

		// Add author-specific columns
		$emptyColumnActions = array();
		$cellProvider = new SubmissionsListGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'status',
				'common.status',
				$emptyColumnActions,
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
}