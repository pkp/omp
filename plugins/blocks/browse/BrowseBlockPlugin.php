<?php

/**
 * @file plugins/blocks/browse/BrowseBlockPlugin.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BrowseBlockPlugin
 *
 * @ingroup plugins_blocks_browse
 *
 * @brief Class for browse block plugin
 */

namespace APP\plugins\blocks\browse;

use APP\facades\Repo;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\BlockPlugin;
use PKP\template\PKPTemplateManager;

class BrowseBlockPlugin extends BlockPlugin
{
    /**
     * Install default settings on press creation.
     *
     * @return string
     */
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.block.browse.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription()
    {
        return __('plugins.block.browse.description');
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $actionArgs)
    {
        $router = $request->getRouter();
        return array_merge(
            $this->getEnabled() ? [
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url($request, null, null, 'manage', null, array_merge($actionArgs, ['verb' => 'settings'])),
                        $this->getDisplayName(),
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ] : [],
            parent::getActions($request, $actionArgs)
        );
    }

    /**
     * @copydoc PKPPlugin::manage()
     */
    public function manage($args, $request)
    {
        $press = $request->getPress();

        switch ($request->getUserVar('verb')) {
            case 'settings':
                $form = new BrowseBlockSettingsForm($this, $press->getId());
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        return new JSONMessage(true);
                    }
                } else {
                    $form->initData();
                }
                return new JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
    }

    /**
     * Get the HTML contents of the browse block.
     *
     * @param PKPTemplateManager $templateMgr
     * @param null|mixed $request
     *
     * @return string
     */
    public function getContents($templateMgr, $request = null)
    {
        $press = $request->getPress();

        $browseNewReleases = $this->getSetting($press->getId(), 'browseNewReleases');
        $templateMgr->assign('browseNewReleases', $browseNewReleases);

        $seriesDisplay = $this->getSetting($press->getId(), 'browseSeries');
        if ($seriesDisplay) {
            // Provide a list of series to browse
            $series = Repo::section()
                ->getCollector()
                ->filterByContextIds([$press->getId()])
                ->getMany();
            $templateMgr->assign('browseSeries', $series->toArray());
        }

        $categoriesDisplay = $this->getSetting($press->getId(), 'browseCategories');
        if ($categoriesDisplay) {
            // Provide a list of categories to browse
            $categories = Repo::category()->getCollector()
                ->filterByContextIds([$press->getId()])
                ->getMany();

            $templateMgr->assign('browseCategories', iterator_to_array($categories));
        }

        // If we're currently viewing a series or catalog, detect it
        // so that we can highlight the current selection in the
        // dropdown.
        $router = $request->getRouter();
        switch ($router->getRequestedOp($request)) {
            case 'category':
                $args = $router->getRequestedArgs($request);
                $templateMgr->assign('browseBlockSelectedCategory', reset($args));
                break;
            case 'series':
                $args = $router->getRequestedArgs($request);
                $templateMgr->assign('browseBlockSelectedSeries', reset($args));
                break;
        }

        return parent::getContents($templateMgr);
    }
}
