<?php

/**
 * @file classes/submission/seriesEditor/SeriesEditorSubmissionDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorSubmissionDAO
 * @ingroup submission
 * @see SeriesEditorSubmission
 *
 * @brief Operations for retrieving and modifying SeriesEditorSubmission objects.
 * FIXME #5557: We need a general code cleanup here (remove useless functions), and to integrate with monograph_stage_assignments table
 */

import('classes.submission.seriesEditor.SeriesEditorSubmission');
import('classes.monograph.MonographDAO');

// Bring in editor decision constants
import('classes.submission.reviewer.ReviewerSubmission');

class SeriesEditorSubmissionDAO extends MonographDAO {
	var $authorDao;
	var $userDao;
	var $reviewAssignmentDao;
	var $submissionFileDao;
	var $signoffDao;
	var $submissionEmailLogDao;
	var $submissionCommentDao;
	var $reviewRoundDao;

	/**
	 * Constructor.
	 */
	function SeriesEditorSubmissionDAO() {
		parent::MonographDAO();
		$this->authorDao = DAORegistry::getDAO('AuthorDAO');
		$this->userDao = DAORegistry::getDAO('UserDAO');
		$this->reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
		$this->submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$this->signoffDao = DAORegistry::getDAO('SignoffDAO');
		$this->submissionEmailLogDao = DAORegistry::getDAO('SubmissionEmailLogDAO');
		$this->submissionCommentDao = DAORegistry::getDAO('SubmissionCommentDAO');
		$this->reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
	}

