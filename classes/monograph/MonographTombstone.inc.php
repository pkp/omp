<?php

/**
 * @file classes/monograph/MonographTombstone.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographTombstone
 * @ingroup monograph
 * @see MonographTombstoneDAO
 *
 * @brief Class for monograph tombstones.
 */

import('lib.pkp.classes.submission.SubmissionTombstone');

class MonographTombstone extends SubmissionTombstone {
	/**
	 * Constructor.
	 */
	function MonographTombstone() {
		parent::SubmissionTombstone();
	}

	/**
	 * get press id
	 * @return int
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * set press id
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * get series id
	 * @return int
	 */
	function getSeriesId() {
		return $this->getData('seriesId');
	}

	/**
	 * set series id
	 * @param $seriesId int
	 */
	function setSeriesId($seriesId) {
		return $this->setData('seriesId', $seriesId);
	}
}

?>