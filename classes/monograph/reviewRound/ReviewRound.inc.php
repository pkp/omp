<?php

/**
 * @defgroup monograph_reviewRound
 */

/**
 * @file ReviewRound.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRound
 * @ingroup monograph_reviewRound
 * @see ReviewRoundDAO
 *
 * @brief Basic class describing a review round.
 */

//$Id$

class ReviewRound extends DataObject {
	//
	// Get/set methods
	//

	/**
	 * get monograph id
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * set monograph id
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}	

	/**
	 * Get date review type.
	 * @return int
	 */
	function getReviewType() {
		return $this->getData('reviewType');
	}

	/**
	 * Set review Type
	 * @param $reviewType int
	 */
	function setReviewType($reviewType) {
		return $this->setData('reviewType', $reviewType);
	}
	
	/**
	 * Get review round
	 * @return int
	 */
	function getRound() {
		return $this->getData('round');
	}

	/**
	 * Set review round
	 * @param $assocType int
	 */
	function setRound($round) {
		return $this->setData('round', $round);
	}

	/**
	 * Get review revision
	 * @return int
	 */
	function getReviewRevision() {
		return $this->getData('reviewRevision');
	}

	/**
	 * Set review reviesion
	 * @param $reviewRevision int
	 */
	function setReviewRevision($reviewRevision) {
		return $this->setData('reviewRevision', $reviewRevision);
	}
}

?>
