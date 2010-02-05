<?php

/**
 * @file classes/manager/listbuilder/DivisionsListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DivisionsListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding Press Divisions
 */

import('controllers.listbuilder.ListbuilderHandler');
import('press.Division');

class DivisionsListbuilderHandler extends ListbuilderHandler {
	/** @var boolean internal state variable, true if row handler has been instantiated */
	var $_rowHandlerInstantiated = false;

	/**
	 * Constructor
	 */
	function DivisionsListbuilderHandler() {
		parent::ListbuilderHandler();
	}


	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$press =& $request->getPress();
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');

		$divisions = $divisionDao->getByPressId($press->getId());

		$items = array();
		while ($division =& $divisions->next()) {
			$id = $division->getId();
			$items[$id] = array('item' => $division->getLocalizedTitle());
			unset($division);
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
		$this->setId('divisions');
		$this->setTitle('manager.setup.division');
		$this->setSourceTitle('manager.setup.division');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.currentDivisions');

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
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$press =& $request->getPress();

		$divisionTitle = $args['sourceTitle-divisions'];

		if(!isset($divisionTitle)) {
			$json = new JSON('false');
			echo $json->getString();
		} 	else {
			// Make sure the item doesn't already exist
			$divisions = $divisionDao->getByTitle($divisionTitle, $press->getId());
			if (isset($divisions)) {
				$json = new JSON('false', Locale::translate('common.listbuilder.itemExists'));
				echo $json->getString();
				return false;
			}

			$division =& $divisionDao->newDataObject();
			$division->setTitle($divisionTitle, Locale::getLocale()); //FIXME: Get locale from form
			$division->setPressId($press->getId());

			$divisionId = $divisionDao->insertObject($division);

			// Return JSON with formatted HTML to insert into list
			$divisionRow =& $this->getRowHandler();
			$rowData = array('item' => $divisionTitle);
			$divisionRow->configureRow($request);
			$divisionRow->setData($rowData);
			$divisionRow->setId($divisionId);

			$json = new JSON('true', $divisionRow->renderRowInternally($request));
			echo $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteitems(&$args, &$request) {
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$press =& $request->getPress();

		foreach($args as $item) {
			$divisionDao->deleteById($item, $press->getId());
		}

		$json = new JSON('true');
		echo $json->getString();
	}
}
?>
