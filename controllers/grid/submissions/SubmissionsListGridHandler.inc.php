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

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import submissionsList grid specific classes
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
	// Overridden methods from PKPHandler
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_PKP_SUBMISSION));

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
	// Private helper functions
	//
	function _getSubmissions(&$request, $userId, $pressId) {
		assert(false);
	}
}