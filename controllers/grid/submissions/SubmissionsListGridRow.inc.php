<?php

/**
 * @file controllers/grid/submissions/SubmissionsListGridRow.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileRow
 * @ingroup controllers_grid_submissions_pressEditor
 *
 * @brief Handle editor submission list grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');
import('lib.pkp.classes.linkAction.request.AjaxModal');

class SubmissionsListGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SubmissionsListGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		$rowId = $this->getId();

		if (!empty($rowId) && is_numeric($rowId)) {
			// Actions
			import('controllers.informationCenter.linkAction.SubmissionInfoCenterLinkAction');
			$this->addAction(new SubmissionInfoCenterLinkAction($request, $rowId, 'grid.action.moreInformation'));
		}
	}
}

?>
