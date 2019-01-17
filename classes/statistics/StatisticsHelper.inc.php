<?php

/**
* @file classes/statistics/StatisticsHelper.inc.php
*
* Copyright (c) 2013-2019 Simon Fraser University
* Copyright (c) 2003-2019 John Willinsky
* Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
*
* @class StatisticsHelper
* @ingroup statistics
*
* @brief Statistics helper class.
*
*/

import('lib.pkp.classes.statistics.PKPStatisticsHelper');

// Give an OMP name to the section dimension.
define('STATISTICS_DIMENSION_SERIES_ID', STATISTICS_DIMENSION_PKP_SECTION_ID);

class StatisticsHelper extends PKPStatisticsHelper {

	function __construct() {
		parent::__construct();
	}

	/**
	 * @see PKPStatisticsHelper::getAppColumnTitle()
	 */
	protected function getAppColumnTitle($column) {
		switch ($column) {
			case STATISTICS_DIMENSION_SUBMISSION_ID:
				return __('submission.monograph');
			case STATISTICS_DIMENSION_SERIES_ID:
				return __('series.series');
			case STATISTICS_DIMENSION_CONTEXT_ID:
				return __('context.context');
			default:
				assert(false);
		}
	}

	/**
	 * @see PKPStatisticsHelper::getReportObjectTypesArray()
	 */
	protected function getReportObjectTypesArray() {
		$objectTypes = parent::getReportObjectTypesArray();
		$objectTypes = $objectTypes + array(
				ASSOC_TYPE_PRESS => __('context.context'),
				ASSOC_TYPE_SERIES => __('series.series'),
				ASSOC_TYPE_MONOGRAPH => __('submission.monograph'),
				ASSOC_TYPE_PUBLICATION_FORMAT => __('grid.catalogEntry.publicationFormatType')
		);

		return $objectTypes;
	}

}


