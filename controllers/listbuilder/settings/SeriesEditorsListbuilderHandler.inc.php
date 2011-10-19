<?php

/**
 * @file controllers/listbuilder/settings/SeriesEditorsListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
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
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('fetch', 'fetchRow', 'fetchOptions')
		);
	}

	/**
	 * Set the series ID
	 * @param $seriesId int
	 */
	function setSeriesId($seriesId) {
		$this->seriesId = $seriesId;
	}

	/**
	 * Get the series ID
	 * @return int
	 */
	function getSeriesId() {
		return $this->seriesId;
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @param $request PKPRequest
	 */
	function loadData(&$request) {
		$press =& $this->getPress();
		$seriesId = $this->getSeriesId();

		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$assignedSeriesEditors =& $seriesEditorsDao->getEditorsBySeriesId($seriesId, $press->getId());
		$returner = array();
		foreach ($assignedSeriesEditors as $seriesEditorData) {
			$seriesEditor = $seriesEditorData['user'];
			$returner[$seriesEditor->getId()] = $seriesEditor;
		}
		return $returner;
	}

	/**
	 * Get possible items to populate autosuggest list with
	 */
	function getOptions() {
		$press =& $this->getPress();
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');

		$unassignedSeriesEditors =& $seriesEditorsDao->getEditorsNotInSeries($press->getId(), $this->getSeriesId());

		$itemList = array(0 => array());
		foreach ($unassignedSeriesEditors as $seriesEditor) {
			$itemList[0][$seriesEditor->getId()] = $seriesEditor->getFullName();
		}

		return $itemList;
	}

	/**
	 * Preserve the series ID for internal listbuilder requests.
	 * @see GridHandler::getRequestArgs
	 */
	function getRequestArgs() {
		$args = parent::getRequestArgs();
		$args['seriesId'] = $this->getSeriesId();
		return $args;
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

		return parent::fetch($args, $request, $additionalVars);
	}

	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER));

		// Basic configuration
		$this->setTitle('user.role.seriesEditors');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('seriesEditors');

		$this->setSeriesId($request->getUserVar('seriesId'));

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');

		// We can reuse the User cell provider because getFullName
		import('controllers.listbuilder.users/UserListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new UserListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}

	//
	// Public AJAX-accessible functions
	//

	/**
	 * Fetch either a block of data for local autocomplete, or return a URL to another function for AJAX autocomplete
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function getAutocompleteSource($args, &$request) {
		//FIXME: add validation here?
		$this->setupTemplate();

		$sourceArray = $this->getPossibleItemList($request);

		$sourceJson = new JSONMessage(true, null, 'local');
		$sourceContent = array();
		foreach ($sourceArray as $id => $item) {
			// The autocomplete code requires the JSON data to use 'label' as the array key for labels, and 'value' for the id
			$additionalAttributes = array(
				'label' =>  sprintf('%s (%s)', $item['name'], $item['abbrev']),
				'value' => $id
			);
			$itemJson = new JSONMessage(true, '', null, $additionalAttributes);
			$sourceContent[] = $itemJson->getString();

			unset($itemJson);
		}
		$sourceJson->setContent('[' . implode(',', $sourceContent) . ']');

		echo $sourceJson->getString();
	}
}

?>
