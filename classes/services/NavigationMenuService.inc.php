<?php

/**
 * @file classes/services/NavigationMenuService.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NavigationMenuService
 * @ingroup services
 *
 * @brief Helper class that encapsulates NavigationMenu business logic
 */

namespace OMP\Services;

/** types for all omp default navigationMenuItems */
define('NMI_TYPE_CATALOG', 'NMI_TYPE_CATALOG');
define('NMI_TYPE_SERIES', 'NMI_TYPE_SERIES');
define('NMI_TYPE_CATEGORY', 'NMI_TYPE_CATEGORY');

class NavigationMenuService extends \PKP\Services\PKPNavigationMenuService {

	/**
	 * Initialize hooks for extending PKPSubmissionService
	 */
    public function __construct() {

		\HookRegistry::register('NavigationMenus::itemTypes', array($this, 'getMenuItemTypesCallback'));
		\HookRegistry::register('NavigationMenus::displaySettings', array($this, 'getDisplayStatusCallback'));
		\HookRegistry::register('NavigationMenus::nmiFormTemplateParameters', array($this, 'getFormTemplateParametersCallback'));
	}

	/**
	 * Return all default navigationMenuItemTypes.
	 * @param $hookName string
	 * @param $args array of arguments passed
	 */
	public function getMenuItemTypesCallback($hookName, $args) {
		$types =& $args[0];

		\AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_USER);

		$ompTypes = array(
			NMI_TYPE_CATALOG => array(
				'title' => __('navigation.catalog'),
				'description' => __('navigation.navigationMenus.catalog.description'),
			),
			NMI_TYPE_SERIES => array(
				'title' => __('navigation.series.generic'),
				'description' => __('navigation.navigationMenus.series.description'),
			),
			NMI_TYPE_CATEGORY => array(
				'title' => __('navigation.category.generic'),
				'description' => __('navigation.navigationMenus.category.description'),
			),
		);

		$types = array_merge($types, $ompTypes);
	}

	/**
	 * Callback for display menu item functionallity
	 * @param $hookName string
	 * @param $args array of arguments passed
	 */
	function getDisplayStatusCallback($hookName, $args) {
		$navigationMenuItem =& $args[0];

		$request = \Application::getRequest();
		$dispatcher = $request->getDispatcher();
		$templateMgr = \TemplateManager::getManager(\Application::getRequest());

		$isUserLoggedIn = \Validation::isLoggedIn();
		$isUserLoggedInAs = \Validation::isLoggedInAs();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

		$this->transformNavMenuItemTitle($templateMgr, $navigationMenuItem);

		$menuItemType = $navigationMenuItem->getType();

		if ($navigationMenuItem->getIsDisplayed()) {

			$request = \Application::getRequest();
			$context = $request->getContext();
			$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

			$menuItemType = $navigationMenuItem->getType();

			$relatedObject = null;

			// Adjust some titles
			switch ($menuItemType) {
			  case NMI_TYPE_SERIES:
			    $seriesId = $navigationMenuItem->getPath();

			    $seriesDao = \DAORegistry::getDAO('SeriesDAO');
			    $relatedObject = $seriesDao->getById($seriesId, $contextId);

			    if($relatedObject) {
			      $navigationMenuItem->setTitle(__('navigation.series', array('name' => $relatedObject->getLocalizedTitle())), \AppLocale::getLocale());
			    }

			    break;
			  case NMI_TYPE_CATEGORY:
			    $categoryId = $navigationMenuItem->getPath();

			    $categoryDao = \DAORegistry::getDAO('CategoryDAO');
			    $relatedObject = $categoryDao->getById($categoryId, $contextId);

			    if($relatedObject) {
			      $navigationMenuItem->setTitle(__('navigation.category', array('name' => $relatedObject->getLocalizedTitle())), \AppLocale::getLocale());
			    }

			    break;
			}

			// Set the URL
			switch ($menuItemType) {
				case NMI_TYPE_CATALOG:
					$navigationMenuItem->setUrl($dispatcher->url(
						$request,
						ROUTE_PAGE,
						null,
						'catalog',
						null,
						null
					));
					break;
				case NMI_TYPE_SERIES:
					if ($relatedObject) {
						$navigationMenuItem->setUrl($dispatcher->url(
							$request,
							ROUTE_PAGE,
							null,
							'catalog',
							'series',
							$relatedObject->getPath()
						));
					} else {
						$navigationMenuItem->setIsDisplayed(false);
					}
					break;
				case NMI_TYPE_CATEGORY:
					if ($relatedObject) {
						$navigationMenuItem->setUrl($dispatcher->url(
							$request,
							ROUTE_PAGE,
							null,
							'catalog',
							'category',
							$relatedObject->getPath()
						));
					} else {
						$navigationMenuItem->setIsDisplayed(false);
					}
					break;
			}
		}
	}

	function getFormTemplateParametersCallback($hookName, $args) {
		$templateParameters =& $args[0];

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

		$ompTemplateParameters = array(
			'navigationMenuItemSeriesTitles' => $seriesTitles,
			'navigationMenuItemCategoryTitles' => $categoryTitles,
		);

		$templateParameters = array_merge($templateParameters, $ompTemplateParameters);
	}

	//public function getNMIForm($contextId, $navigationMenuItemId, $navigationMenuIdParent) {
	//  import('controllers.grid.navigationMenus.form.OMPNavigationMenuItemsForm');
	//  $navigationMenuItemForm = new \OMPNavigationMenuItemsForm($contextId, $navigationMenuItemId, $navigationMenuIdParent);

	//  return $navigationMenuItemForm;
	//}
}
