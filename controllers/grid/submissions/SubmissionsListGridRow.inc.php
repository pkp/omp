<?php

/**
 * @file controllers/grid/submissions/SubmissionsListGridRow.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($rowId);

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$press = & $pressDao->getPress($monograph->getPressId());

		if (!empty($rowId) && is_numeric($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$this->addAction(
					new LinkAction(
							'moreInfo',
							new AjaxModal(
									$router->url($request, null,
											'informationCenter.SubmissionInformationCenterHandler',
											'viewInformationCenter', null, array('monographId' => $rowId)),
									__('informationCenter.informationCenter'),
									'informationCenter'),
							__('grid.action.moreInformation'),
							'more_info'));
		}
	}
}
