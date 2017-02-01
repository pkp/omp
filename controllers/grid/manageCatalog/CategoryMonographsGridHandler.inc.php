<?php

/**
 * @file controllers/grid/manageCatalog/CategoryMonographsGridHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryMonographsGridHandler
 * @ingroup controllers_grid_manageCatalog
 *
 * @brief Handle category monographs grid requests.
 */

import('controllers.grid.manageCatalog.CatalogMonographsGridHandler');

class CategoryMonographsGridHandler extends CatalogMonographsGridHandler {

	/**
	 * @copydoc GridHandler::initialize()
	 */
	function initialize($request) {
		$this->assocId = (int) $request->getUserVar('categoryId');
		if (!$this->assocId) {
			// No specific category in request, get the press first one, if any.
			$pressId = $request->getRouter()->getContext($request)->getId();
			$categoryFactory = DAORegistry::getDAO('CategoryDAO')->getByPressId($pressId);
			$category = $categoryFactory->next();
			if ($category) $this->assocId = $category->getId();
		}
		$this->assocType = ASSOC_TYPE_CATEGORY;
		parent::initialize($request);

		$userRoles = (array) $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
		if (array_intersect(array(ROLE_ID_MANAGER), $userRoles)) {
			import('lib.pkp.classes.linkAction.LinkAction');
			import('lib.pkp.classes.linkAction.request.AjaxModal');

			$dispatcher = $request->getDispatcher();
			$manageCategoriesLinkAction =
				new LinkAction(
					'manageCategories',
					new AjaxModal(
						$dispatcher->url($request, ROUTE_PAGE, null, 'management', 'categories'),
						__('catalog.manage.manageCategories'),
						'modal_manage',
						true
					),
					__('catalog.manage.manageCategories'),
					'manage'
				);
			$this->addAction($manageCategoriesLinkAction);
		}
	}

	/**
	 * @copydoc GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		return array('categoryId' => $this->assocId);
	}

	/**
	 * @copydoc GridHandler::isFilterFormCollapsible()
	 */
	protected function isFilterFormCollapsible() {
		return false;
	}

	/**
	 * @copydoc GridHandler::renderFilter()
	 */
	function renderFilter($request, $filterData = array()) {
		$filterData['categoryId'] = $this->assocId;
		$pressId = $request->getRouter()->getContext($request)->getId();
		$categoryFactory = DAORegistry::getDAO('CategoryDAO')->getByPressId($pressId);
		$categoryOptions = array();
		while ($category = $categoryFactory->next()) {
			$categoryOptions[$category->getId()] = $category->getLocalizedTitle();
		}

		$filterData['categoryOptions'] = $categoryOptions;

		return parent::renderFilter($request, $filterData);
	}

	/**
	 * @copydoc GridHandler::getFilterSelectionData()
	 */
	function getFilterSelectionData($request) {
		$filterData = parent::getFilterSelectionData($request);
		$filterData['categoryId'] = $this->assocId;
		return $filterData;
	}

	/**
	 * @copydoc GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		$pressId = $request->getRouter()->getContext($request)->getId();
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$monographFactory = $publishedMonographDao->getByCategoryId($filter['categoryId'], $pressId, $filter['searchText'], null, null, null, $filter['featured'], $filter['newReleased']);
		return $monographFactory->toAssociativeArray();
	}

	/**
	 * @copydoc CatalogMonographsGridHandler::getIsFeaturedColumnTitle()
	 */
	protected function getIsFeaturedColumnTitle() {
		return 'catalog.manage.categoryFeatured';
	}

	/**
	 * @copydoc CatalogMonographsGridHandler::getNewReleaseColumnTitle()
	 */
	protected function getNewReleaseColumnTitle() {
		return 'catalog.manage.feature.categoryNewRelease';
	}
}


