<?php

/**
 * @file controllers/grid/content/navigation/ManageFooterGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterGridHandler
 * @ingroup controllers_grid_content_navigation
 *
 * @brief Handle manager requests for Footer navigation items.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');


// import format grid specific classes
import('controllers.grid.content.navigation.FooterGridCellProvider');
import('controllers.grid.content.navigation.FooterGridCategoryRow');
import('controllers.grid.content.navigation.FooterGridRow');
import('controllers.grid.content.navigation.form.FooterCategoryForm');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class ManageFooterGridHandler extends CategoryGridHandler {
	/**
	 * @var Press
	 */
	var $_press;

	/**
	 * Constructor
	 */
	function ManageFooterGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addFooterCategory',
				'editFooterCategory', 'updateFooterCategory', 'deleteFooterCategory'));
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the press associated with this grid.
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}

	/**
	 * Set the Press (authorized)
	 * @param Press
	 */
	function setPress($press) {
		$this->_press =& $press;
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		$returner = parent::authorize($request, $args, $roleAssignments);

		$footerLinkId = $request->getUserVar('footerLinkId');
		if ($footerLinkId) {
			$press =& $request->getPress();
			$footerLinkDao =& DAORegistry::getDAO('FooterLinkDAO');
			$footerLink =& $footerLinkDao->getById($footerLinkId, $press->getId());
			if (!isset($footerLink)) {
				return false;
			}
		}

		return $returner;
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic grid configuration
		$this->setTitle('grid.content.navigation.footer');

		// Set the no items row text
		$this->setEmptyRowText('grid.content.navigation.footer.noneExist');

		$press =& $request->getPress();
		$this->setPress($press);

		// Columns
		import('controllers.grid.content.navigation.FooterGridCellProvider');
		$footerLinksGridCellProvider = new FooterGridCellProvider();

		$gridColumn = new GridColumn(
				'title',
				'common.title',
				null,
				'controllers/grid/gridCell.tpl',
				$footerLinksGridCellProvider,
				array()
			);

		$gridColumn->addFlag('html', true);
		$this->addColumn($gridColumn);

		// Add grid action.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addFooterCategoryLink',
				new AjaxModal(
					$router->url($request, null, null, 'addFooterCategory', null, null),
					__('grid.content.navigation.footer.addCategory'),
					'add',
					true
				),
				__('grid.content.navigation.footer.addCategory'),
				'add_item')
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return FooterGridRow
	 */
	function &getRowInstance() {
		$row = new FooterGridRow($this->getPress());
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 * @return FooterGridCategoryRow
	 */
	function &getCategoryRowInstance() {
		$row = new FooterGridCategoryRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData($category) {

		$footerLinkDao =& DAORegistry::getDAO('FooterLinkDAO');
		$press =& $this->getPress();
		$footerLinks =& $footerLinkDao->getByCategoryId($category->getId(), $press->getId());
		return $footerLinks->toArray();
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the press.
	 * @return array
	 */
	function getRequestArgs() {
		$press =& $this->getPress();
		return array(
			'pressId' => $press->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		// set our labels for the FooterLink categories.
		$footerCategoryDao =& DAORegistry::getDAO('FooterCategoryDAO');
		$press =& $this->getPress();
		$categories =& $footerCategoryDao->getByPressId($press->getId());
		$data = array();
		while ($category =& $categories->next()) {
			$data[ $category->getId() ] = $category;
		}

		return $data;
	}


	//
	// Public Footer Grid Actions
	//

	/**
	 * Add a footer category entry.  This simply calls editFooterCategory().
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addFooterCategory($args, &$request) {
		return $this->editFooterCategory($args, $request);
	}

	/**
	 * Edit a footer category entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editFooterCategory($args, &$request) {
		$footerCategoryId = $request->getUserVar('footerCategoryId');
		$footerCategoryDao =& DAORegistry::getDAO('FooterCategoryDAO');
		$press =& $request->getPress();

		$footerCategory =& $footerCategoryDao->getById($footerCategoryId, $press->getId());
		$footerCategoryForm = new FooterCategoryForm($press->getId(), $footerCategory);
		$footerCategoryForm->initData($args, $request);

		$json = new JSONMessage(true, $footerCategoryForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a footer category entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateFooterCategory($args, &$request) {
		// Identify the footerLink entry to be updated
		$footerCategoryId = $request->getUserVar('footerCategoryId');

		$press =& $this->getPress();

		$footerCategoryDao =& DAORegistry::getDAO('FooterCategoryDAO');
		$footerCategory =& $footerCategoryDao->getById($footerCategoryId, $press->getId());

		// Form handling
		$footerCategoryForm = new FooterCategoryForm($press->getId(), $footerCategory);
		$footerCategoryForm->readInputData();
		if ($footerCategoryForm->validate()) {
			$footerCategoryId = $footerCategoryForm->execute($request);

			if(!isset($footerCategory)) {
				// This is a new entry
				$footerCategory =& $footerCategoryDao->getById($footerCategoryId, $press->getId());
				$notificationContent = __('notification.addedFooterCategory');

				// Prepare the grid row data
				$row =& $this->getRowInstance();
				$row->setGridId($this->getId());
				$row->setId($footerCategoryId);
				$row->setData($footerCategory);
				$row->initialize($request);
			} else {
				$notificationContent = __('notification.editedFooterCategory');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $footerCategoryForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a footer category entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteFooterCategory($args, &$request) {

		// Identify the entry to be deleted
		$footerCategoryId = $request->getUserVar('footerCategoryId');

		$footerCategoryDao =& DAORegistry::getDAO('FooterCategoryDAO');
		$press =& $this->getPress();
		$footerCategory =& $footerCategoryDao->getById($footerCategoryId, $press->getId());
		if (isset($footerCategory)) { // authorized

			// remove links in this category.
			$footerLinkDao =& DAORegistry::getDAO('FooterLinkDAO');
			$footerLinks =& $footerLinkDao->getByCategoryId($footerCategoryId, $press->getId());
			while ($footerLink =& $footerLinks->next()) {
				$footerLinkDao->deleteObject($footerLink);
			}

			$result = $footerCategoryDao->deleteObject($footerCategory);

			if ($result) {
				$currentUser =& $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedFooterCategory')));
				return DAO::getDataChangedEvent();
			} else {
				$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
				return $json->getString();
			}
		}
	}
}
?>
