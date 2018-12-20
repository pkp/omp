<?php

/**
 * @file classes/search/MonographSearchDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographSearchDAO
 * @ingroup search
 * @see MonographSearch
 *
 * @brief DAO class for monograph search index.
 */

import('classes.search.MonographSearch');
import('lib.pkp.classes.search.SubmissionSearchDAO');

class MonographSearchDAO extends SubmissionSearchDAO {
	/**
	 * Retrieve the top results for a phrases with the given
	 * limit (default 500 results).
	 * @param $keywordId int
	 * @return array of results (associative arrays)
	 */
	function getPhraseResults($press, $phrase, $publishedFrom = null, $publishedTo = null, $type = null, $limit = 500, $cacheHours = 24) {
		import('lib.pkp.classes.db.DBRowIterator');
		if (empty($phrase)) {
			$results = false;
			return new DBRowIterator($results);
		}

		$sqlFrom = '';
		$sqlWhere = '';
		$params = array();

		for ($i = 0, $count = count($phrase); $i < $count; $i++) {
			if (!empty($sqlFrom)) {
				$sqlFrom .= ', ';
				$sqlWhere .= ' AND ';
			}
			$sqlFrom .= 'submission_search_object_keywords o'.$i.' NATURAL JOIN submission_search_keyword_list k'.$i;
			if (strstr($phrase[$i], '%') === false) $sqlWhere .= 'k'.$i.'.keyword_text = ?';
			else $sqlWhere .= 'k'.$i.'.keyword_text LIKE ?';
			if ($i > 0) $sqlWhere .= ' AND o0.object_id = o'.$i.'.object_id AND o0.pos+'.$i.' = o'.$i.'.pos';

			$params[] = $phrase[$i];
		}

		if (!empty($type)) {
			$sqlWhere .= ' AND (o.type & ?) != 0';
			$params[] = $type;
		}

		if (!empty($press)) {
			$sqlWhere .= ' AND s.context_id = ?';
			$params[] = $press->getId();
		}

		$result = $this->retrieveCached(
			$sql = 'SELECT
				o.submission_id,
				s.context_id as press_id,
				ps.date_published as s_pub,
				COUNT(*) AS count
			FROM
				submissions s,
				published_submissions ps,
				submission_search_objects o NATURAL JOIN ' . $sqlFrom . '
			WHERE
				s.submission_id = ps.submission_id AND o.submission_id = s.submission_id AND ' . $sqlWhere . '
			GROUP BY o.submission_id, s.context_id, ps.date_published
			ORDER BY count DESC
			LIMIT ' . $limit,
			$params,
			3600 * $cacheHours // Cache for 24 hours
		);

		$returner = array();
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$returner[$row['submission_id']] = array(
				'count' => $row['count'],
				'press_id' => $row['press_id'],
				'publicationDate' => $this->datetimeFromDB($row['s_pub'])
			);
			$result->MoveNext();
		}
		$result->Close();

		return $returner;
	}
}


