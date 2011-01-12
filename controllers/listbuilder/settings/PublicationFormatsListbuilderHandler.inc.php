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
			$items[$id] = array('item' => $item->getLocalizedName(), 'attribute' => $item->getLocalizedDesignation());
		}
		$this->setData($items);
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
		$this->setTitle('manager.setup.publicationFormats');
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.publicationFormats');
		$this->setAttributeNames(array('common.designation'));

		$this->loadList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
		$this->addColumn(new GridColumn('attribute', 'common.designation'));
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addItem($args, &$request) {
		$this->setupTemplate();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$press =& $request->getPress();

		$nameIndex = 'sourceTitle-' . $this->getId();
		$publicationFormatName = $args[$nameIndex];
		$abbrevIndex = 'attribute-1-' . $this->getId();
		$publicationFormatDesignation = $args[$abbrevIndex];

		if(empty($publicationFormatName) || empty($publicationFormatDesignation)) {
			$json = new JSON('false', Locale::translate('common.listbuilder.completeForm'));
			return $json->getString();
		} else {
			// Make sure the role name or abbreviation doesn't already exist
			$publicationFormats =& $publicationFormatDao->getEnabledByPressId($press->getId());
			foreach ($publicationFormats as $publicationFormat) {
				if ($publicationFormatName == $publicationFormat->getLocalizedName() || $publicationFormatDesignation == $publicationFormat->getLocalizedDesignation()) {
					$json = new JSON('false', Locale::translate('common.listbuilder.itemExists'));
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

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteItems($args, &$request) {
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');

		foreach($args as $publicationFormatId) {
			$publicationFormatDao->deleteById($publicationFormatId);
		}

		$json = new JSON('true');
		return $json->getString();
	}
}
?>
