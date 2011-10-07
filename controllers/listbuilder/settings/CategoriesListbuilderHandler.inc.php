<?php

/**
 * @file controllers/listbuilder/settings/CategoriesListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoriesListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding Press Categories
 */

import('controllers.listbuilder.settings.SetupListbuilderHandler');
import('classes.press.Category');

class CategoriesListbuilderHandler extends SetupListbuilderHandler {
	/**
	 * Constructor
	 */
	function CategoriesListbuilderHandler() {
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
		$this->setTitle('category.categories');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input

		import('controllers.listbuilder.settings.CategoriesListbuilderGridCellProvider');
		$cellProvider =& new CategoriesListbuilderGridCellProvider();

		$titleColumn = new MultilingualListbuilderGridColumn($this, 'title', 'manager.setup.currentFormats');
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
	function insertEntry(&$request, $rowId) {
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$press =& $this->getPress();

		// Make sure the item doesn't already exist
		$category = $categoryDao->getByTitle($rowId['title'], $press->getId());
		if (isset($category)) return false;
		unset($category);

		// Create and populate the new entry.
		$category =& $categoryDao->newDataObject();
		$category->setPressId($press->getId());

		$category->setTitle($rowId['title'], null);

		$categoryDao->insertObject($category);
		return true;
	}

	/**
	 * Persist an update to an entry.
	 * @param $request PKPRequest
	 * @param $rowId mixed ID of row to modify
	 * @param $newRowId mixed New entry with changes to persist
	 * @return boolean
	 */
	function updateEntry(&$request, $rowId, $newRowId) {
		// Get and validate the divison
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$category = $categoryDao->getById($rowId);
		$press =& $this->getPress();
		if (!$category || $category->getPressId() !== $press->getId()) fatalError('Invalid category!');

		// Update the existing entry.
		$category->setTitle($newRowId['title'], null);

		$categoryDao->updateObject($category);
		return true;
	}

	/**
	 * Persist the deletion of an entry.
	 * @param $request PKPRequest
	 * @param $rowId mixed ID of row to modify
	 * @return boolean
	 */
	function deleteEntry(&$request, $rowId) {
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$press =& $this->getPress();
		$categoryDao->deleteById($rowId['title'], $press->getId());
		return true;
	}

	/**
	 * Bounce a modified entry back to the client
	 * @see ListbuilderHandler::getRowDataElement
	 */
	function getRowDataElement(&$request, $rowId) {
		// Create a non-persisted entry
		$category = new Category();
		$category->setId($rowId);

		// Populate the entry
		$category->setTitle($this->getNewRowId($request), null);

		return $category;
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @param $request Request
	 */
	function loadData() {
		$press =& $this->getPress();
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categories = $categoryDao->getByPressId($press->getId());
		return $categories;
	}
}

?>
