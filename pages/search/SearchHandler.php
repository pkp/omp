<?php

/**
 * @file pages/search/SearchHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SearchHandler
 *
 * @ingroup pages_search
 *
 * @brief Handle site index requests.
 */

namespace APP\pages\search;

use APP\facades\Repo;
use APP\handler\Handler;
use APP\search\MonographSearch;
use APP\template\TemplateManager;

class SearchHandler extends Handler
{
    /**
     * Show the search form
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args, $request)
    {
        $this->search($args, $request);
    }

    /**
     * View the results of a search operation.
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return string
     */
    public function search($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $press = $request->getPress();
        $this->setupTemplate($request);

        // Get the range info.
        $rangeInfo = $this->getRangeInfo($request, 'search');

        // Fetch the monographs to display
        $monographSearch = new MonographSearch();
        $error = null;
        $query = $request->getUserVar('query');
        $templateMgr->assign([
            'results' => $monographSearch->retrieveResults($request, $press, [null => $query], $error, null, null, $rangeInfo),
            'searchQuery' => $query,
            'authorUserGroups' => Repo::userGroup()->getCollector()
                ->filterByRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])
                ->filterByContextIds($press ? [$press->getId()] : null)
                ->getMany()->remember(),
        ]);

        // Display
        $templateMgr->display('frontend/pages/search.tpl');
    }
}
