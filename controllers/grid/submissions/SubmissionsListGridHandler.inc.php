<?php

/**
 * @file controllers/grid/submissions/SubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListGridHandler
 * @ingroup controllers_grid_submissions
 *
 * @brief Handle submission list grid requests.
 */

// Import grid base classes.
import('lib.pkp.classes.controllers.grid.GridHandler');

// Import submissions list grid specific classes.
import('controllers.grid.submissions.SubmissionsListGridCellProvider');
import('classes.submission.common.Action');

class SubmissionsListGridHandler extends GridHandler {

	/**
	 * Constructor
	 */
	function SubmissionsListGridHandler() {
		parent::GridHandler();
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$router =& $request->getRouter();
		$operation = $router->getRequestedOp($request);

		switch($operation) {
			case 'fetchGrid':
				// The user only needs press-level permission to see a list
				// of submissions.
				import('classes.security.authorization.OmpPressAccessPolicy');
				$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
				break;

			default:
				// All other operations require full submission access.
				import('classes.security.authorization.OmpSubmissionAccessPolicy');
				$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		}
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations.
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION));

		// Load submissions.
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();
		$this->setData($this->getSubmissions($request, $user->getId(), $press->getId()));

		// Add title column which is common to all submission lists.
		$cellProvider = new SubmissionsListGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'title',
				'monograph.title',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Protected template methods to be overridden by sub-classes.
	//
	/**
	 * Return a list of submissions.
	 * @param $request Request
	 * @param $userId integer
	 * @param $pressId integer
	 * @return array a list of submission objects
	 */
	function getSubmissions(&$request, $userId, $pressId) {
		// Must be implemented by sub-classes.
		assert(false);
	}
}