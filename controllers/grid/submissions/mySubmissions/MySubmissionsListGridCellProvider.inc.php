<?php

/**
 * @file controllers/grid/submissions/mySubmissions/MySubmissionsListGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListGridCellProvider
 * @ingroup controllers_grid_submissions
 *
 * @brief Class for a cell provider that can retrieve labels from submissions
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');
import('controllers.grid.submissions.SubmissionsListGridCellProvider');

class MySubmissionsListGridCellProvider extends SubmissionsListGridCellProvider {
	/**
	 * Constructor
	 */
	function MySubmissionsListGridCellProvider() {
		parent::SubmissionsListGridCellProvider();
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
			$router =& $request->getRouter();
			$dispatcher =& $router->getDispatcher();

			$title = $monograph->getLocalizedTitle();
			if ( empty($title) ) $title = __('common.untitled');

			$pressId = $monograph->getPressId();
			$pressDao = DAORegistry::getDAO('PressDAO');
			$press = $pressDao->getPress($pressId);

			if ($monograph->getSubmissionProgress() > 0 && $monograph->getSubmissionProgress() <= 3) {
				$url = $dispatcher->url($request, ROUTE_PAGE, $press->getPath(),
										'submission', 'wizard', $monograph->getSubmissionProgress(),
										array('monographId' => $monograph->getId())
										);
			} else {
				$pageAndOperation = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $monograph->getId());
				$url = $dispatcher->url($request, ROUTE_PAGE, $press->getPath(), $pageAndOperation[0], $pageAndOperation[1], $monograph->getId());
			}
			import('lib.pkp.classes.linkAction.request.RedirectAction');
			$action = new LinkAction(
				'details',
				new RedirectAction($url),
				$title
			);

			return array($action);
		}
		return parent::getCellActions($request, $row, $column, $position);
	}
}

?>
