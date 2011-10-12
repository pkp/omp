<?php

/**
 * @file controllers/grid/settings/category/CategoryCategoryGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryCategoryGridHandler
 * @ingroup controllers_grid_settings_category
 *
 * @brief Handle operations for category management operations.
 */

// Import the base GridHandler.
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// Import user group grid specific classes
import('controllers.grid.settings.category.CategoryGridCategoryRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class CategoryCategoryGridHandler extends CategoryGridHandler {
	var $_pressId;

	/**
	 * Constructor
	 */
	function CategoryCategoryGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array(
				'fetchGrid',
				'fetchRow',
				'addCategory',
				'editCategory',
				'updateCategory'
			)
		);
	}

	//
	// Overridden methods from PKPHandler.
	//
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
	 * @see PKPHandler::initialize()
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {

		parent::initialize($request);

		$press =& $request->getPress();
		$this->_pressId =& $press->getId();

		// Load user-related translations.
		/* Locale::requireComponents(array(
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_OMP_MANAGER)
		);*/

		// Basic grid configuration.
		$this->setTitle('grid.category.categories');

		// Add grid-level actions.
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'addCategory',
				new AjaxModal(
					$router->url($request, null, null, 'addCategory'),
					__('grid.category.add'),
					'modal_add_category'
				),
				__('grid.category.add'),
				'add_category'
			)
		);

		// Add grid columns.
		$cellProvider = new DataObjectGridCellProvider();
		$cellProvider->setLocale(Locale::getLocale());

		$this->addColumn(
			new GridColumn(
				'title',
				'grid.category.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter) {
		// For top-level rows, only list categories without parents.
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categoriesIterator =& $categoryDao->getByParentId(null, $this->_getPressId());

		$categories = array();
		while ($category =& $categoriesIterator->next()) {
			$categories[$category->getId()] =& $category;
			unset($category);
		}

		return $categories;
	}

	/**
	* @see GridHandler::getRowInstance()
	* @return CategoryGridRow
	*/
	function &getRowInstance() {
		import('controllers.grid.settings.category.CategoryGridRow');
		$row = new CategoryGridRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::geCategorytRowInstance()
	 * @return CategoryGridCategoryRow
	 */
	function &getCategoryRowInstance() {
		$row = new CategoryGridCategoryRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData(&$category) {
		$categoryId = $category->getId();
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categoriesIterator =& $categoryDao->getByParentId($categoryId, $this->_getPressId());
		$categories = $categoriesIterator->toAssociativeArray();
		return $categories;
	}

	/**
	 * Handle the add category operation.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addCategory($args, &$request) {
		return $this->editCategory($args, $request);
	}

	/**
	 * Handle the edit category operation.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editCategory($args, &$request) {
		$categoryForm = $this->_getCategoryForm($request);

		$categoryForm->initData();

		$json = new JSONMessage(true, $categoryForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update category data in database and grid.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateCategory($args, &$request) {
		$categoryForm = $this->_getCategoryForm($request);

		$categoryForm->readInputData();
		if($categoryForm->validate()) {
			$categoryForm->execute($request);
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(true, $categoryForm->fetch($request));
			return $json->getString();
		}
	}

	//
	// Private helper methods.
	//

	/**
	* Get a CategoryForm instance.
	* @param $request Request
	* @return UserGroupForm
	*/
	function _getCategoryForm(&$request) {
		// Get the category ID.
		$categoryId = (int) $request->getUserVar('categoryId');

		// Instantiate the files form.
		import('controllers.grid.settings.category.form.CategoryForm');
		$pressId = $this->_getPressId();
		return new CategoryForm($pressId, $categoryId);
	}

	/**
	 * Get press id.
	 * @return int
	 */
	function _getPressId() {
		return $this->_pressId;
	}
}

?>
