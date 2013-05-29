<?php

/**
 * @file classes/search/MonographSearch.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographSearch
 * @ingroup search
 * @see MonographSearchDAO
 *
 * @brief Class for retrieving monograph search results.
 *
 * FIXME: NEAR; precedence w/o parents?; stemming; weighted counting
 */

// Search types
define('MONOGRAPH_SEARCH_AUTHOR',		0x00000001);
define('MONOGRAPH_SEARCH_TITLE',		0x00000002);
define('MONOGRAPH_SEARCH_ABSTRACT',		0x00000004);
define('MONOGRAPH_SEARCH_DISCIPLINE',		0x00000008);
define('MONOGRAPH_SEARCH_SUBJECT',		0x00000010);
define('MONOGRAPH_SEARCH_TYPE',			0x00000020);
define('MONOGRAPH_SEARCH_COVERAGE',		0x00000040);
define('MONOGRAPH_SEARCH_GALLEY_FILE',		0x00000080);
define('MONOGRAPH_SEARCH_SUPPLEMENTARY_FILE',	0x00000100);
define('MONOGRAPH_SEARCH_INDEX_TERMS',		0x00000078);

import('classes.search.MonographSearchIndex');

class MonographSearch {

	/**
	 * Parses a search query string.
	 * Supports +/-, AND/OR, parens
	 * @param $query
	 * @return array of the form ('+' => <required>, '' => <optional>, '-' => excluded)
	 */
	function parseQuery($query) {
		$count = preg_match_all('/(\+|\-|)("[^"]+"|\(|\)|[^\s\)]+)/', $query, $matches);
		$pos = 0;
		return MonographSearch::_parseQuery($matches[1], $matches[2], $pos, $count);
	}

	/**
	 * Query parsing helper routine.
	 * Returned structure is based on that used by the Search::QueryParser Perl module.
	 */
	function _parseQuery($signTokens, $tokens, &$pos, $total) {
		$return = array('+' => array(), '' => array(), '-' => array());
		$postBool = $preBool = '';

		$notOperator = String::strtolower(__('search.operator.not'));
		$andOperator = String::strtolower(__('search.operator.and'));
		$orOperator = String::strtolower(__('search.operator.or'));
		while ($pos < $total) {
			if (!empty($signTokens[$pos])) $sign = $signTokens[$pos];
			else if (empty($sign)) $sign = '+';
			$token = String::strtolower($tokens[$pos++]);
			switch ($token) {
				case $notOperator:
					$sign = '-';
					break;
				case ')':
					return $return;
				case '(':
					$token = MonographSearch::_parseQuery($signTokens, $tokens, $pos, $total);
				default:
					$postBool = '';
					if ($pos < $total) {
						$peek = String::strtolower($tokens[$pos]);
						if ($peek == $orOperator) {
							$postBool = 'or';
							$pos++;
						} else if ($peek == $andOperator) {
							$postBool = 'and';
							$pos++;
						}
					}
					$bool = empty($postBool) ? $preBool : $postBool;
					$preBool = $postBool;
					if ($bool == 'or') $sign = '';
					if (is_array($token)) $k = $token;
					else $k = MonographSearchIndex::filterKeywords($token, true);
					if (!empty($k)) $return[$sign][] = $k;
					$sign = '';
					break;
			}
		}
		return $return;
	}

	/**
	 * See implementation of retrieveResults for a description of this
	 * function.
	 */
	function _getMergedArray($press, $keywords, $publishedFrom, $publishedTo, &$resultCount) {
		$resultsPerKeyword = Config::getVar('search', 'results_per_keyword');
		$resultCacheHours = Config::getVar('search', 'result_cache_hours');
		if (!is_numeric($resultsPerKeyword)) $resultsPerKeyword = 100;
		if (!is_numeric($resultCacheHours)) $resultCacheHours = 24;

		$mergedKeywords = array('+' => array(), '' => array(), '-' => array());
		foreach ($keywords as $type => $keyword) {
			if (!empty($keyword['+']))
				$mergedKeywords['+'][] = array('type' => $type, '+' => $keyword['+'], '' => array(), '-' => array());
			if (!empty($keyword['']))
				$mergedKeywords[''][] = array('type' => $type, '+' => array(), '' => $keyword[''], '-' => array());
			if (!empty($keyword['-']))
				$mergedKeywords['-'][] = array('type' => $type, '+' => array(), '' => $keyword['-'], '-' => array());
		}
		$mergedResults = MonographSearch::_getMergedKeywordResults($press, $mergedKeywords, null, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);

		$resultCount = count($mergedResults);
		return $mergedResults;
	}

	/**
	 * Recursive helper for _getMergedArray.
	 */
	function _getMergedKeywordResults($press, $keyword, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours) {
		$mergedResults = null;

		if (isset($keyword['type'])) {
			$type = $keyword['type'];
		}

		foreach ($keyword['+'] as $phrase) {
			$results = MonographSearch::_getMergedPhraseResults($press, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);
			if ($mergedResults === null) {
				$mergedResults = $results;
			} else {
				foreach ($mergedResults as $monographId => $count) {
					if (isset($results[$monographId])) {
						$mergedResults[$monographId] += $results[$monographId];
					} else {
						unset($mergedResults[$monographId]);
					}
				}
			}
		}

		if ($mergedResults == null) {
			$mergedResults = array();
		}

		if (!empty($mergedResults) || empty($keyword['+'])) {
			foreach ($keyword[''] as $phrase) {
				$results = MonographSearch::_getMergedPhraseResults($press, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);
				foreach ($results as $monographId => $count) {
					if (isset($mergedResults[$monographId])) {
						$mergedResults[$monographId] += $count;
					} else if (empty($keyword['+'])) {
						$mergedResults[$monographId] = $count;
					}
				}
			}

			foreach ($keyword['-'] as $phrase) {
				$results = MonographSearch::_getMergedPhraseResults($press, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);
				foreach ($results as $monographId => $count) {
					if (isset($mergedResults[$monographId])) {
						unset($mergedResults[$monographId]);
					}
				}
			}
		}

		return $mergedResults;
	}

