<?php

/**
 * @file controllers/listbuilder/settings/SeriesEditorsListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorsListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding a series editor
 */

import('controllers.listbuilder.settings.SetupListbuilderHandler');

class SeriesEditorsListbuilderHandler extends SetupListbuilderHandler {
	/** @var The group ID for this listbuilder */
	var $seriesId;

	/**
	 * Constructor
	 */
	function SeriesEditorsListbuilderHandler() {
		parent::SetupListbuilderHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER, 'getAutocompleteSource');
	}

	function setSeriesId($seriesId) {
		$this->seriesId = $seriesId;
	}

	function getSeriesId() {
		return $this->seriesId;
	}

	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$press =& $this->getPress();
		$seriesId = $this->getSeriesId();

		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');

		$assignedSeriesEditors =& $seriesEditorsDao->getEditorsBySeriesId($seriesId, $press->getId());

		$items = array();
		foreach ($assignedSeriesEditors as $seriesEditor) {
			$user = $seriesEditor['user'];
			$id = $user->getId();
			$items[$id] = array('item' => $user->getFullName(), 'attribute' => $user->getUsername());
		}
		$this->setGridDataElements($items);
	}


	/* Get possible items to populate autosuggest list with */
	function getPossibleItemList(&$request) {
		$press =& $this->getPress();
		$seriesId = $this->getSeriesId();

		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');

		$unassignedSeriesEditors =& $seriesEditorsDao->getEditorsNotInSeries($press->getId(), $seriesId);

		$itemList = array();
		foreach ($unassignedSeriesEditors as $seriesEditor) {
			$itemList[$seriesEditor->getId()] = array(
				'name' => $seriesEditor->getFullName(),
				'abbrev' => $seriesEditor->getUsername()
			);
		}

		return $itemList;
	}


	//
	// Overridden template methods
	//
	/**
	 * Need to add additional data to the template via the fetch method
	 * @see Form::fetch()
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetch($args, &$request) {
		$router =& $request->getRouter();

		$seriesId = $request->getUserVar('seriesId');
		$additionalVars = array('itemId' => $seriesId,
			'addUrl' => $router->url($request, array(), null, 'addItem', null, array('seriesId' => $seriesId)),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, array('seriesId' => $seriesId)),
			'autocompleteUrl' => $router->url($request, array(), null, 'getAutocompleteSource')
		);

		return parent::fetch($args, &$request, $additionalVars);
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER));

		// Basic configuration
		$this->setTitle('user.role.seriesEditors');
		$this->setSourceTitle('common.user');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setListTitle('manager.groups.existingUsers');

		$this->setSeriesId($request->getUserVar('seriesId'));

		$this->loadList($request);

		$this->addColumn(new ListbuilderGridColumn($this, 'item', 'common.name'));
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Fetch either a block of data for local autocomplete, or return a URL to another function for AJAX autocomplete
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function getAutocompleteSource($args, &$request) {
		//FIXME: add validation here?
		$this->setupTemplate();

		$sourceArray = $this->getPossibleItemList($request);

		$sourceJson = new JSONMessage(true, null, false, 'local');
		$sourceContent = array();
		foreach ($sourceArray as $id => $item) {
			// The autocomplete code requires the JSON data to use 'label' as the array key for labels, and 'value' for the id
			$additionalAttributes = array(
				'label' =>  sprintf('%s (%s)', $item['name'], $item['abbrev']),
				'value' => $id
			);
			$itemJson = new JSONMessage(true, '', false, null, $additionalAttributes);
			$sourceContent[] = $itemJson->getString();

			unset($itemJson);
		}
		$sourceJson->setContent('[' . implode(',', $sourceContent) . ']');

		echo $sourceJson->getString();
	}

	/*
	 * Handle adding an item to the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addItem($args, &$request) {
		$this->setupTemplate();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$press =& $this->getPress();

		$seriesId = $args['seriesId'];
		$index = 'sourceId-' . $this->getId() . '-' .$seriesId;
		$userId = $args[$index];

		if(empty($userId)) {
			$json = new JSONMessage(false, Locale::translate('common.listbuilder.completeForm'));
			return $json->getString();
		} else {
			$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');

			// Make sure the membership doesn't already exist
			if ($seriesEditorsDao->editorExists($press->getId(), $seriesId, $userId)) {
				$json = new JSONMessage(false, Locale::translate('common.listbuilder.itemExists'));
				return $json->getString();
				return false;
			}
			unset($groupMembership);

			$seriesEditorsDao->insertEditor($press->getId(), $request->getUserVar('seriesId'), $userId, true, true);

			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($userId);
			$rowData = array('item' => $user->getFullName(), 'attribute' => $user->getUsername());
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSONMessage(true, $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteItems($args, &$request) {
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$press =& $this->getPress();
		$seriesId = (int) array_shift($args);

		foreach($args as $userId) {
			$seriesEditorsDao->deleteEditor($press->getId(), $seriesId, $userId);
		}

		$json = new JSONMessage(true);
		return $json->getString();
	}
}

?>
