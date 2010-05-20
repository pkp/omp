<?php

/**
 * @defgroup monograph_reviewRound
 */

/**
 * @file ReviewRound.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
	 * get submission id
	 * @return int
	 */
	function getSubmissionId() {
		return $this->getData('submissionId');
	}

	/**
	 * set submission id
	 * @param $submissionId int
	 */
	function setSubmissionId($submissionId) {
		return $this->setData('submissionId', $submissionId);
	}	

	/**
	 * Get review type (internal or external review).
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
