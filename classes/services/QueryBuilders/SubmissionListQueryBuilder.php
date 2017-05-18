<?php

/**
 * @file classes/services/QueryBuilders/SubmissionListQueryBuilder.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionListQueryBuilder
 * @ingroup query_builders
 *
 * @brief Submission list Query builder
 */

namespace App\Services\QueryBuilders;

use Illuminate\Database\Capsule\Manager as Capsule;

class SubmissionListQueryBuilder extends PKPSubmissionListQueryBuilder {

	/** @var int|array Category ID(s) */
	protected $categoryIds = null;

	/** @var int|array Series ID(s) */
	protected $seriesIds = null;

	/**
	 * Set category filter
	 *
	 * @param int|array $categoryIds
	 *
	 * @return \App\Services\QueryBuilders\SubmissionListQueryBuilder
	 */
	public function filterByCategories($categoryIds) {
		if (!is_null($categoryIds) && !is_array($categoryIds)) {
			$categoryIds = array($categoryIds);
		}
		$this->categoryIds = $categoryIds;
		return $this;
	}

	/**
	 * Set series filter
	 *
	 * @param int|array $seriesIds
	 *
	 * @return \App\Services\QueryBuilders\SubmissionListQueryBuilder
	 */
	public function filterBySeries($seriesIds) {
		if (!is_null($seriesIds) && !is_array($seriesIds)) {
			$seriesIds = array($seriesIds);
		}
		$this->seriesIds = $seriesIds;
		return $this;
	}
	/**
	 * Execute additional actions for app-specific query objects
	 *
	 * @param object Query object
	 * @return object Query object
	 */
	public function appGet($q) {

		if (!empty($this->seriesIds)) {
			$q->whereIn('s.series_id', $this->seriesIds);
		}

		if (!empty($this->categoryIds)) {
			$q->leftJoin('submission_categories as sc','s.submission_id','=','sc.submission_id')
				->whereIn('sc.category_id', $this->categoryIds);
		}

		return $q;
	}
}
