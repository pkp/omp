<?php

/**
 * @file classes/search/MonographSearch.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographSearch
 * @ingroup search
 * @see MonographSearchDAO
 *
 * @brief Class for retrieving monograph search results.
 *
 */

import('lib.pkp.classes.search.SubmissionSearch');

class MonographSearch extends SubmissionSearch {
	/**
	 * See SubmissionSearch::getSparseArray()
	 */
	function getSparseArray($unorderedResults, $orderBy, $orderDir, $exclude) {
		// Calculate a well-ordered (unique) score.
		$resultCount = count($unorderedResults);
		$i = 0;
		foreach ($unorderedResults as $submissionId => &$data) {
			// Reference is necessary to permit modification
			$data['score'] = ($resultCount * $data['count']) + $i++;
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
		$orderedResults = array();
		$authorDao = DAORegistry::getDAO('AuthorDAO'); /* @var $authorDao AuthorDAO */
		$submissionDao = Application::getSubmissionDAO();
		$contextDao = Application::getContextDAO();
		$contextTitles = array();
		if ($orderBy == 'popularityAll' || $orderBy == 'popularityMonth') {
			$application = Application::getApplication();
			$metricType = $application->getDefaultMetricType();
			if (is_null($metricType)) {
				// If no default metric has been found then sort by score...
				$orderBy = 'score';
			} else {
				// Retrieve a metrics report for all submissions.
				$column = STATISTICS_DIMENSION_SUBMISSION_ID;
				$filter = array(
					STATISTICS_DIMENSION_ASSOC_TYPE => array(ASSOC_TYPE_GALLEY, ASSOC_TYPE_SUBMISSION),
					STATISTICS_DIMENSION_SUBMISSION_ID => array(array_keys($unorderedResults))
				);
				if ($orderBy == 'popularityMonth') {
					$oneMonthAgo = date('Ymd', strtotime('-1 month'));
					$today = date('Ymd');
					$filter[STATISTICS_DIMENSION_DAY] = array('from' => $oneMonthAgo, 'to' => $today);
				}
				$rawReport = $application->getMetrics($metricType, $column, $filter);
				foreach ($rawReport as $row) {
					$unorderedResults[$row['submission_id']]['metric'] = (int)$row['metric'];
				}
			}
		}

		$i=0; // Used to prevent ties from clobbering each other
		foreach ($unorderedResults as $submissionId => $data) {
			// Exclude unwanted IDs.
			if (in_array($submissionId, $exclude)) continue;

			switch ($orderBy) {
				case 'authors':
					$authors = $authorDao->getBySubmissionId($submissionId);
					$authorNames = array();
					foreach ($authors as $author) { /* @var $author Author */
						$authorNames[] = $author->getFullName(false, true);
					}
					$orderKey = implode('; ', $authorNames);
					unset($authors, $authorNames);
					break;

				case 'title':
					$submission = $submissionDao->getById($submissionId);
					$orderKey = $submission->getLocalizedTitle(null, false);
					break;

				case 'pressTitle':
					if (!isset($contextTitles[$data['press_id']])) {
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
					$orderKey = (isset($data['metric']) ? $data['metric'] : 0);
					break;

				default: // order by score.
					$orderKey = $data['score'];
			}
			if (!isset($orderedResults[$orderKey])) {
				$orderedResults[$orderKey] = array();
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
		$finalOrder = array();
		foreach($orderedResults as $orderKey => $submissionIds) {
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
	 * @param $request Request
	 * @return array All search filters (empty and active)
	 */
	function getSearchFilters($request) {
		$searchFilters = array(
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
		);

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
		$siteSearch = !((boolean)$context);
		if ($siteSearch) {
			$contextDao = Application::getContextDAO();
			if (!empty($searchFilters['searchPress'])) {
				$context = $contextDao->getById($searchFilters['searchPress']);
			} elseif (array_key_exists('pressTitle', $request->getUserVars())) {
				$contexts = $contextDao->getAll(true);
				while ($context = $contexts->next()) {
					if (in_array(
						$request->getUserVar('pressTitle'),
						(array) $context->getTitle(null)
					)) break;
				}
			}
		}
		$searchFilters['searchPress'] = $context;
		$searchFilters['siteSearch'] = $siteSearch;

		return $searchFilters;
	}

	/**
	 * Load the keywords array from a given search filter.
	 * @param $searchFilters array Search filters as returned from
	 *  MonographSearch::getSearchFilters()
	 * @return array Keyword array as required by SubmissionSearch::retrieveResults()
	 */
	function getKeywordsFromSearchFilters($searchFilters) {
		$indexFieldMap = $this->getIndexFieldMap();
		$indexFieldMap[SUBMISSION_SEARCH_INDEX_TERMS] = 'indexTerms';
		$keywords = array();
		if (isset($searchFilters['query'])) {
			$keywords[null] = $searchFilters['query'];
		}
		foreach($indexFieldMap as $bitmap => $searchField) {
			if (isset($searchFilters[$searchField]) && !empty($searchFilters[$searchField])) {
				$keywords[$bitmap] = $searchFilters[$searchField];
			}
		}
		return $keywords;
	}

	/**
	 * @copydoc SubmissionSearch::formatResults()
	 */
	function formatResults($results, $user = null) {
		$contextDao = Application::getContextDAO();
		$submissionDao = Application::getSubmissionDAO();
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');

		$publishedSubmissionCache = array();
		$monographCache = array();
		$contextCache = array();
		$seriesCache = array();

		$returner = array();
		foreach ($results as $monographId) {
			// Get the monograph, storing in cache if necessary.
			if (!isset($monographCache[$monographId])) {
				$monographCache[$monographId] = $submissionDao->getById($monographId);
				$publishedSubmissionCache[$monographId] = $publishedSubmissionDao->getBySubmissionId($monographId);
			}
			unset($monograph, $publishedSubmission);
			$monograph = $monographCache[$monographId];
			$publishedSubmission = $publishedSubmissionCache[$monographId];

			if ($monograph) {
				$seriesId = $monograph->getSeriesId();
				if (!isset($seriesCache[$seriesId])) {
					$seriesCache[$seriesId] = $seriesDao->getById($seriesId);
				}

				// Get the context, storing in cache if necessary.
				$contextId = $monograph->getPressId();
				if (!isset($contextCache[$contextId])) {
					$contextCache[$contextId] = $contextDao->getById($contextId);
				}

				// Store the retrieved objects in the result array.
				$returner[] = array(
					'press' => $contextCache[$contextId],
					'monograph' => $monograph,
					'publishedSubmission' => $publishedSubmission,
					'seriesArrangment' => $seriesCache[$seriesId]
				);
			}
		}
		return $returner;
	}

	/**
	 * Identify similarity terms for a given submission.
	 * @param $submissionId integer
	 * @return null|array An array of string keywords or null
	 * if some kind of error occurred.
	 */
	function getSimilarityTerms($submissionId) {
		// Check whether a search plugin provides terms for a similarity search.
		$searchTerms = array();
		$result = HookRegistry::call('MonographSearch::getSimilarityTerms', array($submissionId, &$searchTerms));

		// If no plugin implements the hook then use the subject keywords
		// of the submission for a similarity search.
		if ($result === false) {
			// Retrieve the submission.
			$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO'); /* @var $publishedSubmissionDao PublishedSubmissionDAO */
			$monograph = $publishedSubmissionDao->getBySubmissionId($submissionId);
			if (is_a($monograph, 'PublishedSubmission')) {
				// Retrieve keywords (if any).
				$searchTerms = $monograph->getLocalizedSubject();
				// Tokenize keywords.
				$searchTerms = trim(preg_replace('/\s+/', ' ', strtr($searchTerms, ',;', ' ')));
				if (!empty($searchTerms)) $searchTerms = explode(' ', $searchTerms);
			}
		}

		return $searchTerms;
	}

	function getIndexFieldMap() {
		return array(
			SUBMISSION_SEARCH_AUTHOR => 'authors',
			SUBMISSION_SEARCH_TITLE => 'title',
			SUBMISSION_SEARCH_ABSTRACT => 'abstract',
			SUBMISSION_SEARCH_GALLEY_FILE => 'galleyFullText',
			SUBMISSION_SEARCH_SUPPLEMENTARY_FILE => 'suppFiles',
			SUBMISSION_SEARCH_DISCIPLINE => 'discipline',
			SUBMISSION_SEARCH_SUBJECT => 'subject',
			SUBMISSION_SEARCH_TYPE => 'type',
			SUBMISSION_SEARCH_COVERAGE => 'coverage'
		);
	}

	/**
	 * See SubmissionSearch::getResultSetOrderingOptions()
	 */
	function getResultSetOrderingOptions($request) {
		$resultSetOrderingOptions = array(
			'score' => __('search.results.orderBy.relevance'),
			'authors' => __('search.results.orderBy.author'),
			'publicationDate' => __('search.results.orderBy.date'),
			'title' => __('search.results.orderBy.monograph')
		);

		// Only show the "popularity" options if we have a default metric.
		$application = Application::getApplication();
		$metricType = $application->getDefaultMetricType();
		if (!is_null($metricType)) {
			$resultSetOrderingOptions['popularityAll'] = __('search.results.orderBy.popularityAll');
			$resultSetOrderingOptions['popularityMonth'] = __('search.results.orderBy.popularityMonth');
		}

		// Only show the "press title" option if we have several presses.
		$context = $request->getContext();
		if (!is_a($context, 'Context')) {
			$resultSetOrderingOptions['pressTitle'] = __('search.results.orderBy.press');
		}

		// Let plugins mangle the search ordering options.
		HookRegistry::call(
			'SubmissionSearch::getResultSetOrderingOptions',
			array($context, &$resultSetOrderingOptions)
		);

		return $resultSetOrderingOptions;
	}

	/**
	 * See SubmissionSearch::getDefaultOrderDir()
	 */
	function getDefaultOrderDir($orderBy) {
		$orderDir = 'asc';
		if (in_array($orderBy, array('score', 'publicationDate', 'popularityAll', 'popularityMonth'))) {
			$orderDir = 'desc';
		}
		return $orderDir;
	}

	/**
	 * Return the search DAO
	 * @return DAO
	 */
	protected function getSearchDao() {
		return DAORegistry::getDAO('MonographSearchDAO');
	}
}


