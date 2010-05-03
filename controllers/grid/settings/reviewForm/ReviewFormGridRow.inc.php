<?php

/**
 * @file controllers/grid/settings/reviewForm/ReviewFormGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormGridRow
 * @ingroup controllers_grid_reviewForm
 *
 * @brief Handle Review Form grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ReviewFormGridRow extends GridRow {
	/** @var Review Form associated with the request **/
	var $reviewForm;

	/**
	 * Constructor
	 */
	function ReviewFormGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//

	/**
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
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $this->getId()
			);

			$router =& $request->getRouter();

			$this->addAction(
				new GridAction(
					'editReviewForm',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editReviewForm', null, $actionArgs),
					'grid.action.edit',
					'edit'
				));
			$this->addAction(
				new GridAction(
					'deleteReviewForm',
					GRID_ACTION_MODE_CONFIRM,
					GRID_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteReviewForm', null, $actionArgs),
					'grid.action.delete',
					'delete'
				));
			$this->addAction(
				new GridAction(
					'previewReviewForm',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_NOTHING,
					$router->url($request, null, 'grid.setup.reviewForm.ReviewFormGridRow', 'previewReviewForm', null, $actionArgs),
					Locale::translate('grid.action.preview')
				));
			$this->addAction(
				new GridAction(
					'reviewFormElements',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_NOTHING,
					$router->url($request, null, 'grid.setup.reviewForm.ReviewFormElementGridHandler', 'fetchGrid', null, $actionArgs),
					Locale::translate('grid.action.reviewFormElements')
				));
		}
	}

	function setupTemplate(&$args, &$request) {
		// Load manager translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));
	}
}