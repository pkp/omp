<?php

/**
 * @file classes/controllers/grid/submissions/SubmissionsListGridCellProvider.inc.php
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
import('controllers.grid.submissions.SubmissionsListGridCellProvider');

class AssignedSubmissionsListGridCellProvider extends SubmissionsListGridCellProvider {
	/**
	 * Constructor
	 */
	function AssignedSubmissionsListGridCellProvider() {
		parent::SubmissionsListGridCellProvider();
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LegacyLinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		if ( $column->getId() == 'title' ) {
			$monograph =& $row->getData();
			$router =& $request->getRouter();
			$dispatcher =& $router->getDispatcher();

			$title = $monograph->getLocalizedTitle();
			if ( empty($title) ) $title = Locale::translate('common.untitled');

			$pressId = $monograph->getPressId();
			$pressDao = DAORegistry::getDAO('PressDAO');
			$press = $pressDao->getPress($pressId);

			$stageId = $monograph->getCurrentStageId();
			$monographId = $monograph->getId();
			$user =& $request->getUser();
			switch ($stageId) {
				case 1: default:
					$url = $dispatcher->url($request, ROUTE_PAGE, $press->getPath(), 'workflow', 'submission', $monographId);
					break;
				case 2:
				case 3:
					// If user is reviewer for submission, send to review wizard; Else they are an editor in the review stage
					$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
					$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($monographId, $user->getId(), $monograph->getCurrentRound());
					if(isset($reviewAssignment)) {
						$url = $dispatcher->url($request, ROUTE_PAGE, null, 'reviewer', 'submission', null, array('monographId' => $monographId, 'reviewId' => $reviewAssignment->getId()));
					} else {
						$url = $dispatcher->url($request, ROUTE_PAGE, $press->getPath(), 'workflow', 'review', array($monographId));
					}
					break;
				case 4:
					$url = $dispatcher->url($request, ROUTE_PAGE, $press->getPath(), 'workflow', 'copyediting', $monograph->getId());
					break;
				case 5:
					$url = $dispatcher->url($request, ROUTE_PAGE, $press->getPath(), 'workflow', 'production', $monograph->getId());
					break;
			}

			$action = new LegacyLinkAction(
				'details',
				LINK_ACTION_MODE_LINK,
				LINK_ACTION_TYPE_NOTHING,
				$url,
				null,
				$title
			);

			return array($action);
		}
		return parent::getCellActions($request, $row, $column, $position);
	}
}

?>
