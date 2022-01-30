<?php

/**
 * @file classes/services/NavigationMenuService.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NavigationMenuService
 * @ingroup services
 *
 * @brief Helper class that encapsulates NavigationMenu business logic
 */

namespace APP\services;

use APP\core\Application;
use APP\i18n\AppLocale;
use APP\template\TemplateManager;

use PKP\core\PKPApplication;
use PKP\db\DAORegistry;
use PKP\plugins\HookRegistry;

// FIXME: Add namespacing
use Validation;

class NavigationMenuService extends \PKP\services\PKPNavigationMenuService
{
    // Types for all omp default navigationMenuItems
    public const NMI_TYPE_CATALOG = 'NMI_TYPE_CATALOG';
    public const NMI_TYPE_SERIES = 'NMI_TYPE_SERIES';
    public const NMI_TYPE_CATEGORY = 'NMI_TYPE_CATEGORY';
    public const NMI_TYPE_NEW_RELEASE = 'NMI_TYPE_NEW_RELEASE';

    /**
     * Initialize hooks for extending PKPSubmissionService
     */
    public function __construct()
    {
        HookRegistry::register('NavigationMenus::itemTypes', [$this, 'getMenuItemTypesCallback']);
        HookRegistry::register('NavigationMenus::displaySettings', [$this, 'getDisplayStatusCallback']);
        HookRegistry::register('NavigationMenus::itemCustomTemplates', [$this, 'getMenuItemCustomEditTemplatesCallback']);
    }

    /**
     * Return all default navigationMenuItemTypes.
     *
     * @param string $hookName
     * @param array $args of arguments passed
     */
    public function getMenuItemTypesCallback($hookName, $args)
    {
        $types = & $args[0];

        AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_USER);

        $ompTypes = [
            self::NMI_TYPE_CATALOG => [
                'title' => __('navigation.catalog'),
                'description' => __('navigation.navigationMenus.catalog.description'),
            ],
            self::NMI_TYPE_NEW_RELEASE => [
                'title' => __('navigation.navigationMenus.newRelease'),
                'description' => __('navigation.navigationMenus.newRelease.description'),
            ],
        ];

        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

        $seriesDao = DAORegistry::getDAO('SeriesDAO');
        $series = $seriesDao->getByContextId($contextId);

        if ($series->count) {
            $newArray = [
                self::NMI_TYPE_SERIES => [
                    'title' => __('navigation.navigationMenus.series.generic'),
                    'description' => __('navigation.navigationMenus.series.description'),
                ],
            ];

            $ompTypes = array_merge($ompTypes, $newArray);
        }

        $categoryCount = Repo::category()->count(Repo::category()->getCollector()
            ->filterByParentIds([null])
            ->filterByContextIds([$contextId]));

        if ($categoryCount) {
            $newArray = [
                self::NMI_TYPE_CATEGORY => [
                    'title' => __('navigation.navigationMenus.category.generic'),
                    'description' => __('navigation.navigationMenus.category.description'),
                ],
            ];

            $ompTypes = array_merge($ompTypes, $newArray);
        }

        $types = array_merge($types, $ompTypes);
    }

    /**
     * Return all navigationMenuItem Types custom edit templates.
     *
     * @param string $hookName
     * @param array $args of arguments passed
     */
    public function getMenuItemCustomEditTemplatesCallback($hookName, $args)
    {
        $templates = & $args[0];

        AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_USER);

        $ompTemplates = [
            self::NMI_TYPE_CATEGORY => [
                'template' => 'controllers/grid/navigationMenus/categoriesNMIType.tpl',
            ],
            self::NMI_TYPE_SERIES => [
                'template' => 'controllers/grid/navigationMenus/seriesNMIType.tpl',
            ],
        ];

        $templates = array_merge($templates, $ompTemplates);
    }

    /**
     * Callback for display menu item functionallity
     *
     * @param string $hookName
     * @param array $args of arguments passed
     */
    public function getDisplayStatusCallback($hookName, $args)
    {
        $navigationMenuItem = & $args[0];

        $request = Application::get()->getRequest();
        $dispatcher = $request->getDispatcher();
        $templateMgr = TemplateManager::getManager(Application::get()->getRequest());

        $isUserLoggedIn = Validation::isLoggedIn();
        $isUserLoggedInAs = Validation::isLoggedInAs();
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

        $this->transformNavMenuItemTitle($templateMgr, $navigationMenuItem);

        $menuItemType = $navigationMenuItem->getType();

        if ($navigationMenuItem->getIsDisplayed()) {
            $menuItemType = $navigationMenuItem->getType();

            $relatedObject = null;

            switch ($menuItemType) {
                case self::NMI_TYPE_SERIES:
                    $seriesId = $navigationMenuItem->getPath();

                    $seriesDao = DAORegistry::getDAO('SeriesDAO');
                    $relatedObject = $seriesDao->getById($seriesId, $contextId);

                    break;
                case self::NMI_TYPE_CATEGORY:
                    $categoryId = $navigationMenuItem->getPath();

                    $relatedObject = Repo::category()->get($categoryId);
                    if ($relatedObject && $relatedObject->getContextId() != $contextId) $relatedObject = null;

                    break;
            }

            // Set the URL
            switch ($menuItemType) {
                case self::NMI_TYPE_CATALOG:
                    $navigationMenuItem->setUrl($dispatcher->url(
                        $request,
                        PKPApplication::ROUTE_PAGE,
                        null,
                        'catalog',
                        null,
                        null
                    ));
                    break;
                case self::NMI_TYPE_NEW_RELEASE:
                    $navigationMenuItem->setUrl($dispatcher->url(
                        $request,
                        PKPApplication::ROUTE_PAGE,
                        null,
                        'catalog',
                        'newReleases',
                        null
                    ));
                    break;
                case self::NMI_TYPE_SERIES:
                    if ($relatedObject) {
                        $navigationMenuItem->setUrl($dispatcher->url(
                            $request,
                            PKPApplication::ROUTE_PAGE,
                            null,
                            'catalog',
                            'series',
                            $relatedObject->getPath()
                        ));
                    } else {
                        $navigationMenuItem->setIsDisplayed(false);
                    }
                    break;
                case self::NMI_TYPE_CATEGORY:
                    if ($relatedObject) {
                        $navigationMenuItem->setUrl($dispatcher->url(
                            $request,
                            PKPApplication::ROUTE_PAGE,
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
}

if (!PKP_STRICT_MODE) {
    foreach ([
        'NMI_TYPE_CATALOG',
        'NMI_TYPE_SERIES',
        'NMI_TYPE_CATEGORY',
        'NMI_TYPE_NEW_RELEASE',
    ] as $constantName) {
        define($constantName, constant('\APP\services\NavigationMenuService::' . $constantName));
    }
}
