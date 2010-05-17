<?php

/**
 * @file classes/controllers/grid/users/ReviewerGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObjectGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Base class for a cell provider that can retrieve labels for submission contributors
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
				return ($element->getDateCompleted())?'new':'';
			case 'reviewer':
				if ( $element->getDateCompleted() ) {
					return 'completed';
				} elseif ( $element->getDateDue() < Core::getCurrentDate()) {
					return 'overdue';
				} elseif ( $element->getDateConfirmed() ) {
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
	 * @return array an array of GridAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		$state = $this->getCellState($row, $column);

		$monograph =& $row->getData();
		$router =& $request->getRouter();
		$actionArgs = array(
			'gridId' => $row->getGridId(),
			'reviewId' => $monograph->getId()
		);

		$action = false;
		switch ($state) {
			case 'linkReview':
				$reviewAssignment =& $row->getData();
				$action =& new GridAction(
								'readReview',
								GRID_ACTION_MODE_MODAL,
								GRID_ACTION_TYPE_REPLACE,
								$router->url($request, null, null, 'readReview', null, $actionArgs),
								null,
								$reviewAssignment->getReviewerFullName()
							);
			case 'new':
				// The 'new' state could be for the editor or the reviewer
				if ( is_numeric($column->getId()) ) {
					$reviewAssignment =& $row->getData();
					$action =& new GridAction(
									'readReview',
									GRID_ACTION_MODE_MODAL,
									GRID_ACTION_TYPE_REPLACE,
									$router->url($request, null, null, 'readReview', null, $actionArgs),
									null,
									null,
									$state
								);
				}
				// There is no action for the reviewer
				break;
			case 'declined':
			case 'accepted':
			case 'completed':
				break;
			case 'overdue':
				$action =& new GridAction(
								'sendReminder',
								GRID_ACTION_MODE_MODAL,
								GRID_ACTION_TYPE_REPLACE,
								$router->url($request, null, null, 'sendReminder', null, $actionArgs),
								null,
								null,
								$state
							);
				break;
		}
		return ($action)?array($action):array();
	}
}