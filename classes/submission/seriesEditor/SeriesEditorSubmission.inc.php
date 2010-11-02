<?php

/**
 * @file classes/submission/seriesEditor/SeriesEditorSubmission.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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

		if (isset($this->reviewAssignments[$reviewAssignment->getReviewType()][$reviewAssignment->getRound()])) {
			$roundReviewAssignments = $this->reviewAssignments[$reviewAssignment->getReviewType()][$reviewAssignment->getRound()];
		} else {
			$roundReviewAssignments = Array();
		}
		array_push($roundReviewAssignments, $reviewAssignment);

		return $this->reviewAssignments[$reviewAssignment->getReviewType()][$reviewAssignment->getRound()] = $roundReviewAssignments;
	}

	/**
	 * Add an editorial decision for this monograph.
	 * @param $editorDecision array
	 * @param $reviewType int
	 * @param $round int
	 */
	function addDecision($editorDecision, $reviewType, $round) {
		if (isset($this->editorDecisions[$reviewType][$round]) && is_array($this->editorDecisions[$reviewType][$round])) {
			array_push($this->editorDecisions[$reviewType][$round], $editorDecision);
		}
		else $this->editorDecisions[$reviewType][$round] = Array($editorDecision);
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
			foreach ($this->reviewAssignments as $reviewType => $reviewRounds)  {
				foreach ($reviewRounds as $round => $junk )  {
					$roundReviewAssignments = $this->reviewAssignments[$reviewType][$round];
					foreach ( $roundReviewAssignments as $assignment ) {
						if ($assignment->getReviewId() == $reviewId) {
							array_push($this->removedReviewAssignments, $reviewId);
							$found = true;
						} else {
							array_push($reviewAssignments, $assignment);
						}
					}
					$this->reviewAssignments[$reviewType][$round] = $reviewAssignments;
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
		$roundReviewAssignments = $this->reviewAssignments[$reviewAssignment->getReviewType()][$reviewAssignment->getRound()];
		for ($i=0, $count=count($roundReviewAssignments); $i < $count; $i++) {
			if ($roundReviewAssignments[$i]->getReviewId() == $reviewAssignment->getReviewId()) {
				array_push($reviewAssignments, $reviewAssignment);
			} else {
				array_push($reviewAssignments, $roundReviewAssignments[$i]);
			}
		}
		$this->reviewAssignments[$reviewAssignment->getReviewType()][$reviewAssignment->getRound()] = $reviewAssignments;
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
	// Review Assignments
	//

	/**
	 * Get review assignments for this monograph.
	 * @return array ReviewAssignments
	 */
	function &getReviewAssignments($reviewType = null, $round = null) {
		if ($reviewType == null) {
			return $this->reviewAssignments;
		} elseif ($round == null) {
			return $this->reviewAssignments[$reviewType];
		} else {
			return $this->reviewAssignments[$reviewType][$round];
		}
	}

	/**
	 * Set review assignments for this monograph.
	 * @param $reviewAssignments array ReviewAssignments
	 */
	function setReviewAssignments($reviewAssignments, $reviewType, $round) {
		return $this->reviewAssignments[$reviewType][$round] = $reviewAssignments;
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
	function getDecisions($reviewType = null, $round = null) {
		if ($reviewType == null) {
			return $this->editorDecisions;
		} elseif ($round == null) {
			if (isset($this->editorDecisions[$reviewType])) return $this->editorDecisions[$reviewType];
		} else {
			if (isset($this->editorDecisions[$reviewType][$round])) return $this->editorDecisions[$reviewType][$round];
		}

		return null;

	}

	/**
	 * Set editor decisions.
	 * @param $editorDecisions array
	 * @param $reviewType int
	 * @param $round int
	 */
	function setDecisions($editorDecisions, $reviewType, $round) {
		$this->editorDecisions[$reviewType][$round] = $editorDecisions;
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
	 * Get review file.
	 * @return MonographFile
	 */
	function &getReviewFile() {
		$returner =& $this->getData('reviewFile');
		return $returner;
	}

	/**
	 * Set review file.
	 * @param $reviewFile MonographFile
	 */
	function setReviewFile($reviewFile) {
		return $this->setData('reviewFile', $reviewFile);
	}

	/**
	 * Get all editor file revisions.
	 * @return array MonographFiles
	 */
	function getEditorFileRevisions($reviewType = null, $round = null) {
		if ($reviewType == null) {
			return $this->editorFileRevisions;
		} elseif ( $round == null ) {
			return $this->editorFileRevisions[$reviewType];
		} else {
			return $this->editorFileRevisions[$reviewType][$round];
		}
	}

	/**
	 * Set all editor file revisions.
	 * @param $editorFileRevisions array MonographFiles
	 */
	function setEditorFileRevisions($editorFileRevisions, $reviewType, $round) {
		return $this->editorFileRevisions[$reviewType][$round] = $editorFileRevisions;
	}

	/**
	 * Get all author file revisions.
	 * @return array MonographFiles
	 */
	function getAuthorFileRevisions($reviewType = null, $round = null) {
		if ($reviewType == null) {
			return $this->authorFileRevisions;
		} elseif ( $round == null ) {
			return $this->authorFileRevisions[$reviewType];
		} else {
			return $this->authorFileRevisions[$reviewType][$round];
		}
	}

	/**
	 * Set all author file revisions.
	 * @param $authorFileRevisions array MonographFiles
	 */
	function setAuthorFileRevisions($authorFileRevisions, $reviewType, $round) {
		return $this->authorFileRevisions[$reviewType][$round] = $authorFileRevisions;
	}

	/**
	 * Get post-review file.
	 * @return MonographFile
	 */
	function &getEditorFile() {
		$returner =& $this->getData('editorFile');
		return $returner;
	}

	/**
	 * Set post-review file.
	 * @param $editorFile MonographFile
	 */
	function setEditorFile($editorFile) {
		return $this->setData('editorFile', $editorFile);
	}

	//
	// Review Rounds
	//

	/**
	 * Get review file revision.
	 * @return int
	 */
	function getReviewRevision() {
		return $this->getData('reviewRevision');
	}

	/**
	 * Set review file revision.
	 * @param $reviewRevision int
	 */
	function setReviewRevision($reviewRevision) {
		return $this->setData('reviewRevision', $reviewRevision);
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
			SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.monograph.decision.decline'
		);
		return $editorDecisionOptions;
	}

}

?>
