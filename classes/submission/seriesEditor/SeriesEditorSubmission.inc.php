<?php

/**
 * @file classes/submission/seriesEditor/SeriesEditorSubmission.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorSubmission
 * @ingroup submission
 * @see SeriesEditorSubmissionDAO
 *
 * @brief SeriesEditorSubmission class.
 */



import('classes.monograph.Monograph');

class SeriesEditorSubmission extends Monograph {

	/** @var array ReviewAssignments of this monograph */
	var $reviewAssignments;

	/** @var array IDs of ReviewAssignments removed from this monograph */
	var $removedReviewAssignments;

	/** @var array the editor decisions of this monograph */
	var $editorDecisions;

	/** @var array the revisions of the editor file */
	var $editorFileRevisions;

	/** @var array the revisions of the author file */
	var $authorFileRevisions;

	/** @var array the revisions of the revised copyedit file */
	var $copyeditFileRevisions;

	/**
	 * Constructor.
	 */
	function SeriesEditorSubmission() {
		parent::Monograph();
		$this->reviewAssignments = array();
		$this->removedReviewAssignments = array();
	}

	/**
	 * Add a review assignment for this monograph.
	 * @param $reviewAssignment ReviewAssignment
	 */
	function addReviewAssignment($reviewAssignment) {
		if ($reviewAssignment->getSubmissionId() == null) {
			$reviewAssignment->setSubmissionId($this->getMonographId());
		}

		if (isset($this->reviewAssignments[$reviewAssignment->getStageId()][$reviewAssignment->getRound()])) {
			$roundReviewAssignments = $this->reviewAssignments[$reviewAssignment->getStageId()][$reviewAssignment->getRound()];
		} else {
			$roundReviewAssignments = Array();
		}
		array_push($roundReviewAssignments, $reviewAssignment);

		return $this->reviewAssignments[$reviewAssignment->getStageId()][$reviewAssignment->getRound()] = $roundReviewAssignments;
	}

	/**
	 * Add an editorial decision for this monograph.
	 * @param $editorDecision array
	 * @param $stageId int
	 * @param $round int
	 */
	function addDecision($editorDecision, $stageId, $round) {
		if (isset($this->editorDecisions[$stageId][$round]) && is_array($this->editorDecisions[$stageId][$round])) {
			array_push($this->editorDecisions[$stageId][$round], $editorDecision);
		}
		else $this->editorDecisions[$stageId][$round] = Array($editorDecision);
	}

	/**
	 * Remove a review assignment.
	 * @param $reviewId ID of the review assignment to remove
	 * @return boolean review assignment was removed
	 */
	function removeReviewAssignment($reviewId) {
		$found = false;

		if ($reviewId != 0) {
			// FIXME maintain a hash for quicker get/remove
			$reviewAssignments = array();
			$empty = array();
			foreach ($this->reviewAssignments as $stageId => $reviewRounds)  {
				foreach ($reviewRounds as $round => $junk )  {
					$roundReviewAssignments = $this->reviewAssignments[$stageId][$round];
					foreach ( $roundReviewAssignments as $assignment ) {
						if ($assignment->getReviewId() == $reviewId) {
							array_push($this->removedReviewAssignments, $reviewId);
							$found = true;
						} else {
							array_push($reviewAssignments, $assignment);
						}
					}
					$this->reviewAssignments[$stageId][$round] = $reviewAssignments;
					$reviewAssignments = $empty;
				}
			}
		}
		return $found;
	}

	/**
	 * Updates an existing review assignment.
	 * @param $reviewAssignment ReviewAssignment
	 */
	function updateReviewAssignment($reviewAssignment) {
		$reviewAssignments = array();
		$roundReviewAssignments = $this->reviewAssignments[$reviewAssignment->getStageId()][$reviewAssignment->getRound()];
		for ($i=0, $count=count($roundReviewAssignments); $i < $count; $i++) {
			if ($roundReviewAssignments[$i]->getReviewId() == $reviewAssignment->getId()) {
				array_push($reviewAssignments, $reviewAssignment);
			} else {
				array_push($reviewAssignments, $roundReviewAssignments[$i]);
			}
		}
		$this->reviewAssignments[$reviewAssignment->getStageId()][$reviewAssignment->getRound()] = $reviewAssignments;
	}

	/**
	 * Get the submission status. Returns one of the defined constants
	 * (STATUS_INCOMPLETE, STATUS_ARCHIVED, STATUS_PUBLISHED,
	 * STATUS_DECLINED, STATUS_QUEUED_UNASSIGNED, STATUS_QUEUED_REVIEW,
	 * or STATUS_QUEUED_EDITING). Note that this function never returns
	 * a value of STATUS_QUEUED -- the three STATUS_QUEUED_... constants
	 * indicate a queued submission.
	 * NOTE that this code is similar to getSubmissionStatus in
	 * the AuthorSubmission class and changes should be made there as well.
	 */
	function getSubmissionStatus() {
			$status = $this->getStatus();
		if ($status == STATUS_ARCHIVED || $status == STATUS_PUBLISHED || $status == STATUS_DECLINED) return $status;

		// The submission is STATUS_QUEUED or the author's submission was STATUS_INCOMPLETE.
		if ($this->getSubmissionProgress()) return (STATUS_INCOMPLETE);

		if($this->getStageId() == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $this->getStageId() == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			return STATUS_QUEUED_REVIEW;
		}

		$decisions = $this->getDecisions();
		if (!is_array($decisions)) {
			$decisions = array($decisions);
		}
		$decision = array_pop($decisions);
		if (!empty($decision)) {
			$latestDecision = array_pop($decision);
			if ($latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT || $latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_DECLINE) {
				return STATUS_QUEUED_EDITING;
			}
		}

		return STATUS_QUEUED_UNASSIGNED;
	}


