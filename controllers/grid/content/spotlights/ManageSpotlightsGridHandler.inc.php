<?php

/**
 * @file controllers/grid/content/spotlights/ManageSpotlightsGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridHandler
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Handle publication format grid requests for spotlights.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');


// import format grid specific classes
import('controllers.grid.content.spotlights.SpotlightsGridCellProvider');
import('controllers.grid.content.spotlights.SpotlightsGridCategoryRow');
import('controllers.grid.content.spotlights.SpotlightsGridRow');
import('controllers.grid.content.spotlights.form.SpotlightForm');

// import Spotlight class for class constants
import('classes.spotlight.Spotlight');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class ManageSpotlightsGridHandler extends CategoryGridHandler {

	/**
	 * @var Press
	 */
	var $_press;

	/**
	 * Constructor
	 */
	function ManageSpotlightsGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addSpotlight', 'editSpotlight',
				'updateSpotlight', 'deleteSpotlight', 'itemAutocomplete'));
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

		$spotlightId = $request->getUserVar('spotlightId');
		if ($spotlightId) {
			$press =& $request->getPress();
			$spotlightDao =& DAORegistry::getDAO('SpotlightDAO');
			$spotlight =& $spotlightDao->getById($spotlightId);
			if ($spotlight == null || $spotlight->getPressId() != $press->getId()) {
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
		$this->setTitle('spotlight.spotlights');

		// Set the no items row text
		$this->setEmptyRowText('spotlight.noneExist');

		$press =& $request->getPress();
		$this->setPress($press);

		// Columns
		import('controllers.grid.content.spotlights.SpotlightsGridCellProvider');
		$spotlightsGridCellProvider = new SpotlightsGridCellProvider();
		$this->addColumn(
			new GridColumn('title',
				'common.title',
				null,
				'controllers/grid/gridCell.tpl',
				$spotlightsGridCellProvider,
				array('width' => 60)
			)
		);

		$this->addColumn(
			new GridColumn('type',
				'common.type',
				null,
				'controllers/grid/gridCell.tpl',
				$spotlightsGridCellProvider
			)
		);

		// Add grid action.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addSpotlight',
				new AjaxModal(
					$router->url($request, null, null, 'addSpotlight', null, null),
					__('grid.action.addItem'),
					'add',
					true
				),
				__('grid.action.addItem'),
				'add')
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return SpotlightsGridRow
	 */
	function &getRowInstance() {
		$row = new SpotlightsGridRow($this->getPress());
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 * @return SpotlightsGridCategoryRow
	 */
	function &getCategoryRowInstance() {
		$row = new SpotlightsGridCategoryRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData($category) {

		$spotlightDao =& DAORegistry::getDAO('SpotlightDAO');
		$press =& $this->getPress();
		$spotlights =& $spotlightDao->getByLocationAndPressId($category['location'], $press->getId());
		return $spotlights->toArray();
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
		// set our labels for the two Spotlight categories
		$categories = array(
				array('name' => 'grid.content.spotlights.category.homepage', 'location' => SPOTLIGHT_LOCATION_HOMEPAGE),
				array('name' => 'grid.content.spotlights.category.sidebar', 'location' => SPOTLIGHT_LOCATION_SIDEBAR)
			);

		return $categories;
	}


	//
	// Public Spotlights Grid Actions
	//

	function addSpotlight($args, $request) {
		return $this->editSpotlight($args, $request);
	}

	/**
	 * Edit a spotlight entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editSpotlight($args, &$request) {
		$spotlightId = (int)$request->getUserVar('spotlightId');
		$press =& $request->getPress();
		$pressId = $press->getId();

		$spotlightForm =$this->_getForm($pressId, $spotlightId);
		$spotlightForm->initData($args, $request);

		$json = new JSONMessage(true, $spotlightForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a spotlight entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateSpotlight($args, &$request) {
		// Identify the spotlight entry to be updated
		$spotlightId = $request->getUserVar('spotlightId');

		$press =& $this->getPress();

		$spotlightDao =& DAORegistry::getDAO('SpotlightDAO');
		$spotlight = $spotlightDao->getById($spotlightId, $press->getId());

		// Form handling
		$spotlightForm = $this->_getForm($press->getId(), $spotlightId);

		$spotlightForm->readInputData();
		if ($spotlightForm->validate()) {
			$spotlightId = $spotlightForm->execute($request);

			if(!isset($spotlight)) {
				// This is a new entry
				$spotlight =& $spotlightDao->getById($spotlightId, $press->getId());
				// New added entry action notification content.
				$notificationContent = __('notification.addedSpotlight');
			} else {
				// entry edit action notification content.
				$notificationContent = __('notification.editedSpotlight');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($spotlightId);
			$row->setData($spotlight);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $spotlightForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a spotlight entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteSpotlight($args, &$request) {

		// Identify the entry to be deleted
		$spotlightId = $request->getUserVar('spotlightId');

		$spotlightDao =& DAORegistry::getDAO('SpotlightDAO');
		$press =& $this->getPress();
		$spotlight =& $spotlightDao->getById($spotlightId, $press->getId());
		if ($spotlight != null) { // authorized

			$result = $spotlightDao->deleteObject($spotlight);

			if ($result) {
				$currentUser =& $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedSpotlight')));
				return DAO::getDataChangedEvent();
			} else {
				$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
				return $json->getString();
			}
		}
	}

	/**
	 * Returns a JSON list for the autocomplete field. Fetches a list of possible spotlight options
	 * based on the spotlight type chosen.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function itemAutocomplete($args, &$request) {

		$spotlightType = (int)$request->getUserVar('type');
		$name = $request->getUserVar('name');

		$press =& $this->getPress();

		$itemList = array();

		switch ($spotlightType) {
			case SPOTLIGHT_TYPE_BOOK:
				$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
				$publishedMonographs =& $publishedMonographDao->getByPressId($press->getId());
				while ($monograph =& $publishedMonographs->next()) {
					if ($name == '' || preg_match('/'. preg_quote($name, '/') . '/i', $monograph->getLocalizedTitle())) {
						$itemList[] = array('label' => $monograph->getLocalizedTitle(), 'value' => $monograph->getId());
					}
				}
				break;
			case SPOTLIGHT_TYPE_SERIES:
				$seriesDao =& DAORegistry::getDAO('SeriesDAO');
				$allSeries =& $seriesDao->getByPressId($press->getId());
				while ($series =& $allSeries->next()) {
					if ($name == '' || preg_match('/'. preg_quote($name, '/') . '/i', $series->getLocalizedTitle())) {
						$itemList[] = array('label' => $series->getLocalizedTitle(), 'value' => $series->getId());
					}
				}
				break;
			case SPOTLIGHT_TYPE_AUTHOR:
				$authorDao =& DAORegistry::getDAO('AuthorDAO');
				$authors =& $authorDao->getAuthorsAlphabetizedByPress($press->getId());
				while ($author =& $authors->next()) {
					if ($name == '' || preg_match('/'. preg_quote($name, '/') . '/i', $author->getFullName())) {
						$itemList[] = array('label' => $author->getFullName(), 'value' => $author->getId());
					}
				}
				break;
			default:
				fatalError('invalid type specified');
		}

		if (count($itemList) == 0) {
			return $this->noAutocompleteResults();
		}

		$json = new JSONMessage(true, $itemList);
		return $json->getString();
	}

	/**
	 * Internal method to retrieve the SpotlightForm
	 * @return SpotlightForm
	 */
	function _getForm($pressId, $spotlightId) {
		if (checkPhpVersion('5.0.0')) {
			// WARNING: This form needs $this in constructor
			$spotlightForm = new SpotlightForm($pressId, $spotlightId);
		} else {
			$spotlightForm =& new SpotlightForm($pressId, $spotlightId);
		}

		return $spotlightForm;
	}
}

?>
