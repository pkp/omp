<?php

/**
 * @file controllers/listbuilder/settings/DivisionsListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DivisionsListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding Press Divisions
 */

import('controllers.listbuilder.settings.SetupListbuilderHandler');
import('classes.press.Division');

class DivisionsListbuilderHandler extends SetupListbuilderHandler {
	/**
	 * Constructor
	 */
	function DivisionsListbuilderHandler() {
		parent::SetupListbuilderHandler();
	}


	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Basic configuration
		$this->setTitle('manager.setup.division');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input

		import('controllers.listbuilder.settings.DivisionsListbuilderGridCellProvider');
		$cellProvider =& new DivisionsListbuilderGridCellProvider();

		$titleColumn = new ListbuilderGridColumn($this, 'title', 'manager.setup.currentFormats');
		$titleColumn->setCellProvider($cellProvider);
		$this->addColumn($titleColumn);
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
	}

	//
	// Public methods
	//
	/**
	 * Persist a new entry insert.
	 * @param $entry mixed New entry with data to persist
	 * @return boolean
	 */
	function insertEntry(&$request, $rowId) {
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$press =& $this->getPress();

		// Make sure the item doesn't already exist
		$division = $divisionDao->getByTitle($rowId, $press->getId());
		if (isset($division)) return false;
		unset($division);

		// Create and populate the new entry.
		$division =& $divisionDao->newDataObject();
		$division->setPressId($press->getId());

		//FIXME: Localize.
		$division->setTitle($rowId, Locale::getLocale());

		$divisionDao->insertObject($division);
		return true;
	}

	/**
	 * Persist an update to an entry.
	 * @param $rowId mixed ID of row to modify
	 * @param $existingEntry mixed Existing entry to be modified
	 * @param $newEntry mixed New entry with changes to persist
	 * @return boolean
	 */
	function updateEntry(&$request, $rowId, $newRowId) {
		// Get and validate the divison
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$division = $divisionDao->getById($rowId);
		$press =& $this->getPress();
		assert ($division && $division->getPressId() == $press->getId());

		// Update the existing entry.
		// FIXME: Localize.
		$locale = Locale::getLocale();
		$division->setTitle($newRowId, $locale);

		$divisionDao->updateObject($division);
		return true;
	}

	/**
	 * Bounce a modified entry back to the client
	 * @see ListbuilderHandler::getRowDataElement
	 */
	function &getRowDataElement(&$request, $rowId) {
		// Create a non-persisted entry
		$division = new Division();
		$division->setId($rowId);

		// Populate the entry
		// FIXME: Localize.
		$locale = Locale::getLocale();
		$division->setTitle(array_shift($request->getUserVar('newRowId')), $locale);

		return $division;
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @param $request Request
	 */
	function loadData() {
		$press =& $this->getPress();
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$divisions = $divisionDao->getByPressId($press->getId());
		return $divisions;
	}
}

?>
