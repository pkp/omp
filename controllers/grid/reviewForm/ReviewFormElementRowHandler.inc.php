<?php

/**
 * @file controllers/grid/reviewForm/ReviewFormElementRowHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElementRowHandler
 * @ingroup controllers_grid_reviewForm
 *
 * @brief Handle Review Form Element grid row requests.
 */

import('controllers.grid.GridRowHandler');

class ReviewFormElementRowHandler extends GridRowHandler {

	/** @var Review Form Id associated with the request **/
	var $_reviewFormId;
	
	/**
	 * Constructor
	 */
	function ReviewFormElementRowHandler() {
		parent::GridRowHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(),
				array('editReviewFormElement', 'updateReviewFormElement', 'deleteReviewFormElement'));
	}

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
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		$emptyActions = array();

		// Basic grid row configuration
		import('controllers.grid.reviewForm.ReviewFormElementGridCellProvider');
		$cellProvider =& new ReviewFormElementGridCellProvider();
		$this->addColumn(new GridColumn('reviewFormElement', 'grid.reviewFormElements.column.elements', $emptyActions, 'controllers/grid/gridCellInSpan.tpl', $cellProvider));

		import('controllers.grid.reviewForm.ReviewFormElementTypeCellProvider');
		$cellProvider =& new ReviewFormElementTypeCellProvider();
		$this->addColumn(new GridColumn('elementType', 'common.type', $emptyActions, 'controllers/grid/gridCell.tpl', $cellProvider));

		parent::initialize($request);
	}

	function _configureRow(&$request, $args = null) {
		// assumes row has already been initialized
		// do the default configuration
		parent::_configureRow($request, $args);

		$reviewFormId = $this->getReviewFormId();
		if (!isset($reviewFormId)) {
			$reviewFormId = $request->getUserVar('reviewFormId');
			$this->setReviewFormId($reviewFormId);
		}

		$actionArgs = array(
			'gridId' => $this->getGridId(),
			'reviewFormId' => $reviewFormId,
			'rowId' => $this->getId()
		);

		$router =& $request->getRouter();

		$this->addAction(
			new GridAction(
				'editReviewForm',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_REPLACE,
				$router->url($request, null, 'grid.reviewForm.ReviewFormElementRowHandler', 'editReviewFormElement', null, $actionArgs),
				'grid.action.edit',
				'edit'
			));
		$this->addAction(
			new GridAction(
				'deleteReviewForm',
				GRID_ACTION_MODE_CONFIRM,
				GRID_ACTION_TYPE_REMOVE,
				$router->url($request, null, 'grid.reviewForm.ReviewFormElementRowHandler', 'deleteReviewFormElement', null, $actionArgs),
				'grid.action.delete',
				'delete'
			));
	}

	/**
	 * Display form to create/edit a review form element.
	 * @param $args ($reviewFormId, $reviewFormElementId)
	 */
	function editReviewFormElement(&$args, &$request) {

		$this->_configureRow($request, $args);
		$this->setupTemplate($args, $request);

		$reviewFormElementId = $this->getId();
		$reviewFormId = $this->getReviewFormId();

		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, $press->getId());
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

			import('controllers.grid.reviewForm.form.ReviewFormElementForm');
			$reviewFormElementForm = new ReviewFormElementForm($reviewFormId, $reviewFormElementId);
			if ($reviewFormElementForm->isLocaleResubmit()) {
				$reviewFormElementForm->readInputData();
			} else {
				$reviewFormElementForm->initData($args, $request);
			}

			$reviewFormElementForm->display();
		}
	}

	/**
	 * Save changes to a review form element.
	 */
	function updateReviewFormElement(&$args, &$request) {
		$this->_configureRow($request, $args);

		$reviewFormId = Request::getUserVar('reviewFormId') === null? null : (int) Request::getUserVar('reviewFormId');
		$reviewFormElementId = Request::getUserVar('reviewFormElementId') === null? null : (int) Request::getUserVar('reviewFormElementId');

		$press =& Request::getPress();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, $press->getId());

		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');

		if (!$reviewFormDao->unusedReviewFormExists($reviewFormId, $press->getId()) || ($reviewFormElementId != null && !$reviewFormElementDao->reviewFormElementExists($reviewFormElementId, $reviewFormId))) {
			Request::redirect(null, null, 'reviewFormElements', array($reviewFormId));
		}

		import('controllers.grid.reviewForm.form.ReviewFormElementForm');
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

			$this->setId($reviewFormElementForm->reviewFormElementId);
			$this->setData($reviewFormElementForm->reviewFormElement);

			$json = new JSON('true', $this->renderRowInternally($request));
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
	 * @param $args array ($reviewFormId, $reviewFormElementId)
	 */
	function deleteReviewFormElement(&$args, &$request) {
		$this->_configureRow($request, $args);

		$reviewFormId = $this->getReviewFormId();
		$reviewFormElementId = $this->getId();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$press =& Request::getPress();

		if ($reviewFormDao->unusedReviewFormExists($reviewFormId, $press->getId())) {
			$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
			$reviewFormElementDao->deleteReviewFormElementById($reviewFormElementId);
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('manager.setup.errorDeletingReviewForm'));
		}
		echo $json->getString();
	}

	function setupTemplate(&$args, &$request) {
		// Load manager translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));		
	}
}