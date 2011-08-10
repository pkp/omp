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
		while ($item =& $publicationFormats->next()) {
			$id = $item->getId();
			$items[$id] = array('name' => $item->getLocalizedName(), 'designation' => $item->getLocalizedDesignation(), 'id' => $id);
			unset($item);
		}
		$this->setGridDataElements($items);
	}


	/**
	 * Bounce a modified entry back to the client
	 * @see ListbuilderHandler::getRowDataElement
	 */
	function getRowDataElement(&$request, $rowId) {
		// FIXME: Localize.
		$locale = Locale::getLocale();
		list($name, $designation) = $this->getNewRowId($request);
		return(array(
			'name' => $name,
			'designation' => $designation
		));
	}

	/**
	 * Persist an update to an entry.
	 * @param $rowId mixed ID of row to modify
	 * @param $existingEntry mixed Existing entry to be modified
	 * @param $newEntry mixed New entry with changes to persist
	 * @return boolean
	 */
	function updateEntry(&$request, $rowId, $newRowId) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($rowId);

		$locale = Locale::getLocale(); // FIXME: Localize.
		list($name, $designation) = $newRowId;
		$publicationFormat->setName($name, $locale);
		$publicationFormat->setDesignation($designation, $locale);

		$publicationFormatDao->updateObject($publicationFormat);
		return true;
	}


	/**
	 * Persist the deletion of an entry.
	 * @param $rowId mixed ID of row to modify
	 * @return boolean
	 */
	function deleteEntry(&$request, $rowId) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormatDao->deleteById($rowId);
		return true;
	}


	/**
	 * Persist a new entry insert.
	 * @param $entry mixed New entry with data to persist
	 * @return boolean
	 */
	function insertEntry(&$request, $newRowId) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->newDataObject();
		$press =& $this->getPress();
		$publicationFormat->setPressId($press->getId());
		$publicationFormat->setEnabled(true);

		$locale = Locale::getLocale(); // FIXME: Localize.

		list($name, $designation) = $newRowId;
		$publicationFormat->setName($name, $locale);
		$publicationFormat->setDesignation($designation, $locale);

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

		$this->setTitle('manager.setup.publicationFormats');

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
