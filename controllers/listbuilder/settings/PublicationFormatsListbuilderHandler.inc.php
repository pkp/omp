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


	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$press =& $request->getPress();

		$publicationFormats =& $publicationFormatDao->getEnabledByPressId($press->getId());

		$items = array();
		foreach($publicationFormats as $item) {
			$id = $item->getId();
			$items[$id] = array('name' => $item->getLocalizedName(), 'designation' => $item->getLocalizedDesignation(), 'id' => $id);
		}
		$this->setGridDataElements($items);
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

		$this->loadList($request);

		$nameColumn = new GridColumn('name', 'common.name');
		$nameColumn->addFlag('editable');
		$this->addColumn($nameColumn);

		$designationColumn = new GridColumn('designation', 'common.designation');
		$designationColumn->addFlag('editable');
		$this->addColumn($designationColumn);
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
			'name' => $request->getUserVar('name'),
			'designation' => $request->getUserVar('designation')
		);
		$elementId = $request->getUserVar('rowId');
		return $newItem;
	}

	//
	// Public AJAX-accessible functions
	//
	/*
	 * Handle adding an item to the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
/*	function addItem($args, &$request) {
		$this->setupTemplate();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$press =& $request->getPress();

		$nameIndex = 'sourceTitle-' . $this->getId();
		$publicationFormatName = $args[$nameIndex];
		$abbrevIndex = 'attribute-1-' . $this->getId();
		$publicationFormatDesignation = $args[$abbrevIndex];

		if(empty($publicationFormatName) || empty($publicationFormatDesignation)) {
			$json = new JSON(false, Locale::translate('common.listbuilder.completeForm'));
			return $json->getString();
		} else {
			// Make sure the role name or abbreviation doesn't already exist
			$publicationFormats =& $publicationFormatDao->getEnabledByPressId($press->getId());
			foreach ($publicationFormats as $publicationFormat) {
				if ($publicationFormatName == $publicationFormat->getLocalizedName() || $publicationFormatDesignation == $publicationFormat->getLocalizedDesignation()) {
					$json = new JSON(false, Locale::translate('common.listbuilder.itemExists'));
					return $json->getString();
					return false;
				}
			}

			$locale = Locale::getLocale();
			$publicationFormat = $publicationFormatDao->newDataObject();
			$publicationFormat->setName($publicationFormatName, $locale);
			$publicationFormat->setDesignation($publicationFormatDesignation, $locale);

			$publicationFormatId = $publicationFormatDao->insertObject($publicationFormat);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($publicationFormatId);
			$rowData = array('item' => $publicationFormatName, 'attribute' => $publicationFormatDesignation);
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON(true, $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	} */

	/*
	 * Handle deleting items from the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
/*	function deleteItems($args, &$request) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');

		foreach($args as $publicationFormatId) {
			$publicationFormatDao->deleteById($publicationFormatId);
		}

		$json = new JSON(true);
		return $json->getString();
	} */
}

?>
