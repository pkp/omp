<?php

/**
 * @file classes/install/Upgrade.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Upgrade
 *
 * @ingroup install
 *
 * @brief Perform system upgrade.
 */

namespace APP\install;

use APP\core\Application;
use Illuminate\Support\Facades\Schema;
use PKP\db\DAORegistry;
use PKP\install\Installer;
use PKP\navigationMenu\NavigationMenuItemDAO;

class Upgrade extends Installer
{
    protected $appEmailTemplateVariableNames = [
        'contextName' => 'pressName',
        'contextUrl' => 'pressUrl',
        'contextSignature' => 'pressSignature',
    ];

    /**
     * Constructor.
     *
     * @param array $params upgrade parameters
     */
    public function __construct($params, $installFile = 'upgrade.xml', $isPlugin = false)
    {
        parent::__construct($installFile, $params, $isPlugin);
    }


    /**
     * Returns true iff this is an upgrade process.
     *
     * @return bool
     */
    public function isUpgrade()
    {
        return true;
    }


    //
    // Specific upgrade actions
    //
    /**
     * If StaticPages table exists we should port the data as NMIs
     *
     * @return bool
     */
    public function migrateStaticPagesToNavigationMenuItems()
    {
        if (Schema::hasTable('static_pages')) {
            $contextDao = Application::getContextDAO();
            $navigationMenuItemDao = DAORegistry::getDAO('NavigationMenuItemDAO'); /** @var NavigationMenuItemDAO $navigationMenuItemDao */

            $staticPagesDao = new \APP\plugins\generic\staticPages\classes\StaticPagesDAO();

            $contexts = $contextDao->getAll();
            while ($context = $contexts->next()) {
                $contextStaticPages = $staticPagesDao->getByContextId($context->getId())->toAssociativeArray();
                foreach ($contextStaticPages as $staticPage) {
                    $retNMIId = $navigationMenuItemDao->portStaticPage($staticPage);
                    if ($retNMIId) {
                        $staticPagesDao->deleteById($staticPage->getId());
                    } else {
                        error_log('WARNING: The StaticPage "' . $staticPage->getLocalizedTitle() . '" uses a path (' . $staticPage->getPath() . ') that conflicts with an existing Custom Navigation Menu Item path. Skipping this StaticPage.');
                    }
                }
            }
        }

        return true;
    }
}