	/**
	 * Recursive helper for _getMergedArray.
	 */
	function _getMergedPhraseResults($press, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours) {
		if (isset($phrase['+'])) {
			return MonographSearch::_getMergedKeywordResults($press, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);
		}

		$mergedResults = array();
		$monographSearchDao = DAORegistry::getDAO('MonographSearchDAO');
		$results = $monographSearchDao->getPhraseResults(
			$press,
			$phrase,
			$publishedFrom,
			$publishedTo,
			$type,
			$resultsPerKeyword,
			$resultCacheHours
		);
		while (!$results->eof()) {
			$result = $results->next();
			$monographId = $result['submission_id'];
			if (!isset($mergedResults[$monographId])) {
				$mergedResults[$monographId] = $result['count'];
			} else {
				$mergedResults[$monographId] += $result['count'];
			}
		}
		return $mergedResults;
	}

	/**
	 * See implementation of retrieveResults for a description of this
	 * function.
	 */
	function _getSparseArray($mergedResults, $resultCount) {
		$results = array();
		$i = 0;
		foreach ($mergedResults as $monographId => $count) {
			$frequencyIndicator = ($resultCount * $count) + $i++;
			$results[$frequencyIndicator] = $monographId;
		}
		krsort($results);
		return $results;
	}

	/**
	 * See implementation of retrieveResults for a description of this
	 * function.
	 * Note that this function is also called externally to fetch
	 * results for the title index, and possibly elsewhere.
	 */
	function formatResults($results) {
		$pressDao = DAORegistry::getDAO('PressDAO');
		$monographDao = DAORegistry::getDAO('MonographDAO');
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');

		$publishedMonographCache = array();
		$monographCache = array();
		$pressCache = array();
		$seriesCache = array();

		$returner = array();
		foreach ($results as $monographId) {
			// Get the monograph, storing in cache if necessary.
			if (!isset($monographCache[$monographId])) {
				$monographCache[$monographId] = $monographDao->getById($monographId);
				$publishedMonographCache[$monographId] = $publishedMonographDao->getById($monographId);
			}
			unset($monograph, $publishedMonograph);
			$monograph = $monographCache[$monographId];
			$publishedMonograph = $publishedMonographCache[$monographId];

			if ($monograph) {
				$seriesId = $monograph->getSeriesId();
				if (!isset($seriesCache[$seriesId])) {
					$seriesCache[$seriesId] = $seriesDao->getById($seriesId);
				}

				// Get the press, storing in cache if necessary.
				$pressId = $monograph->getPressId();
				if (!isset($pressCache[$pressId])) {
					$pressCache[$pressId] = $pressDao->getById($pressId);
				}

				// Store the retrieved objects in the result array.
				$returner[] = array(
					'press' => $pressCache[$pressId],
					'monograph' => $monograph,
					'publishedMonograph' => $publishedMonograph,
					'seriesArrangment' => $seriesCache[$seriesId]
				);
			}
		}
		return $returner;
	}

	/**
	 * Return an array of search results matching the supplied
	 * keyword IDs in decreasing order of match quality.
	 * Keywords are supplied in an array of the following format:
	 * $keywords[MONOGRAPH_SEARCH_AUTHOR] = array('John', 'Doe');
	 * $keywords[MONOGRAPH_SEARCH_...] = array(...);
	 * $keywords[null] = array('Matches', 'All', 'Fields');
	 * @param $press object The press to search
	 * @param $keywords array List of keywords
	 * @param $publishedFrom object Search-from date
	 * @param $publishedTo object Search-to date
	 * @param $rangeInfo Information on the range of results to return
	 */
	function retrieveResults($press, $keywords, $publishedFrom = null, $publishedTo = null, $rangeInfo = null) {
		// Fetch all the results from all the keywords into one array
		// (mergedResults), where mergedResults[submission_id]
		// = sum of all the occurences for all keywords associated with
		// that monograph ID.
		// resultCount contains the sum of result counts for all keywords.
		$mergedResults = MonographSearch::_getMergedArray($press, $keywords, $publishedFrom, $publishedTo, $resultCount);

		// Convert mergedResults into an array (frequencyIndicator =>
		// $monographId).
		// The frequencyIndicator is a synthetically-generated number,
		// where higher is better, indicating the quality of the match.
		// It is generated here in such a manner that matches with
		// identical frequency do not collide.
		$results = MonographSearch::_getSparseArray($mergedResults, $resultCount);

		$totalResults = count($results);

		// Use only the results for the specified page, if specified.
		if ($rangeInfo && $rangeInfo->isValid()) {
			$results = array_slice(
				$results,
				$rangeInfo->getCount() * ($rangeInfo->getPage()-1),
				$rangeInfo->getCount()
			);
			$page = $rangeInfo->getPage();
			$itemsPerPage = $rangeInfo->getCount();
		} else {
			$page = 1;
			$itemsPerPage = max($totalResults, 1);
		}

		// Take the range of results and retrieve the Monograph, Press,
		// and associated objects.
		$results = MonographSearch::formatResults($results);

		// Return the appropriate iterator.
		import('lib.pkp.classes.core.VirtualArrayIterator');
		return new VirtualArrayIterator($results, $totalResults, $page, $itemsPerPage);
	}
}

?>
