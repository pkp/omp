<?php

/**
 * @file classes/search/MonographSearch.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographSearch
 * @ingroup search
 * @see MonographSearchDAO
 *
 * @brief Class for retrieving monograph search results.
 *
 */

import('classes.search.MonographSearchIndex');
import('lib.pkp.classes.search.SubmissionSearch');

class MonographSearch extends SubmissionSearch {
	/**
	 * Constructor
	 */
	function MonographSearch() {
		parent::SubmissionSearch();
	}

	/**
	 * See implementation of retrieveResults for a description of this
	 * function.
	 * Note that this function is also called externally to fetch
	 * results for the title index, and possibly elsewhere.
	 */
	static function formatResults($results) {
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
	 * Return the search DAO
	 * @return DAO
	 */
	protected function getSearchDao() {
		return DAORegistry::getDAO('MonographSearchDAO');
	}
}

?>
