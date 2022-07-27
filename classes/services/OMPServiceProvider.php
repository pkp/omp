<?php

/**
 * @file classes/services/OMPServiceProvider.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OMPServiceProvider
 * @ingroup services
 *
 * @brief Utility class to package all OMP services
 */

namespace APP\services;

require_once(dirname(__FILE__) . '/../../lib/pkp/lib/vendor/pimple/pimple/src/Pimple/Container.php');
require_once(dirname(__FILE__) . '/../../lib/pkp/lib/vendor/pimple/pimple/src/Pimple/ServiceProviderInterface.php');

use Pimple\Container;

use PKP\services\PKPFileService;
use PKP\services\PKPSchemaService;
use PKP\services\PKPSiteService;
use PKP\services\PKPStatsContextService;
use PKP\services\PKPStatsGeoService;
use PKP\services\PKPStatsSushiService;

class OMPServiceProvider implements \Pimple\ServiceProviderInterface
{
    /**
     * Registers services
     *
     */
    public function register(Container $pimple)
    {
        // File service
        $pimple['file'] = function () {
            return new PKPFileService();
        };

        // PublicationFormat service
        $pimple['publicationFormat'] = function () {
            return new PublicationFormatService();
        };

        // NavigationMenus service
        $pimple['navigationMenu'] = function () {
            return new NavigationMenuService();
        };

        // Context service
        $pimple['context'] = function () {
            return new ContextService();
        };

        // Schema service
        $pimple['schema'] = function () {
            return new PKPSchemaService();
        };

        // Site service
        $pimple['site'] = function () {
            return new PKPSiteService();
        };

        // Context statistics service
        $pimple['contextStats'] = function () {
            return new PKPStatsContextService();
        };

        // Publication statistics service
        $pimple['publicationStats'] = function () {
            return new StatsPublicationService();
        };

        // Geo statistics service
        $pimple['geoStats'] = function () {
            return new PKPStatsGeoService();
        };

        // SUSHI statistics service
        $pimple['sushiStats'] = function () {
            return new PKPStatsSushiService();
        };

        // Editorial statistics service
        $pimple['editorialStats'] = function () {
            return new StatsEditorialService();
        };
    }
}
