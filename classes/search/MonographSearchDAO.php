<?php

/**
 * @file classes/search/MonographSearchDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographSearchDAO
 *
 * @ingroup search
 *
 * @see MonographSearch
 *
 * @brief DAO class for monograph search index.
 */

namespace APP\search;

use PKP\search\SubmissionSearchDAO;
use PKP\submission\PKPSubmission;

class MonographSearchDAO extends SubmissionSearchDAO
{
    /**
     * Retrieve the top results for a phrases with the given
     * limit (default 500 results).
     *
     * @param null|mixed $publishedFrom
     * @param null|mixed $publishedTo
     * @param null|mixed $type
     *
     * @return array of results (associative arrays)
     */
    public function getPhraseResults($press, $phrase, $publishedFrom = null, $publishedTo = null, $type = null, $limit = 500, $cacheHours = 24)
    {
        if (empty($phrase)) {
            return [];
        }

        $sqlFrom = '';
        $sqlWhere = '';
        $params = [];

        for ($i = 0, $count = count($phrase); $i < $count; $i++) {
            if (!empty($sqlFrom)) {
                $sqlFrom .= ', ';
                $sqlWhere .= ' AND ';
            }
            $sqlFrom .= 'submission_search_object_keywords o' . $i . ' NATURAL JOIN submission_search_keyword_list k' . $i;
            if (strstr($phrase[$i], '%') === false) {
                $sqlWhere .= 'k' . $i . '.keyword_text = ?';
            } else {
                $sqlWhere .= 'k' . $i . '.keyword_text LIKE ?';
            }
            if ($i > 0) {
                $sqlWhere .= ' AND o0.object_id = o' . $i . '.object_id AND o0.pos+' . $i . ' = o' . $i . '.pos';
            }

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

        $params[] = PKPSubmission::STATUS_PUBLISHED;

        $result = $this->retrieve(
            $sql = 'SELECT
				o.submission_id,
				s.context_id as press_id,
				p.date_published as s_pub,
				COUNT(*) AS count
			FROM
				submissions s,
				publications p,
				submission_search_objects o NATURAL JOIN ' . $sqlFrom . '
			WHERE o.submission_id = s.submission_id
			AND s.current_publication_id = p.publication_id
			AND ' . $sqlWhere . '
			AND s.status = ?
			GROUP BY o.submission_id, s.context_id, p.date_published
			ORDER BY count DESC
			LIMIT ' . $limit,
            $params,
            3600 * $cacheHours // Cache for 24 hours
        );

        $returner = [];
        foreach ($result as $row) {
            $returner[$row->submission_id] = [
                'count' => $row->count,
                'press_id' => $row->press_id,
                'publicationDate' => $this->datetimeFromDB($row->s_pub)
            ];
        }
        return $returner;
    }
}
