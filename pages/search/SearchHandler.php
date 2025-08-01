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

use APP\core\Request;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\template\TemplateManager;
use Laravel\Scout\Builder;
use PKP\search\SubmissionSearchResult;
use PKP\userGroup\UserGroup;

class SearchHandler extends Handler
{
    /**
     * Show the search form
     *
     * @param array $args
     * @param Request $request
     */
    public function index($args, $request)
    {
        $this->search($args, $request);
    }

    /**
     * View the results of a search operation.
     */
    public function search(array $args, Request $request)
    {
        $this->validate(null, $request);

        $context = $request->getContext();
        $contextId = $context?->getId() ?? (int) $request->getUserVar('searchContext');

        $query = (string) $request->getUserVar('query');
        $dateFrom = $request->getUserDateVar('dateFrom');
        $dateTo = $request->getUserDateVar('dateTo');

        $rangeInfo = $this->getRangeInfo($request, 'search');

        // Retrieve results.
        $results = (new Builder(new SubmissionSearchResult(), $query))
            ->where('contextId', $contextId)
            ->where('publishedFrom', $dateFrom)
            ->where('publishedTo', $dateTo)
            ->whereIn('categoryIds', $request->getUserVar('categoryIds'))
            ->whereIn('sectionIds', $request->getUserVar('sectionIds'))
            ->paginate($rangeInfo->getCount(), 'submissions', $rangeInfo->getPage());

        $this->setupTemplate($request);

        $templateMgr = TemplateManager::getManager($request);

        // Assign the year range.
        $collector = Repo::publication()->getCollector();
        $collector->filterByContextIds($contextId ? [$contextId] : null);
        $yearRange = Repo::publication()->getDateBoundaries($collector);
        $yearStart = substr($yearRange->min_date_published, 0, 4);
        $yearEnd = substr($yearRange->max_date_published, 0, 4);

        $templateMgr->assign([
            'query' => $query,
            'results' => $results,
            'searchContext' => $contextId,
            'dateFrom' => $dateFrom ? date('Y-m-d H:i:s', $dateFrom) : null,
            'dateTo' => $dateTo ? date('Y-m-d H:i:s', $dateTo) : null,
            'yearStart' => $yearStart,
            'yearEnd' => $yearEnd,
            'authorUserGroups' => UserGroup::withRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])
                ->withContextIds($contextId ? [$contextId] : null)
                ->get(),
        ]);

        if (!$request->getContext()) {
            $templateMgr->assign([
                'searchableContexts' => $this->getSearchableContexts(),
            ]);
        }

        $templateMgr->display('frontend/pages/search.tpl');
    }
}
