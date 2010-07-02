<?php

/**
 * @file controllers/grid/users/submissionContributor/ReviewerGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGridRow
 * @ingroup controllers_grid_submissionContributor
 *
 * @brief SubmissionContributor grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ReviewerGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function ReviewerGridRow() {
		parent::GridRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Retrieve the monograph id from the request
		$monographId = $request->getUserVar('monographId');
		assert(is_numeric($monographId));

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $monographId,
				'submissionContributorId' => $rowId
			);
			$this->addAction(
				new LinkAction(
					'remove',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'editSubmissionContributor', null, $actionArgs),
					'grid.action.remove',
					null,
					'delete'
				)
			);
			$this->addAction(
				new LinkAction(
					'moreInfo',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'moreInformation', null, $actionArgs),
					'grid.action.moreInformation',
					null,
					'more_info'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}