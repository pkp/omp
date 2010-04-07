<?php

/**
 * @file controllers/grid/submissions/submissionsList/SubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionContributorGridHandler
 * @ingroup controllers_grid_submissionContributor
 *
 * @brief Handle press editor submissions list grid requests.
 */

// import grid base classes
import('controllers.grid.submissions.submissionsList.SubmissionsListGridHandler');

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

class PressEditorSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function PressEditorSubmissionsListGridHandler() {
		parent::GridHandler();
		$this->roleId = ROLE_ID_EDITOR;
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
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR));

		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();

		$this->setData($this->_getSubmissions($request, $user->getId(), $press->getId()));

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
			case 'submissionsInReview':
				$functionName = 'getInReview';
				$this->setTitle('common.queue.long.submissionsInReview');
			case 'submissionsInEditing':
				$functionName = 'getInEditing';
				$this->setTitle('common.queue.long.submissionsInEditing');
				break;
			case 'submissionsArchives':
				$functionName = 'getArchives';
				$this->setTitle('common.queue.long.submissionsArchives');
				break;
			default:
				$functionName = 'getUnassigned';
				$this->setTitle('common.queue.long.submissionsUnassigned');
				break;
		}

		// TODO: nulls represent search options which have not yet been implemented
		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$submissions =& $editorSubmissionDao->$functionName($pressId, FILTER_EDITOR_ALL);
		
		$data = array();
		while($submission =& $submissions->next()) {
			$submissionId = $submission->getId();
			$data[$submissionId] = $submission;
			unset($submision);
		}

		return $data;
	}
}