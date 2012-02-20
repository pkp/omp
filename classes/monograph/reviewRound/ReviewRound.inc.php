<?php

/**
 * @defgroup monograph_reviewRound
 */

/**
 * @file classes/monograph/reviewRound/ReviewRound.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRound
 * @ingroup monograph_reviewRound
 * @see ReviewRoundDAO
 *
 * @brief Basic class describing a review round.
 */

define('REVIEW_ROUND_STATUS_REVISIONS_REQUESTED', 1);
define('REVIEW_ROUND_STATUS_RESUBMITTED', 2);
define('REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL', 3);
define('REVIEW_ROUND_STATUS_ACCEPTED', 4);
define('REVIEW_ROUND_STATUS_DECLINED', 5);
define('REVIEW_ROUND_STATUS_PENDING_REVIEWERS', 6);
define('REVIEW_ROUND_STATUS_PENDING_REVIEWS', 7);
define('REVIEW_ROUND_STATUS_REVIEWS_READY', 8);
define('REVIEW_ROUND_STATUS_REVIEWS_COMPLETED', 9);

class ReviewRound extends DataObject {
	/**
	 * Constructor
	 */
	function ReviewRound() {
		parent::DataObject();
	}

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
	 * Get review stage id (internal or external review).
	 * @return int
	 */
	function getStageId() {
		return $this->getData('stageId');
	}

	/**
	 * Set review stage id
	 * @param $stageId int
	 */
	function setStageId($stageId) {
		return $this->setData('stageId', $stageId);
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
			case REVIEW_ROUND_STATUS_RESUBMITTED:
				return 'editor.monograph.roundStatus.resubmitted';
			case REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL:
				return 'editor.monograph.roundStatus.sentToExternal';
			case REVIEW_ROUND_STATUS_ACCEPTED:
				return 'editor.monograph.roundStatus.accepted';
			case REVIEW_ROUND_STATUS_DECLINED:
				return 'editor.monograph.roundStatus.declined';
			case REVIEW_ROUND_STATUS_PENDING_REVIEWERS:
				return 'editor.monograph.roundStatus.pendingReviewers';
			case REVIEW_ROUND_STATUS_PENDING_REVIEWS:
				return 'editor.monograph.roundStatus.pendingReviews';
			case REVIEW_ROUND_STATUS_REVIEWS_READY:
				return 'editor.monograph.roundStatus.reviewsReady';
			case REVIEW_ROUND_STATUS_REVIEWS_COMPLETED:
				return 'editor.monograph.roundStatus.reviewsCompleted';
			default: return null;
		}
	}
}

?>
