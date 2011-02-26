<?php

/**
 * @file classes/submission/author/AuthorSubmission.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmission
 * @ingroup submission
 * @see AuthorSubmissionDAO
 *
 * @brief AuthorSubmission class.
 */



import('classes.monograph.Monograph');

class AuthorSubmission extends Monograph {

	/** @var array ReviewAssignments of this monograph */
	var $reviewAssignments;

	/** @var array the editor decisions of this monograph */
	var $editorDecisions;

	/** @var array the revisions of the author file */
	var $authorFileRevisions;

	/** @var array the revisions of the editor file */
	var $editorFileRevisions;

	/** @var array the revisions of the author copyedit file */
	var $copyeditFileRevisions;

	/**
	 * Constructor.
	 */
	function AuthorSubmission() {
		parent::Monograph();
		$this->reviewAssignments = array();
	}

	/**
	 * Get/Set Methods.
	 */

	/**
	 * Add a review assignment for this monograph.
	 * @param $reviewAssignment ReviewAssignment
	 */
	function addReviewAssignment($reviewAssignment) {
		if ($reviewAssignment->getSubmissionId() == null) {
			$reviewAssignment->setSubmissionId($this->getMonographId());
		}

		array_push($this->reviewAssignments, $reviewAssignment);
	}

	/**
	 * Remove a review assignment.
	 * @param $reviewId ID of the review assignment to remove
	 * @return boolean review assignment was removed
	 */
	function removeReviewAssignment($reviewId) {
		$reviewAssignments = array();
		$found = false;
		for ($i=0, $count=count($this->reviewAssignments); $i < $count; $i++) {
			if ($this->reviewAssignments[$i]->getReviewId() == $reviewId) {
				$found = true;
			} else {
				array_push($reviewAssignments, $this->reviewAssignments[$i]);
			}
		}
		$this->reviewAssignments = $reviewAssignments;

		return $found;
	}

	//
	// Review Assignments
	//

	/**
	 * Get review assignments for this monograph.
	 * @return array ReviewAssignments
	 */
	function &getReviewAssignments($reviewType = null, $round = null) {
		if ($reviewType == null) {
			return $this->reviewAssignments;
		} else {
			$returner = $round != null && isset($this->reviewAssignments[$reviewType][$round]) ?
						$this->reviewAssignments[$reviewType][$round] : null;
		}
		return $returner;
	}

	/**
	 * Set review assignments for this monograph.
	 * @param $reviewAssignments array ReviewAssignments
	 */
	function setReviewAssignments($reviewAssignments, $round) {
		return $this->reviewAssignments[$round] = $reviewAssignments;
	}

	//
	// Editor Decisions
	//

	/**
	 * Get editor decisions.
	 * @return array
	 */
	function getDecisions($reviewType = null, $round = null) {
		if ($reviewType == null) {
			return $this->editorDecisions;
		} else {
			return $round != null && isset($this->editorDecisions[$reviewType][$round]) ?
					$this->editorDecisions[$reviewType][$round] : null;
		}
	}

	/**
	 * Set editor decisions.
	 * @param $editorDecisions array
	 * @param $round int
	 */
	function setDecisions($editorDecisions) {
		return $this->editorDecisions = $editorDecisions;
	}

	/**
	 * Get the submission status. Returns one of the defined constants
	 * (STATUS_INCOMPLETE, STATUS_ARCHIVED, STATUS_PUBLISHED,
	 * STATUS_DECLINED, STATUS_QUEUED_UNASSIGNED, STATUS_QUEUED_REVIEW,
	 * or STATUS_QUEUED_EDITING). Note that this function never returns
	 * a value of STATUS_QUEUED -- the three STATUS_QUEUED_... constants
	 * indicate a queued submission. NOTE that this code is similar to
	 * getSubmissionStatus in the SeriesEditorSubmission class and
	 * changes here should be propagated.
	 */
	function getSubmissionStatus() {
		$status = $this->getStatus();
		if ($status == STATUS_ARCHIVED || $status == STATUS_PUBLISHED ||
		    $status == STATUS_DECLINED) return $status;

		// The submission is STATUS_QUEUED or the author's submission was STATUS_INCOMPLETE.
		if ($this->getSubmissionProgress()) return (STATUS_INCOMPLETE);

		if($this->getCurrentStageId() == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $this->getCurrentStageId() == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
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
	// Files
	//

	/**
	 * Get submission file for this monograph.
	 * @return MonographFile
	 */
	function &getSubmissionFile() {
		$returner =& $this->getData('submissionFile');
		return $returner;
	}

	/**
	 * Set submission file for this monograph.
	 * @param $submissionFile MonographFile
	 */
	function setSubmissionFile($submissionFile) {
		return $this->setData('submissionFile', $submissionFile);
	}

	/**
	 * Get revised file for this monograph.
	 * @return MonographFile
	 */
	function &getRevisedFile() {
		$returner =& $this->getData('revisedFile');
		return $returner;
	}

	/**
	 * Set revised file for this monograph.
	 * @param $submissionFile MonographFile
	 */
	function setRevisedFile($revisedFile) {
		return $this->setData('revisedFile', $revisedFile);
	}

	/**
	 * Get all author file revisions.
	 * @return array MonographFiles
	 */
	function getAuthorFileRevisions($reviewType = null, $round = null) {
		if ($reviewType == null) {
			return $this->authorFileRevisions;
		} else {
			return $round != null ? $this->authorFileRevisions[$reviewType][$round] : $this->authorFileRevisions[$reviewType];
		}
	}

	/**
	 * Set all author file revisions.
	 * @param $authorFileRevisions array MonographFiles
	 */
	function setAuthorFileRevisions($authorFileRevisions) {
		return $this->authorFileRevisions = $authorFileRevisions;
	}

	/**
	 * Get all editor file revisions.
	 * @return array MonographFiles
	 */
	function getEditorFileRevisions($reviewType = null, $round = null) {
		if ($reviewType == null) {
			return $this->editorFileRevisions;
		} else {
			return $round != null ? $this->editorFileRevisions[$reviewType][$round] : $this->editorFileRevisions[$reviewType];
		}
	}

	/**
	 * Set all editor file revisions.
	 * @param $editorFileRevisions array MonographFiles
	 */
	function setEditorFileRevisions($editorFileRevisions) {
		return $this->editorFileRevisions = $editorFileRevisions;
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
}

?>
