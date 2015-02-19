<?php

/**
 * @file classes/press/SeriesEditorsDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorsDAO
 * @ingroup press
 *
 * @brief Class for DAO relating series to editors.
 */

import('lib.pkp.classes.context.SubEditorsDAO');

class SeriesEditorsDAO extends SubEditorsDAO {
	/**
	 * Constructor
	 */
	function SeriesEditorsDAO() {
		parent::SubEditorsDAO();
	}

	/**
	 * Retrieve a list of all series editors assigned to the specified series.
	 * @param $seriesId int
	 * @param $pressId int
	 * @return array matching Users
	 */
	function getBySeriesId($seriesId, $pressId) {
		return parent::getBySectionId($seriesId, $pressId);
	}

	/**
	 * Retrieve a list of all series editors not assigned to the specified series.
	 * @param $pressId int
	 * @param $seriesId int
	 * @return array matching Users
	 */
	function getEditorsNotInSeries($pressId, $seriesId) {
		return parent::getEditorsNotInSection($pressId, $seriesId);
	}

	/**
	 * Delete all series editors for a specified series in a press.
	 * @param $seriesId int
	 * @param $pressId int
	 */
	function deleteBySeriesId($seriesId, $pressId = null) {
		return parent::deleteBySectionId($seriesId, $pressId);
	}
}

?>
