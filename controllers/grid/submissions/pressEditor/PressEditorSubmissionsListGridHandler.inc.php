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
		// FIXME: Please correctly distribute the operations among roles.
		$this->addRoleAssignment(ROLE_ID_AUTHOR,
				$authorOperations = array());
		$this->addRoleAssignment(ROLE_ID_PRESS_ASSISTANT,
				$pressAssistantOperations = array_merge($authorOperations, array()));
		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array_merge($pressAssistantOperations,
				array('fetchGrid', 'showApprove', 'saveApprove', 'showApproveAndReview',
				'saveApproveAndReview', 'showDecline', 'saveDecline')));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStagePolicy');
		$this->addPolicy(new OmpWorkflowStagePolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
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
	// Public SubmissionsList Grid Actions
	//
	/**
	 * Show the submission approval->send to review modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function showApproveAndReview(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submissions.pressEditor.form.ApproveAndReviewSubmissionForm');
		$approveForm = new ApproveAndReviewSubmissionForm($monographId);

		if ($approveForm->isLocaleResubmit()) {
			$approveForm->readInputData();
		} else {
			$approveForm->initData($args, $request);
		}

		$json = new JSON('true', $approveForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the submission approval->send to review modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveApproveAndReview(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submissions.pressEditor.form.ApproveAndReviewSubmissionForm');
		$approveForm = new ApproveAndReviewSubmissionForm($monographId);

		$approveForm->readInputData();
		if ($approveForm->validate()) {
			$approveForm->execute($args, $request);

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}


	/**
	 * Show the submission approval modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function showApprove(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submissions.pressEditor.form.ApproveSubmissionForm');
		$approveForm = new ApproveSubmissionForm($monographId);

		if ($approveForm->isLocaleResubmit()) {
			$approveForm->readInputData();
		} else {
			$approveForm->initData($args, $request);
		}

		$json = new JSON('true', $approveForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the submission approval modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveApprove(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submissions.pressEditor.form.ApproveSubmissionForm');
		$approveForm = new ApproveSubmissionForm($monographId);

		$approveForm->readInputData();
		if ($approveForm->validate()) {
			$approveForm->execute($args, $request);

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
	}

	/**
	 * Show the submission decline modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function showDecline(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submissions.pressEditor.form.DeclineSubmissionForm');
		$declineForm = new DeclineSubmissionForm($monographId);

		if ($declineForm->isLocaleResubmit()) {
			$declineForm->readInputData();
		} else {
			$declineForm->initData($args, $request);
		}

		$json = new JSON('true', $declineForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the submission decline modal
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function saveDecline(&$args, &$request) {
		$monographId = $request->getUserVar('monographId');

		import('controllers.grid.submissions.pressEditor.form.DeclineSubmissionForm');
		$declineForm = new DeclineSubmissionForm($monographId);

		$declineForm->readInputData();
		if ($declineForm->validate()) {
			$declineForm->execute($args, $request);

			$json = new JSON('true');
		} else {
			$json = new JSON('false');
		}

		return $json->getString();
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
		$submissions =& $editorSubmissionDao->$functionName($pressId, 0, FILTER_EDITOR_ALL);

		$data = array();
		while($submission =& $submissions->next()) {
			$submissionId = $submission->getId();
			$data[$submissionId] = $submission;
			unset($submision);
		}

		return $data;
	}
}