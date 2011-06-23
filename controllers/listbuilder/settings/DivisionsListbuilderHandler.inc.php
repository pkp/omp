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
	function insertEntry($entry) {
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$press =& $this->getPress();

		// Make sure the item doesn't already exist
		$divisions = $divisionDao->getByTitle($entry->item, $press->getId());
		if (isset($divisions)) {
			return false;
		} else {
			$division =& $divisionDao->newDataObject();
			$division->setTitle($entry->item, Locale::getLocale()); //FIXME: Get locale from form
			$division->setPressId($press->getId());

			$divisionDao->insertObject($division);
			return true;
		}

	}

	/**
	 * Persist an update to an entry.
	 * @param $rowId mixed ID of row to modify
	 * @param $existingEntry mixed Existing entry to be modified
	 * @param $newEntry mixed New entry with changes to persist
	 * @return boolean
	 */
	function updateEntry($rowId, $existingEntry, $newEntry) {
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$division = $divisionDao->getById($rowId);

		$locale = Locale::getLocale(); // FIXME: Localize.
		$division->setTitle($newEntry->item, $locale);

		$divisionDao->updateObject($division);
		return true;
	}

	function &getRowDataElement(&$request, $rowId) {
		$division = new Division();
		$division->setId($rowId);
		$division->setTitle($request->getUserVar('title'), 'en_US');
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