	//
	// Review Assignments
	//
	/**
	 * Get review assignments for this monograph.
	 * @return array ReviewAssignments
	 */
	function &getReviewAssignments($stageId = null, $round = null) {
		if ($stageId == null) {
			return $this->reviewAssignments;
		} elseif ($round == null) {
			return $this->reviewAssignments[$stageId];
		} else {
			return $this->reviewAssignments[$stageId][$round];
		}
	}

	/**
	 * Set review assignments for this monograph.
	 * @param $reviewAssignments array ReviewAssignments
	 */
	function setReviewAssignments($reviewAssignments, $stageId, $round) {
		return $this->reviewAssignments[$stageId][$round] = $reviewAssignments;
	}

	/**
	 * Get the IDs of all review assignments removed..
	 * @return array int
	 */
	function &getRemovedReviewAssignments() {
		return $this->removedReviewAssignments;
	}


	//
	// Editor Decisions
	//
	/**
	 * Get editor decisions.
	 * @return array
	 */
	function getDecisions($stageId = null, $round = null) {
		if ($stageId == null) {
			return $this->editorDecisions;
		} elseif ($round == null) {
			if (isset($this->editorDecisions[$stageId])) return $this->editorDecisions[$stageId];
		} else {
			if (isset($this->editorDecisions[$stageId][$round])) return $this->editorDecisions[$stageId][$round];
		}

		return null;

	}

	/**
	 * Set editor decisions.
	 * @param $editorDecisions array
	 * @param $stageId int
	 * @param $round int
	 */
	function setDecisions($editorDecisions, $stageId, $round) {
		$this->editorDecisions[$stageId][$round] = $editorDecisions;
	}

	//
	// Comments
	//

	/**
	 * Get most recent editor decision comment.
	 * @return MonographComment
	 */
	function getMostRecentEditorDecisionComment() {
		return $this->getData('mostRecentEditorDecisionComment');
	}

	/**
	 * Set most recent editor decision comment.
	 * @param $mostRecentEditorDecisionComment MonographComment
	 */
	function setMostRecentEditorDecisionComment($mostRecentEditorDecisionComment) {
		return $this->setData('mostRecentEditorDecisionComment', $mostRecentEditorDecisionComment);
	}

	/**
	 * Get most recent copyedit comment.
	 * @return MonographComment
	 */
	function getMostRecentCopyeditComment() {
		return $this->getData('mostRecentCopyeditComment');
	}

	/**
	 * Set most recent copyedit comment.
	 * @param $mostRecentCopyeditComment MonographComment
	 */
	function setMostRecentCopyeditComment($mostRecentCopyeditComment) {
		return $this->setData('mostRecentCopyeditComment', $mostRecentCopyeditComment);
	}

	/**
	 * Get most recent layout comment.
	 * @return MonographComment
	 */
	function getMostRecentLayoutComment() {
		return $this->getData('mostRecentLayoutComment');
	}

	/**
	 * Set most recent layout comment.
	 * @param $mostRecentLayoutComment MonographComment
	 */
	function setMostRecentLayoutComment($mostRecentLayoutComment) {
		return $this->setData('mostRecentLayoutComment', $mostRecentLayoutComment);
	}

	/**
	 * Get most recent proofread comment.
	 * @return MonographComment
	 */
	function getMostRecentProofreadComment() {
		return $this->getData('mostRecentProofreadComment');
	}

	/**
	 * Set most recent proofread comment.
	 * @param $mostRecentProofreadComment MonographComment
	 */
	function setMostRecentProofreadComment($mostRecentProofreadComment) {
		return $this->setData('mostRecentProofreadComment', $mostRecentProofreadComment);
	}

	/**
	 * Get the galleys for an monograph.
	 * @return array MonographGalley
	 */
	function &getGalleys() {
		$galleys =& $this->getData('galleys');
		return $galleys;
	}

	/**
	 * Set the galleys for a monograph.
	 * @param $galleys array MonographGalley
	 */
	function setGalleys(&$galleys) {
		return $this->setData('galleys', $galleys);
	}

	/**
	 * Return array mapping editor decision constants to their locale strings.
	 * (Includes default mapping '' => "Choose One".)
	 * @return array decision => localeString
	 */
	function &getEditorDecisionOptions() {
		static $editorDecisionOptions = array(
			'' => 'common.chooseOne',
			SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.monograph.decision.accept',
			SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.monograph.decision.pendingRevisions',
			SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.monograph.decision.resubmit',
			SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => 'editor.monograph.decision.externalReview',
			SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline'
		);
		return $editorDecisionOptions;
	}

}

?>
