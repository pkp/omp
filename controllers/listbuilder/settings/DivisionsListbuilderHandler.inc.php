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
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
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
		$this->setTitle('manager.setup.division');
		$this->setSourceTitle('manager.setup.division');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.currentDivisions');

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
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$press =& $request->getPress();

		$index = 'sourceTitle-' . $this->getId();
		$divisionTitle = $args[$index];

		if(!isset($divisionTitle)) {
			$json = new JSON(false);
			return $json->getString();
		} 	else {
			// Make sure the item doesn't already exist
			$divisions = $divisionDao->getByTitle($divisionTitle, $press->getId());
			if (isset($divisions)) {
				$json = new JSON(false, Locale::translate('common.listbuilder.itemExists'));
				return $json->getString();
				return false;
			}

			$division =& $divisionDao->newDataObject();
			$division->setTitle($divisionTitle, Locale::getLocale()); //FIXME: Get locale from form
			$division->setPressId($press->getId());

			$divisionId = $divisionDao->insertObject($division);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($divisionId);
			$rowData = array('item' => $divisionTitle);
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
		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$press =& $request->getPress();

		foreach($args as $item) {
			$divisionDao->deleteById($item, $press->getId());
		}

		$json = new JSON(true);
		return $json->getString();
	}
}
?>
