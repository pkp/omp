<?php

/**
 * @file classes/controllers/grid/submissions/reviewer/ReviewerSubmissionsListGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionsListGridCellProvider.inc.php
 * @ingroup controllers_grid_submissionsList_reviewer
 *
 * @brief Class for a cell provider that can retrieve labels from submissions
 */

import('controllers.grid.submissions.SubmissionsListGridCellProvider');

class ReviewerSubmissionsListGridCellProvider extends SubmissionsListGridCellProvider {
	/**
	 * Constructor
	 */
	function ReviewerSubmissionsListGridCellProvider() {
		parent::SubmissionsListGridCellProvider();
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of GridAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		if ( $column->getId() == 'title' ) {

			$monograph =& $row->getData();
			$router =& $request->getRouter();
			$dispatcher =& $router->getDispatcher();
			$user =& $request->getUser();

			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssigment =& $reviewAssignmentDao->getReviewAssignment($monograph->getId(), $user->getId(), $monograph->getCurrentRound(), $monograph->getCurrentReviewType());
			$actionArgs = array(
				'gridId' => $row->getGridId(),
				'reviewId' => $reviewAssigment->getId()
			);

			$action =& new GridAction(
							'performReview',
							GRID_ACTION_MODE_LINK,
							GRID_ACTION_TYPE_NOTHING,
							$dispatcher->url($request, ROUTE_PAGE, null, 'reviewer', 'submission', $reviewAssigment->getId()),
							null,
							$monograph->getLocalizedTitle(),
							$state
						);
			return array($action);
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
	function getTemplateVarsFromRowColumn(&$row, &$column) {
		// do not set the label because the cell contents will be a link set by getCellActions
		if ( $column->getId() == 'title' ) {
			return array();
		}

		// if this is not a userGroupId column, then fallback on the parent.
		return parent::getTemplateVarsFromRowColumn($row, $column);
	}
}