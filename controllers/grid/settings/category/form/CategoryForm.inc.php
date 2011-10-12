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
		return array('name');
	}

	/**
	 * @see Form::initData()
	 */
	function initData() {
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$category = $categoryDao->getById($this->getCategoryId(), $this->getPressId());

		if ($category) {
			$this->setData('name', $category->getTitle(null)); // Localized
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('name'));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('categoryId', $this->getCategoryId());
		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 */
	function execute(&$request) {
		$categoryId = $this->getCategoryId();
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');

		// Check if we are editing an existing user group or creating another one.
		if ($categoryId == null) {
			$category = $categoryDao->newDataObject();
			$category->setPressId($this->getPressId());
			$category->setTitle($this->getData('name'), null); // Localized
			$categoryId = $categoryDao->insertObject($category);
		} else {
			$category = $categoryDao->getById($categoryId);
			$category->setTitle($this->getData('name'), null); // Localized
			$categoryDao->updateObject($category);
		}
	}
}

?>
