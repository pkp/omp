<?php

/**
 * @file classes/submission/reviewAssignment/ReviewAssignment.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewAssignment
 * @ingroup submission
 * @see ReviewAssignmentDAO
 *
 * @brief Describes review assignment properties.
 */

// $Id$


import('lib.pkp.classes.submission.reviewAssignment.PKPReviewAssignment');

class ReviewAssignment extends PKPReviewAssignment {
	/**
	 * Constructor.
	 */
	function ReviewAssignment() {
		parent::PKPReviewAssignment();
	}

	//
	// Get/set methods
	//

	/**
	 * Get ID of monograph.
	 * @return int
	 */
	function getMonographId() {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->getSubmissionId();
	}

	/**
	 * Set ID of monograph.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->setSubmissionId($monographId);
	}

	/**
	 * Get the type of the review (internal or external).
	 * @return int
	 */
	function getReviewType() {
		return $this->getData('reviewType');
	}

	/**
	 * Set the type of review.
	 * @param $type int
	 */
	function setReviewType($type) {
		return $this->setData('reviewType', $type);
	}

	/**
	 * Get the method of the review (open, blind, or double-blind).
	 * @return int
	 */
	function getReviewMethod() {
		return $this->getData('reviewMethod');
	}

	/**
	 * Set the type of review.
	 * @param $method int
	 */
	function setReviewMethod($method) {
		return $this->setData('reviewMethod', $method);
	}
	
	/**
	 * Get an associative array matching reviewer recommendation codes with locale strings.
	 * (Includes default '' => "Choose One" string.)
	 * @return array recommendation => localeString
	 */
	function &getReviewerRecommendationOptions() {
		// Bring in reviewer constants
		import('classes.submission.reviewer.ReviewerSubmission');

		static $reviewerRecommendationOptions = array(
			'' => 'common.chooseOne',
			SUBMISSION_REVIEWER_RECOMMENDATION_ACCEPT => 'reviewer.monograph.decision.accept',
			SUBMISSION_REVIEWER_RECOMMENDATION_PENDING_REVISIONS => 'reviewer.monograph.decision.pendingRevisions',
			SUBMISSION_REVIEWER_RECOMMENDATION_RESUBMIT_HERE => 'reviewer.monograph.decision.resubmitHere',
			SUBMISSION_REVIEWER_RECOMMENDATION_RESUBMIT_ELSEWHERE => 'reviewer.monograph.decision.resubmitElsewhere',
			SUBMISSION_REVIEWER_RECOMMENDATION_DECLINE => 'reviewer.monograph.decision.decline',
			SUBMISSION_REVIEWER_RECOMMENDATION_SEE_COMMENTS => 'reviewer.monograph.decision.seeComments'
		);
		return $reviewerRecommendationOptions;
	}

	/**
	 * Get an associative array matching reviewer rating codes with locale strings.
	 * @return array recommendation => localeString
	 */
	function &getReviewerRatingOptions() {
		static $reviewerRatingOptions = array(
			SUBMISSION_REVIEWER_RATING_VERY_GOOD => 'editor.monograph.reviewerRating.veryGood',
			SUBMISSION_REVIEWER_RATING_GOOD => 'editor.monograph.reviewerRating.good',
			SUBMISSION_REVIEWER_RATING_AVERAGE => 'editor.monograph.reviewerRating.average',
			SUBMISSION_REVIEWER_RATING_POOR => 'editor.monograph.reviewerRating.poor',
			SUBMISSION_REVIEWER_RATING_VERY_POOR => 'editor.monograph.reviewerRating.veryPoor'
		);
		return $reviewerRatingOptions;
	}
}

?>
