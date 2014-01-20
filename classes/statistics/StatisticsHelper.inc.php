<?php

/**
* @file classes/statistics/StatisticsHelper.inc.php
*
* Copyright (c) 2013 Simon Fraser University Library
* Copyright (c) 2003-2013 John Willinsky
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

	function StatisticsHelper() {
		parent::PKPStatisticsHelper();
	}

	/**
	 * @see PKPStatisticsHelper::getColumnsArray()
	 */
	protected function getReportColumnsArray() {
		$columns = parent::getReportColumnsArray();
		$columns[STATISTICS_DIMENSION_SERIES_ID] = __('series.series');

		return $columns;
	}

	/**
	 * @see PKPStatisticsHelper::getReportObjectTypesArray()
	 */
	function getReportObjectTypesArray() {
		$objectTypes = parent::getReportObjectTypesArray();
		$objectTypes = $objectTypes + array(
				ASSOC_TYPE_PRESS => __('context.context'),
				ASSOC_TYPE_SERIES => __('series.series'),
				ASSOC_TYPE_MONOGRAPH => __('submission.workType.authoredWork'),
				ASSOC_TYPE_PUBLICATION_FORMAT => __('grid.catalogEntry.publicationFormatType')
		);

		return $objectTypes;
	}

}

?>
