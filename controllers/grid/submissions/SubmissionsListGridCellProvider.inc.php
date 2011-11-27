<?php

/**
 * @file controllers/grid/submissions/SubmissionsListGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListGridCellProvider
 * @ingroup controllers_grid_submissions
 *
 * @brief Class for a cell provider that can retrieve labels from submissions
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class SubmissionsListGridCellProvider extends DataObjectGridCellProvider {

	/** @var $authorizedRoles Array */
	var $_authorizedRoles;

	/**
	 * Constructor
	 */
	function SubmissionsListGridCellProvider($authorizedRoles = null) {
		if ($authorizedRoles) {
			$this->_authorizedRoles = $authorizedRoles;
		}

		parent::DataObjectGridCellProvider();
	}


	//
	// Getters and setters.
	//
	/**
	 * Get the user authorized roles.
	 * @return array
	 */
	function getAuthorizedRoles() {
		return $this->_authorizedRoles;
	}


	//
	// Public functions.
	//
	/**
	 * Gathers the state of a given cell given a $row/$column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 */
	function getCellState(&$row, &$column) {
		return '';
	}


	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		if ( $column->getId() == 'title' ) {
			$monograph =& $row->getData();

			if (is_a($monograph, 'ReviewerSubmission')) {
				// Reviewer: Add a review link action.
				return array($this->_getCellLinkAction($request, 'reviewer', 'submission', $monograph));
			} else {
				// If user has only author role user groups stage assignments,
				// then add an author dashboard link action.
				$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
				$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

				$authorUserGroupIds = $userGroupDao->getUserGroupIdsByRoleId(ROLE_ID_AUTHOR);
				$user =& $request->getUser();
				$stageAssignmentsFactory = $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), null, null, $user->getId());
				$stageAssignments =& $stageAssignmentsFactory->toAssociativeArray('stageId');

				$authorDashboard = true;
				foreach ($stageAssignments as $stageAssignment) {
					if (!in_array($stageAssignment->getUserGroupId(), $authorUserGroupIds)) {
						$authorDashboard = false;
						break;
					}
					unset($stageAssignment);
				}
				if ($authorDashboard) {
					return array($this->_getCellLinkAction($request, 'authorDashboard', 'submission', $monograph));
				}

				// Press assistant, Series Editor, or Press Manager:
				// Add a workflow link action.
				reset($stageAssignments);
				unset($stageAssignment);
				$stageId = $monograph->getStageId();
				$workflowStagePath = null;
				$workflowStagesMap = $userGroupDao->getWorkflowStageKeysAndPaths();

				// FIXME: Duplicating code from workflow stage authorization policy. See #6841
				// Press managers can access all stages.
				if (in_array(ROLE_ID_PRESS_MANAGER, $this->getAuthorizedRoles())) {
					$workflowStagePath = $workflowStagesMap[$stageId]['path'];
				}

				// Other roles need to have an stage assignment Get the closest workflow stage that user has an assignment.
				if (is_null($workflowStagePath)) {
					$workingStageId = $stageId;
					$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');

					while ($workingStageId) {
						foreach ($stageAssignments as $stageAssignment) {
							if ($stageAssignment->getStageId() == $workingStageId) {
								// Make sure that, if in review stage, we have at least one
								// initiated review round.
								if ($workingStageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW ||
								$workingStageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
									$reviewRoundsFactory =& $reviewRoundDao->getByMonographId($monograph->getId(), $workingStageId);
									if ($reviewRoundsFactory->wasEmpty()) {
										continue;
									}
								}
								$workflowStagePath = $workflowStagesMap[$workingStageId]['path'];
								break 2;
							}
						}
						$workingStageId--;
						unset($stageAssignment);
					}
				}

				if (is_null($workflowStagePath)) {
					fatalError('Invalid stage ID!');
				}

				return array($this->_getCellLinkAction($request, 'workflow', $workflowStagePath, $monograph));
			}

			// This should be unreachable code.
			assert(false);
		}
		return parent::getCellActions($request, $row, $column, $position);
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$monograph =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($monograph, 'DataObject') && !empty($columnId));

		$pressId = $monograph->getPressId();
		$pressDao = DAORegistry::getDAO('PressDAO');
		$press = $pressDao->getPress($pressId);

		switch ($columnId) {
			case 'title':
				$title = $monograph->getLocalizedTitle();
				if ( empty($title) ) $title = __('common.untitled');
				return array('label' => $title);
				break;
			case 'press':
				return array('label' => $press->getLocalizedName());
				break;
			case 'author':
				return array('label' => $monograph->getAuthorString(true));
				break;
			case 'dateAssigned':
				$dateAssigned = strftime(Config::getVar('general', 'date_format_short'), strtotime($monograph->getDateAssigned()));
				if ( empty($dateAssigned) ) $dateAssigned = '--';
				return array('label' => $dateAssigned);
				break;
			case 'dateDue':
				$dateDue = strftime(Config::getVar('general', 'date_format_short'), strtotime($monograph->getDateDue()));
				if ( empty($dateDue) ) $dateDue = '--';
				return array('label' => $dateDue);
				break;
			case 'status':
				$stageId = $monograph->getStageId();
				switch ($stageId) {
					case WORKFLOW_STAGE_ID_SUBMISSION: default:
						// FIXME: better way to determine if submission still incomplete?
						if ($monograph->getSubmissionProgress() > 0 && $monograph->getSubmissionProgress() <= 3) {
							$returner = array('label' => __('submissions.incomplete'));
						} else {
							$returner = array('label' => __('submission.status.submission'));
						}
						break;
					case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
						$returner = array('label' => __('submission.status.review'));
						break;
					case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
						$returner = array('label' => __('submission.status.review'));
						break;
					case WORKFLOW_STAGE_ID_EDITING:
						$returner = array('label' => __('submission.status.editorial'));
						break;
					case WORKFLOW_STAGE_ID_PRODUCTION:
						$returner = array('label' => __('submission.status.production'));
						break;
				}
				return $returner;
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Get the cell link action.
	 * @param $request Request
	 * @param $page string
	 * @param $operation string
	 * @param $monograph Monograph
	 * @return LinkAction
	 */
	function _getCellLinkAction($request, $page, $operation, &$monograph) {
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();

		$title = $monograph->getLocalizedTitle();
		if ( empty($title) ) $title = __('common.untitled');

		$pressId = $monograph->getPressId();
		$pressDao = DAORegistry::getDAO('PressDAO');
		$press = $pressDao->getPress($pressId);

		import('lib.pkp.classes.linkAction.request.RedirectAction');

		return new LinkAction(
			'details',
			new RedirectAction(
				$dispatcher->url(
					$request, ROUTE_PAGE,
					$press->getPath(),
					$page, $operation,
					$monograph->getId()
				)
			),
			$title
		);
	}
}

?>
