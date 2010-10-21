<?php

/**
 * @file classes/controllers/grid/submissions/SubmissionsListGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListGridCellProvider
 * @ingroup controllers_grid_submissions
 *
 * @brief Class for a cell provider that can retrieve labels from submissions
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class SubmissionsListGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function SubmissionsListGridCellProvider() {
		parent::DataObjectGridCellProvider();
	}


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
			$router =& $request->getRouter();
			$dispatcher =& $router->getDispatcher();

			$title = $monograph->getLocalizedTitle();
			if ( empty($title) ) $title = Locale::translate('common.untitled');

			$pressId = $monograph->getPressId();
			$pressDao = DAORegistry::getDAO('PressDAO');
			$press = $pressDao->getPress($pressId);

			$action =& new LinkAction(
							'details',
							LINK_ACTION_MODE_LINK,
							LINK_ACTION_TYPE_NOTHING,
							$dispatcher->url($request, ROUTE_PAGE, $press->getPath(), 'submission', 'details', $monograph->getId()),
							null,
							$title
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
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$element =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));

		$pressId = $element->getPressId();
		$pressDao = DAORegistry::getDAO('PressDAO');
		$press = $pressDao->getPress($pressId);

		switch ($columnId) {
			case 'title':
				$title = $element->getLocalizedTitle();
				if ( empty($title) ) $title = Locale::translate('common.untitled');
				return array('label' => $title);
				break;
			case 'press':
				return array('label' => $press->getLocalizedName());
				break;
			case 'author':
				return array('label' => $element->getAuthorString(true));
				break;
			case 'dateAssigned':
				$dateAssigned = strftime(Config::getVar('general', 'date_format_short'), strtotime($element->getDateAssigned()));
				if ( empty($dateAssigned) ) $dateAssigned = '--';
				return array('label' => $dateAssigned);
				break;
			case 'dateDue':
				$dateDue = strftime(Config::getVar('general', 'date_format_short'), strtotime($element->getDateDue()));
				if ( empty($dateDue) ) $dateDue = '--';
				return array('label' => $dateDue);
				break;
			case 'status':
				$stageId = $element->getCurrentStageId();
				switch ($stageId) {
					case WORKFLOW_STAGE_ID_SUBMISSION: default:
						$returner = array('label' => Locale::translate('submission.status.submission'));
						break;
					case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
						$returner = array('label' => Locale::translate('submission.status.review'));
						break;
					case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
						$returner = array('label' => Locale::translate('submission.status.review'));
						break;
					case WORKFLOW_STAGE_ID_EDITING:
						$returner = array('label' => Locale::translate('submission.status.editorial'));
						break;
					case WORKFLOW_STAGE_ID_PRODUCTION:
						$returner = array('label' => Locale::translate('submission.status.production'));
						break;
				}
				return $returner;
		}
	}
}