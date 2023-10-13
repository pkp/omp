<?php

/**
 * @file classes/search/MonographSearch.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographSearch
 *
 * @ingroup search
 *
 * @see MonographSearchDAO
 *
 * @brief Class for retrieving monograph search results.
 *
 */

namespace APP\search;

use APP\core\Application;
use APP\core\Request;
use APP\core\Services;
use APP\facades\Repo;
use APP\press\Press;
use PKP\db\DAORegistry;
use PKP\plugins\Hook;
use PKP\search\SubmissionSearch;

class MonographSearch extends SubmissionSearch
{
    /**
     * See SubmissionSearch::getSparseArray()
     */
    public function getSparseArray($unorderedResults, $orderBy, $orderDir, $exclude)
    {
        // Calculate a well-ordered (unique) score.
        $resultCount = count($unorderedResults);
        $i = 0;
        $contextIds = [];
        foreach ($unorderedResults as $submissionId => &$data) {
            // Reference is necessary to permit modification
            $data['score'] = ($resultCount * $data['count']) + $i++;
            $contextIds[] = $data['press_id'];
        }

        // If we got a primary sort order then apply it and use score as secondary
        // order only.
        // NB: We apply order after merging and before paging/formatting. Applying
        // order before merging would require us to retrieve dependent objects for
        // results being purged later. Doing everything in a closed SQL is not
        // possible (e.g. for authors). Applying sort order after paging and
        // formatting is not possible as we have to order the whole list before
        // slicing it. So this seems to be the most appropriate place, although we
        // may have to retrieve some objects again when formatting results.
        $orderedResults = [];
        $contextDao = Application::getContextDAO();
        $contextTitles = [];
        if ($orderBy == 'popularityAll' || $orderBy == 'popularityMonth') {
            // Retrieve a metrics report for all submissions.
            $filter = [
                'submissionIds' => [array_keys($unorderedResults)],
                'contextIds' => $contextIds,
                'assocTypes' => [Application::ASSOC_TYPE_SUBMISSION, Application::ASSOC_TYPE_SUBMISSION_FILE]
            ];
            if ($orderBy == 'popularityMonth') {
                $oneMonthAgo = date('Ymd', strtotime('-1 month'));
                $today = date('Ymd');
                $filter['dateStart'] = $oneMonthAgo;
                $filter['dateEnd'] = $today;
            }
            $rawReport = Services::get('publicationStats')->getTotals($filter);
            foreach ($rawReport as $row) {
                $unorderedResults[$row->submission_id]['metric'] = $row->metric;
            }
        }

        $i = 0; // Used to prevent ties from clobbering each other
        $authorUserGroups = Repo::userGroup()->getCollector()->filterByRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])->getMany();
        foreach ($unorderedResults as $submissionId => $data) {
            // Exclude unwanted IDs.
            if (in_array($submissionId, $exclude)) {
                continue;
            }

            switch ($orderBy) {
                case 'authors':
                    $submission = Repo::submission()->get($submissionId);
                    $orderKey = $submission->getCurrentPublication()->getAuthorString($authorUserGroups);
                    break;

                case 'title':
                    $submission = Repo::submission()->get($submissionId);
                    $orderKey = '';
                    if (!empty($submission->getCurrentPublication())) {
                        $orderKey = $submission->getCurrentPublication()->getLocalizedData('title');
                    }
                    break;

                case 'pressTitle':
                    if (!isset($contextTitles[$data['press_id']])) {
                        /** @var Press */
                        $press = $contextDao->getById($data['press_id']);
                        $contextTitles[$data['press_id']] = $press->getLocalizedName();
                    }
                    $orderKey = $contextTitles[$data['press_id']];
                    break;

                case 'publicationDate':
                    $orderKey = $data[$orderBy];
                    break;

                case 'popularityAll':
                case 'popularityMonth':
                    $orderKey = ($data['metric'] ?? 0);
                    break;

                default: // order by score.
                    $orderKey = $data['score'];
            }
            if (!isset($orderedResults[$orderKey])) {
                $orderedResults[$orderKey] = [];
            }
            $orderedResults[$orderKey][$data['score'] + $i++] = $submissionId;
        }

        // Order the results by primary order.
        if (strtolower($orderDir) == 'asc') {
            ksort($orderedResults);
        } else {
            krsort($orderedResults);
        }

        // Order the result by secondary order and flatten it.
        $finalOrder = [];
        foreach ($orderedResults as $orderKey => $submissionIds) {
            if (count($submissionIds) == 1) {
                $finalOrder[] = array_pop($submissionIds);
            } else {
                if (strtolower($orderDir) == 'asc') {
                    ksort($submissionIds);
                } else {
                    krsort($submissionIds);
                }
                $finalOrder = array_merge($finalOrder, array_values($submissionIds));
            }
        }
        return $finalOrder;
    }

    /**
     * Retrieve the search filters from the request.
     *
     * @param Request $request
     *
     * @return array All search filters (empty and active)
     */
    public function getSearchFilters($request)
    {
        $searchFilters = [
            'query' => $request->getUserVar('query'),
            'searchPress' => $request->getUserVar('searchPress'),
            'abstract' => $request->getUserVar('abstract'),
            'authors' => $request->getUserVar('authors'),
            'title' => $request->getUserVar('title'),
            'galleyFullText' => $request->getUserVar('galleyFullText'),
            'suppFiles' => $request->getUserVar('suppFiles'),
            'discipline' => $request->getUserVar('discipline'),
            'subject' => $request->getUserVar('subject'),
            'type' => $request->getUserVar('type'),
            'coverage' => $request->getUserVar('coverage'),
            'indexTerms' => $request->getUserVar('indexTerms')
        ];

        // Is this a simplified query from the navigation
        // block plugin?
        $simpleQuery = $request->getUserVar('simpleQuery');
        if (!empty($simpleQuery)) {
            // In the case of a simplified query we get the
            // filter type from a drop-down.
            $searchType = $request->getUserVar('searchField');
            if (array_key_exists($searchType, $searchFilters)) {
                $searchFilters[$searchType] = $simpleQuery;
            }
        }

        // Publishing dates.
        $fromDate = $request->getUserDateVar('dateFrom', 1, 1);
        $searchFilters['fromDate'] = (is_null($fromDate) ? null : date('Y-m-d H:i:s', $fromDate));
        $toDate = $request->getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
        $searchFilters['toDate'] = (is_null($toDate) ? null : date('Y-m-d H:i:s', $toDate));

        // Instantiate the context.
        $context = $request->getContext();
        $siteSearch = !((bool)$context);
        if ($siteSearch) {
            $contextDao = Application::getContextDAO();
            if (!empty($searchFilters['searchPress'])) {
                $context = $contextDao->getById($searchFilters['searchPress']);
            } elseif (array_key_exists('pressTitle', $request->getUserVars())) {
                $contexts = $contextDao->getAll(true);
                while ($context = $contexts->next()) {
                    if (in_array(
                        $request->getUserVar('pressTitle'),
                        (array) $context->getName(null)
                    )) {
                        break;
                    }
                }
            }
        }
        $searchFilters['searchPress'] = $context;
        $searchFilters['siteSearch'] = $siteSearch;

        return $searchFilters;
    }

    /**
     * Load the keywords array from a given search filter.
     *
     * @param array $searchFilters Search filters as returned from
     *  MonographSearch::getSearchFilters()
     *
     * @return array Keyword array as required by SubmissionSearch::retrieveResults()
     */
    public function getKeywordsFromSearchFilters($searchFilters)
    {
        $indexFieldMap = $this->getIndexFieldMap();
        $indexFieldMap[SubmissionSearch::SUBMISSION_SEARCH_INDEX_TERMS] = 'indexTerms';
        $keywords = [];
        if (isset($searchFilters['query'])) {
            $keywords[''] = $searchFilters['query'];
        }
        foreach ($indexFieldMap as $bitmap => $searchField) {
            if (isset($searchFilters[$searchField]) && !empty($searchFilters[$searchField])) {
                $keywords[$bitmap] = $searchFilters[$searchField];
            }
        }
        return $keywords;
    }

    /**
     * @copydoc SubmissionSearch::formatResults()
     *
     * @param null|mixed $user
     */
    public function formatResults($results, $user = null)
    {
        $contextDao = Application::getContextDAO();

        $publishedSubmissionCache = [];
        $monographCache = [];
        $contextCache = [];
        $seriesCache = [];

        $returner = [];
        foreach ($results as $monographId) {
            // Get the monograph, storing in cache if necessary.
            if (!isset($monographCache[$monographId])) {
                $submission = Repo::submission()->get($monographId);
                $monographCache[$monographId] = $submission;
                $publishedSubmissionCache[$monographId] = $submission;
            }
            unset($monograph, $publishedSubmission);
            $monograph = $monographCache[$monographId];
            $publishedSubmission = $publishedSubmissionCache[$monographId];

            if ($monograph) {
                $seriesId = $monograph->getSeriesId();
                if (!isset($seriesCache[$seriesId])) {
                    $seriesCache[$seriesId] = $seriesId ? Repo::section()->get($seriesId) : null;
                }

                // Get the context, storing in cache if necessary.
                $contextId = $monograph->getData('contextId');
                if (!isset($contextCache[$contextId])) {
                    $contextCache[$contextId] = $contextDao->getById($contextId);
                }

                // Store the retrieved objects in the result array.
                $returner[] = [
                    'press' => $contextCache[$contextId],
                    'monograph' => $monograph,
                    'publishedSubmission' => $publishedSubmission,
                    'seriesArrangement' => $seriesCache[$seriesId]
                ];
            }
        }
        return $returner;
    }

    public function getIndexFieldMap()
    {
        return [
            SubmissionSearch::SUBMISSION_SEARCH_AUTHOR => 'authors',
            SubmissionSearch::SUBMISSION_SEARCH_TITLE => 'title',
            SubmissionSearch::SUBMISSION_SEARCH_ABSTRACT => 'abstract',
            SubmissionSearch::SUBMISSION_SEARCH_GALLEY_FILE => 'galleyFullText',
            SubmissionSearch::SUBMISSION_SEARCH_SUPPLEMENTARY_FILE => 'suppFiles',
            SubmissionSearch::SUBMISSION_SEARCH_DISCIPLINE => 'discipline',
            SubmissionSearch::SUBMISSION_SEARCH_SUBJECT => 'subject',
            SubmissionSearch::SUBMISSION_SEARCH_KEYWORD => 'keyword',
            SubmissionSearch::SUBMISSION_SEARCH_TYPE => 'type',
            SubmissionSearch::SUBMISSION_SEARCH_COVERAGE => 'coverage'
        ];
    }

    /**
     * See SubmissionSearch::getResultSetOrderingOptions()
     *
     * @hook SubmissionSearch::getResultSetOrderingOptions [[$context, &$resultSetOrderingOptions]]
     */
    public function getResultSetOrderingOptions($request)
    {
        $resultSetOrderingOptions = [
            'score' => __('search.results.orderBy.relevance'),
            'authors' => __('search.results.orderBy.author'),
            'publicationDate' => __('search.results.orderBy.date'),
            'title' => __('search.results.orderBy.monograph')
        ];

        // Only show the "popularity" options if we have a default metric.
        $resultSetOrderingOptions['popularityAll'] = __('search.results.orderBy.popularityAll');
        $resultSetOrderingOptions['popularityMonth'] = __('search.results.orderBy.popularityMonth');

        // Only show the "press title" option if we have several presses.
        $context = $request->getContext();
        if (!is_a($context, 'Context')) {
            $resultSetOrderingOptions['pressTitle'] = __('search.results.orderBy.press');
        }

        // Let plugins mangle the search ordering options.
        Hook::call(
            'SubmissionSearch::getResultSetOrderingOptions',
            [$context, &$resultSetOrderingOptions]
        );

        return $resultSetOrderingOptions;
    }

    /**
     * See SubmissionSearch::getDefaultOrderDir()
     */
    public function getDefaultOrderDir($orderBy)
    {
        $orderDir = 'asc';
        if (in_array($orderBy, ['score', 'publicationDate', 'popularityAll', 'popularityMonth'])) {
            $orderDir = 'desc';
        }
        return $orderDir;
    }

    /**
     * Return the search DAO
     *
     * @return MonographSearchDAO
     */
    protected function getSearchDao()
    {
        /** @var MonographSearchDAO */
        $dao = DAORegistry::getDAO('MonographSearchDAO');
        return $dao;
    }
}
