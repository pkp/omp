<?php

/**
 * @file controllers/grid/reviewForm/ReviewFormRowHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormRowHandler
 * @ingroup controllers_grid_reviewForm
 *
 * @brief Handle Review Form grid row requests.
 */

import('controllers.grid.GridRowHandler');

class ReviewFormRowHandler extends GridRowHandler {
	/** @var Review Form associated with the request **/
	var $reviewForm;
	
	/**
	 * Constructor
	 */
	function ReviewFormRowHandler() {
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
				array('editReviewForm', 'updateReviewForm', 'deleteReviewForm', 'previewReviewForm'));
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
		import('controllers.grid.reviewForm.ReviewFormGridCellProvider');
		$cellProvider =& new ReviewFormGridCellProvider();
		$this->addColumn(new GridColumn('titles', 'common.title', $emptyActions, 'controllers/grid/gridCellInSpan.tpl', $cellProvider));

		/* http://pkp.sfu.ca/bugzilla/show_bug.cgi?id=5122 */
		//$this->addColumn(new GridColumn('completeCount', 'common.completed'));
		//$this->addColumn(new GridColumn('incompleteCount', 'common.title'));

		parent::initialize($request);
	}

	function _configureRow(&$request, $args = null) {
		// assumes row has already been initialized
		// do the default configuration
		parent::_configureRow($request, $args);

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
				$router->url($request, null, 'grid.reviewForm.ReviewFormRowHandler', 'editReviewForm', null, $actionArgs),
				'grid.action.edit',
				'edit'
			));
		$this->addAction(
			new GridAction(
				'deleteReviewForm',
				GRID_ACTION_MODE_CONFIRM,
				GRID_ACTION_TYPE_REMOVE,
				$router->url($request, null, 'grid.reviewForm.ReviewFormRowHandler', 'deleteReviewForm', null, $actionArgs),
				'grid.action.delete',
				'delete'
			));
		$this->addAction(
			new GridAction(
				'previewReviewForm',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_NOTHING,
				$router->url($request, null, 'grid.reviewForm.ReviewFormRowHandler', 'previewReviewForm', null, $actionArgs),
				'grid.action.preview'
			));
		$this->addAction(
			new GridAction(
				'reviewFormElements',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_NOTHING,
				$router->url($request, null, 'grid.reviewForm.ReviewFormElementGridHandler', 'fetchGrid', null, $actionArgs),
				'grid.action.reviewFormElements'
			));

	}

	//
	// Public Review Form Row Actions
	//

	/**
	 * Display form to create/edit a review form.
	 * @param $args array, first parameter is the ID of the review form to edit
	 * @param $request PKPRequest	
	 */
	function editReviewForm(&$args, &$request) {
		$this->_configureRow($request, $args);
		$this->setupTemplate($args, $request);

		$reviewFormId = $this->getId();

		$press =& Request::getPress();
		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, $press->getId());

		if ($reviewFormId != null && (!isset($reviewForm) || $reviewForm->getCompleteCount() != 0 || $reviewForm->getIncompleteCount() != 0)) {
			Request::redirect(null, null, 'reviewForms');
		} else {
			$templateMgr =& TemplateManager::getManager();

			if ($reviewFormId == null) {
				$templateMgr->assign('pageTitle', 'manager.reviewForms.create');
			} else {
				$templateMgr->assign('pageTitle', 'manager.reviewForms.edit');
			}

			import('controllers.grid.reviewForm.form.ReviewFormForm');
			$reviewFormForm = new ReviewFormForm($reviewFormId);

			if ($reviewFormForm->isLocaleResubmit()) {
				$reviewFormForm->readInputData();
			} else {
				$reviewFormForm->initData($args, $request);
			}
			$reviewFormForm->display();
		}
	}

	/**
	 * Save changes to a review form.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateReviewForm(&$args, &$request) {
		$this->_configureRow($request, $args);

		$reviewFormId = Request::getUserVar('reviewFormId') === null? null : (int) Request::getUserVar('reviewFormId');

		if ($reviewFormId === null) {
			$reviewForm = null;
		} else {
			$router =& $request->getRouter();
			$context =& $router->getContext($request);
			$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
			$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, $context->getId());
		}

		$press =& Request::getPress();

		import('controllers.grid.reviewForm.form.ReviewFormForm');
		$reviewFormForm = new ReviewFormForm($reviewFormId);
		$reviewFormForm->readInputData();

		if ($reviewFormForm->validate()) {
			$reviewFormForm->execute();

			$this->setId($reviewFormForm->reviewForm->getReviewFormId());
			$this->setData($reviewFormForm->reviewForm);

			$json = new JSON('true', $this->renderRowInternally($request));
		} else {
			$json = new JSON('false');

			$templateMgr =& TemplateManager::getManager();
			if ($reviewFormId == null) {
				$templateMgr->assign('pageTitle', 'manager.reviewForms.create');
			} else {
				$templateMgr->assign('pageTitle', 'manager.reviewForms.edit');
			}

			$reviewFormForm->display();
		}
		return $json->getString();
	}

	/**
	 * Delete a review form.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteReviewForm(&$args, &$request) {
		$this->_configureRow($request, $args);

		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$reviewFormId = $this->getId();
		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, $press->getId());

		if (isset($reviewForm) && $reviewForm->getCompleteCount() == 0 && $reviewForm->getIncompleteCount() == 0) {
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssignments =& $reviewAssignmentDao->getByReviewFormId($reviewFormId);

			foreach ($reviewAssignments as $reviewAssignment) {
				$reviewAssignment->setReviewFormId('');
				$reviewAssignmentDao->updateObject($reviewAssignment);
			}

			$reviewFormDao->deleteReviewFormById($reviewFormId, $press->getId());
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('manager.setup.errorDeletingReviewForm'));
		}

		echo $json->getString();
	}

	/**
	 * Preview a review form.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function previewReviewForm(&$args, &$request) {
		$this->_configureRow($request, $args);

		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		$reviewFormId = $this->getId();

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewForm =& $reviewFormDao->getReviewForm($reviewFormId, $press->getId());
		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewFormId);

		if (!isset($reviewForm)) {
			return '';
		}

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('pageTitle', 'manager.reviewForms.preview');
		$templateMgr->assign_by_ref('reviewForm', $reviewForm);
		$templateMgr->assign('reviewFormElements', $reviewFormElements);
		$templateMgr->display('controllers/grid/reviewForm/previewReviewForm.tpl');
	}

	function setupTemplate(&$args, &$request) {
		// Load manager translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));		
	}
}