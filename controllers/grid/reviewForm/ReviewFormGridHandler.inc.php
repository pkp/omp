<?php

/**
 * @file controllers/grid/reviewForm/ReviewFormGridHandler.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormGridHandler
 * @ingroup controllers_grid_reviewForm
 *
 * @brief Handle requests for Review Form management functions.
 *
*/

import('controllers.grid.GridMainHandler');

class ReviewFormGridHandler extends GridMainHandler {
	/**
	 * Constructor
	 **/
	function ReviewFormGridHandler() {
		parent::GridMainHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('createReviewForm'));
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return ReviewFormRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandler) {
			import('controllers.grid.reviewForm.ReviewFormRowHandler');
			$rowHandler =& new ReviewFormRowHandler();
			$this->setRowHandler($rowHandler);
		}
		return parent::getRowHandler();
	}

	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		// Basic grid configuration
		$this->setId('reviewForm');
		$this->setTitle('grid.reviewForm.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForms =& $reviewFormDao->getPressReviewForms($press->getId());
		$this->setData($reviewForms);

		// Add grid-level actions
		$this->addAction(
			new GridAction(
				'createReviewForm',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'createReviewForm', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		parent::initialize($request);
	}

	/**
	 * Display form to create a new review form.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function createReviewForm(&$args, &$request) {
		// Delegate to the row handler
		$reviewFormRow =& $this->getRowHandler();

		// Calling editReviewForm with an empty row id will add a new review form.
		$reviewFormRow->editReviewForm($args, $request);
	}
}

?>
