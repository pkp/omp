<?php

/**
 * @file controllers/listbuilder/settings/PublicationFormatsListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding new publication formats
 */

import('controllers.listbuilder.settings.SetupListbuilderHandler');

class PublicationFormatsListbuilderHandler extends SetupListbuilderHandler {
	/**
	 * Constructor
	 */
	function PublicationFormatsListbuilderHandler() {
		parent::SetupListbuilderHandler();
	}


	/**
	 * Load the list from an external source into the grid structure
	 */
	function loadList() {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$press =& $this->getPress();

		$publicationFormats =& $publicationFormatDao->getEnabledByPressId($press->getId());

		$items = array();
		foreach($publicationFormats as $item) {
			$id = $item->getId();
			$items[$id] = array('name' => $item->getLocalizedName(), 'designation' => $item->getLocalizedDesignation(), 'id' => $id);
		}
		$this->setGridDataElements($items);
	}


	/**
	 * Bounce a modified entry back to the client
	 * @see ListbuilderHandler::getRowDataElement
	 */
	function &getRowDataElement(&$request, $rowId) {
		// FIXME: Localize.
		$locale = Locale::getLocale();
		$values = (array) $request->getUserVar('newRowId');
		return(array(
			'name' => array_shift($values),
			'designation' => array_shift($values)
		));
	}

	/**
	 * Persist an update to an entry.
	 * @param $rowId mixed ID of row to modify
	 * @param $existingEntry mixed Existing entry to be modified
	 * @param $newEntry mixed New entry with changes to persist
	 * @return boolean
	 */
	function updateEntry($rowId, $existingEntry, $newEntry) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($rowId);

		$locale = Locale::getLocale(); // FIXME: Localize.
		$publicationFormat->setName($newEntry->name, $locale);
		$publicationFormat->setDesignation($newEntry->designation, $locale);

		$publicationFormatDao->updateObject($publicationFormat);
		return true;
	}


	/**
	 * Persist a new entry insert.
	 * @param $entry mixed New entry with data to persist
	 * @return boolean
	 */
	function insertEntry($entry) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->newDataObject();
		$press =& $this->getPress();
		$publicationFormat->setPressId($press->getId());
		$publicationFormat->setEnabled(true);

		$locale = Locale::getLocale(); // FIXME: Localize.
		$publicationFormat->setName($entry->name, $locale);
		$publicationFormat->setDesignation($entry->designation, $locale);

		$publicationFormatDao->insertObject($publicationFormat);
		return true;
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

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));

		// Basic configuration
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input

		// Load the listbuilder contents
		$this->loadList();

		// Configure the listbuilder columns
		$this->addColumn(new ListbuilderGridColumn($this, 'name', 'common.name'));
		$this->addColumn(new ListbuilderGridColumn($this, 'designation', 'common.designation'));
	}
}

?>
