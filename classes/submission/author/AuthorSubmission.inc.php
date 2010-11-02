<?php

/**
 * @file classes/submission/author/AuthorSubmission.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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

	/**
	 * Get the galleys for a monograph.
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

	//
	// Copyeditor Assignment
	//

	/**
	 * Get copyed id.
	 * @return int
	 */
	function getCopyedId() {
		return $this->getData('copyedId');
	}

	/**
	 * Set copyed id.
	 * @param $copyedId int
	 */
	function setCopyedId($copyedId)
	{
		return $this->setData('copyedId', $copyedId);
	}

	/**
	 * Get copyeditor id.
	 * @return int
	 */
	function getCopyeditorId() {
		return $this->getData('copyeditorId');
	}

	/**
	 * Set copyeditor id.
	 * @param $copyeditorId int
	 */
	function setCopyeditorId($copyeditorId)
	{
		return $this->setData('copyeditorId', $copyeditorId);
	}

	/**
	 * Get copyeditor of this monograph.
	 * @return User
	 */
	function &getCopyeditor() {
		$copyEditor =& $this->getData('copyeditor');
		return $copyEditor;
	}

	/**
	 * Set copyeditor of this monograph.
	 * @param $copyeditor User
	 */
	function setCopyeditor($copyeditor) {
		return $this->setData('copyeditor', $copyeditor);
	}

	/**
	 * Get copyeditor date notified.
	 * @return string
	 */
	function getCopyeditorDateNotified() {
		return $this->getData('copyeditorDateNotified');
	}

	/**
	 * Set copyeditor date notified.
	 * @param $copyeditorDateNotified string
	 */
	function setCopyeditorDateNotified($copyeditorDateNotified)
	{
		return $this->setData('copyeditorDateNotified', $copyeditorDateNotified);
	}

	/**
	 * Get copyeditor date underway.
	 * @return string
	 */
	function getCopyeditorDateUnderway() {
		return $this->getData('copyeditorDateUnderway');
	}

	/**
	 * Set copyeditor date underway.
	 * @param $copyeditorDateUnderway string
	 */
	function setCopyeditorDateUnderway($copyeditorDateUnderway) {
		return $this->setData('copyeditorDateUnderway', $copyeditorDateUnderway);
	}

	/**
	 * Get copyeditor date completed.
	 * @return string
	 */
	function getCopyeditorDateCompleted() {
		return $this->getData('copyeditorDateCompleted');
	}

	/**
	 * Set copyeditor date completed.
	 * @param $copyeditorDateCompleted string
	 */
	function setCopyeditorDateCompleted($copyeditorDateCompleted)
	{
		return $this->setData('copyeditorDateCompleted', $copyeditorDateCompleted);
	}

	/**
	 * Get copyeditor date acknowledged.
	 * @return string
	 */
	function getCopyeditorDateAcknowledged() {
		return $this->getData('copyeditorDateAcknowledged');
	}

	/**
	 * Set copyeditor date acknowledged.
	 * @param $copyeditorDateAcknowledged string
	 */
	function setCopyeditorDateAcknowledged($copyeditorDateAcknowledged)
	{
		return $this->setData('copyeditorDateAcknowledged', $copyeditorDateAcknowledged);
	}

	/**
	 * Get copyeditor date author notified.
	 * @return string
	 */
	function getCopyeditorDateAuthorNotified() {
		return $this->getData('copyeditorDateAuthorNotified');
	}

	/**
	 * Set copyeditor date author notified.
	 * @param $copyeditorDateAuthorNotified string
	 */
	function setCopyeditorDateAuthorNotified($copyeditorDateAuthorNotified) {
		return $this->setData('copyeditorDateAuthorNotified', $copyeditorDateAuthorNotified);
	}

	/**
	 * Get copyeditor date authorunderway.
	 * @return string
	 */
	function getCopyeditorDateAuthorUnderway() {
		return $this->getData('copyeditorDateAuthorUnderway');
	}

	/**
	 * Set copyeditor date author underway.
	 * @param $copyeditorDateAuthorUnderway string
	 */
	function setCopyeditorDateAuthorUnderway($copyeditorDateAuthorUnderway) {
		return $this->setData('copyeditorDateAuthorUnderway', $copyeditorDateAuthorUnderway);
	}

	/**
	 * Get copyeditor date author completed.
	 * @return string
	 */
	function getCopyeditorDateAuthorCompleted() {
		return $this->getData('copyeditorDateAuthorCompleted');
	}

	/**
	 * Set copyeditor date author completed.
	 * @param $copyeditorDateAuthorCompleted string
	 */
	function setCopyeditorDateAuthorCompleted($copyeditorDateAuthorCompleted)
	{
		return $this->setData('copyeditorDateAuthorCompleted', $copyeditorDateAuthorCompleted);
	}

	/**
	 * Get copyeditor date author acknowledged.
	 * @return string
	 */
	function getCopyeditorDateAuthorAcknowledged() {
		return $this->getData('copyeditorDateAuthorAcknowledged');
	}

	/**
	 * Set copyeditor date author acknowledged.
	 * @param $copyeditorDateAuthorAcknowledged string
	 */
	function setCopyeditorDateAuthorAcknowledged($copyeditorDateAuthorAcknowledged)
	{
		return $this->setData('copyeditorDateAuthorAcknowledged', $copyeditorDateAuthorAcknowledged);
	}

	/**
	 * Get copyeditor date final notified.
	 * @return string
	 */
	function getCopyeditorDateFinalNotified() {
		return $this->getData('copyeditorDateFinalNotified');
	}

	/**
	 * Set copyeditor date final notified.
	 * @param $copyeditorDateFinalNotified string
	 */
	function setCopyeditorDateFinalNotified($copyeditorDateFinalNotified) {
		return $this->setData('copyeditorDateFinalNotified', $copyeditorDateFinalNotified);
	}

	/**
	 * Get copyeditor date final underway.
	 * @return string
	 */
	function getCopyeditorDateFinalUnderway() {
		return $this->getData('copyeditorDateFinalUnderway');
	}

	/**
	 * Set copyeditor date final underway.
	 * @param $copyeditorDateFinalUnderway string
	 */
	function setCopyeditorDateFinalUnderway($copyeditorDateFinalUnderway) {
		return $this->setData('copyeditorDateFinalUnderway', $copyeditorDateFinalUnderway);
	}

	/**
	 * Get copyeditor date finak completed.
	 * @return string
	 */
	function getCopyeditorDateFinalCompleted() {
		return $this->getData('copyeditorDateFinalCompleted');
	}

	/**
	 * Set copyeditor date final completed.
	 * @param $copyeditorDateFinalCompleted string
	 */
	function setCopyeditorDateFinalCompleted($copyeditorDateFinalCompleted)
	{
		return $this->setData('copyeditorDateFinalCompleted', $copyeditorDateFinalCompleted);
	}

	/**
	 * Get copyeditor date final acknowledged.
	 * @return string
	 */
	function getCopyeditorDateFinalAcknowledged() {
		return $this->getData('copyeditorDateFinalAcknowledged');
	}

	/**
	 * Set copyeditor date final acknowledged.
	 * @param $copyeditorDateFinalAcknowledged string
	 */
	function setCopyeditorDateFinalAcknowledged($copyeditorDateFinalAcknowledged)
	{
		return $this->setData('copyeditorDateFinalAcknowledged', $copyeditorDateFinalAcknowledged);
	}

	/**
	 * Get copyeditor initial revision.
	 * @return int
	 */
	function getCopyeditorInitialRevision() {
		return $this->getData('copyeditorInitialRevision');
	}

	/**
	 * Set copyeditor initial revision.
	 * @param $copyeditorInitialRevision int
	 */
	function setCopyeditorInitialRevision($copyeditorInitialRevision)	{
		return $this->setData('copyeditorInitialRevision', $copyeditorInitialRevision);
	}

	/**
	 * Get copyeditor editor/author revision.
	 * @return int
	 */
	function getCopyeditorEditorAuthorRevision() {
		return $this->getData('copyeditorEditorAuthorRevision');
	}

	/**
	 * Set copyeditor editor/author revision.
	 * @param $editorAuthorRevision int
	 */
	function setCopyeditorEditorAuthorRevision($copyeditorEditorAuthorRevision)	{
		return $this->setData('copyeditorEditorAuthorRevision', $copyeditorEditorAuthorRevision);
	}

	/**
	 * Get copyeditor final revision.
	 * @return int
	 */
	function getCopyeditorFinalRevision() {
		return $this->getData('copyeditorFinalRevision');
	}

	/**
	 * Set copyeditor final revision.
	 * @param $copyeditorFinalRevision int
	 */
	function setCopyeditorFinalRevision($copyeditorFinalRevision)	{
		return $this->setData('copyeditorFinalRevision', $copyeditorFinalRevision);
	}

	/**
	 * Get layout assignment.
	 * @return layoutAssignment object
	 */
	function &getLayoutAssignment() {
		$layoutAssignment =& $this->getData('layoutAssignment');
		return $layoutAssignment;
	}

	/**
	 * Set layout assignment.
	 * @param $layoutAssignment
	 */
	function setLayoutAssignment($layoutAssignment) {
		return $this->setData('layoutAssignment', $layoutAssignment);
	}

	/**
	 * Get proof assignment.
	 * @return proofAssignment object
	 */
	function &getProofAssignment() {
		$proofAssignment =& $this->getData('proofAssignment');
		return $proofAssignment;
	}

	/**
	 * Set proof assignment.
	 * @param $proofAssignment
	 */
	function setProofAssignment($proofAssignment) {
		return $this->setData('proofAssignment', $proofAssignment);
	}
}

?>
