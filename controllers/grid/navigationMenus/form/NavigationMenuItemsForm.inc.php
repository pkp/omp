<?php

/**
 * @file controllers/grid/navigationMenus/form/NavigationMenuItemsForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NavigationMenuItemsForm
 * @ingroup controllers_grid_navigationMenus
 *
 * @brief Form for managers to create/edit navigationMenuItems.
 */

import('lib.pkp.controllers.grid.navigationMenus.form.PKPNavigationMenuItemsForm');
import('classes.core.ServicesContainer');

class NavigationMenuItemsForm extends PKPNavigationMenuItemsForm {

	function __construct($request, $navigationMenuItemId = null) {
		ServicesContainer::instance()
			->get('navigationMenu');

		parent::__construct($request, $navigationMenuItemId);
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		import('classes.core.ServicesContainer');
		$customTemplates = ServicesContainer::instance()
			->get('navigationMenu')
			->getMenuItemCustomEditTemplates();

		$request = \Application::getRequest();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

		$seriesDao = \DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getByContextId($contextId);
		$seriesTitlesArray = $series->toAssociativeArray();

		$seriesTitles = array();
		foreach ($seriesTitlesArray as $series) {
			$seriesTitles[$series->getId()] = $series->getLocalizedTitle();
		}

		$categoryDao = \DAORegistry::getDAO('CategoryDAO');
		$categories = $categoryDao->getByParentId(null, $contextId);
		$categoryTitlesArray = $categories->toAssociativeArray();

		$categoryTitles = array();
		foreach ($categoryTitlesArray as $category) {
			$categoryTitles[$category->getId()] = $category->getLocalizedTitle();
		}

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('customTemplates', $customTemplates);
		$templateMgr->assign('navigationMenuItemSeriesTitles', $seriesTitles);
		$templateMgr->assign('navigationMenuItemCategoryTitles', $categoryTitles);

		return parent::fetch($request, $template, $display);
	}

	/**
	 * @copydoc PKPNavigationMenuItemsForm::initData
	 */
	function initData() {
		$navigationMenuItemDao = DAORegistry::getDAO('NavigationMenuItemDAO');
		$navigationMenuItem = $navigationMenuItemDao->getById($this->navigationMenuItemId);

		if ($navigationMenuItem) {
			parent::initData();
			$ompInitData = array(
				'selectedRelatedObjectId' => $navigationMenuItem->getPath(),
			);

			$this->_data = array_merge($ompInitData, $this->_data);
		} else {
			parent::initData();
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'relatedSeriesId',
			'relatedCategoryId',
		));
		parent::readInputData();
	}

	/**
	 * Save NavigationMenuItem.
	 */
	function execute() {
		parent::execute();

		$navigationMenuItemDao = DAORegistry::getDAO('NavigationMenuItemDAO');

		$navigationMenuItem = $navigationMenuItemDao->getById($this->navigationMenuItemId);
		if (!$navigationMenuItem) {
			$navigationMenuItem = $navigationMenuItemDao->newDataObject();
		}

		if ($this->getData('menuItemType') == NMI_TYPE_SERIES) {
			$navigationMenuItem->setPath($this->getData('relatedSeriesId'));
		} else if ($this->getData('menuItemType') == NMI_TYPE_CATEGORY) {
			$navigationMenuItem->setPath($this->getData('relatedCategoryId'));
		}

		// Update navigation menu item
		$navigationMenuItemDao->updateObject($navigationMenuItem);

		return $navigationMenuItem->getId();
	}

}


