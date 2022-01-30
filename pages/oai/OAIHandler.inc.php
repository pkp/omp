<?php

/**
 * @file pages/oai/OAIHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OAIHandler
 * @ingroup pages_oai
 *
 * @brief Handle OAI protocol requests.
 */

define('SESSION_DISABLE_INIT', 1); // FIXME?

use PKP\oai\OAIConfig;
use APP\oai\omp\PressOAI;
use APP\handler\Handler;

class OAIHandler extends Handler
{
    /**
     * @copydoc PKPHandler::authorize
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $returner = parent::authorize($request, $args, $roleAssignments);

        if (!Config::getVar('oai', 'oai')) {
            return false;
        } else {
            return $returner;
        }
    }

    /**
     * Handle an OAI request.
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args, $request)
    {
        PluginRegistry::loadCategory('oaiMetadataFormats', true);

        $oai = new PressOAI(new OAIConfig($request->url(null, 'oai'), Config::getVar('oai', 'repository_id')));
        $oai->execute();
    }
}
