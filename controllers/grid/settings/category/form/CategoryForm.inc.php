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
		$this->addCheck(new FormValidatorAlphaNum($this, 'path', 'required', 'grid.category.pathAlphaNumeric'));
		$this->addCheck(new FormValidatorCustom(
			$this, 'path', 'required', 'grid.category.pathExists',
			create_function(
				'$path,$form,$categoryDao,$pressId',
				'return !$categoryDao->categoryExistsByPath($path,$pressId) || ($form->getData(\'oldPath\') != null && $form->getData(\'oldPath\') == $path);'
			),
			array(&$this, DAORegistry::getDAO('CategoryDAO'), $pressId)
		));
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
			$this->setData('path', $category->getPath());
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('name', 'parentId', 'path', 'description'));

		// For path duplicate checking; excuse the current path.
		if ($categoryId = $this->getCategoryId()) {
			$categoryDao =& DAORegistry::getDAO('CategoryDAO');
			$category =& $categoryDao->getById($categoryId, $this->getPressId());
			$this->setData('oldPath', $category->getPath());
		}
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$press =& $request->getPress();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('categoryId', $this->getCategoryId());

		// Provide a list of root categories to the template
		$rootCategoriesIterator =& $categoryDao->getByParentId(0, $press->getId());
		$rootCategories = array(0 => __('common.none'));
		while ($category =& $rootCategoriesIterator->next()) {
			$categoryId = $category->getId();
			if ($categoryId != $this->getCategoryId()) {
				// Don't permit time travel paradox
				$rootCategories[$categoryId] = $category->getLocalizedTitle();
			}
			unset($category);
		}
		$templateMgr->assign('rootCategories', $rootCategories);

		// Determine if this category has children of its own;
		// if so, prevent the user from giving it a parent.
		// (Forced two-level maximum tree depth.)
		if ($this->getCategoryId()) {
			$children =& $categoryDao->getByParentId($this->getCategoryId(), $press->getId());
			if ($children->next()) {
				$templateMgr->assign('cannotSelectChild', true);
			}
		}

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
		$category->setDescription($this->getData('description'), null); // Localized
		$category->setParentId($this->getData('parentId'));
		$category->setPath($this->getData('path'));

		// Update or insert the category object
		if ($categoryId == null) {
			$categoryId = $categoryDao->insertObject($category);
		} else {
			$categoryDao->updateObject($category);
		}
	}
}

?>
