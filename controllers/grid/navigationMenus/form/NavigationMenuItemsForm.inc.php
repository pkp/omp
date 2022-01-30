<?php

/**
 * @file controllers/grid/navigationMenus/form/NavigationMenuItemsForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NavigationMenuItemsForm
 * @ingroup controllers_grid_navigationMenus
 *
 * @brief Form for managers to create/edit navigationMenuItems.
 */

import('lib.pkp.controllers.grid.navigationMenus.form.PKPNavigationMenuItemsForm');

use APP\template\TemplateManager;

class NavigationMenuItemsForm extends PKPNavigationMenuItemsForm
{
    /**
     * @copydoc Form::fetch()
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $customTemplates = \Services::get('navigationMenu')->getMenuItemCustomEditTemplates();

        $request = \Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

        $seriesDao = \DAORegistry::getDAO('SeriesDAO');
        $series = $seriesDao->getByContextId($contextId);
        $seriesTitlesArray = $series->toAssociativeArray();

        $seriesTitles = [];
        foreach ($seriesTitlesArray as $series) {
            $seriesTitles[$series->getId()] = $series->getLocalizedTitle();
        }

        $categories = Repo::category()->getMany(Repo::category()->getCollector()
            ->filterByParentIds([null])
            ->filterByContextIds([$contextId]));

        $categoryTitles = [];
        foreach ($categories as $category) {
            $categoryTitles[$category->getId()] = $category->getLocalizedTitle();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'customTemplates' => $customTemplates,
            'navigationMenuItemSeriesTitles' => $seriesTitles,
            'navigationMenuItemCategoryTitles' => $categoryTitles,
        ]);

        return parent::fetch($request, $template, $display);
    }

    /**
     * @copydoc PKPNavigationMenuItemsForm::initData
     */
    public function initData()
    {
        $navigationMenuItemDao = DAORegistry::getDAO('NavigationMenuItemDAO'); /** @var NavigationMenuItemDAO $navigationMenuItemDao */
        $navigationMenuItem = $navigationMenuItemDao->getById($this->navigationMenuItemId);

        if ($navigationMenuItem) {
            parent::initData();
            $ompInitData = [
                'selectedRelatedObjectId' => $navigationMenuItem->getPath(),
            ];

            $this->_data = array_merge($ompInitData, $this->_data);
        } else {
            parent::initData();
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData()
    {
        $this->readUserVars([
            'relatedSeriesId',
            'relatedCategoryId',
        ]);
        parent::readInputData();
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);

        $navigationMenuItemDao = DAORegistry::getDAO('NavigationMenuItemDAO'); /** @var NavigationMenuItemDAO $navigationMenuItemDao */

        $navigationMenuItem = $navigationMenuItemDao->getById($this->navigationMenuItemId);
        if (!$navigationMenuItem) {
            $navigationMenuItem = $navigationMenuItemDao->newDataObject();
        }

        if ($this->getData('menuItemType') == NMI_TYPE_SERIES) {
            $navigationMenuItem->setPath($this->getData('relatedSeriesId'));
        } elseif ($this->getData('menuItemType') == NMI_TYPE_CATEGORY) {
            $navigationMenuItem->setPath($this->getData('relatedCategoryId'));
        }

        // Update navigation menu item
        $navigationMenuItemDao->updateObject($navigationMenuItem);

        return $navigationMenuItem->getId();
    }
}
