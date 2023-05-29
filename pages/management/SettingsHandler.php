<?php

/**
 * @file pages/management/SettingsHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 *
 * @ingroup pages_management
 *
 * @brief Handle requests for settings pages.
 */

namespace APP\pages\management;

use APP\core\Request;
use APP\template\TemplateManager;
use PKP\pages\management\ManagementHandler;
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
     * Add the workflow settings page
     *
     * @param array $args
     * @param Request $request
     */
    public function workflow($args, $request)
    {
        parent::workflow($args, $request);

        $this->addReviewFormWorkflowSupport($request);

        TemplateManager::getManager($request)->display('management/workflow.tpl');
    }

    /**
     * Add the distribution settings page
     *
     * @param array $args
     * @param Request $request
     */
    public function distribution($args, $request)
    {
        parent::distribution($args, $request);
        TemplateManager::getManager($request)->display('management/distribution.tpl');
    }
}
