<?php

/**
 * @file controllers/grid/reviewForm/ReviewFormElementGridHandler.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElementGridHandler
 * @ingroup controllers_grid_reviewForm
 *
 * @brief Handle requests for Review Form Element management functions.
 *
*/

import('controllers.grid.GridMainHandler');

class ReviewFormElementGridHandler extends GridMainHandler {
	/**
	 * Constructor
	 **/
	function ReviewFormElementGridHandler() {
		parent::GridMainHandler();
	}

	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('createReviewFormElement'));
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return ReviewFormRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandler) {
			import('controllers.grid.reviewForm.ReviewFormElementRowHandler');
			$rowHandler =& new ReviewFormElementRowHandler();
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
		$this->setId('reviewFormElement');
		$this->setTitle('grid.reviewFormElements.title');

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$reviewFormId = $request->getUserVar('rowId');
		$rowHandler =& $this->getRowHandler();
		$rowHandler->setReviewFormId($reviewFormId);

		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		$reviewFormElements =& $reviewFormElementDao->getReviewFormElementsByReviewForm($reviewFormId);
		$this->setData($reviewFormElements);

		// Add grid-level actions
		$this->addAction(
			new GridAction(
				'createReviewFormElement',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'createReviewFormElement', null, array('gridId' => $this->getId(), 'reviewFormId' => $reviewFormId)),
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
	function createReviewFormElement(&$args, &$request) {
		// Delegate to the row handler
		$reviewFormRow =& $this->getRowHandler();

		// Calling editReviewForm with an empty row id will add a new review form.
		$reviewFormRow->editReviewFormElement($args, $request);
	}
}

?>
