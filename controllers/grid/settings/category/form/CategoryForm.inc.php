<?php

/**
 * @file controllers/grid/settings/roles/form/CategoryForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryForm
 * @ingroup controllers_grid_settings_category_form
 *
 * @brief Form to add/edit category.
 */

import('lib.pkp.classes.form.Form');

class CategoryForm extends Form {
	/** @var Id of the category being edited */
	var $_categoryId;

	/** @var The press of the category being edited */
	var $_pressId;


	/**
	 * Constructor.
	 * @param $pressId Press id.
	 * @param $categoryId Category id.
	 */
	function CategoryForm($pressId, $categoryId = null) {
		parent::Form('controllers/grid/settings/category/form/categoryForm.tpl');
		$this->_pressId = $pressId;
		$this->_categoryId = $categoryId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'grid.category.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the user group id.
	 * @return int categoryId
	 */
	function getCategoryId() {
		return $this->_categoryId;
	}

	/**
	 * Get the press id.
	 * @return int pressId
	 */
	function getPressId() {
		return $this->_pressId;
	}

	//
	// Implement template methods from Form.
	//
	/**
	 * Get all locale field names
	 */
	function getLocaleFieldNames() {
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		return $categoryDao->getLocaleFieldNames();
	}

	/**
	 * @see Form::initData()
	 */
	function initData() {
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$category = $categoryDao->getById($this->getCategoryId(), $this->getPressId());

		if ($category) {
			$this->setData('name', $category->getTitle(null)); // Localized
			$this->setData('description', $category->getDescription(null)); // Localized
			$this->setData('parentId', $category->getParentId());
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('name', 'parentId', 'description'));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('categoryId', $this->getCategoryId());

		// Provide a list of root categories to the template
		$press =& $request->getPress();
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$rootCategories =& $categoryDao->getByParentId(0, $press->getId());
		$templateMgr->assign_by_ref('rootCategories', $rootCategories);

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 */
	function execute(&$request) {
		$categoryId = $this->getCategoryId();
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');

		// Get a category object to edit or create
		if ($categoryId == null) {
			$category = $categoryDao->newDataObject();
			$category->setPressId($this->getPressId());
		} else {
			$category = $categoryDao->getById($categoryId, $this->getPressId());
		}

		// Set the editable properties of the category object
		$category->setTitle($this->getData('name'), null); // Localized
		$category->setParentId($this->getData('parentId'));

		// Update or insert the category object
		if ($categoryId == null) {
			$categoryId = $categoryDao->insertObject($category);
		} else {
			$categoryDao->updateObject($category);
		}
	}
}

?>
