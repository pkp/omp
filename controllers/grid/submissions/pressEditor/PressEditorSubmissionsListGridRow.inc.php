<?php

/**
 * @file controllers/grid/files/submissionFiles/SubmissionFilesGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileRow
 * @ingroup controllers_grid_file
 *
 * @brief Handle submission file grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class PressEditorSubmissionsListGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function PressEditorSubmissionsListGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		// Is this a new row or an existing row?
		$rowId = $this->getId();

		if (!empty($rowId) && is_numeric($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'monographId' => $rowId
			);
			$this->addAction(
				new GridAction(
					'approve',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'showApprove', null, $actionArgs),
					'grid.action.approve',
					null,
					'promote'
				));
			$this->addAction(
				new GridAction(
					'decline',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'showDecline', null, $actionArgs),
					'grid.action.decline',
					null,
					'delete'
				));
			$this->addAction(
				new GridAction(
					'moreInfo',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_NOTHING,
					$router->url($request, null, 'informationCenter.SubmissionInformationCenterHandler', 'viewInformationCenter', null, array('assocId' => $rowId)),
					'grid.action.moreInformation',
					null,
					'more_info'
				));
		}
	}
}