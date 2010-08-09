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

// import grid base classes
import('controllers.grid.submissions.SubmissionsListGridHandler');

// import specific grid classes
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
		// FIXME: Missing permission spec, see comment in the authorize() method.
		// We have to enter a role assignement here once #5593 is fixed.
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		// FIXME: This component is being used on the editor's page which is
		// not specified in the permission documentation or in the application
		// specification, see #5593. We have to enter a policy here once we've
		// specified the permissions for this component.
		return true;
	}

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

		$this->setTitle('common.queue.long.active');
		$this->setData($this->_getSubmissions($request, $user->getId(), $press->getId()));

		// change the first column cell template to allow for actions
		$titleColumn =& $this->getColumn('title');

		// Add author-specific columns
		$cellProvider = new PressEditorSubmissionsListGridCellProvider();

		$session =& $request->getSession();
		$actingAsUserGroupId = $session->getActingAsUserGroupId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$actingAsUserGroup =& $userGroupDao->getById($actingAsUserGroupId);

		// add a column for the role the user is acting as
		$this->addColumn(
			new GridColumn(
				$actingAsUserGroupId,
				null,
				$actingAsUserGroup->getLocalizedAbbrev(),
				'controllers/grid/common/cell/roleCell.tpl',
				$cellProvider
			)
		);

		// Add one column for each of the Author user groups
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

	//
	// Private helper functions
	//
	function _getSubmissions(&$request, $userId, $pressId) {
		// TODO: nulls represent search options which have not yet been implemented
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
}