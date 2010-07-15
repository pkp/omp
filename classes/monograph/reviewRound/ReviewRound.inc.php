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

define('REVIEW_TYPE_INTERNAL', 1);
define('REVIEW_TYPE_EXTERNAL', 2);

define('REVIEW_ROUND_STATUS_REVISIONS_REQUESTED', 1);
define('REVIEW_ROUND_STATUS_RESUBMITTED', 2);
define('REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL', 3);
define('REVIEW_ROUND_STATUS_ACCEPTED', 4);
define('REVIEW_ROUND_STATUS_DECLINED', 5);
define('REVIEW_ROUND_STATUS_PENDING_REVIEWERS', 6);
define('REVIEW_ROUND_STATUS_PENDING_REVIEWS', 7);

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

	/**
	 * Get current round status
	 * @return int
	 */
	function getStatus() {
		return $this->getData('status');
	}

	/**
	 * Set current round status
	 * @param $status int
	 */
	function setStatus($status) {
		return $this->setData('status', $status);
	}

	/**
	 * Get locale key associated with current status
	 * @return int
	 */
	function getStatusKey() {
		switch ($this->getStatus()) {
			case REVIEW_ROUND_STATUS_REVISIONS_REQUESTED:
				return 'editor.monograph.roundStatus.revisionsRequested';
				break;
			case REVIEW_ROUND_STATUS_RESUBMITTED:
				return 'editor.monograph.roundStatus.resubmitted';
				break;
			case REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL:
				return 'editor.monograph.roundStatus.sentToExternal';
				break;
			case REVIEW_ROUND_STATUS_ACCEPTED:
				return 'editor.monograph.roundStatus.accepted';
				break;
			case REVIEW_ROUND_STATUS_DECLINED:
				return 'editor.monograph.roundStatus.declined';
				break;
			case REVIEW_ROUND_STATUS_PENDING_REVIEWERS:
				return 'editor.monograph.roundStatus.pendingReviewers';
				break;
			case REVIEW_ROUND_STATUS_PENDING_REVIEWS:
				return 'editor.monograph.roundStatus.pendingReviews';
				break;
			default: return null;
		}

	}
}

?>
