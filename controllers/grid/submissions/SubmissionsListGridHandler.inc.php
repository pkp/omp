<?php

/**
 * @file controllers/grid/submissions/SubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
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

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');

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
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations.
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APPLICATION_COMMON,
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION
		);

		// Load submissions.
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$user =& $request->getUser();
		$this->setGridDataElements($this->getSubmissions($request, $user->getId()));

		// If there is more than one press in the system, add a press column
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPresses();
		$authorizedRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
		$cellProvider = new SubmissionsListGridCellProvider($authorizedRoles);
		if($presses->getCount() > 1) {
			$this->addColumn(
				new GridColumn(
					'press',
					'press.press',
					null,
					'controllers/grid/gridCell.tpl',
					$cellProvider
				)
			);
		}

		$this->addColumn(
			new GridColumn(
				'author',
				'monograph.authors',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'title',
				'monograph.title',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('html' => true,
						'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);

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
	// Protected template methods to be overridden by sub-classes.
	//
	/**
	 * Return a list of submissions.
	 * @param $request Request
	 * @param $userId integer
	 * @param $pressId integer
	 * @return array a list of submission objects
	 */
	function getSubmissions(&$request, $userId) {
		// Must be implemented by sub-classes.
		assert(false);
	}
}

?>
