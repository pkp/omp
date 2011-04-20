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
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'name':
				return ($element->getDateCompleted())?'linkReview':'';

			case is_numeric($columnId):
				// numeric implies a role column.
				if ($element->getDateCompleted()) {
					$sessionManager =& SessionManager::getManager();
					$session =& $sessionManager->getUserSession();
					$user =& $session->getUser();
					$viewsDao =& DAORegistry::getDAO('ViewsDAO');
					$lastViewed = $viewsDao->getLastViewDate(ASSOC_TYPE_REVIEW_RESPONSE, $element->getId(), $user->getId());
					if($lastViewed) {
						return 'completed';
					} else {
						return 'new';
					}
				}
				return '';

			case 'reviewer':
				if ($element->getDateCompleted()) {
					return 'completed';
				} elseif ($element->getDateDue() < Core::getCurrentDate()) {
					return 'overdue';
				} elseif ($element->getDateConfirmed()) {
					return ($element->getDeclined())?'declined':'accepted';
				}
				return 'new';
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

			case is_numeric($columnId):
			case 'reviewer':
				return array('status' => $this->getCellState($row, $column));
		}

		return parent::getTemplateVarsFromRowColumn($row, $column);
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LegacyLinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		$reviewAssignment =& $row->getData();
		$actionArgs = array(
			'gridId' => $row->getGridId(),
			'monographId' => $reviewAssignment->getSubmissionId(),
			'reviewId' => $reviewAssignment->getId(),
			'reviewType' => $reviewAssignment->getReviewType(),
			'round' => $reviewAssignment->getRound()
		);

		$router =& $request->getRouter();
		$action = false;
		$state = $this->getCellState($row, $column);
		switch ($state) {
			case 'linkReview':
				$action = new LegacyLinkAction(
					'readReview',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_NOTHING,
					$router->url($request, null, null, 'readReview', null, $actionArgs),
					null,
					$reviewAssignment->getReviewerFullName()
				);
				break;

			case 'new':
				// The 'new' state could be for the editor or the reviewer.
				if (is_numeric($column->getId()) ) {
					$action = new LegacyLinkAction(
						'readReview',
						LINK_ACTION_MODE_MODAL,
						LINK_ACTION_TYPE_NOTHING,
						$router->url($request, null, null, 'readReview', null, $actionArgs),
						null,
						null,
						$state
					);
				}
				break;

			case 'declined':
			case 'accepted':
			case 'completed':
				// There are no actions for these states.
				break;

			case 'overdue':
				$action = new LinkAction(
					'sendReminder',
					new AjaxModal(
						$router->url($request, null, null, 'editReminder', null, $actionArgs),
						__('editor.review.reminder'),
						'edit'
					),
					'',
					$state
				);
				break;
		}

		return ($action) ? array($action) : array();
	}
}

?>
