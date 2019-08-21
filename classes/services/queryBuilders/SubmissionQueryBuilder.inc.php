<?php

/**
 * @file classes/services/QueryBuilders/SubmissionQueryBuilder.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionQueryBuilder
 * @ingroup query_builders
 *
 * @brief Submission list Query builder
 */

namespace APP\Services\QueryBuilders;

use Illuminate\Database\Capsule\Manager as Capsule;

class SubmissionQueryBuilder extends \PKP\Services\QueryBuilders\PKPSubmissionQueryBuilder {

	/** @var int|array Series ID(s) */
	protected $seriesIds = null;

	/** @var bool Order featured items first */
	protected $orderByFeaturedSeq = null;

	/**
	 * Set series filter
	 *
	 * @param int|array $seriesIds
	 *
	 * @return \App\Services\QueryBuilders\SubmissionQueryBuilder
	 */
	public function filterBySeries($seriesIds) {
		if (!is_null($seriesIds) && !is_array($seriesIds)) {
			$seriesIds = array($seriesIds);
		}
		$this->seriesIds = $seriesIds;
		return $this;
	}

	/**
	 * Implement app-specific ordering options for catalog
	 *
	 * Publication date, Title or Series position, with featured items first
	 *
	 * @param string $column
	 * @param string $direction
	 *
	 * @return \App\Services\QueryBuilders\SubmissionQueryBuilder
	 */
	public function orderBy($column, $direction = 'DESC') {
		// Bring in orderby constants
		import('classes.submission.SubmissionDAO');
		switch ($column) {
			case ORDERBY_SERIES_POSITION:
				$this->orderColumn = 's.series_position';
				break;
			default:
				return parent::orderBy($column, $direction);
		}
		$this->orderDirection = $direction;

		return $this;
	}

	/**
	 * Order featured items first
	 *
	 * @return \App\Services\QueryBuilders\SubmissionQueryBuilder
	 */
	public function orderByFeatured() {
		$this->orderByFeaturedSeq = true;
	}

	/**
	 * Execute additional actions for app-specific query objects
	 *
	 * @param object Query object
	 * @param SubmissionQueryBuilder Query object
	 * @return object Query object
	 */
	public function appGet($q) {

		if (!empty($this->seriesIds)) {
			$q->leftJoin('publications as publication_s', 's.current_publication_id', '=', 'publication_s.publication_id');
			$q->whereIn('publication_s.series_id', $this->seriesIds);
		}

		if (!empty($this->orderByFeaturedSeq)) {
			if (!empty($this->seriesIds)) {
				$assocType = ASSOC_TYPE_SERIES;
				$assocIds = $this->seriesIds;
			} elseif (!empty($this->categoryIds)) {
				$assocType = ASSOC_TYPE_CATEGORY;
				$assocIds = $this->categoryIds;
			} else {
				$assocType = ASSOC_TYPE_PRESS;
				$assocIds = array(1); // OMP only supports a single press
			}
			$q->leftJoin('features as sf', function($join) use ($assocType, $assocIds) {
				$join->on('s.submission_id', '=', 'sf.submission_id')
					->on('sf.assoc_type', '=', Capsule::raw($assocType));
				foreach ($assocIds as $assocId) {
					$join->on('sf.assoc_id', '=', Capsule::raw(intval($assocId)));
				}
			});

			// Featured sorting should be the first sort parameter. We sort by
			// the seq parameter, with null values last
			$q->groupBy(Capsule::raw('sf.seq'));
			$this->columns[] = 'sf.seq';
			$this->columns[] = Capsule::raw('case when sf.seq is null then 1 else 0 end');
			array_unshift(
				$q->orders,
				array('type' => 'raw', 'sql' => 'case when sf.seq is null then 1 else 0 end'),
				array('column' => 'sf.seq', 'direction' => 'ASC')
			);
		}

		return $q;
	}
}
