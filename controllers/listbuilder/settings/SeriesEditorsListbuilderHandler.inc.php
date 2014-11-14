<?php

/**
 * @file controllers/listbuilder/settings/SeriesEditorsListbuilderHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorsListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding a series editor
 */

import('lib.pkp.controllers.listbuilder.settings.SetupListbuilderHandler');

class SeriesEditorsListbuilderHandler extends SetupListbuilderHandler {
	/** @var The group ID for this listbuilder */
	var $seriesId;

	/**
	 * Constructor
	 */
	function SeriesEditorsListbuilderHandler() {
		parent::SetupListbuilderHandler();
		$this->addRoleAssignment(
			ROLE_ID_MANAGER,
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
	function loadData($request) {
		$press = $this->getContext();
		$seriesId = $this->getSeriesId();

		$seriesEditorsDao = DAORegistry::getDAO('SeriesEditorsDAO');
		$assignedSeriesEditors = $seriesEditorsDao->getEditorsBySeriesId($seriesId, $press->getId());
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
		$press = $this->getContext();
		$seriesEditorsDao = DAORegistry::getDAO('SeriesEditorsDAO');

		if ($this->getSeriesId()) {
			$unassignedSeriesEditors = $seriesEditorsDao->getEditorsNotInSeries($press->getId(), $this->getSeriesId());
		} else {
			$roleDao = DAORegistry::getDAO('RoleDAO');
			$editors = $roleDao->getUsersByRoleId(ROLE_ID_SUB_EDITOR, $press->getId());
			$unassignedSeriesEditors = $editors->toArray();
		}
		$itemList = array(0 => array());
		foreach ($unassignedSeriesEditors as $seriesEditor) {
			$itemList[0][$seriesEditor->getId()] = $seriesEditor->getFullName();
		}

		return $itemList;
	}

	/**
	 * @see GridHandler::getRowDataElement
	 * Get the data element that corresponds to the current request
	 * Allow for a blank $rowId for when creating a not-yet-persisted row
	 */
	function getRowDataElement($request, &$rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the $newRowId
		$newRowId = $this->getNewRowId($request);
		$seriesEditorId = $newRowId['name'];
		$userDao = DAORegistry::getDAO('UserDAO');
		return $userDao->getById($seriesEditorId);
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
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize($request) {
		parent::initialize($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Basic configuration
		$this->setTitle('user.role.seriesEditors');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('seriesEditors');

		$this->setSeriesId($request->getUserVar('seriesId'));

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');

		// We can reuse the User cell provider because getFullName
		import('lib.pkp.controllers.listbuilder.users.UserListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new UserListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}
}

?>