	/**
	 * Retrieve a series editor submission by monograph ID.
	 * @param $monographId int
	 * @return SeriesEditorSubmission
	 */
	function getById($monographId) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		$result = $this->retrieve(
			'SELECT	m.*, ps.date_published,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	submissions m
				LEFT JOIN published_submissions ps ON (ps.submission_id = m.submission_id)
				LEFT JOIN series s ON (s.series_id = m.series_id)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	m.submission_id = ?',
			array(
				'title', $primaryLocale, // Series title
				'title', $locale, // Series title
				'abbrev', $primaryLocale, // Series abbreviation
				'abbrev', $locale, // Series abbreviation
				(int) $monographId
			)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SeriesEditorSubmission
	 */
	function newDataObject() {
		return new SeriesEditorSubmission();
	}

	/**
	 * Internal function to return a SeriesEditorSubmission object from a row.
	 * @param $row array
	 * @return SeriesEditorSubmission
	 */
	function _fromRow($row) {
		// Populate the monograph parts of the object
		$seriesEditorSubmission = parent::_fromRow($row);

		// Editor Decisions
		$reviewRounds = $this->reviewRoundDao->getBySubmissionId($row['submission_id']);
		$editDecisionDao = DAORegistry::getDAO('EditDecisionDAO');
		while ($reviewRound = $reviewRounds->next()) {
			$stageId = $reviewRound->getStageId();
			$round = $reviewRound->getRound();
			$seriesEditorSubmission->setDecisions(
				$editDecisionDao->getEditorDecisions($row['submission_id'], $stageId, $round),
				$stageId,
				$round
			);
		}

		// Comments
		$seriesEditorSubmission->setMostRecentEditorDecisionComment($this->submissionCommentDao->getMostRecentSubmissionComment($row['submission_id'], COMMENT_TYPE_EDITOR_DECISION, $row['submission_id']));
		$seriesEditorSubmission->setMostRecentCopyeditComment($this->submissionCommentDao->getMostRecentSubmissionComment($row['submission_id'], COMMENT_TYPE_COPYEDIT, $row['submission_id']));
		$seriesEditorSubmission->setMostRecentLayoutComment($this->submissionCommentDao->getMostRecentSubmissionComment($row['submission_id'], COMMENT_TYPE_LAYOUT, $row['submission_id']));
		$seriesEditorSubmission->setMostRecentProofreadComment($this->submissionCommentDao->getMostRecentSubmissionComment($row['submission_id'], COMMENT_TYPE_PROOFREAD, $row['submission_id']));

		// Review Assignments
		$reviewRounds = $this->reviewRoundDao->getBySubmissionId($row['submission_id']);
		while ($reviewRound = $reviewRounds->next()) {
			$stageId = $reviewRound->getStageId();
			$round = $reviewRound->getRound();
			$seriesEditorSubmission->setReviewAssignments(
				$this->reviewAssignmentDao->getBySubmissionId($row['submission_id'], $reviewRound->getId()),
				$stageId,
				$round
			);
		}

		HookRegistry::call('SeriesEditorSubmissionDAO::_fromRow', array(&$seriesEditorSubmission, &$row));

		return $seriesEditorSubmission;
	}

	/**
	 * Update an existing series editorsubmission.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 */
	function updateSeriesEditorSubmission(&$seriesEditorSubmission) {
		$monographId = $seriesEditorSubmission->getId();

		// Get all submission editor decisions.
		$editorDecisions = $seriesEditorSubmission->getDecisions();

		// Update review stages editor decisions.
		$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$reviewRounds = $reviewRoundDao->getBySubmissionId($monographId);

		$editDecisionDao = DAORegistry::getDAO('EditDecisionDAO');
		while ($reviewRound = $reviewRounds->next()) {
			$stageId = $reviewRound->getStageId();
			$round = $reviewRound->getRound();
			$reviewStageEditorDecisions = array();
			if (isset($editorDecisions[$stageId][$round])) {
				$reviewStageEditorDecisions = $editorDecisions[$stageId][$round];
				unset($editorDecisions[$stageId][$round]);
			}
			foreach ($reviewStageEditorDecisions as $editorDecision) {
				$editDecisionDao->updateEditorDecision($monographId, $editorDecision, $stageId, $reviewRound);
			}
		}

		// Update the remaining stages editor decisions.
		foreach ($editorDecisions as $stageId => $stageEditorDecision) {
			if (isset($stageEditorDecision[REVIEW_ROUND_NONE])) {
				foreach ($stageEditorDecision[REVIEW_ROUND_NONE] as $editorDecision) {
					$editDecisionDao->updateEditorDecision($monographId, $editorDecision, $stageId);
				}
			}
		}

		// update review assignments
		$removedReviewAssignments =& $seriesEditorSubmission->getRemovedReviewAssignments();

		unset($reviewRounds);
		$reviewRounds =& $reviewRoundDao->getBySubmissionId($monographId);

		while ($reviewRound = $reviewRounds->next()) {
			$stageId = $reviewRound->getStageId();
			$round = $reviewRound->getRound();
			foreach ($seriesEditorSubmission->getReviewAssignments($stageId, $round) as $reviewAssignment) {
				if (isset($removedReviewAssignments[$reviewAssignment->getId()])) continue;

				if ($reviewAssignment->getId() > 0) {
					$this->reviewAssignmentDao->updateObject($reviewAssignment);
				} else {
					$this->reviewAssignmentDao->insertObject($reviewAssignment);
				}
			}
		}

		// Remove deleted review assignments
		foreach ($removedReviewAssignments as $removedReviewAssignmentId) {
			$this->reviewAssignmentDao->deleteById($removedReviewAssignmentId);
		}

		// Update monograph
		if ($seriesEditorSubmission->getId()) {
			$monograph = parent::getById($monographId);

			// Only update fields that can actually be edited.
			$monograph->setSeriesId($seriesEditorSubmission->getSeriesId());
			$monograph->setStatus($seriesEditorSubmission->getStatus());
			$monograph->setDateStatusModified($seriesEditorSubmission->getDateStatusModified());
			$monograph->setLastModified($seriesEditorSubmission->getLastModified());
			$monograph->setCommentsStatus($seriesEditorSubmission->getCommentsStatus());

			parent::updateObject($monograph);
		}
	}
}

?>
