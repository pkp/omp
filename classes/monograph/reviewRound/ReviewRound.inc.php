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
	 * get assoc id
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('assocId');
	}

	/**
	 * set assoc id
	 * @param $assocId int
	 */
	function setMonographId($assocId) {
		return $this->setData('assocId', $assocId);
	}	

	/**
	 * Get associated type.
	 * @return int
	 */
	function getRound() {
		return $this->getData('assocType');
	}

	/**
	 * Set associated type.
	 * @param $assocType int
	 */
	function setRound($assocType) {
		return $this->setData('assocType', $assocType);
	}

	/**
	 * Get date notified.
	 * @return string
	 */
	function getReviewRevision() {
		return $this->getData('dateNotified');
	}

	/**
	 * Set date notified.
	 * @param $dateNotified string
	 */
	function setReviewRevision($dateNotified) {
		return $this->setData('dateNotified', $dateNotified);
	}

	/**
	 * Get date underway.
	 * @return string
	 */
	function getReviewType() {
		return $this->getData('dateUnderway');
	}

	/**
	 * Set date underway.
	 * @param $dateUnderway string
	 */
	function setReviewType($dateUnderway) {
		return $this->setData('dateUnderway', $dateUnderway);
	}
}

?>
