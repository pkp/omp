<?php

/**
 * @file classes/submission/seriesEditor/SeriesEditorSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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

// Bring in editor decision constants
import('classes.submission.author.AuthorSubmission');
import('classes.submission.reviewer.ReviewerSubmission');

class SeriesEditorSubmissionDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $userDao;
	var $reviewAssignmentDao;
	var $copyeditorSubmissionDao;
	var $monographFileDao;
	var $signoffDao;
	var $galleyDao;
	var $monographEmailLogDao;
	var $monographCommentDao;

	/**
	 * Constructor.
	 */
	function SeriesEditorSubmissionDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$this->copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$this->monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
	}

	/**
	 * Retrieve an series editor submission by monograph ID.
	 * @param $monographId int
	 * @return EditorSubmission
	 */
	function &getSeriesEditorSubmission($monographId) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev,
				rr.review_revision AS review_revision
			FROM	monographs m
				LEFT JOIN series s ON (s.series_id = m.series_id)
				LEFT JOIN review_rounds rr ON (m.monograph_id = rr.submission_id AND m.current_review_type = rr.review_type AND m.current_round = rr.round)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	m.monograph_id = ?',
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$monographId
			)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

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
	function &_fromRow(&$row) {
		$seriesEditorSubmission = $this->newDataObject();

		// Monograph attributes
		$this->monographDao->_monographFromRow($seriesEditorSubmission, $row);

		$reviewRoundsInfo =& $this->monographDao->getReviewRoundsInfoById($row['monograph_id']);

		// Editor Decisions
		foreach ( $reviewRoundsInfo as $reviewType => $currentReviewRound) {
			for ($i = 1; $i <= $currentReviewRound; $i++) {
				$seriesEditorSubmission->setDecisions($this->getEditorDecisions($row['monograph_id'], $reviewType, $i), $reviewType, $i);
			}
		}

		// Comments
		$seriesEditorSubmission->setMostRecentEditorDecisionComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_EDITOR_DECISION, $row['monograph_id']));
		$seriesEditorSubmission->setMostRecentCopyeditComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_COPYEDIT, $row['monograph_id']));
		$seriesEditorSubmission->setMostRecentLayoutComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_LAYOUT, $row['monograph_id']));
		$seriesEditorSubmission->setMostRecentProofreadComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_PROOFREAD, $row['monograph_id']));

		// Files
		$seriesEditorSubmission->setSubmissionFile($this->monographFileDao->getMonographFile($row['submission_file_id']));
		$seriesEditorSubmission->setRevisedFile($this->monographFileDao->getMonographFile($row['revised_file_id']));
		$seriesEditorSubmission->setReviewFile($this->monographFileDao->getMonographFile($row['review_file_id']));
		$seriesEditorSubmission->setEditorFile($this->monographFileDao->getMonographFile($row['editor_file_id']));

		foreach ( $reviewRoundsInfo as $reviewType => $currentReviewRound) {
			for ($i = 1; $i <= $currentReviewRound; $i++) {
				$seriesEditorSubmission->setEditorFileRevisions($this->monographFileDao->getMonographFileRevisions($row['editor_file_id'], $reviewType, $i), $reviewType, $i);
				$seriesEditorSubmission->setAuthorFileRevisions($this->monographFileDao->getMonographFileRevisions($row['revised_file_id'], $reviewType, $i), $reviewType, $i);
			}
		}

		// Review Rounds
		$seriesEditorSubmission->setReviewRevision($row['review_revision']);

		// Review Assignments
		foreach ( $reviewRoundsInfo as $reviewType => $currentReviewRound) {
			for ($i = 1; $i <= $currentReviewRound; $i++) {
				$seriesEditorSubmission->setReviewAssignments($this->reviewAssignmentDao->getBySubmissionId($row['monograph_id'], $i, $reviewType), $reviewType, $i);
			}
		}

		// Proof Assignment

		HookRegistry::call('SeriesEditorSubmissionDAO::_fromRow', array(&$seriesEditorSubmission, &$row));

		return $seriesEditorSubmission;
	}

	/**
	 * Update an existing series editorsubmission.
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 */
	function updateSeriesEditorSubmission(&$seriesEditorSubmission) {
		$reviewRounds = $seriesEditorSubmission->getReviewRoundsInfo();

		// Update editor decisions
		foreach ($reviewRounds as $reviewType => $round) {
		for ($i = 1; $i <= $round; $i++) {
			$editorDecisions = $seriesEditorSubmission->getDecisions($reviewType, $i);
			if (is_array($editorDecisions)) {
				foreach ($editorDecisions as $editorDecision) {
					if ($editorDecision['editDecisionId'] == null) {
						$this->update(
							sprintf(
								'INSERT INTO edit_decisions
								(monograph_id, review_type, round, editor_id, decision, date_decided)
								VALUES (?, ?, ?, ?, ?, %s)',
								$this->datetimeToDB($editorDecision['dateDecided'])
							),
							array(
								$seriesEditorSubmission->getId(),
								$reviewType,
								$i,
								$editorDecision['editorId'],
								$editorDecision['decision']
							)
						);
					}
				}
			}
		}
		}
		$round = $seriesEditorSubmission->getCurrentRound();
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');

		$reviewType = $seriesEditorSubmission->getCurrentReviewType();

		if (isset($reviewType)) {
			$reviewRound = $reviewRoundDao->build(
							$seriesEditorSubmission->getId(),
							$seriesEditorSubmission->getCurrentReviewType(),
							$round == null ? 1 : $round
						);
			if ($seriesEditorSubmission->getReviewRevision() != null) {
				$reviewRound->setReviewRevision($seriesEditorSubmission->getReviewRevision());
				$reviewRoundDao->updateObject($reviewRound);
			}
		}

		// update review assignments
		$removedReviewAssignments =& $seriesEditorSubmission->getRemovedReviewAssignments();

		foreach ($reviewRounds as $reviewType => $round) {
			for ($i = 1; $i <= $round; $i++) {
				foreach ($seriesEditorSubmission->getReviewAssignments($reviewType, $i) as $reviewAssignment) {
					if (isset($removedReviewAssignments[$reviewAssignment->getReviewId()])) continue;

					if ($reviewAssignment->getReviewId() > 0) {
						$this->reviewAssignmentDao->updateObject($reviewAssignment);
					} else {
						$this->reviewAssignmentDao->insertObject($reviewAssignment);
					}
				}
			}
		}

		// Remove deleted review assignments
		foreach ($removedReviewAssignments as $removedReviewAssignmentId) {
			$this->reviewAssignmentDao->deleteById($removedReviewAssignmentId);
		}

		// Update monograph
		if ($seriesEditorSubmission->getId()) {

			$monograph =& $this->monographDao->getMonograph($seriesEditorSubmission->getId());

			// Only update fields that can actually be edited.
			$monograph->setSeriesId($seriesEditorSubmission->getSeriesId());
			$monograph->setCurrentRound($seriesEditorSubmission->getCurrentRound());
			$monograph->setCurrentReviewType($seriesEditorSubmission->getCurrentReviewType());
			$monograph->setReviewFileId($seriesEditorSubmission->getReviewFileId());
			$monograph->setEditorFileId($seriesEditorSubmission->getEditorFileId());
			$monograph->setStatus($seriesEditorSubmission->getStatus());
			$monograph->setDateStatusModified($seriesEditorSubmission->getDateStatusModified());
			$monograph->setLastModified($seriesEditorSubmission->getLastModified());
			$monograph->setCommentsStatus($seriesEditorSubmission->getCommentsStatus());

			$this->monographDao->updateMonograph($monograph);
		}

	}

	/**
	 * Get all series editorsubmissions for an series editor.
	 * @param $seriesEditorId int
	 * @param $status boolean true if active, false if completed.
	 * @return array SeriesEditorSubmission
	 */
	function &getSeriesEditorSubmissions($seriesEditorId, $pressId, $status = true) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();

		$seriesEditorSubmissions = array();

		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev,
				rr.review_revision AS review_revision
			FROM	monographs m
				LEFT JOIN edit_assignments e ON (e.monograph_id = m.monograph_id)
				LEFT JOIN series s ON (s.series_id = m.series_id)
				LEFT JOIN review_rounds r2 ON (m.monograph_id = r2.submission_id AND m.current_round = r2.round)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	m.press_id = ?
				AND e.editor_id = ?
				AND m.status = ?',
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$pressId,
				$seriesEditorId,
				$status
			)
		);

		while (!$result->EOF) {
			$seriesEditorSubmissions[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $seriesEditorSubmissions;
	}

	/**
	 * Retrieve unfiltered series editor submissions
	 */
	function &_getUnfilteredSeriesEditorSubmissions($seriesEditorId, $pressId, $seriesId = 0, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $additionalWhereSql = '', $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();

		$params = array(
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_COPYEDITING_FINAL',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_PROOFREADING_PROOFREADER',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_LAYOUT',
			'title', // Series title (primary locale)
			$primaryLocale,
			'title', // Series title (current locale)
			$locale,
			'abbrev', // Series abbrev (primary locale)
			$primaryLocale,
			'abbrev', // Series abbrev (current locale)
			$locale,
			'title', // Monograph title (monograph locale)
			'title', // Monograph title (current locale)
			$locale,
			$pressId,
			$seriesEditorId
		);

		$searchSql = '';

		if (!empty($search)) switch ($searchField) {
			case SUBMISSION_FIELD_TITLE:
				if ($searchMatch === 'is') {
					$searchSql = ' AND LOWER(atl.setting_value) = LOWER(?)';
				} elseif ($searchMatch === 'contains') {
					$searchSql = ' AND LOWER(atl.setting_value) LIKE LOWER(?)';
					$search = '%' . $search . '%';
				} else { // $searchMatch === 'startsWith'
					$searchSql = ' AND LOWER(atl.setting_value) LIKE LOWER(?)';
					$search = '%' . $search . '%';
				}
				$params[] = $search;
				break;
			case SUBMISSION_FIELD_AUTHOR:
				$first_last = $this->_dataSource->Concat('aa.first_name', '\' \'', 'aa.last_name');
				$first_middle_last = $this->_dataSource->Concat('aa.first_name', '\' \'', 'aa.middle_name', '\' \'', 'aa.last_name');
				$last_comma_first = $this->_dataSource->Concat('aa.last_name', '\', \'', 'aa.first_name');
				$last_comma_first_middle = $this->_dataSource->Concat('aa.last_name', '\', \'', 'aa.first_name', '\' \'', 'aa.middle_name');

				if ($searchMatch === 'is') {
					$searchSql = " AND (LOWER(aa.last_name) = LOWER(?) OR LOWER($first_last) = LOWER(?) OR LOWER($first_middle_last) = LOWER(?) OR LOWER($last_comma_first) = LOWER(?) OR LOWER($last_comma_first_middle) = LOWER(?))";
				} elseif ($searchMatch === 'contains') {
					$searchSql = " AND (LOWER(aa.last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
					$search = '%' . $search . '%';
				} else { // $searchMatch === 'startsWith
					$searchSql = " AND (LOWER(aa.last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
					$search = $search . '%';
				}
				$params[] = $params[] = $params[] = $params[] = $params[] = $search;
				break;
			case SUBMISSION_FIELD_EDITOR:
				$first_last = $this->_dataSource->Concat('ed.first_name', '\' \'', 'ed.last_name');
				$first_middle_last = $this->_dataSource->Concat('ed.first_name', '\' \'', 'ed.middle_name', '\' \'', 'ed.last_name');
				$last_comma_first = $this->_dataSource->Concat('ed.last_name', '\', \'', 'ed.first_name');
				$last_comma_first_middle = $this->_dataSource->Concat('ed.last_name', '\', \'', 'ed.first_name', '\' \'', 'ed.middle_name');
				if ($searchMatch === 'is') {
					$searchSql = " AND (LOWER(ed.last_name) = LOWER(?) OR LOWER($first_last) = LOWER(?) OR LOWER($first_middle_last) = LOWER(?) OR LOWER($last_comma_first) = LOWER(?) OR LOWER($last_comma_first_middle) = LOWER(?))";
				} elseif ($searchMatch === 'contains') {
					$searchSql = " AND (LOWER(ed.last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
					$search = '%' . $search . '%';
				} else { // $searchMatch === 'startsWith'
					$searchSql = " AND (LOWER(ed.last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
					$search = $search . '%';
				}
				$params[] = $params[] = $params[] = $params[] = $params[] = $search;
				break;
		}

		if (!empty($dateFrom) || !empty($dateTo)) switch($dateField) {
			case SUBMISSION_FIELD_DATE_SUBMITTED:
				if (!empty($dateFrom)) {
					$searchSql .= ' AND m.date_submitted >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= ' AND m.date_submitted <= ' . $this->datetimeToDB($dateTo);
				}
				break;
			case SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE:
				if (!empty($dateFrom)) {
					$searchSql .= ' AND c.date_final_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= ' AND c.date_final_completed <= ' . $this->datetimeToDB($dateTo);
				}
				break;
			case SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE:
				if (!empty($dateFrom)) {
					$searchSql .= ' AND l.date_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= ' AND l.date_completed <= ' . $this->datetimeToDB($dateTo);
				}
				break;
			case SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE:
				if (!empty($dateFrom)) {
					$searchSql .= ' AND p.date_proofreader_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= 'AND p.date_proofreader_completed <= ' . $this->datetimeToDB($dateTo);
				}
				break;
		}

		$sql = 'SELECT DISTINCT
				m.*,
				scf.date_completed as copyedit_completed,
				spr.date_completed as proofread_completed,
				sle.date_completed as layout_completed,
				COALESCE(atl.setting_value, atpl.setting_value) AS submission_title,
				aap.last_name AS author_name,
				e.can_review AS can_review,
				e.can_edit AS can_edit,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev,
				r2.review_revision
			FROM	monographs m
				LEFT JOIN authors aa ON (aa.submission_id = m.monograph_id)
				LEFT JOIN authors aap ON (aap.submission_id = m.monograph_id AND aap.primary_contact = 1)
				LEFT JOIN edit_assignments e ON (e.monograph_id = m.monograph_id)
				LEFT JOIN users ed ON (e.editor_id = ed.user_id)
				LEFT JOIN series s ON (s.series_id = m.series_id)
				LEFT JOIN signoffs scf ON (m.monograph_id = scf.assoc_id AND scf.assoc_type = ? AND scf.symbolic = ?)
				LEFT JOIN users ce ON (scf.user_id = ce.user_id)
				LEFT JOIN signoffs spr ON (m.monograph_id = spr.assoc_id AND spr.assoc_type = ? AND spr.symbolic = ?)
				LEFT JOIN users pe ON (pe.user_id = spr.user_id)
				LEFT JOIN review_rounds r2 ON (m.monograph_id = r2.submission_id and m.current_review_type = r2.review_type AND m.current_round = r2.round)
				LEFT JOIN signoffs sle ON (m.monograph_id = sle.assoc_id AND sle.assoc_type = ? AND sle.symbolic = ?) LEFT JOIN users le ON (le.user_id = sle.user_id)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN monograph_settings atpl ON (atpl.monograph_id = m.monograph_id AND atpl.setting_name = ? AND atpl.locale = m.locale)
				LEFT JOIN monograph_settings atl ON (m.monograph_id = atl.monograph_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN edit_decisions edec ON (m.monograph_id = edec.monograph_id)
				LEFT JOIN edit_decisions edec2 ON (m.monograph_id = edec2.monograph_id AND edec.edit_decision_id < edec2.edit_decision_id)
			WHERE	m.press_id = ?
				AND e.editor_id = ?
				AND m.submission_progress = 0' . (!empty($additionalWhereSql)?" AND ($additionalWhereSql)":'') . '
				AND edec2.edit_decision_id IS NULL';

		if ($seriesId) {
			$params[] = $seriesId;
			$searchSql .= ' AND m.series_id = ?';
		}

		$result =& $this->retrieveRange($sql . ' ' . $searchSql . ($sortBy?(' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : ''),
			$params,
			$rangeInfo
		);

		return $result;
	}

	/**
	 * Get all submissions in review for a press.
	 * @param $pressId int
	 * @param $seriesId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains" or "startsWith"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getSeriesEditorSubmissionsInReview($seriesEditorId, $pressId, $seriesId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$result =& $this->_getUnfilteredSeriesEditorSubmissions(
			$seriesEditorId, $pressId, $seriesId,
			$searchField, $searchMatch, $search,
			$dateField, $dateFrom, $dateTo,
			'm.status = ' . STATUS_QUEUED . ' AND e.can_review = 1 AND (edec.decision IS NULL OR edec.decision <> ' . SUBMISSION_EDITOR_DECISION_ACCEPT . ')',
			$rangeInfo, $sortBy, $sortDirection
		);
		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;

	}

	/**
	 * Get all submissions in editing for a press.
	 * @param $pressId int
	 * @param $seriesId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains" or "startsWith"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getSeriesEditorSubmissionsInEditing($seriesEditorId, $pressId, $seriesId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$result =& $this->_getUnfilteredSeriesEditorSubmissions(
			$seriesEditorId, $pressId, $seriesId,
			$searchField, $searchMatch, $search,
			$dateField, $dateFrom, $dateTo,
			'm.status = ' . STATUS_QUEUED . ' AND e.can_edit = 1 AND edec.decision = ' . SUBMISSION_EDITOR_DECISION_ACCEPT,
			$rangeInfo, $sortBy, $sortDirection
		);
		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get all submissions in archives for a press.
	 * @param $pressId int
	 * @param $seriesId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains" or "startsWith"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getSeriesEditorSubmissionsArchives($seriesEditorId, $pressId, $seriesId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$result = $this->_getUnfilteredSeriesEditorSubmissions(
			$seriesEditorId, $pressId, $seriesId,
			$searchField, $searchMatch, $search,
			$dateField, $dateFrom, $dateTo,
			'm.status <> ' . STATUS_QUEUED,
			$rangeInfo, $sortBy, $sortDirection
		);
		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Function used for counting purposes for right nav bar
	 */
	function &getSeriesEditorSubmissionsCount($seriesEditorId, $pressId) {

		$submissionsCount = array();
		for($i = 0; $i < 2; $i++) {
			$submissionsCount[$i] = 0;
		}

		// Fetch a count of submissions in review.
		// "d2" and "d" are used to fetch the single most recent
		// editor decision.
		$result =& $this->retrieve(
			'SELECT	COUNT(*) AS review_count
			FROM	monographs m
				LEFT JOIN edit_assignments e ON (m.monograph_id = e.monograph_id)
				LEFT JOIN edit_decisions d ON (m.monograph_id = d.monograph_id)
				LEFT JOIN edit_decisions d2 ON (m.monograph_id = d2.monograph_id AND d.edit_decision_id < d2.edit_decision_id)
			WHERE	m.press_id = ?
				AND e.editor_id = ?
				AND m.submission_progress = 0
				AND m.status = ' . STATUS_QUEUED . '
				AND d2.edit_decision_id IS NULL
				AND (d.decision IS NULL OR d.decision <> ' . SUBMISSION_EDITOR_DECISION_ACCEPT . ')',
			array((int) $pressId, (int) $seriesEditorId)
		);
		$submissionsCount[0] = $result->Fields('review_count');
		$result->Close();

		// Fetch a count of submissions in editing.
		// "d2" and "d" are used to fetch the single most recent
		// editor decision.
		$result =& $this->retrieve(
			'SELECT	COUNT(*) AS editing_count
			FROM	monographs m
				LEFT JOIN edit_assignments e ON (m.monograph_id = e.monograph_id)
				LEFT JOIN edit_decisions d ON (m.monograph_id = d.monograph_id)
				LEFT JOIN edit_decisions d2 ON (m.monograph_id = d2.monograph_id AND d.edit_decision_id < d2.edit_decision_id)
			WHERE	m.press_id = ?
				AND e.editor_id = ?
				AND m.submission_progress = 0
				AND m.status = ' . STATUS_QUEUED . '
				AND d2.edit_decision_id IS NULL
				AND d.decision = ' . SUBMISSION_EDITOR_DECISION_ACCEPT,
			array((int) $pressId, (int) $seriesEditorId)
		);
		$submissionsCount[1] = $result->Fields('editing_count');
		$result->Close();
		return $submissionsCount;
	}

	//
	// Miscellaneous
	//

	/**
	 * Delete copyediting assignments by monograph.
	 * @param $monographId int
	 */
	function deleteDecisionsByMonograph($monographId) {
		return $this->update(
			'DELETE FROM edit_decisions WHERE monograph_id = ?',
			$monographId
		);
	}

	/**
	 * Delete review rounds monograph.
	 * @param $monographId int
	 */
	function deleteReviewRoundsByMonograph($monographId) {
		return $this->update(
			'DELETE FROM review_rounds WHERE submission_id = ?',
			$monographId
		);
	}

	/**
	 * Get the editor decisions for a review round of a monograph.
	 * @param $monographId int
	 */
	function getEditorDecisions($monographId, $reviewType = null, $round = null) {
		$decisions = array();

		if ($reviewType == null) {
			$result =& $this->retrieve(
					'SELECT edit_decision_id, editor_id, decision, date_decided, review_type, round
					FROM edit_decisions
					WHERE monograph_id = ?
					ORDER BY date_decided ASC',
					$monographId
				);
		} elseif ($round == null) {
			$result =& $this->retrieve(
					'SELECT edit_decision_id, editor_id, decision, date_decided, review_type, round
					FROM edit_decisions
					WHERE monograph_id = ? AND review_type = ?
					ORDER BY date_decided ASC',
					array($monographId, $reviewType)
				);
		} else {
			$result =& $this->retrieve(
					'SELECT edit_decision_id, editor_id, decision, date_decided, review_type, round
					FROM edit_decisions
					WHERE monograph_id = ? AND review_type = ? AND round = ?
					ORDER BY date_decided ASC',
					array($monographId, $reviewType, $round)
				);
		}

		while (!$result->EOF) {
			$value = array(
					'editDecisionId' => $result->fields['edit_decision_id'],
					'editorId' => $result->fields['editor_id'],
					'decision' => $result->fields['decision'],
					'dateDecided' => $this->datetimeFromDB($result->fields['date_decided'])
				);

			$decisions[] = $value;
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $decisions;
	}

	/**
	 * Get the highest review round.
	 * @param $monographId int
	 * @return int
	 */
	function getMaxReviewRound($monographId, $reviewType) {
		$result =& $this->retrieve(
			'SELECT MAX(round) FROM review_rounds WHERE submission_id = ? AND review_type = ?', array($monographId, $reviewType)
		);
		$returner = isset($result->fields[0]) ? $result->fields[0] : 0;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Check if a reviewer is assigned to a specified monograph.
	 * @param $monographId int
	 * @param $reviewerId int
	 * @return boolean
	 */
	function reviewerExists($monographId, $reviewerId, $reviewType, $round) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM review_assignments WHERE submission_id = ? AND reviewer_id = ? AND review_type = ? AND round = ? AND cancelled = 0', array($monographId, $reviewerId, $reviewType, $round)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve a list of all reviewers along with information about their current status with respect to an monograph's current round.
	 * @param $pressId int
	 * @param $monographId int
	 * @param $round int
	 * @param $searchType int USER_FIELD_...
	 * @param $search string
	 * @param $searchMatch string "is" or "contains" or "startsWith"
	 * @param $rangeInfo RangeInfo optional
	 * @return DAOResultFactory containing matching Users
	 */
	function &getReviewersForMonograph($pressId, $monographId, $reviewType, $round = null, $searchType = null, $search = null, $searchMatch = null, $rangeInfo = null,  $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$paramArray = array($monographId, $reviewType, $round, 'interests', $pressId, RoleDAO::getRoleIdFromPath('reviewer'));
		$searchSql = '';

		$searchTypeMap = array(
			USER_FIELD_FIRSTNAME => 'u.first_name',
			USER_FIELD_LASTNAME => 'u.last_name',
			USER_FIELD_USERNAME => 'u.username',
			USER_FIELD_EMAIL => 'u.email',
			USER_FIELD_INTERESTS => 's.setting_value'
		);

		if (isset($search) && isset($searchTypeMap[$searchType])) {
			$fieldName = $searchTypeMap[$searchType];
			switch ($searchMatch) {
				case 'is':
					$searchSql = "AND LOWER($fieldName) = LOWER(?)";
					$paramArray[] = $search;
					break;
				case 'contains':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = '%' . $search . '%';
					break;
				case 'startsWith':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = $search . '%';
					break;
			}
		} elseif (isset($search)) switch ($searchType) {
			case USER_FIELD_USERID:
				$searchSql = 'AND user_id=?';
				$paramArray[] = $search;
				break;
			case USER_FIELD_INITIAL:
				$searchSql = 'AND (LOWER(last_name) LIKE LOWER(?) OR LOWER(username) LIKE LOWER(?))';
				$paramArray[] = $search . '%';
				$paramArray[] = $search . '%';
				break;
		}

		$result =& $this->retrieveRange(
			'SELECT DISTINCT
				u.user_id,
				u.last_name,
				ar.review_id,
				AVG(a.quality) AS average_quality,
				COUNT(ac.review_id) AS completed,
				COUNT(ai.review_id) AS incomplete,
				MAX(ac.date_notified) AS latest,
				AVG(ac.date_completed-ac.date_notified) AS average
			FROM	users u
			LEFT JOIN review_assignments a ON (a.reviewer_id = u.user_id)
				LEFT JOIN review_assignments ac ON (ac.reviewer_id = u.user_id AND ac.date_completed IS NOT NULL)
				LEFT JOIN review_assignments ai ON (ai.reviewer_id = u.user_id AND ai.date_completed IS NULL)
				LEFT JOIN review_assignments ar ON (ar.reviewer_id = u.user_id AND ar.cancelled = 0 AND ar.submission_id = ? AND ar.review_type = ? AND ar.round = ?)
				LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?)
				LEFT JOIN user_user_groups uug ON (uug.user_id = u.user_id)
				LEFT JOIN user_groups ug ON (ug.user_group_id = uug.user_group_id)
				LEFT JOIN monographs m ON (m.monograph_id = a.submission_id)
			WHERE	ug.press_id = ? AND
				ug.user_group_id = ? ' . $searchSql . 'GROUP BY u.user_id, u.last_name, ar.review_id' .
			($sortBy?(' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : ''),
			$paramArray, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnReviewerUserFromRow');
		return $returner;
	}

	function &_returnReviewerUserFromRow(&$row) { // FIXME
		$user =& $this->userDao->_returnUserFromRowWithData($row);
		if(isset($row['review_id'])) $user->review_id = $row['review_id'];

		HookRegistry::call('SeriesEditorSubmissionDAO::_returnReviewerUserFromRow', array(&$user, &$row));

		return $user;
	}

	/**
	 * Retrieve a list of all reviewers not assigned to the specified monograph.
	 * @param $pressId int
	 * @param $monographId int
	 * @return array matching Users
	 */
	function &getReviewersNotAssignedToMonograph($pressId, $monographId) {

		$result =& $this->retrieve(
			'SELECT	u.*
			FROM	users u
				LEFT JOIN user_user_groups uug ON (uug.user_id = u.user_id)
				LEFT JOIN user_groups ug ON (ug.user_group_id = uug.user_group_id)
				LEFT JOIN review_assignments r ON (r.reviewer_id = u.user_id AND r.submission_id = ?)
			WHERE	ug.press_id = ? AND
				ug.role_id = ? AND
				r.submission_id IS NULL
			ORDER BY last_name, first_name',
			array($monographId, $pressId, ROLE_ID_REVIEWER)
		);

		$returner = new DAOResultFactory($result, $this, '_returnReviewerUserFromRow');
		return $returner;

	}

	/**
	 * Retrieve a list of all reviewers in a press
	 * @param $pressId int
	 * @return array matching Users
	 */
	function &getAllReviewers($pressId) {
		$result =& $this->retrieve(
			'SELECT	u.*
			FROM	users u
				LEFT JOIN user_user_groups uug ON (uug.user_id = u.user_id)
				LEFT JOIN user_groups ug ON (ug.user_group_id = uug.user_group_id)
			WHERE	ug.press_id = ? AND
				ug.role_id = ?
			ORDER BY last_name, first_name',
			array($pressId, ROLE_ID_REVIEWER)
		);

		$returner = new DAOResultFactory($result, $this, '_returnReviewerUserFromRow');
		return $returner;

	}

	/**
	 * Check if a copyeditor is assigned to a specified monograph.
	 * @param $monographId int
	 * @param $copyeditorId int
	 * @return boolean
	 */
	function copyeditorExists($monographId, $copyeditorId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*)
			FROM signoffs
			WHERE assoc_id = ? AND assoc_type = ? AND user_id = ? AND symbolic = ?',
			array($monographId, ASSOC_TYPE_MONOGRAPH, $copyeditorId, 'SIGNOFF_COPYEDITING_INITIAL')
		);
		return isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;
	}

	/**
	 * Retrieve a list of all copyeditors not assigned to the specified monograph.
	 * @param $pressId int
	 * @param $monographId int
	 * @return array matching Users
	 */
	function &getCopyeditorsNotAssignedToMonograph($pressId, $monographId, $searchType = null, $search = null, $searchMatch = null) {
		$users = array();

		$paramArray = array(
				'interests', $monographId, ASSOC_TYPE_MONOGRAPH,
				'SIGNOFF_COPYEDITING_INITIAL',
				$pressId, RoleDAO::getRoleIdFromPath('copyeditor')
				);
		$searchSql = '';

		$searchTypeMap = array(
			USER_FIELD_FIRSTNAME => 'u.first_name',
			USER_FIELD_LASTNAME => 'u.last_name',
			USER_FIELD_USERNAME => 'u.username',
			USER_FIELD_EMAIL => 'u.email',
			USER_FIELD_INTERESTS => 's.setting_value'
		);

		if (isset($search) && isset($searchTypeMap[$searchType])) {
			$fieldName = $searchTypeMap[$searchType];
			switch ($searchMatch) {
				case 'is':
					$searchSql = "AND LOWER($fieldName) = LOWER(?)";
					$paramArray[] = $search;
					break;
				case 'contains':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = '%' . $search . '%';
					break;
				case 'startsWith':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = $search . '%';
					break;
			}
		} elseif (isset($search)) switch ($searchType) {
			case USER_FIELD_USERID:
				$searchSql = 'AND user_id=?';
				$paramArray[] = $search;
				break;
			case USER_FIELD_INITIAL:
				$searchSql = 'AND (LOWER(last_name) LIKE LOWER(?) OR LOWER(username) LIKE LOWER(?))';
				$paramArray[] = $search . '%';
				$paramArray[] = $search . '%';
				break;
		}

		$result =& $this->retrieve(
			'SELECT	u.*
			FROM	users u
				LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?)
				LEFT JOIN roles r ON (r.user_id = u.user_id)
				LEFT JOIN signoffs sci ON (sci.user_id = u.user_id AND sci.assoc_id = ? AND sci.assoc_type = ? AND sci.symbolic = ?)
			WHERE	r.press_id = ? AND
				r.role_id = ? AND
				sci.assoc_id IS NULL
				m.monograph_id IS NULL
				' . $searchSql . '
			ORDER BY last_name, first_name',
			$paramArray
		);

		while (!$result->EOF) {
			$users[] =& $this->userDao->_returnUserFromRowWithData($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $users;
	}

	/**
	 * Get the number of reviews done, avg. number of days per review, days since last review, and num. of
	 * active reviews for all reviewers of the given press.
	 * @return array
	 */
	function getAnonymousReviewerStatistics() {
		// Setup default array -- Minimum values Will always be set to 0 (to accomodate reviewers that have never reviewed, and thus aren't in review_assignment)
		$reviewerValues =  array('doneMin' => 0, // Will always be set to 0
								'doneMax' => 0,
								'avgMin' => 0, // Will always be set to 0
								'avgMax' => 0,
								'lastMin' => 0, // Will always be set to 0
								'lastMax' => 0,
								'activeMin' => 0, // Will always be set to 0
								'activeMax' => 0);

		// Get number of reviews completed
		$result =& $this->retrieve(
			'SELECT r.reviewer_id, COUNT(*) as completed_count
			FROM review_assignments r
			WHERE r.date_completed IS NOT NULL
			GROUP BY r.reviewer_id'
		);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if ($reviewerValues['doneMax'] < $row['completed_count']) $reviewerValues['doneMax'] = $row['completed_count'];
			$result->MoveNext();
		}
		$result->Close();
		unset($result);



		// Get average number of days per review and days since last review
		$result =& $this->retrieve(
			'SELECT r.reviewer_id, r.date_completed, r.date_notified
			FROM review_assignments r
			WHERE r.date_notified IS NOT NULL AND
				r.date_completed IS NOT NULL AND
				r.declined = 0'
		);
		$averageTimeStats = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($averageTimeStats[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = array();

			$completed = strtotime($this->datetimeFromDB($row['date_completed']));
			$notified = strtotime($this->datetimeFromDB($row['date_notified']));
			$timeSinceNotified = time() - $notified;
			if (isset($averageTimeStats[$row['reviewer_id']]['total_span'])) {
				$averageTimeStats[$row['reviewer_id']]['total_span'] += $completed - $notified;
				$averageTimeStats[$row['reviewer_id']]['completed_review_count'] += 1;
			} else {
				$averageTimeStats[$row['reviewer_id']]['total_span'] = $completed - $notified;
				$averageTimeStats[$row['reviewer_id']]['completed_review_count'] = 1;
			}

			// Calculate the average length of review in days.
			$averageTimeStats[$row['reviewer_id']]['average_span'] = (($averageTimeStats[$row['reviewer_id']]['total_span'] / $averageTimeStats[$row['reviewer_id']]['completed_review_count']) / 86400);

			// This reviewer has the highest average; put in global statistics array
			if ($reviewerValues['avgMax'] < $averageTimeStats[$row['reviewer_id']]['average_span']) $reviewerValues['avgMax'] = round($averageTimeStats[$row['reviewer_id']]['average_span']);
			if ($timeSinceNotified > $reviewerValues['lastMax']) $reviewerValues['lastMax'] = $timeSinceNotified;

			$result->MoveNext();
		}
		$reviewerValues['lastMax'] = round($reviewerValues['lastMax'] / 86400); // Round to nearest day
		$result->Close();
		unset($result);


		// Get number of currently active reviews
		$result =& $this->retrieve(
			'SELECT r.reviewer_id, COUNT(*) AS incomplete
			FROM review_assignments r
			WHERE r.date_notified IS NOT NULL AND
				r.date_completed IS NULL AND
				r.cancelled = 0
			GROUP BY r.reviewer_id'
		);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);

			if ($row['incomplete'] > $reviewerValues['activeMax']) $reviewerValues['activeMax'] = $row['incomplete'];
			$result->MoveNext();
		}
		$result->Close();
		unset($result);


		return $reviewerValues;
	}

	/**
	 * Get the assignment counts and last assigned date for all designers of the given press.
	 * @return array
	 */
	function getDesignerStatistics($pressId) {
		$statistics = Array();

		// Get counts of completed submissions
		$result =& $this->retrieve(
			'SELECT	sc.user_id, COUNT(sc.assoc_id) AS complete
			FROM	signoffs sc,
				monographs m
			WHERE	sc.assoc_id = m.monograph_id AND
				sc.date_completed IS NOT NULL AND
				m.press_id = ? AND
				sc.symbolic = ? AND
				sc.assoc_type = ?
			GROUP BY sc.user_id',
			array((int) $pressId, 'PRODUCTION_DESIGN', ASSOC_TYPE_PRODUCTION_ASSIGNMENT)
		);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['user_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['user_id']]['complete'] = $row['complete'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Get counts of incomplete submissions
		$result =& $this->retrieve(
			'SELECT	sc.user_id, COUNT(sc.assoc_id) AS incomplete
			FROM	signoffs sc,
				monographs m
			WHERE	sc.assoc_id = m.monograph_id AND
				sc.date_completed IS NULL AND
				m.press_id = ? AND
				sc.symbolic = ? AND
				sc.assoc_type = ?
			GROUP BY sc.user_id',
			array((int) $pressId, 'PRODUCTION_DESIGN', ASSOC_TYPE_PRODUCTION_ASSIGNMENT)
		);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['user_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['user_id']]['incomplete'] = $row['incomplete'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Get last assignment date
		$result =& $this->retrieve(
			'SELECT	sc.user_id, MAX(sc.date_notified) AS last_assigned
			FROM	signoffs sc, monographs m
			WHERE	sc.assoc_id = m.monograph_id AND
				m.press_id = ? AND
				sc.symbolic = ? AND
				sc.assoc_type = ?
			GROUP BY sc.user_id',
			array((int) $pressId, 'PRODUCTION_DESIGN', ASSOC_TYPE_PRODUCTION_ASSIGNMENT)
		);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['user_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['user_id']]['last_assigned'] = $this->datetimeFromDB($row['last_assigned']);
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		return $statistics;
	}

	/**
	 * Given the ranges selected by the editor, produce a filtered list of reviewers
	 * @param int $pressId
	 * @param int $doneMin # of reviews completed
	 * @param int $doneMax
	 * @param int $avgMin Average period of time in days to complete a review
	 * @param int $avgMax
	 * @param int $lastMin Days since most recently completed review
	 * @param int $lastMax
	 * @param int $activeMin How many reviews are currently being considered or underway
	 * @param int $activeMax
	 * @param array $interests
	 * @return array Users
	 */
	function getFilteredReviewers($pressId, $doneMin, $doneMax, $avgMin, $avgMax, $lastMin, $lastMax, $activeMin, $activeMax, $interests) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$interestDao =& DAORegistry::getDAO('InterestDAO');
		$reviewerStats = $this->getReviewerStatistics($pressId);

		// Get userIds that match all interests
		$allInterestIds = array();
		if(isset($interests)) {
			foreach ($interests as $key => $interest) {
				$interestIds = $interestDao->getUserIdsByInterest($interest);
				if ($key == 0) $allInterestIds = $interestIds;
				else $allInterestIds = array_intersect($allInterestIds, $interestIds);
			}
		}

		$filteredReviewers = array();
		foreach ($reviewerStats as $userId => $reviewerStat) {
			// Get the days since the user was last notified for a review
			if(!isset($reviewerStat['last_notified'])) {
				$lastNotifiedInDays = 0;
			} else {
				$lastNotifiedInDays = round((time() - strtotime($reviewerStat['last_notified'])) / 86400);
			}

			// If there are interests to check, make sure user is in allInterestIds array
			if(!empty($allInterestIds)) {
				$interestCheck = in_array($userId, $allInterestIds);
			} else $interestCheck = true;

			if ($interestCheck && $reviewerStat['completed_review_count'] <= $doneMax && $reviewerStat['completed_review_count'] >= $doneMin &&
				$reviewerStat['average_span'] <= $avgMax && $reviewerStat['average_span'] >= $avgMin && $lastNotifiedInDays <= $lastMax  &&
				$lastNotifiedInDays >= $lastMin && $reviewerStat['incomplete'] <= $activeMax && $reviewerStat['incomplete'] >= $activeMin) {
					$filteredReviewers[] = $userDao->getUser($userId);
				}
		}

		return $filteredReviewers;
	}

	/**
	 * Get the last assigned and last completed dates for all reviewers of the given press.
	 * @return array
	 */
	function getReviewerStatistics($pressId) {
		// Build an array of all reviewers and provide a placeholder for all statistics (so even if they don't
		//  have a value, it will be filled in as 0
		$statistics = Array();
		$reviewerStatsPlaceholder = array('last_notified' => null, 'incomplete' => 0, 'total_span' => 0, 'completed_review_count' => 0, 'average_span' => 0);

		$allReviewers =& $this->getAllReviewers($pressId);
		while($reviewer =& $allReviewers->next()) {
				$statistics[$reviewer->getId()] = $reviewerStatsPlaceholder;
			unset($reviewer);
		}

		// Get counts of completed submissions
		$result =& $this->retrieve(
			'SELECT	r.reviewer_id, MAX(r.date_notified) AS last_notified
			FROM	review_assignments r, monographs m
			WHERE	r.submission_id = m.monograph_id AND
				m.press_id = ?
			GROUP BY r.reviewer_id',
			(int) $pressId
		);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = $reviewerStatsPlaceholder;
			$statistics[$row['reviewer_id']]['last_notified'] = $this->datetimeFromDB($row['last_notified']);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Get completion status
		$result =& $this->retrieve(
			'SELECT	r.reviewer_id, COUNT(*) AS incomplete
			FROM	review_assignments r, monographs m
			WHERE	r.submission_id = m.monograph_id AND
				r.date_notified IS NOT NULL AND
				r.date_completed IS NULL AND
				r.cancelled = 0 AND
				m.press_id = ?
			GROUP BY r.reviewer_id',
			(int) $pressId
		);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = $reviewerStatsPlaceholder;
			$statistics[$row['reviewer_id']]['incomplete'] = $row['incomplete'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Calculate time taken for completed reviews
		$result =& $this->retrieve(
			'SELECT	r.reviewer_id, r.date_notified, r.date_completed
			FROM	review_assignments r, monographs m
			WHERE	r.submission_id = m.monograph_id AND
				r.date_notified IS NOT NULL AND
				r.date_completed IS NOT NULL AND
				r.declined = 0 AND
				m.press_id = ?',
			(int) $pressId
		);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = $reviewerStatsPlaceholder;

			$completed = strtotime($this->datetimeFromDB($row['date_completed']));
			$notified = strtotime($this->datetimeFromDB($row['date_notified']));
			if (isset($statistics[$row['reviewer_id']]['total_span'])) {
				$statistics[$row['reviewer_id']]['total_span'] += $completed - $notified;
				$statistics[$row['reviewer_id']]['completed_review_count'] += 1;
			} else {
				$statistics[$row['reviewer_id']]['total_span'] = $completed - $notified;
				$statistics[$row['reviewer_id']]['completed_review_count'] = 1;
			}

			// Calculate the average length of review in days.
			$statistics[$row['reviewer_id']]['average_span'] = round(($statistics[$row['reviewer_id']]['total_span'] / $statistics[$row['reviewer_id']]['completed_review_count']) / 86400);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $statistics;
	}

	/**
	 * Get the assignment counts and last assigned date for all copyeditors of the given press.
	 * @return array
	 */
	function getCopyeditorStatistics($pressId) {
		$statistics = Array();

		// Get counts of completed submissions
		$result =& $this->retrieve(
				'SELECT sc.user_id AS editor_id, COUNT(sc.assoc_id) AS complete
				FROM signoffs sc, monographs m
				WHERE sc.assoc_id = m.monograph_id AND
					sc.date_completed IS NOT NULL AND
					m.press_id = ? AND
					sc.symbolic = ? AND
					sc.assoc_type = ?
				GROUP BY sc.user_id',
				array($pressId, 'SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH)
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['editor_id']]['complete'] = $row['complete'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Get counts of incomplete submissions
		$result =& $this->retrieve(
				'SELECT sc.user_id AS editor_id, COUNT(sc.assoc_id) AS incomplete
				FROM signoffs sc, monographs m
				WHERE sc.assoc_id = m.monograph_id AND
					sc.date_completed IS NULL AND
					m.press_id = ? AND
					sc.symbolic = ? AND
					sc.assoc_type = ?
				GROUP BY sc.user_id',
				array($pressId, 'SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH)
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['editor_id']]['incomplete'] = $row['incomplete'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Get last assignment date
		$result =& $this->retrieve(
				'SELECT sc.user_id AS editor_id, MAX(sc.date_notified) AS last_assigned
				FROM signoffs sc, monographs m
				WHERE sc.assoc_id = m.monograph_id AND
					m.press_id = ? AND
					sc.symbolic = ? AND
					sc.assoc_type = ?
				GROUP BY sc.user_id',
				array($pressId, 'SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH)
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['editor_id']]['last_assigned'] = $this->datetimeFromDB($row['last_assigned']);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $statistics;
	}

	/**
	 * Get the assignment counts and last assigned date for all proofreaders of the given press.
	 * @return array
	 */
	function getProofreaderStatistics($pressId) {
		$statistics = Array();

		// Get counts of completed submissions
		$result =& $this->retrieve(
				'SELECT sc.user_id AS editor_id, COUNT(sc.assoc_id) AS complete
				FROM signoffs sc, monographs m
				WHERE sc.assoc_id = m.monograph_id AND
					sc.date_completed IS NOT NULL AND
					m.press_id = ? AND
					sc.symbolic = ? AND
					sc.assoc_type = ?
				GROUP BY sc.user_id',
				array($pressId, 'PRODUCTION_PROOF_PROOFREADER', ASSOC_TYPE_PRODUCTION_ASSIGNMENT)
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['editor_id']]['complete'] = $row['complete'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Get counts of incomplete submissions
		$result =& $this->retrieve(
				'SELECT sc.user_id AS editor_id, COUNT(sc.assoc_id) AS incomplete
				FROM signoffs sc, monographs m
				WHERE sc.assoc_id = m.monograph_id AND
					sc.date_completed IS NULL AND
					m.press_id = ? AND
					sc.symbolic = ? AND
					sc.assoc_type = ?
				GROUP BY sc.user_id',
				array($pressId, 'PRODUCTION_PROOF_PROOFREADER', ASSOC_TYPE_PRODUCTION_ASSIGNMENT)
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['editor_id']]['incomplete'] = $row['incomplete'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Get last assignment date
		$result =& $this->retrieve(
				'SELECT sc.user_id AS editor_id, MAX(sc.date_notified) AS last_assigned
				FROM signoffs sc, monographs m
				WHERE sc.assoc_id = m.monograph_id AND
					m.press_id = ? AND
					sc.symbolic = ? AND
					sc.assoc_type = ?
				GROUP BY sc.user_id',
				array($pressId, 'PRODUCTION_PROOF_PROOFREADER', ASSOC_TYPE_PRODUCTION_ASSIGNMENT)
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['editor_id']]['last_assigned'] = $this->datetimeFromDB($row['last_assigned']);
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		return $statistics;
	}

	/**
	 * Map a column heading value to a database value for sorting
	 * @param string
	 * @return string
	 */
	function getSortMapping($heading) {
		switch ($heading) {
			case 'id': return 'm.monograph_id';
			case 'submitDate': return 'm.date_submitted';
			case 'series': return 'series_abbrev';
			case 'authors': return 'author_name';
			case 'title': return 'submission_title';
			case 'active': return 'incomplete';
			case 'subCopyedit': return 'copyedit_completed';
			case 'subLayout': return 'layout_completed';
			case 'subProof': return 'proofread_completed';
			case 'reviewerName': return 'u.last_name';
			case 'quality': return 'average_quality';
			case 'done': return 'completed';
			case 'latest': return 'latest';
			case 'active': return 'active';
			case 'average': return 'average';

			default: return null;
		}
	}
}

?>
