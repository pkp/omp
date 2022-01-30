<?php

/**
 * @file controllers/grid/settings/plugins/SettingsPluginGridHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SettingsPluginGridHandler
 * @ingroup controllers_grid_settings_plugins
 *
 * @brief Handle plugin grid requests.
 */

use PKP\controllers\grid\plugins\PluginGridHandler;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\PluginAccessPolicy;
use PKP\security\Role;

class SettingsPluginGridHandler extends PluginGridHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $roles = [Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER];
        $this->addRoleAssignment($roles, ['manage']);
        parent::__construct($roles);
    }


    //
    // Extended methods from PluginGridHandler
    //
    /**
     * @copydoc PluginGridHandler::loadCategoryData()
     *
     * @param null|mixed $filter
     */
    public function loadCategoryData($request, &$categoryDataElement, $filter = null)
    {
        $plugins = parent::loadCategoryData($request, $categoryDataElement, $filter);

        $pressDao = DAORegistry::getDAO('PressDAO'); /** @var PressDAO $pressDao */
        $presses = $pressDao->getAll();
        $firstPress = $presses->next();
        $secondPress = $presses->next();
        $singlePress = $firstPress && !$secondPress;

        $userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

        $showSitePlugins = false;
        if ($singlePress && in_array(Role::ROLE_ID_SITE_ADMIN, $userRoles)) {
            $showSitePlugins = true;
        }

        if ($showSitePlugins) {
            return $plugins;
        } else {
            $contextLevelPlugins = [];
            foreach ($plugins as $plugin) {
                if (!$plugin->isSitePlugin()) {
                    $contextLevelPlugins[$plugin->getName()] = $plugin;
                }
            }
            return $contextLevelPlugins;
        }
    }

    //
    // Overriden template methods.
    //
    /**
     * @copydoc GridHandler::getRowInstance()
     */
    public function getRowInstance()
    {
        import('lib.pkp.controllers.grid.plugins.PluginGridRow');
        return new PluginGridRow($this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES));
    }

    /**
     * @copydoc GridHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $categoryName = $request->getUserVar('category');
        $pluginName = $request->getUserVar('plugin');

        if ($categoryName && $pluginName) {
            switch ($request->getRequestedOp()) {
                case 'enable':
                case 'disable':
                case 'manage':
                    $accessMode = PluginAccessPolicy::ACCESS_MODE_MANAGE;
                    break;
                default:
                    $accessMode = PluginAccessPolicy::ACCESS_MODE_ADMIN;
                    break;
            }
            $this->addPolicy(new PluginAccessPolicy($request, $args, $roleAssignments, $accessMode));
        } else {
            $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        }
        return parent::authorize($request, $args, $roleAssignments);
    }
}
