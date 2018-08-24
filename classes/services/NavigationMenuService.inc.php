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
define('NMI_TYPE_NEW_RELEASE', 'NMI_TYPE_NEW_RELEASE');

class NavigationMenuService extends \PKP\Services\PKPNavigationMenuService {

	/**
	 * Initialize hooks for extending PKPSubmissionService
	 */
    public function __construct() {

		\HookRegistry::register('NavigationMenus::itemTypes', array($this, 'getMenuItemTypesCallback'));
		\HookRegistry::register('NavigationMenus::displaySettings', array($this, 'getDisplayStatusCallback'));
		\HookRegistry::register('NavigationMenus::nmiFormTemplateParameters', array($this, 'getFormTemplateParametersCallback'));
		\HookRegistry::register('NavigationMenus::nmiFormExecute', array($this, 'getFormExecuteCallback'));
		\HookRegistry::register('NavigationMenus::nmiFormData', array($this, 'getFormDataCallback'));
		\HookRegistry::register('NavigationMenus::nmiFormInputData', array($this, 'getFormInputDataCallback'));
		\HookRegistry::register('NavigationMenus::nmiFormValidate', array($this, 'getFormValidateCallback'));
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
				'title' => __('navigation.navigationMenus.series.generic'),
				'description' => __('navigation.navigationMenus.series.description'),
			),
			NMI_TYPE_CATEGORY => array(
				'title' => __('navigation.navigationMenus.category.generic'),
				'description' => __('navigation.navigationMenus.category.description'),
			),
			NMI_TYPE_NEW_RELEASE => array(
				'title' => __('navigation.navigationMenus.newRelease'),
				'description' => __('navigation.navigationMenus.newRelease.description'),
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
				case NMI_TYPE_NEW_RELEASE:
					$navigationMenuItem->setUrl($dispatcher->url(
						$request,
						ROUTE_PAGE,
						null,
						'catalog',
						'newReleases',
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

	function getFormExecuteCallback($hookName, $args) {
		$form =& $args[0];
		$navigationMenuItem =& $args[1];

		if ($form->getData('menuItemType') == NMI_TYPE_SERIES) {
			$navigationMenuItem->setPath($form->getData('relatedSeriesId'));
		} else if ($form->getData('menuItemType') == NMI_TYPE_CATEGORY) {
			$navigationMenuItem->setPath($form->getData('relatedCategoryId'));
		}
	}

	function getFormDataCallback($hookName, $args) {
		$formDataArray =& $args[0];
		$navigationMenuItem =& $args[1];

		$ompFormData = array(
			'selectedRelatedObjectId' => $navigationMenuItem->getPath(),
		);

		$formDataArray = array_merge($formDataArray, $ompFormData);
	}

	function getFormInputDataCallback($hookName, $args) {
		$formInputDataArray =& $args[0];

		$ompFormInputData = array(
			'relatedSeriesId',
			'relatedCategoryId',
		);

		$formInputDataArray = array_merge($formInputDataArray, $ompFormInputData);
	}

	function getFormValidateCallback($hookName, $args) {
		$form =& $args[0];

		if ($form->getData('menuItemType') == NMI_TYPE_SERIES) {
			if ($form->getData('relatedSeriesId') == null || $form->getData('relatedSeriesId') == 0) {
				$form->addError('menuItemType', __('manager.navigationMenus.form.navigationMenuItem.series.noItems'));
			}
		} else if ($form->getData('menuItemType') == NMI_TYPE_CATEGORY) {
			if ($form->getData('relatedCategoryId') == null || $form->getData('relatedCategoryId') == 0) {
				$form->addError('menuItemType', __('manager.navigationMenus.form.navigationMenuItem.category.noItems'));
			}
		}
	}
}
