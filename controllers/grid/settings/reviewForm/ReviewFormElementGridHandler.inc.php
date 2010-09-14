<?php

/**
 * @file controllers/grid/settings/reviewForm/ReviewFormElementGridHandler.inc.php
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

import('controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.reviewForm.ReviewFormElementGridRow');

class ReviewFormElementGridHandler extends SetupGridHandler {
	/**
	 * Constructor
	 **/
	function ReviewFormElementGridHandler() {
		parent::SetupGridHandler();
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'createReviewFormElement', 'editReviewFormElement', 'updateReviewFormElement', 'deleteReviewFormElement'));
	}


	//
	// Implementation of template methods from PKPHandler
	//
	/**
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$this->setId('reviewFormElement');
		$this->setTitle('grid.reviewFormElements.title');

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$reviewFormId = $request->getUserVar('rowId');
		$row =& $this->getRow();
		$row->setReviewFormId($reviewFormId);

		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		$reviewFormElements =& $reviewFormElementDao->getReviewFormElementsByReviewForm($reviewFormId);
		$this->setData($reviewFormElements);

		// Add grid-level actions
		$this->addAction(
			new LinkAction(
				'createReviewFormElement',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'createReviewFormElement', null, array('gridId' => $this->getId(), 'reviewFormId' => $reviewFormId)),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		// Columns
		import('controllers.grid.settings.reviewForm.ReviewFormElementGridCellProvider');
		$cellProvider =& new ReviewFormElementGridCellProvider();
		$this->addColumn(new GridColumn('reviewFormElement', 'grid.reviewFormElements.column.elements', 'controllers/grid/gridCell.tpl', $cellProvider));

		import('controllers.grid.settings.reviewForm.ReviewFormElementTypeCellProvider');
		$cellProvider =& new ReviewFormElementTypeCellProvider();
		$this->addColumn(new GridColumn('elementType',
										'common.type',
										null,
										'controllers/grid/gridCell.tpl',
										$cellProvider));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return ReviewFormElementGridRow
	 */
	function &getRowInstance() {
		$row = new ReviewFormElementGridRow();
		return $row;
	}

	//
	// Public Review Form Element Grid Actions
	//
	/**
	 * Display form to create a new review form.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function createReviewFormElement(&$args, &$request) {
		// Delegate to the row handler
		$reviewFormRow =& $this->getRow();

		// Calling editReviewForm with an empty row id will add a new review form.
		return $reviewFormRow->editReviewFormElement($args, $request);
	}


	/**
	 * Display form to create/edit a review form element.
	 * @param $args ($reviewFormId, $reviewFormElementId)
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function editReviewFormElement(&$args, &$request) {
		$this->setupTemplate($args, $request);

		$reviewFormElementId = $this->getId();
		$reviewFormId = $this->getReviewFormId();

		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_PRESS, $press->getId());
		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');

		if (!isset($reviewForm) || $reviewForm->getCompleteCount() != 0 || $reviewForm->getIncompleteCount() != 0 || ($reviewFormElementId != null && !$reviewFormElementDao->reviewFormElementExists($reviewFormElementId, $reviewFormId))) {
			return ''; // send error to modal
		} else {
			$templateMgr =& TemplateManager::getManager();

			if ($reviewFormId == null) {
				$templateMgr->assign('pageTitle', 'manager.reviewFormElements.create');
			} else {
				$templateMgr->assign('pageTitle', 'manager.reviewFormElements.edit');
			}

			import('controllers.grid.settings.reviewForm.form.ReviewFormElementForm');
			$reviewFormElementForm = new ReviewFormElementForm($reviewFormId, $reviewFormElementId);
			if ($reviewFormElementForm->isLocaleResubmit()) {
				$reviewFormElementForm->readInputData();
			} else {
				$reviewFormElementForm->initData($args, $request);
			}

			$json = new JSON('true', $reviewFormElementForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Save changes to a review form element.
	 * @param $args ($reviewFormId, $reviewFormElementId)
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function updateReviewFormElement(&$args, &$request) {
		$reviewFormId = Request::getUserVar('reviewFormId') === null? null : (int) Request::getUserVar('reviewFormId');
		$reviewFormElementId = Request::getUserVar('reviewFormElementId') === null? null : (int) Request::getUserVar('reviewFormElementId');

		$press =& Request::getPress();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_PRESS, $press->getId());

		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');

		if (!$reviewFormDao->unusedReviewFormExists($reviewFormId, ASSOC_TYPE_PRESS, $press->getId()) || ($reviewFormElementId != null && !$reviewFormElementDao->reviewFormElementExists($reviewFormElementId, $reviewFormId))) {
			Request::redirect(null, null, 'reviewFormElements', array($reviewFormId));
		}

		import('controllers.grid.settings.reviewForm.form.ReviewFormElementForm');
		$reviewFormElementForm = new ReviewFormElementForm($reviewFormId, $reviewFormElementId);
		$reviewFormElementForm->readInputData();
		$formLocale = $reviewFormElementForm->getFormLocale();

		// Reorder response items
		$response = $reviewFormElementForm->getData('possibleResponses');
		if (isset($response[$formLocale]) && is_array($response[$formLocale])) {
			usort($response[$formLocale], create_function('$a,$b','return $a[\'order\'] == $b[\'order\'] ? 0 : ($a[\'order\'] < $b[\'order\'] ? -1 : 1);'));
		}
		$reviewFormElementForm->setData('possibleResponses', $response);

		if (Request::getUserVar('addResponse')) {
			// Add a response item
			$editData = true;
			$response = $reviewFormElementForm->getData('possibleResponses');
			if (!isset($response[$formLocale]) || !is_array($response[$formLocale])) {
				$response[$formLocale] = array();
				$lastOrder = 0;
			} else {
				$lastOrder = $response[$formLocale][count($response[$formLocale])-1]['order'];
			}
			array_push($response[$formLocale], array('order' => $lastOrder+1));
			$reviewFormElementForm->setData('possibleResponses', $response);

		} else if (($delResponse = Request::getUserVar('delResponse')) && count($delResponse) == 1) {
			// Delete a response item
			$editData = true;
			list($delResponse) = array_keys($delResponse);
			$delResponse = (int) $delResponse;
			$response = $reviewFormElementForm->getData('possibleResponses');
			if (!isset($response[$formLocale])) $response[$formLocale] = array();
			array_splice($response[$formLocale], $delResponse, 1);
			$reviewFormElementForm->setData('possibleResponses', $response);
		}
		if (!isset($editData) && $reviewFormElementForm->validate()) {
			$reviewFormElementForm->execute();

			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($reviewFormElementForm->reviewFormElementId);
			$row->setData($reviewFormElementForm->reviewFormElement);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false');
			$press =& Request::getPress();
			$templateMgr =& TemplateManager::getManager();
			if ($reviewFormElementId == null) {
				$templateMgr->assign('pageTitle', 'manager.reviewFormElements.create');
			} else {
				$templateMgr->assign('pageTitle', 'manager.reviewFormElements.edit');
			}

			$reviewFormElementForm->display();
		}
		return $json->getString();
	}

	/**
	 * Delete a review form element.
	 * @param $args ($reviewFormId, $reviewFormElementId)
	 * @param $request PKPRequest
	 * @return JSON
	 */
	function deleteReviewFormElement(&$args, &$request) {
		$reviewFormId = $this->getReviewFormId();
		$reviewFormElementId = $this->getId();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$press =& Request::getPress();

		if ($reviewFormDao->unusedReviewFormExists($reviewFormId, ASSOC_TYPE_PRESS, $press->getId())) {
			$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
			$reviewFormElementDao->deleteById($reviewFormElementId);
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('settings.setup.errorDeletingReviewForm'));
		}
		return $json->getString();
	}
}

?>
