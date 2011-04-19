<?php

/**
 * @file controllers/listbuilder/settings/CataloguingMetadataListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CataloguingMetadataListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding cataloguing metadata
 */

import('controllers.listbuilder.settings.SetupListbuilderHandler');

class CataloguingMetadataListbuilderHandler extends SetupListbuilderHandler {
	/** @var $press Press */
	var $press;


	/**
	 * Constructor
	 */
	function CataloguingMetadataFieldsListbuilderHandler() {
		parent::SetupListbuilderHandler();
	}


	/**
	 * Load the list from an external source into the grid structure
	 */
	function loadList() {
		$publicationFormatDao =& DAORegistry::getDAO('CataloguingMetadataFieldDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');

		$publicationFormats =& $publicationFormatDao->getEnabledByPressId($this->press->getId());

		$items = array();
		foreach($publicationFormats as $item) {
			$id = $item->getId();
			$items[$id] = array('name' => $item->getLocalizedName(), 'id' => $id);
		}
		$this->setGridDataElements($items);
	}


	/**
	 * Persist an update to an entry.
	 * @param $rowId mixed ID of row to modify
	 * @param $existingEntry mixed Existing entry to be modified
	 * @param $newEntry mixed New entry with changes to persist
	 * @return boolean
	 */
	function updateEntry($rowId, $existingEntry, $newEntry) {
		$publicationFormatDao =& DAORegistry::getDAO('CataloguingMetadataFieldDAO');
		$publicationFormat = $publicationFormatDao->getById($rowId);

		$locale = Locale::getLocale(); // FIXME: Localize.
		$publicationFormat->setName($newEntry->name, $locale);

		$publicationFormatDao->updateObject($publicationFormat);
		return true;
	}


	/**
	 * Persist a new entry insert.
	 * @param $entry mixed New entry with data to persist
	 * @return boolean
	 */
	function insertEntry($entry) {
		$cataloguingMetadataFieldDao =& DAORegistry::getDAO('CataloguingMetadataFieldDAO');
		$cataloguingMetadataField = $cataloguingMetadataFieldDao->newDataObject();
		$cataloguingMetadataField->setPressId($this->press->getId());
		$cataloguingMetadataField->setEnabled(true);

		$locale = Locale::getLocale(); // FIXME: Localize.
		$cataloguingMetadataField->setName($entry->name, $locale);

		$cataloguingMetadataFieldDao->insertObject($cataloguingMetadataField);
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
		$this->press =& $request->getPress();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));

		// Basic configuration
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input

		$this->loadList();

		$nameColumn = new ListbuilderGridColumn('name', 'common.name');
		$this->addColumn($nameColumn);
	}


	/**
	 * Create a new data element from a request. This is used to format
	 * new rows prior to their insertion.
	 * @param $request PKPRequest
	 * @param $elementId int
	 * @return object
	 */
	function &getDataElementFromRequest(&$request, &$elementId) {
		$newItem = array(
			'name' => $request->getUserVar('name')
		);
		$elementId = $request->getUserVar('rowId');
		return $newItem;
	}
}

?>
