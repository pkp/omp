<?php

/**
 * @file controllers/submission/CategoriesListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoriesListbuilderHandler
 * @ingroup controllers_submission
 *
 * @brief Class for assigning categories to submissions.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class CategoriesListbuilderHandler extends ListbuilderHandler {
	/** @var $press Press */
	var $_press;

	/** @var The group ID for this listbuilder */
	var $monographId;

	/**
	 * Constructor
	 */
	function CategoriesListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_AUTHOR),
			array('fetch', 'fetchRow', 'fetchOptions')
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Set the monograph ID
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		$this->monographId = $monographId;
	}

	/**
	 * Get the monograph ID
	 * @return int
	 */
	function getMonographId() {
		return $this->monographId;
	}

	/**
	 * Set the current press
	 * @param $press Press
	 */
	function setPress(&$press) {
		$this->_press =& $press;
	}

	/**
	 * Get the current press
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @param $request PKPRequest
	 */
	function loadData(&$request) {
		$press =& $this->getPress();
		$monographId = $this->getMonographId();

		if ($monographId) {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$assignedCategories =& $monographDao->getCategories($monographId, $press->getId());
			return $assignedCategories;
		}

		return array();
	}

	/**
	 * Get possible items to populate autosuggest list with
	 */
	function getOptions() {
		$press =& $this->getPress();
		$monographId = $this->getMonographId();

		if ($monographId) {
			// Preexisting monograph
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$availableCategories =& $monographDao->getUnassignedCategories($monographId, $press->getId());
		} else {
			// New monograph
			$categoryDao =& DAORegistry::getDAO('CategoryDAO');
			$availableCategories =& $categoryDao->getByPressId($press->getId());
		}

		$itemList = array(0 => array());
		while ($category =& $availableCategories->next()) {
			$itemList[0][$category->getId()] = $category->getLocalizedTitle();
			unset($category);
		}
		return $itemList;
	}

	/**
	 * Preserve the monograph ID for internal listbuilder requests.
	 * @see GridHandler::getRequestArgs
	 */
	function getRequestArgs() {
		$args = parent::getRequestArgs();
		$args['monographId'] = $this->getMonographId();
		return $args;
	}


	/**
	 * @see GridHandler::getRowDataElement
	 * Get the data element that corresponds to the current request
	 * Allow for a blank $rowId for when creating a not-yet-persisted row
	 */
	function getRowDataElement(&$request, $rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the $newRowId
		$newRowId = $this->getNewRowId($request);
		$categoryId = $newRowId['name'];
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$press =& $request->getPress();
		$category =& $categoryDao->getById($categoryId, $press->getId());
		return $category;
	}

	//
	// Overridden template methods
	//
	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Basic configuration
		$this->setPress($request->getPress());
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('categories');

		$this->setMonographId($request->getUserVar('monographId'));

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');

		import('controllers.listbuilder.categories.CategoryListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new CategoryListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}
}

?>
