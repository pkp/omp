<?php

/**
 * @file controllers/grid/users/reviewer/ReviewerGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGridRow
 * @ingroup controllers_grid_users_reviewer
 *
 * @brief Reviewer grid row definition
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
				'reviewId' => $rowId
			);
			$this->addAction(
				new LegacyLinkAction(
					'remove',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteReviewer', null, $actionArgs),
					'grid.action.remove',
					null,
					'delete',
					Locale::translate('common.confirmDelete')
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}