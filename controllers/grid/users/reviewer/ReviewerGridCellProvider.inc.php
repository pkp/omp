<?php

/**
 * @file controllers/grid/users/reviewer/ReviewerGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGridCellProvider
 * @ingroup controllers_grid_users_reviewer
 *
 * @brief Base class for a cell provider that can retrieve labels for reviewer grid rows
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

import('lib.pkp.classes.linkAction.request.AjaxModal');
import('lib.pkp.classes.linkAction.request.AjaxAction');

class ReviewerGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewerGridCellProvider() {
		parent::DataObjectGridCellProvider();
	}


	//
	// Template methods from GridCellProvider
	//
	/**
	 * Gathers the state of a given cell given a $row/$column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return string
	 */
	function getCellState(&$row, &$column) {
		$reviewAssignment =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($reviewAssignment, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'name':
				return ($reviewAssignment->getDateCompleted())?'linkReview':'';

			case 'editor':
				// The review has not been completed.
				if (!$reviewAssignment->getDateCompleted()) return '';

				// The reviewer has been sent an acknowledgement.
				if ($reviewAssignment->getDateAcknowledged()) {
					return 'completed';
				}

				// Check if the somebody assigned to this monograph stage has read the review.
				$monographDao =& DAORegistry::getDAO('MonographDAO');
				$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
				$userStageAssignmentDao =& DAORegistry::getDAO('UserStageAssignmentDAO');
				$viewsDao =& DAORegistry::getDAO('ViewsDAO');

				$monograph =& $monographDao->getById($reviewAssignment->getSubmissionId());

				// Get the user groups for this stage
				$userGroups =& $userGroupDao->getUserGroupsByStage(
					$monograph->getPressId(),
					$reviewAssignment->getStageId(),
					true,
					true
				);
				while ($userGroup = $userGroups->next()) {
					$roleId = $userGroup->getRoleId();
					if ($roleId != ROLE_ID_PRESS_MANAGER && $roleId != ROLE_ID_SERIES_EDITOR) continue;

					// Get the users assigned to this stage and user group
					$stageUsers =& $userStageAssignmentDao->getUsersBySubmissionAndStageId(
						$reviewAssignment->getSubmissionId(),
						$reviewAssignment->getStageId(),
						$userGroup->getId()
					);

					// mark as completed (viewed) if any of the manager/editor users viewed it.
					while ($user =& $stageUsers->next()) {
						if ($viewsDao->getLastViewDate(
							ASSOC_TYPE_REVIEW_RESPONSE,
							$reviewAssignment->getId(), $user->getId()
						)) {
							// Some user has read the review.
							return 'read';
						}
						unset($user);
					}
					unset($stageUsers);
				}

				// Nobody has read the review.
				return 'new';
			case 'reviewer':
				if ($reviewAssignment->getDateCompleted()) {
					return 'completed';
				} elseif ($reviewAssignment->getDateDue() < Core::getCurrentDate()) {
					return 'overdue';
				} elseif ($reviewAssignment->getDateConfirmed()) {
					return ($reviewAssignment->getDeclined())?'declined':'accepted';
				} elseif ($reviewAssignment->getDateResponseDue() < Core::getCurrentDate()) {
					return 'overdue';
				} else {
					return 'new';
				}
		}
	}

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'name':
				if ( $this->getCellState($row, $column) != 'linkReview') {
					return array('label' => $element->getReviewerFullName());
				}
				return array('label' => '');

			case 'editor':
			case 'reviewer':
				return array('status' => $this->getCellState($row, $column));
		}

		return parent::getTemplateVarsFromRowColumn($row, $column);
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		$reviewAssignment =& $row->getData();
		$actionArgs = array(
			'monographId' => $reviewAssignment->getSubmissionId(),
			'reviewAssignmentId' => $reviewAssignment->getId(),
			'stageId' => $reviewAssignment->getStageId()
		);

		$router =& $request->getRouter();
		$action = false;
		$state = $this->getCellState($row, $column);
		if ($state == 'linkReview') {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getById($reviewAssignment->getSubmissionId());

			$action = new LinkAction(
				'readReview',
				new AjaxModal(
					$router->url($request, null, null, 'readReview', null, $actionArgs),
					__('editor.review') . ': ' . $monograph->getLocalizedTitle(),
					'edit' //FIXME: insert icon
				),
				$reviewAssignment->getReviewerFullName(),
				$state
			);
		} elseif ($state == 'new' && $column->getId() == 'editor') {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getById($reviewAssignment->getSubmissionId());

			$action = new LinkAction(
				'readReview',
				new AjaxModal(
					$router->url($request, null, null, 'readReview', null, $actionArgs),
					__('editor.review') . ': ' . $monograph->getLocalizedTitle(),
					'edit' //FIXME: insert icon
				),
				null,
				$state
			);
		} elseif ($state == 'overdue' ||
				($column->getId() == 'reviewer') && ($state == 'new' || $state == 'accepted')) {
			$action = new LinkAction(
				'sendReminder',
				new AjaxModal(
					$router->url($request, null, null, 'editReminder', null, $actionArgs),
					__('editor.review.reminder'),
					'edit' // FIXME: insert icon.
				),
				null,
				$state
			);
		} elseif ($state == 'read') {
			$action = new LinkAction(
				'thankReviewer',
				new AjaxAction($router->url($request, null, null, 'thankReviewer', null, $actionArgs)),
				null,
				'accepted'
			);
		} elseif (in_array($state, array('', 'declined', 'completed'))) {
			// do nothing for these actions
		} else {
			// Inconsistent state
			assert(false);
		}

		return ($action) ? array($action) : array();
	}
}

?>
