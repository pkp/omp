<?php

/**
 * @file controllers/grid/settings/reviewForm/ReviewFormElementGridRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElementGridRow
 * @ingroup controllers_grid_settings_reviewForm
 *
 * @brief Handle Review Form Element grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ReviewFormElementGridRow extends GridRow {

	/** @var Review Form Id associated with the request **/
	var $_reviewFormId;

	/**
	 * Constructor
	 */
	function ReviewFormElementGridRow() {
		parent::GridRow();
	}

	//
	// Getters/Setters
	//

	/**
	 * get the associated review form id
	 * @return int
	 */
	function getReviewFormId() {
		return $this->_reviewFormId;
	}

	/**
	 * set the associated review form id
	 * @param $reviewFormId
	 */
	function setReviewFormId($reviewFormId) {
		$this->_reviewFormId = $reviewFormId;
	}

	//
	// Overridden template methods
	//
	/**
	 * Configure the grid row
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			$reviewFormId = $this->getReviewFormId();
			if (!isset($reviewFormId)) {
				$reviewFormId = $request->getUserVar('reviewFormId');
				$this->setReviewFormId($reviewFormId);
			}

			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'reviewFormId' => $reviewFormId,
				'rowId' => $rowId
			);

			$router =& $request->getRouter();

			$this->addAction(
				new LegacyLinkAction(
					'editReviewForm',
					LINK_ACTION_MODE_MODAL,
					LINK_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editReviewFormElement', null, $actionArgs),
					'grid.action.edit',
					null,
					'edit'
				)
			);
			$this->addAction(
				new LegacyLinkAction(
					'deleteReviewForm',
					LINK_ACTION_MODE_CONFIRM,
					LINK_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteReviewFormElement', null, $actionArgs),
					'grid.action.delete',
					null,
					'delete',
					'common.confirmDelete'
				)
			);
		}

		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate($args, &$request) {
		// Load manager translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));
	}
}

?>
