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
	/**
	 * Constructor
	 */
	function CataloguingMetadataListbuilderHandler() {
		parent::SetupListbuilderHandler();
	}


	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$press =& $request->getPress();
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		$formats = $pressSettingsDao->getSetting($press->getId(), 'cataloguingMetadata');

		$items = array();
		foreach($formats as $item) {
			$id = $item['name'];
			$items[$id] = array('item' => $id);
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
		// Basic configuration
		$this->setTitle('manager.setup.cataloguingMetadata');
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.currentFormats');

		$this->loadList($request);

		$this->addColumn(new GridColumn('item', 'manager.setup.currentFormats'));
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
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& $request->getPress();

		$index = 'sourceTitle-' . $this->getId();
		$format = $args[$index];

		if(!isset($format)) {
			$json = new JSON(false);
			return $json->getString();
		} else {
			// Make sure the item doesn't already exist
			$formats = $pressSettingsDao->getSetting($press->getId(), 'cataloguingMetadata');
			foreach($formats as $item) {
				if($item['name'] == $format) {
					$json = new JSON(false, Locale::translate('common.listbuilder.itemExists'));
					return $json->getString();
					return false;
				}
			}

			$formats[] = array('name' => $format);

			$pressSettingsDao->updateSetting($press->getId(), 'cataloguingMetadata', $formats, 'object');

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($format);
			$rowData = array('item' => $format);
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON(true, $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteItems($args, &$request) {
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& $request->getPress();
		$formats = $pressSettingsDao->getSetting($press->getId(), 'cataloguingMetadata');

		foreach($args as $item) {
			for ($i = 0; $i < count($formats); $i++) {
				if ($formats[$i]['name'] == $item) {
					array_splice($formats, $i, 1);
				}
			}
		}

		$pressSettingsDao->updateSetting($press->getId(), 'cataloguingMetadata', $formats, 'object');

		$json = new JSON(true);
		return $json->getString();
	}
}

?>
