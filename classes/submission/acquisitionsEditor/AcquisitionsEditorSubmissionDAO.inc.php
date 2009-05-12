<?php

/**
 * @file classes/submission/sectionEditor/AcquisitionsEditorSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcquisitionsEditorSubmissionDAO
 * @ingroup submission
 * @see AcquisitionsEditorSubmission
 *
 * @brief Operations for retrieving and modifying AcquisitionsEditorSubmission objects.
 */

// $Id$


import('submission.acquisitionsEditor.AcquisitionsEditorSubmission');
import('submission.author.AuthorSubmission'); // Bring in editor decision constants
import('submission.reviewer.ReviewerSubmission'); // Bring in editor decision constants

class AcquisitionsEditorSubmissionDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $userDao;
	var $editAssignmentDao;
	var $reviewAssignmentDao;
	var $copyeditorSubmissionDao;
	var $layoutAssignmentDao;
	var $monographFileDao;
	var $suppFileDao;
	var $galleyDao;
	var $monographEmailLogDao;
	var $monographCommentDao;
	var $proofAssignmentDao;

	/**
	 * Constructor.
	 */
	function AcquisitionsEditorSubmissionDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$this->reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$this->copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
//		$this->monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
//		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
	}

	/**
	 * Retrieve a section editor submission by monograph ID.
	 * @param $monographId int
	 * @return EditorSubmission
	 */
	function &getAcquisitionsEditorSubmission($monographId) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieve(
			'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS arrangement_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS arrangement_abbrev,
				MAX(rr.round) AS current_round,
				rr.review_revision AS review_revision
			FROM	monographs a
				LEFT JOIN acquisitions_arrangements s ON (s.arrangement_id = a.arrangement_id)
				LEFT JOIN review_rounds rr ON (a.monograph_id = rr.monograph_id AND a.current_review = rr.review_type)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	a.monograph_id = ?
			GROUP BY a.monograph_id',
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
	 * @return AcquisitionsEditorSubmission
	 */
	function newDataObject() {
		return new AcquisitionsEditorSubmission();
	}

	/**
	 * Internal function to return a AcquisitionsEditorSubmission object from a row.
	 * @param $row array
	 * @return AcquisitionsEditorSubmission
	 */
	function &_fromRow(&$row) {
		$acquisitionsEditorSubmission = $this->newDataObject();

		// Monograph attributes
		$this->monographDao->_monographFromRow($acquisitionsEditorSubmission, $row);

		// Editor Assignment
		$editAssignments =& $this->editAssignmentDao->getByMonographId($row['monograph_id']);
		$acquisitionsEditorSubmission->setEditAssignments($editAssignments->toArray());

		$reviewRounds =& $this->monographDao->getReviewRoundsInfoById($row['monograph_id']);

		$acquisitionsEditorSubmission->setReviewRoundsInfo($reviewRounds);

/*		$workflowDao =& DAORegistry::getDAO('WorkflowDAO');
		$currentReviewProcess = $workflowDao->getCurrent($row['monograph_id'], WORKFLOW_PROCESS_ASSESSMENT);

		$currentReviewType = isset($currentReviewProcess) ? $currentReviewProcess->getProcessId() : null;



		$currentReviewRound = isset($currentReviewProcess) && isset($reviewRounds[$currentReviewProcess->getProcessId()]) ? 
						$reviewRounds[$currentReviewProcess->getProcessId()] : null;

		$acquisitionsEditorSubmission->setCurrentReviewType($currentReviewType);
		$acquisitionsEditorSubmission->setCurrentReviewRound($currentReviewRound);
*/
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');

		$reviewType = $acquisitionsEditorSubmission->getCurrentReviewType();
		$round = isset($reviewRounds[$reviewType]) ? $reviewRounds[$reviewType] : null;

		if (isset($reviewType)) {
			$reviewRound = $reviewRoundDao->build(
							$acquisitionsEditorSubmission->getMonographId(), 
							$acquisitionsEditorSubmission->getCurrentReviewType(), 
							$round == null ? 1 : $round,
							1
						);
			if ($acquisitionsEditorSubmission->getReviewRevision() != null) {
				$reviewRound->setReviewRevision($acquisitionsEditorSubmission->getReviewRevision());
				$reviewRoundDao->updateObject($reviewRound);
			}
		}
	
		// Editor Decisions
		$decisions =& $this->getEditorDecisions($row['monograph_id']);
		$acquisitionsEditorSubmission->setDecisions($decisions);

		// Comments
//		$acquisitionsEditorSubmission->setMostRecentEditorDecisionComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_EDITOR_DECISION, $row['monograph_id']));
//		$acquisitionsEditorSubmission->setMostRecentCopyeditComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_COPYEDIT, $row['monograph_id']));
//		$acquisitionsEditorSubmission->setMostRecentLayoutComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_LAYOUT, $row['monograph_id']));
//		$acquisitionsEditorSubmission->setMostRecentProofreadComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_PROOFREAD, $row['monograph_id']));

		// Files
		$acquisitionsEditorSubmission->setSubmissionFile($this->monographFileDao->getMonographFile($row['submission_file_id']));
		$acquisitionsEditorSubmission->setRevisedFile($this->monographFileDao->getMonographFile($row['revised_file_id']));
		$acquisitionsEditorSubmission->setReviewFile($this->monographFileDao->getMonographFile($row['review_file_id']));
		$acquisitionsEditorSubmission->setSuppFiles($this->suppFileDao->getSuppFilesByMonograph($row['monograph_id']));
		$acquisitionsEditorSubmission->setEditorFile($this->monographFileDao->getMonographFile($row['editor_file_id']));
//		$acquisitionsEditorSubmission->setCopyeditFile($this->monographFileDao->getMonographFile($row['copyedit_file_id']));
		$acquisitionsEditorSubmission->setLayoutFile($this->monographFileDao->getMonographFile($row['layout_file_id']));

		$acquisitionsEditorSubmission->setReviewRevision($row['review_revision']);
		// Initial Copyedit File
/*		if ($row['copyeditor_initial_revision'] != null) {

			$acquisitionsEditorSubmission->setInitialCopyeditFile($this->monographFileDao->getMonographFile($row['copyedit_file_id'], $row['copyeditor_initial_revision']));
		}

		// Editor / Author Copyedit File
		if ($row['ce_editor_author_revision'] != null) {
			$acquisitionsEditorSubmission->setEditorAuthorCopyeditFile($this->monographFileDao->getMonographFile($row['copyedit_file_id'], $row['ce_editor_author_revision']));
		}

		// Final Copyedit File
		if ($row['copyeditor_final_revision'] != null) {
			$acquisitionsEditorSubmission->setFinalCopyeditFile($this->monographFileDao->getMonographFile($row['copyedit_file_id'], $row['copyeditor_final_revision']));
		}
*/
		$acquisitionsEditorSubmission->setCopyeditFileRevisions($this->monographFileDao->getMonographFileRevisionsInRange($row['copyedit_file_id']));

		$editorFileRevisions = $this->monographFileDao->getMonographFileRevisions($row['editor_file_id']);
		$authorFileRevisions = $this->monographFileDao->getMonographFileRevisions($row['revised_file_id']);

		$acquisitionsEditorSubmission->setEditorFileRevisions($editorFileRevisions);
		$acquisitionsEditorSubmission->setAuthorFileRevisions($authorFileRevisions);

		// Review Assignments

		$acquisitionsEditorSubmission->setReviewAssignments($this->reviewAssignmentDao->getByMonographId($row['monograph_id'], $reviewType, $round));

		// Copyeditor Assignment
/*		$acquisitionsEditorSubmission->setCopyedId($row['copyed_id']);
		$acquisitionsEditorSubmission->setCopyeditorId($row['copyeditor_id']);
		$acquisitionsEditorSubmission->setCopyeditor($this->userDao->getUser($row['copyeditor_id']), true);
		$acquisitionsEditorSubmission->setCopyeditorDateNotified($this->datetimeFromDB($row['copyeditor_date_notified']));
		$acquisitionsEditorSubmission->setCopyeditorDateUnderway($this->datetimeFromDB($row['copyeditor_date_underway']));
		$acquisitionsEditorSubmission->setCopyeditorDateCompleted($this->datetimeFromDB($row['copyeditor_date_completed']));
		$acquisitionsEditorSubmission->setCopyeditorDateAcknowledged($this->datetimeFromDB($row['copyeditor_date_acknowledged']));
		$acquisitionsEditorSubmission->setCopyeditorDateAuthorNotified($this->datetimeFromDB($row['ce_date_author_notified']));
		$acquisitionsEditorSubmission->setCopyeditorDateAuthorUnderway($this->datetimeFromDB($row['ce_date_author_underway']));
		$acquisitionsEditorSubmission->setCopyeditorDateAuthorCompleted($this->datetimeFromDB($row['ce_date_author_completed']));
		$acquisitionsEditorSubmission->setCopyeditorDateAuthorAcknowledged($this->datetimeFromDB($row['ce_date_author_acknowledged']));
		$acquisitionsEditorSubmission->setCopyeditorDateFinalNotified($this->datetimeFromDB($row['ce_date_final_notified']));
		$acquisitionsEditorSubmission->setCopyeditorDateFinalUnderway($this->datetimeFromDB($row['ce_date_final_underway']));
		$acquisitionsEditorSubmission->setCopyeditorDateFinalCompleted($this->datetimeFromDB($row['ce_date_final_completed']));
		$acquisitionsEditorSubmission->setCopyeditorDateFinalAcknowledged($this->datetimeFromDB($row['ce_date_final_acknowledged']));
		$acquisitionsEditorSubmission->setCopyeditorInitialRevision($row['copyeditor_initial_revision']);
		$acquisitionsEditorSubmission->setCopyeditorEditorAuthorRevision($row['ce_editor_author_revision']);
		$acquisitionsEditorSubmission->setCopyeditorFinalRevision($row['copyeditor_final_revision']);
*/
		// Layout Editing
//		$acquisitionsEditorSubmission->setLayoutAssignments($this->layoutAssignmentDao->getByMonographId($row['monograph_id']));

//		$acquisitionsEditorSubmission->setGalleys($this->galleyDao->getGalleysByMonograph($row['monograph_id']));
 
		// Proof Assignment
//		$acquisitionsEditorSubmission->setProofAssignment($this->proofAssignmentDao->getProofAssignmentByMonographId($row['monograph_id']));
//print_r($acquisitionsEditorSubmission);
		HookRegistry::call('AcquisitionsEditorSubmissionDAO::_fromRow', array(&$acquisitionsEditorSubmission, &$row));

		return $acquisitionsEditorSubmission;
	}

	/**
	 * Update an existing section editor submission.
	 * @param $acquisitionsEditorSubmission AcquisitionsEditorSubmission
	 */
	function updateAcquisitionsEditorSubmission(&$acquisitionsEditorSubmission) {
		// update edit assignment
		$editAssignments =& $acquisitionsEditorSubmission->getEditAssignments();
		foreach ($editAssignments as $editAssignment) {
			if ($editAssignment->getEditId() > 0) {
				$this->editAssignmentDao->updateEditAssignment($editAssignment);
			} else {
				$this->editAssignmentDao->insertEditAssignment($editAssignment);
			}
		}

		$reviewRounds = $acquisitionsEditorSubmission->getReviewRoundsInfo();

		// Update editor decisions
		foreach ($reviewRounds as $reviewType => $round) {
		for ($i = 1; $i <= $round; $i++) {
			$editorDecisions = $acquisitionsEditorSubmission->getDecisions($reviewType, $i);
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
								$acquisitionsEditorSubmission->getMonographId(),
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
		$round = $acquisitionsEditorSubmission->getCurrentReviewRound();
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');

		$reviewType = $acquisitionsEditorSubmission->getCurrentReviewType();

		if (isset($reviewType)) {
			$reviewRound = $reviewRoundDao->build(
							$acquisitionsEditorSubmission->getMonographId(), 
							$acquisitionsEditorSubmission->getCurrentReviewType(), 
							$round == null ? 1 : $round
						);
			if ($acquisitionsEditorSubmission->getReviewRevision() != null) {
				$reviewRound->setReviewRevision($acquisitionsEditorSubmission->getReviewRevision());
				$reviewRoundDao->updateObject($reviewRound);
			}
		}
		// Update copyeditor assignment
/*		if ($acquisitionsEditorSubmission->getCopyedId()) {
			$copyeditorSubmission =& $this->copyeditorSubmissionDao->getCopyeditorSubmission($acquisitionsEditorSubmission->getMonographId());
		} else {
			$copyeditorSubmission = new CopyeditorSubmission();
		}

		// Only update the fields that an editor can modify.
		$copyeditorSubmission->setMonographId($acquisitionsEditorSubmission->getMonographId());
		$copyeditorSubmission->setCopyeditorId($acquisitionsEditorSubmission->getCopyeditorId());
				$copyeditorSubmission->setDateUnderway($acquisitionsEditorSubmission->getCopyeditorDateUnderway());
		$copyeditorSubmission->setDateNotified($acquisitionsEditorSubmission->getCopyeditorDateNotified());
		$copyeditorSubmission->setDateCompleted($acquisitionsEditorSubmission->getCopyeditorDateCompleted());
		$copyeditorSubmission->setDateAcknowledged($acquisitionsEditorSubmission->getCopyeditorDateAcknowledged());
		$copyeditorSubmission->setDateAuthorUnderway($acquisitionsEditorSubmission->getCopyeditorDateAuthorUnderway());
		$copyeditorSubmission->setDateAuthorNotified($acquisitionsEditorSubmission->getCopyeditorDateAuthorNotified());
		$copyeditorSubmission->setDateAuthorCompleted($acquisitionsEditorSubmission->getCopyeditorDateAuthorCompleted());
		$copyeditorSubmission->setDateAuthorAcknowledged($acquisitionsEditorSubmission->getCopyeditorDateAuthorAcknowledged());
		$copyeditorSubmission->setDateFinalUnderway($acquisitionsEditorSubmission->getCopyeditorDateFinalUnderway());
		$copyeditorSubmission->setDateFinalNotified($acquisitionsEditorSubmission->getCopyeditorDateFinalNotified());
		$copyeditorSubmission->setDateFinalCompleted($acquisitionsEditorSubmission->getCopyeditorDateFinalCompleted());
		$copyeditorSubmission->setDateFinalAcknowledged($acquisitionsEditorSubmission->getCopyeditorDateFinalAcknowledged());
		$copyeditorSubmission->setInitialRevision($acquisitionsEditorSubmission->getCopyeditorInitialRevision());
		$copyeditorSubmission->setEditorAuthorRevision($acquisitionsEditorSubmission->getCopyeditorEditorAuthorRevision());
		$copyeditorSubmission->setFinalRevision($acquisitionsEditorSubmission->getCopyeditorFinalRevision());
		$copyeditorSubmission->setDateStatusModified($acquisitionsEditorSubmission->getDateStatusModified());
		$copyeditorSubmission->setLastModified($acquisitionsEditorSubmission->getLastModified());

		if ($copyeditorSubmission->getCopyedId() != null) {
			$this->copyeditorSubmissionDao->updateCopyeditorSubmission($copyeditorSubmission);
		} else {
			$this->copyeditorSubmissionDao->insertCopyeditorSubmission($copyeditorSubmission);
		}
*/

		// update review assignments
		foreach ($acquisitionsEditorSubmission->getReviewAssignments() as $reviewAssignment) {
			if ($reviewAssignment->getReviewId() > 0) {
				$this->reviewAssignmentDao->updateObject($reviewAssignment);
			} else {
				$this->reviewAssignmentDao->insertObject($reviewAssignment);
			}
		}

		// Remove deleted review assignments
		$removedReviewAssignments = $acquisitionsEditorSubmission->getRemovedReviewAssignments();
		for ($i=0, $count=count($removedReviewAssignments); $i < $count; $i++) {
			$this->reviewAssignmentDao->deleteById($removedReviewAssignments[$i]);
		}

		// Update layout editing assignment
/*		$layoutAssignments =& $acquisitionsEditorSubmission->getLayoutAssignments();

		if (isset($layoutAssignment)) {
			if ($layoutAssignment->getId() > 0) {
				$this->layoutAssignmentDao->updateLayoutAssignment($layoutAssignment);
			} else {
				$this->layoutAssignmentDao->insertLayoutAssignment($layoutAssignment);
			}
		}
*/
		// Update monograph
		if ($acquisitionsEditorSubmission->getMonographId()) {

			$monograph =& $this->monographDao->getMonograph($acquisitionsEditorSubmission->getMonographId());

			// Only update fields that can actually be edited.
			$monograph->setAcquisitionsArrangementId($acquisitionsEditorSubmission->getAcquisitionsArrangementId());
	//		$monograph->setCurrentRound($acquisitionsEditorSubmission->getCurrentRound());
			$monograph->setReviewFileId($acquisitionsEditorSubmission->getReviewFileId());
			$monograph->setEditorFileId($acquisitionsEditorSubmission->getEditorFileId());
			$monograph->setLayoutFileId($acquisitionsEditorSubmission->getLayoutFileId());
			$monograph->setStatus($acquisitionsEditorSubmission->getStatus());
		//	$monograph->setCopyeditFileId($acquisitionsEditorSubmission->getCopyeditFileId());
			$monograph->setDateStatusModified($acquisitionsEditorSubmission->getDateStatusModified());
			$monograph->setLastModified($acquisitionsEditorSubmission->getLastModified());
			$monograph->setCommentsStatus($acquisitionsEditorSubmission->getCommentsStatus());

			$this->monographDao->updateMonograph($monograph);
		}

	}

	/**
	 * Get all section editor submissions for a section editor.
	 * @param $acquisitionsEditorId int
	 * @param $status boolean true if active, false if completed.
	 * @return array AcquisitionsEditorSubmission
	 */
	function &getAcquisitionsEditorSubmissions($acquisitionsEditorId, $pressId, $status = true) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();

		$acquisitionsEditorSubmissions = array();

		$result =& $this->retrieve(
			'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev,
				MAX(rr.round) AS current_round,
				rr.review_revision AS review_revision
			FROM	monographs a
				LEFT JOIN edit_assignments e ON (e.monograph_id = a.monograph_id)
				LEFT JOIN acquisitions_arrangements s ON (s.arrangement_id = a.arrangement_id)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	a.press_id = ?
				AND e.editor_id = ?
				AND a.status = ?',
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primarylocale,
				'abbrev',
				$locale,
				$pressId,
				$acquisitionsEditorId,
				$status
			)
		);

		while (!$result->EOF) {
			$acquisitionsEditorSubmissions[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $acquisitionsEditorSubmissions;
	}

	/**
	 * Retrieve unfiltered section editor submissions
	 */
	function &getUnfilteredAcquisitionsEditorSubmissions($acquisitionsEditorId, $pressId, $sectionId = 0, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $status = true, $additionalWhereSql = '', $rangeInfo = null) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();

		$params = array(
			'title', // Acquisitions title
			$primaryLocale,
			'title',
			$locale,
			'abbrev', // Acquisitions abbrev
			$primaryLocale,
			'abbrev',
			$locale,
			'title', // Monograph title
			$pressId,
			$acquisitionsEditorId
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
					$searchSql .= ' AND a.date_submitted >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= ' AND a.date_submitted <= ' . $this->datetimeToDB($dateTo);
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
				a.*,
				e.can_review AS can_review,
				e.can_edit AS can_edit,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev,
			FROM
				monographs a
				INNER JOIN monograph_authors aa ON (aa.monograph_id = a.monograph_id)
				LEFT JOIN edit_assignments e ON (e.monograph_id = a.monograph_id)
				LEFT JOIN users ed ON (e.editor_id = ed.user_id)
				LEFT JOIN acquisitions_arrangements s ON (s.arrangement_id = a.arrangement_id)
				LEFT JOIN users ce ON (c.copyeditor_id = ce.user_id)
				LEFT JOIN proof_assignments p ON (p.monograph_id = a.monograph_id)
				LEFT JOIN users pe ON (pe.user_id = p.proofreader_id)
				LEFT JOIN designer_assignments l ON (l.monograph_id = a.monograph_id) LEFT JOIN users le ON (le.user_id = l.editor_id)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN monograph_settings atl ON (a.monograph_id = atl.monograph_id AND atl.setting_name = ?)
			WHERE	a.press_id = ?
				AND e.editor_id = ?
				AND a.submission_progress = 0' . (!empty($additionalWhereSql)?" AND ($additionalWhereSql)":"");

		// "Active" submissions have a status of STATUS_QUEUED and
		// the layout editor has not yet been acknowledged.
		if ($status) $sql .= ' AND a.status = ' . STATUS_QUEUED;
		else $sql .= ' AND a.status <> ' . STATUS_QUEUED;

		if ($sectionId) {
			$params[] = $sectionId;
			$searchSql .= ' AND a.arrangement_id = ?';
		}

		$result =& $this->retrieveRange($sql . ' ' . $searchSql . ' ORDER BY monograph_id ASC',
			$params,
			$rangeInfo
		);	

		return $result;	
	}

	/**
	 * Get all submissions in review for a press.
	 * @param $pressId int
	 * @param $sectionId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains" or "startsWith"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getAcquisitionsEditorSubmissionsInReview($acquisitionsEditorId, $pressId, $sectionId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
		$submissions = array();

		// FIXME Does not pass $rangeInfo else we only get partial results
		$result = $this->getUnfilteredAcquisitionsEditorSubmissions($acquisitionsEditorId, $pressId, $sectionId, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, true, 'e.can_review = 1');

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$submission =& $this->_fromRow($row);
			$monographId = $submission->getMonographId();

			// check if submission is still in review
			$inReview = true;
			$decisions = $submission->getDecisions();
			$decision = array_pop($decisions);
			if (!empty($decision)) {
				$latestDecision = array_pop($decision);
				if ($latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT) {
					$inReview = false;
				}
			}
			if ($inReview) $submissions[] =& $submission;

			unset($submission);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		import('core.ArrayItemIterator');
		$returner =& ArrayItemIterator::fromRangeInfo($submissions, $rangeInfo);
		return $returner;

	}

	/**
	 * Get all submissions in editing for a press.
	 * @param $pressId int
	 * @param $sectionId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains" or "startsWith"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getAcquisitionsEditorSubmissionsInEditing($acquisitionsEditorId, $pressId, $sectionId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
		$submissions = array();

		// FIXME Does not pass $rangeInfo else we only get partial results
		$result = $this->getUnfilteredAcquisitionsEditorSubmissions($acquisitionsEditorId, $pressId, $sectionId, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, true, 'e.can_edit = 1');

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$submission =& $this->_fromRow($row);

			// check if submission is still in review
			$inReview = true;
			$decisions = $submission->getDecisions();
			$decision = array_pop($decisions);
			if (!empty($decision)) {
				$latestDecision = array_pop($decision);
				if ($latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT) {
					$inReview = false;
				}
			}
			if (!$inReview) $submissions[] =& $submission;

			unset($submission);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		import('core.ArrayItemIterator');
		$returner =& ArrayItemIterator::fromRangeInfo($submissions, $rangeInfo);
		return $returner;
	}

	/**
	 * Get all submissions in archives for a press.
	 * @param $pressId int
	 * @param $sectionId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains" or "startsWith"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getAcquisitionsEditorSubmissionsArchives($acquisitionsEditorId, $pressId, $sectionId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
		$submissions = array();

		$result = $this->getUnfilteredAcquisitionsEditorSubmissions($acquisitionsEditorId, $pressId, $sectionId, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, false, '', $rangeInfo);

		while (!$result->EOF) {
			$submission =& $this->_fromRow($result->GetRowAssoc(false));
			$submissions[] =& $submission;
			unset($submission);
			$result->MoveNext();
		}

		if (isset($rangeInfo) && $rangeInfo->isValid()) {
			import('core.VirtualArrayIterator');
			$returner = new VirtualArrayIterator($submissions, $result->MaxRecordCount(), $rangeInfo->getPage(), $rangeInfo->getCount());
		} else {
			import('core.ArrayItemIterator');
			$returner = new ArrayItemIterator($submissions);
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Function used for counting purposes for right nav bar
	 */
	function &getAcquisitionsEditorSubmissionsCount($acquisitionsEditorId, $pressId) {

		$submissionsCount = array();
		for($i = 0; $i < 4; $i++) {
			$submissionsCount[$i] = 0;
		}

		$result = $this->getUnfilteredAcquisitionsEditorSubmissions($acquisitionsEditorId, $pressId);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$acquisitionsEditorSubmission =& $this->_fromRow($row);

			// check if submission is still in review
			$inReview = true;
			$decisions = $acquisitionsEditorSubmission->getDecisions();
			$decision = array_pop($decisions);
			if (!empty($decision)) {
				$latestDecision = array_pop($decision);
				if ($latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT) {
					$inReview = false;
				}
			}

			if ($inReview) {
				if ($row['can_review']) {
					// in review submissions
					$submissionsCount[0] += 1;
				}
			} else {
				// in editing submissions
				if ($row['can_edit']) {
					$submissionsCount[1] += 1;
				}
			}
			unset($acquisitionsEditorSubmission);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

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
			'DELETE FROM review_rounds WHERE monograph_id = ?',
			$monographId
		);
	}

	/**
	 * Get the editor decisions for a review round of a monograph.
	 * @param $monographId int
	 */
	function getEditorDecisions($monographId) {
		$decisions = array();

		$result =& $this->retrieve(
				'SELECT edit_decision_id, editor_id, decision, date_decided, review_type, round 
				FROM edit_decisions 
				WHERE monograph_id = ? 
				ORDER BY review_type, date_decided ASC', 
				$monographId
			);

		while (!$result->EOF) {

			$value = array(
					'editDecisionId' => $result->fields['edit_decision_id'], 
					'editorId' => $result->fields['editor_id'], 
					'decision' => $result->fields['decision'], 
					'dateDecided' => $this->datetimeFromDB($result->fields['date_decided'])
				);

			$decisions[$result->fields['review_type']][$result->fields['round']][] = $value;
			

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
	function getMaxReviewRound($monographId) {
		$result =& $this->retrieve(
			'SELECT MAX(round) FROM review_rounds WHERE monograph_id = ?', $monographId
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
	 * @param $reviewType int
	 * @param $round int
	 * @return boolean
	 */
	function reviewerExists($monographId, $reviewerId, $reviewType, $round) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM review_assignments WHERE monograph_id = ? AND reviewer_id = ? AND review_type = ? AND round = ? AND cancelled = 0', array($monographId, $reviewerId, $reviewType, $round)
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
	function &getReviewersForMonograph($pressId, $monographId, $reviewType, $round = null, $searchType = null, $search = null, $searchMatch = null, $rangeInfo = null) {
/*		$paramArray = array('interests', $pressId, $monographId, $round);
		$searchSql = '';

		$searchTypeMap = array(
			USER_FIELD_FIRSTNAME => 'u.first_name',
			USER_FIELD_LASTNAME => 'u.last_name',
			USER_FIELD_USERNAME => 'u.username',
			USER_FIELD_EMAIL => 'u.email',
			USER_FIELD_INTERESTS => 's.setting_value'
		);
		$reviewTypeSql = '';
		if (isset($reviewType)) {
			switch ($reviewType) {
				case REVIEW_TYPE_INTERNAL:
					$reviewTypeSql = 'NOT ';
					break;
				case REVIEW_TYPE_EXTERNAL:
					$reviewTypeSql = '';
					break;
			}

		}

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

		$sql = 'SELECT DISTINCT
			u.*,
			a.review_id
			FROM users u 
			LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?)
			LEFT JOIN (
				SELECT DISTINCT rx.user_id FROM roles rx, roles ry LEFT JOIN roles rz 
				ON (ry.user_id = rz.user_id AND rz.role_id = 32)
				WHERE rx.user_id = ry.user_id
				AND rx.role_id = 256
				AND rz.role_id IS '.$reviewTypeSql.'NULL
				AND rx.press_id = ?
				) r ON (r.user_id = u.user_id)
			LEFT JOIN review_assignments a ON (a.reviewer_id = u.user_id AND a.cancelled = 0 AND a.monograph_id = ? AND a.round = ?)
			WHERE
			u.user_id = r.user_id ' . $searchSql . '
			ORDER BY last_name, first_name';

		$result =& $this->retrieveRange(
				$sql, $paramArray, $rangeInfo
			);

		$returner = new DAOResultFactory($result, $this, '_returnReviewerUserFromRow');

		return $returner;*/
		$paramArray = array('interests', $monographId, $reviewType, $pressId, RoleDAO::getRoleIdFromPath('reviewer'));
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
				u.*,
				a.review_id
			FROM	users u
				LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?)
				LEFT JOIN roles r ON (r.user_id = u.user_id)
				LEFT JOIN review_assignments a ON (a.reviewer_id = u.user_id AND a.cancelled = 0 AND a.monograph_id = ? AND a.review_type = ?)
			WHERE	u.user_id = r.user_id AND
				r.press_id = ? AND
				r.role_id = ? ' . $searchSql . '
			ORDER BY last_name, first_name',
			$paramArray, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnReviewerUserFromRow');
		return $returner;
	}

	function &_returnReviewerUserFromRow(&$row) { // FIXME
		$user =& $this->userDao->_returnUserFromRowWithData($row);
		$user->review_id = $row['review_id'];

		HookRegistry::call('AcquisitionsEditorSubmissionDAO::_returnReviewerUserFromRow', array(&$user, &$row));

		return $user;
	}

	/**
	 * Retrieve a list of all reviewers not assigned to the specified monograph.
	 * @param $pressId int
	 * @param $monographId int
	 * @return array matching Users
	 */
	function &getReviewersNotAssignedToMonograph($pressId, $monographId) {
		$users = array();

		$result =& $this->retrieve(
			'SELECT	u.*
			FROM	users u
				LEFT JOIN roles r ON (r.user_id = u.user_id)
				LEFT JOIN review_assignments a ON (a.reviewer_id = u.user_id AND a.monograph_id = ?)
			WHERE	r.press_id = ? AND
				r.role_id = ? AND
				a.monograph_id IS NULL
			ORDER BY last_name, first_name',
			array($monographId, $pressId, RoleDAO::getRoleIdFromPath('reviewer'))
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
	 * Check if a copyeditor is assigned to a specified monograph.
	 * @param $monographId int
	 * @param $copyeditorId int
	 * @return boolean
	 */
	function copyeditorExists($monographId, $copyeditorId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) 
			FROM signoffs 
			WHERE assoc_id = ? AND 
				assoc_type = ? AND
				user_id = ? AND
				symbolic = ?', 
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
				a.monograph_id IS NULL
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
	 * Get the assignment counts and last assigned date for all layout editors of the given press.
	 * @return array
	 */
	function getLayoutEditorStatistics($pressId) {
		$statistics = Array();

		// Get counts of completed submissions
		$result =& $this->retrieve(
				'SELECT la.designer_id AS editor_id, COUNT(la.monograph_id) AS complete 
				FROM designer_assignments la, monographs a 
				INNER JOIN proof_assignments p ON (p.monograph_id = a.monograph_id) 
				WHERE la.monograph_id = a.monograph_id AND 
					(la.date_completed IS NOT NULL AND p.date_layouteditor_completed IS NOT NULL) AND 
					la.date_notified IS NOT NULL AND 
					a.press_id = ? 
				GROUP BY la.designer_id', 
				$pressId
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
				'SELECT la.designer_id AS editor_id, COUNT(la.monograph_id) AS incomplete 
				FROM designer_assignments la, monographs a 
				INNER JOIN proof_assignments p ON (p.monograph_id = a.monograph_id) 
				WHERE la.monograph_id = a.monograph_id AND 
					(la.date_completed IS NULL OR p.date_layouteditor_completed IS NULL) AND 
					la.date_notified IS NOT NULL AND 
					a.press_id = ? 
				GROUP BY la.designer_id', 
				$pressId
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
				'SELECT la.designer_id AS editor_id, MAX(la.date_notified) AS last_assigned 
				FROM designer_assignments la, monographs a 
				WHERE la.monograph_id=a.monograph_id AND 
					a.press_id = ? 
				GROUP BY la.designer_id', 
				$pressId
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
	 * Get the last assigned and last completed dates for all reviewers of the given press.
	 * @return array
	 */
	function getReviewerStatistics($pressId) {
		$statistics = Array();

		// Get counts of completed submissions
		$result =& $this->retrieve(
				'SELECT ra.reviewer_id AS editor_id, MAX(ra.date_notified) AS last_notified 
				FROM review_assignments ra, monographs a 
				WHERE ra.monograph_id = a.monograph_id AND 
					a.press_id = ? 
				GROUP BY ra.reviewer_id', 
				$pressId
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['editor_id']])) $statistics[$row['editor_id']] = array();
			$statistics[$row['editor_id']]['last_notified'] = $this->datetimeFromDB($row['last_notified']);
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Get completion status
		$result =& $this->retrieve(
				'SELECT r.reviewer_id, COUNT(*) AS incomplete 
				FROM review_assignments r, monographs a 
				WHERE r.monograph_id = a.monograph_id AND 
					r.date_notified IS NOT NULL AND 
					r.date_completed IS NULL AND 
					r.cancelled = 0 AND 
					a.press_id = ? 
				GROUP BY r.reviewer_id', 
				$pressId
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = array();
			$statistics[$row['reviewer_id']]['incomplete'] = $row['incomplete'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		// Calculate time taken for completed reviews
		$result =& $this->retrieve(
				'SELECT r.reviewer_id, r.date_notified, r.date_completed 
				FROM review_assignments r, monographs a 
				WHERE r.monograph_id = a.monograph_id AND 
					r.date_notified IS NOT NULL AND 
					r.date_completed IS NOT NULL AND 
					r.declined = 0 AND 
					a.press_id = ?', 
				$pressId
			);
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			if (!isset($statistics[$row['reviewer_id']])) $statistics[$row['reviewer_id']] = array();

			$completed = strtotime($this->datetimeFromDB($row['date_completed']));
			$notified = strtotime($this->datetimeFromDB($row['date_notified']));
			if (isset($statistics[$row['reviewer_id']]['total_span'])) {
				$statistics[$row['reviewer_id']]['total_span'] += $completed - $notified;
				$statistics[$row['reviewer_id']]['completed_review_count'] += 1;
			} else {
				$statistics[$row['reviewer_id']]['total_span'] = $completed - $notified;
				$statistics[$row['reviewer_id']]['completed_review_count'] = 1;
			}

			// Calculate the average length of review in weeks.
			$statistics[$row['reviewer_id']]['average_span'] = (($statistics[$row['reviewer_id']]['total_span'] / $statistics[$row['reviewer_id']]['completed_review_count']) / 60 / 60 / 24 / 7);
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
				'SELECT pa.proofreader_id AS editor_id, COUNT(pa.monograph_id) AS complete 
				FROM proof_assignments pa, monographs m 
				WHERE pa.monograph_id = a.monograph_id AND 
					pa.date_proofreader_completed IS NOT NULL AND 
					m.press_id = ? 
				GROUP BY pa.proofreader_id', 
				$pressId
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
				'SELECT pa.proofreader_id AS editor_id, COUNT(pa.monograph_id) AS incomplete 
				FROM proof_assignments pa, monographs a 
				WHERE pa.monograph_id = a.monograph_id AND 
					pa.date_proofreader_completed IS NULL AND 
					a.press_id = ? 
				GROUP BY pa.proofreader_id', 
				$pressId
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
				'SELECT pa.proofreader_id AS editor_id, MAX(pa.date_proofreader_notified) AS last_assigned 
				FROM proof_assignments pa, monographs a 
				WHERE pa.monograph_id = a.monograph_id AND 
					a.press_id = ? 
				GROUP BY pa.proofreader_id', 
				$pressId
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
}

?>