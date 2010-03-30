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
 * @brief Handle submissionContributor grid requests.
 */

// import grid base classes
import('controllers.grid.GridHandler');

// import submissionsList grid specific classes
import('controllers.grid.submit.submissionsList.SubmissionsListGridCellProvider');
import('controllers.grid.submit.submissionsList.SubmissionsListGridRow');
import('submission.common.Action');

class SubmissionsListGridHandler extends GridHandler {
	/** @var int The current role */
	var $roleId;
	
	/**
	 * Constructor
	 */
	function SubmissionsListGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see PKPHandler::getRemoteOperations()
	 * @return array
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array(null));
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * Validate that ...
	 * fatal error if validation fails.
	 * @param $requiredContexts array
	 * @param $request PKPRequest
	 * @return boolean
	 */
	function validate($requiredContexts, $request) {
		// FIXME:
		// Role ID in path equals user's role ID
		
		// User ID in path equals user's user ID
		
		return true;
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_PKP_SUBMISSION));

		$emptyColumnActions = array();
		$cellProvider = new SubmissionsListGridCellProvider($this->roleId);
		$this->addColumn(
			new GridColumn(
				'id',
				'common.id',
				$emptyColumnActions,
				'controllers/grid/gridCellInSpan.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'dateSubmitted',
				'submissions.submit',
				$emptyColumnActions,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'title',
				'monograph.title',
				$emptyColumnActions,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'authors',
				'monograph.authors',
				$emptyColumnActions,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return SubmissionContributorGridRow
	 */
	function &getRowInstance() {
		// Return a submissionContributor row
		$row = new SubmissionsListGridRow();
		return $row;
	}

	
	//
	// Private helper functions
	//
	function _getSubmissions(&$request, $userId, $pressId) {
		assert(false);
	}
}