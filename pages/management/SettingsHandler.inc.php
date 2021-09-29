<?php

/**
 * @file pages/management/SettingsHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for settings pages.
 */

// Import the base ManagementHandler.
import('lib.pkp.pages.management.ManagementHandler');

use APP\template\TemplateManager;

use PKP\security\Role;

class SettingsHandler extends ManagementHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_SITE_ADMIN],
            [
                'access',
            ]
        );
        $this->addRoleAssignment(
            Role::ROLE_ID_MANAGER,
            [
                'settings',
            ]
        );
    }

    /**
     * @copydoc ManagementHandler::website()
     */
    public function website($args, $request)
    {
        AppLocale::requireComponents(
            LOCALE_COMPONENT_PKP_SUBMISSION,
            LOCALE_COMPONENT_APP_SUBMISSION
        );
        parent::website($args, $request);
    }

    /**
     * Add the workflow settings page
     *
     * @param $args array
     * @param $request Request
     */
    public function workflow($args, $request)
    {
        parent::workflow($args, $request);
        TemplateManager::getManager($request)->display('management/workflow.tpl');
    }

    /**
     * Add the distribution settings page
     *
     * @param $args array
     * @param $request Request
     */
    public function distribution($args, $request)
    {
        parent::distribution($args, $request);
        TemplateManager::getManager($request)->display('management/distribution.tpl');
    }
}
