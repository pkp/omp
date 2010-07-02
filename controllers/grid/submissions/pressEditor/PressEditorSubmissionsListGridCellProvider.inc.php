<?php

/**
 * @file classes/controllers/grid/submissions/pressEditor/PressEditorSubmissionsListGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressEditorSubmissionsListGridCellProvider.inc.php
 * @ingroup controllers_grid_submissionsList_pressEditor
 *
 * @brief Class for a cell provider that can retrieve labels from submissions
 */

import('controllers.grid.submissions.SubmissionsListGridCellProvider');

class PressEditorSubmissionsListGridCellProvider extends SubmissionsListGridCellProvider {
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
		$columnId = $column->getId();
		$monograph =& $row->getData();
		assert(is_a($monograph, 'Monograph') && !empty($columnId));
		// numeric means its a userGroupId column
		if ( is_numeric($columnId) ) {
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroup =& $userGroupDao->getById($columnId);
			$roleId = $userGroup->getRoleId();
			if ( $roleId == ROLE_ID_EDITOR || $roleId == ROLE_ID_SERIES_EDITOR ) {
				// First columns are the PressEditors and SeriesEditors
				// Determine status of editor columns

				$monographId = $monograph->getId();
				$reviewType = $monograph->getCurrentReviewType();
				$round = $monograph->getCurrentRound();

				// FIXME: need to implement more statuses
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				if ( !$reviewRoundDao->reviewRoundExists($monographId, $reviewType, $round) ) {
					return 'new';
				} else {
					return 'accepted';
				}
			} else if ( $roleId == ROLE_ID_AUTHOR ) {
				// Following columns are potential submitters
				if ( $columnId == $monograph->getUserGroupId() ) {
					// Show that this column's user group is the submitter
					return 'uploaded';
				}
				// If column is not the submitter, cell is always empty.
				return '';
			}
		}

		return parent::getCellState($row, $column);
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		$state = $this->getCellState($row, $column);

		$monograph =& $row->getData();
		$router =& $request->getRouter();
		$actionArgs = array(
			'gridId' => $row->getGridId(),
			'monographId' => $monograph->getId(),
			'reviewType' => $monograph->getCurrentReviewType(),
			'round' => $monograph->getCurrentRound()
		);

		switch ($state) {
			case 'new':
				$action =& new LinkAction(
								'showApproveAndReview',
								LINK_ACTION_MODE_MODAL,
								LINK_ACTION_TYPE_REPLACE,
								$router->url($request, null, null, 'showApproveAndReview', null, $actionArgs),
								'grid.action.approveForReview',
								null,
								$state
							);
				return array($action);
			case 'accepted':
				$action =& new LinkAction(
								'showReview',
								LINK_ACTION_MODE_MODAL,
								LINK_ACTION_TYPE_REPLACE,
								$router->url($request, null, null, 'showReview', null, $actionArgs),
								'grid.action.approve',
								null,
								$state
							);
				return array($action);
			case 'uploaded':
				break;
		}

		return array();
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
		// numeric means its a userGroupId column
		if ( is_numeric($column->getId())) {
			$state = $this->getCellState($row, $column);
			return array('status' => $state);
		}

		// if this is not a userGroupId column, then fallback on the parent.
		return parent::getTemplateVarsFromRowColumn($row, $column);
	}
}