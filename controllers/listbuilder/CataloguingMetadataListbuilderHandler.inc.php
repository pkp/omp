<?php

/**
 * @file classes/manager/listbuilder/CataloguingMetadataListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CataloguingMetadataListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding cataloguing metadata
 */

import('controllers.listbuilder.ListbuilderHandler');

class CataloguingMetadataListbuilderHandler extends ListbuilderHandler {
	/** @var boolean internal state variable, true if row handler has been instantiated */
	var $_rowHandlerInstantiated = false;

	/**
	 * Constructor
	 */
	function CataloguingMetadataListbuilderHandler() {
		parent::ListbuilderHandler();
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
		// Only initialize once
		if ($this->getInitialized()) return;

		// Basic configuration
		$this->setId('cataloguingMetadata');
		$this->setTitle('manager.setup.cataloguingMetadata');
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.currentFormats');

		$this->loadList($request);

		parent::initialize($request);
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return SponsorRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandlerInstantiated) {
			import('controllers.listbuilder.ListbuilderGridRowHandler');
			$rowHandler =& new ListbuilderGridRowHandler();

			// Basic grid row configuration
			$rowHandler->addColumn(new GridColumn('item', 'manager.setup.currentFormats'));

			$this->setRowHandler($rowHandler);
			$this->_rowHandlerInstantiated = true;
		}
		return parent::getRowHandler();
	}


	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 */
	function additem(&$args, &$request) {
		$this->setupTemplate();
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& $request->getPress();

		$format = $args['sourceTitle-cataloguingMetadata'];

		if(!isset($format)) {
			$json = new JSON('false');
			echo $json->getString();
		} else {
			// Make sure the item doesn't already exist
			$formats = $pressSettingsDao->getSetting($press->getId(), 'cataloguingMetadata');
			foreach($formats as $item) {
				if($item['name'] == $format) {
					$json = new JSON('false', Locale::translate('common.listbuilder.itemExists'));
					echo $json->getString();
					return false;
				}
			}

			$formats[] = array('name' => $format);

			$pressSettingsDao->updateSetting($press->getId(), 'cataloguingMetadata', $formats, 'object');

			// Return JSON with formatted HTML to insert into list
			$formatRow =& $this->getRowHandler();
			$rowData = array('item' => $format);
			$formatRow->_configureRow($request);
			$formatRow->setData($rowData);
			$formatRow->setId($format);

			$json = new JSON('true', $formatRow->renderRowInternally($request));
			echo $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteitems(&$args, &$request) {
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

		$json = new JSON('true');
		echo $json->getString();
	}
}
?>
