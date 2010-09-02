<?php

/**
 * @file controllers/grid/submissions/pressEditor/PressEditorSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressEditorSubmissionsListGridHandler
 * @ingroup controllers_grid_submissions_pressEditor
 *
 * @brief Handle press editor submissions list grid requests.
 */

// Import grid base classes.
import('controllers.grid.submissions.SubmissionsListGridHandler');

// Import press editor submissions list specific grid classes.
import('controllers.grid.submissions.pressEditor.PressEditorSubmissionsListGridCellProvider');
import('controllers.grid.submissions.pressEditor.PressEditorSubmissionsListGridRow');

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

class PressEditorSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function PressEditorSubmissionsListGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR),
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
		$this->setTitle('common.queue.long.active');

		// Instantiate the cell provider.
		$cellProvider = new PressEditorSubmissionsListGridCellProvider();

		// Add press editor specific columns:
		// 1) Add a column for the role the user is acting as.
		$actingAsUserGroup =& $this->getAuthorizedContextObject(ASSOC_TYPE_USER_GROUP);
		$this->addColumn(
			new GridColumn(
				$actingAsUserGroup->getId(),
				null,
				$actingAsUserGroup->getLocalizedAbbrev(),
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);

		// 2) Add one column for each of the Author user groups
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$router =& $request->getRouter();
		$press =& $router->getContext($request);
		$authorUserGroups =& $userGroupDao->getByRoleId($press->getId(), ROLE_ID_AUTHOR);
		while (!$authorUserGroups->eof()) {
			$authorUserGroup =& $authorUserGroups->next();
			$this->addColumn(
				new GridColumn(
					$authorUserGroup->getId(),
					null,
					$authorUserGroup->getLocalizedAbbrev(),
					'controllers/grid/common/cell/roleCell.tpl',
					$cellProvider
				)
			);
			unset($authorUserGroup);
		}

		// Add editor specific locale component.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR));
	}


	//
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId, $pressId) {
		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$submissions =& $editorSubmissionDao->getUnassigned($pressId, 0, FILTER_EDITOR_ALL);

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
	* Get the row handler - override the default row handler
	* @return PressEditorSubmissionsListGridRow
	*/
	function &getRowInstance() {
		$row = new PressEditorSubmissionsListGridRow();
		return $row;
	}
}