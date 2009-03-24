<?php

/**
 * @file classes/editor/EditorSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorSubmissionDAO
 * @ingroup submission
 * @see EditorSubmission
 *
 * @brief Operations for retrieving and modifying EditorSubmission objects.
 */

// $Id$


import('submission.editor.EditorSubmission');
//import('submission.author.AuthorSubmission'); // Bring in editor decision constants

class EditorSubmissionDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $userDao;
	var $editAssignmentDao;

	/**
	 * Constructor.
	 */
	function EditorSubmissionDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
	}

	/**
	 * Retrieve an editor submission by monograph ID.
	 * @param $monographId int
	 * @return EditorSubmission
	 */
	function &getEditorSubmission($monographId) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieve(
			'SELECT
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM	monographs a
				LEFT JOIN acquisitions_arrangements s ON s.arrangement_id = a.arrangement_id
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	a.monograph_id = ?',
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
			$returner =& $this->_returnEditorSubmissionFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return an EditorSubmission object from a row.
	 * @param $row array
	 * @return EditorSubmission
	 */
	function &_returnEditorSubmissionFromRow(&$row) {
		$editorSubmission = new EditorSubmission();

		$this->monographDao->_monographFromRow($editorSubmission, $row);

		// Editor Assignment
		$editAssignments =& $this->editAssignmentDao->getEditAssignmentsByMonographId($row['monograph_id']);
		$editorSubmission->setEditAssignments($editAssignments->toArray());

		// Editor Decisions
		$reviewRounds =& $editorSubmission->getReviewRounds();

		if (isset($reviewRounds))
		foreach ($reviewRounds as $reviewRound => $round) {
			for ($i = 1; $i <= $round; $i++) {
				$editorSubmission->setDecisions($this->getEditorDecisions($row['monograph_id'], $reviewRound, $i), $reviewRound, $i);
			}
		}
//		for ($i = 1; $i <= $row['current_round']; $i++) {
//			$editorSubmission->setDecisions($this->getEditorDecisions($row['monograph_id'], $i), $i);
//		}

		HookRegistry::call('EditorSubmissionDAO::_returnEditorSubmissionFromRow', array(&$editorSubmission, &$row));

		return $editorSubmission;
	}

	/**
	 * Insert a new EditorSubmission.
	 * @param $editorSubmission EditorSubmission
	 */	
	function insertEditorSubmission(&$editorSubmission) {
		$this->update(
			sprintf('INSERT INTO edit_assignments
				(monograph_id, editor_id, date_notified, date_completed, date_acknowledged)
				VALUES
				(?, ?, %s, %s, %s)',
				$this->datetimeToDB($editorSubmission->getDateNotified()), $this->datetimeToDB($editorSubmission->getDateCompleted()), $this->datetimeToDB($editorSubmission->getDateAcknowledged())),
			array(
				$editorSubmission->getMonographId(),
				$editorSubmission->getEditorId()
			)
		);

		$editorSubmission->setEditId($this->getInsertEditId());

		// Insert review assignments.
		$reviewAssignments =& $editorSubmission->getReviewAssignments();
		for ($i=0, $count=count($reviewAssignments); $i < $count; $i++) {
			$reviewAssignments[$i]->setMonographId($editorSubmission->getMonographId());
			$this->reviewAssignmentDao->insertReviewAssignment($reviewAssignments[$i]);
		}

		return $editorSubmission->getEditId();
	}

	/**
	 * Update an existing monograph.
	 * @param $monograph Monograph
	 */
	function updateEditorSubmission(&$editorSubmission) {
		// update edit assignments
		$editAssignments = $editorSubmission->getEditAssignments();
		foreach ($editAssignments as $editAssignment) {
			if ($editAssignment->getEditId() > 0) {
				$this->editAssignmentDao->updateEditAssignment($editAssignment);
			} else {
				$this->editAssignmentDao->insertEditAssignment($editAssignment);
			}
		}
	}

	/**
	 * Get all submissions for a press.
	 * @param $pressId int
	 * @param $status boolean true if queued, false if archived.
	 * @return array EditorSubmission
	 */
	function &getEditorSubmissions($pressId, $status = true, $sectionId = 0, $rangeInfo = null) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$params = array(
			'title',
			$primaryLocale,
			'title',
			$locale,
			'abbrev',
			$primaryLocale,
			'abbrev',
			$locale,
			$pressId,
			$status
		);
		if ($sectionId) $params[] = $sectionId;

		$sql = 'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev

			FROM	monographs a
				LEFT JOIN acquisitions_arrangements s ON (s.arrangement_id = a.arrangement_id)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	a.press_id = ?
				AND a.status = ?' .
				($sectionId?' AND a.arrangement_id = ?':'') .
			' ORDER BY monograph_id ASC';

		$result =& $this->retrieveRange($sql, $params, $rangeInfo);
		$returner =& new DAOResultFactory($result, $this, '_returnEditorSubmissionFromRow');
		return $returner;
	}

	/**
	 * Get all unfiltered submissions for a press.
	 * @param $pressId int
	 * @param $sectionId int
	 * @param $editorId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $status boolean whether to return active or not
	 * @param $rangeInfo object
	 * @return array result
	 */
	function &getUnfilteredEditorSubmissions($pressId, $sectionId = 0, $editorId = 0, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $status = true, $rangeInfo = null) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$params = array(
			'title', // Section title
			$primaryLocale,
			'title',
			$locale,
			'abbrev', // Section abbrev
			$primaryLocale,
			'abbrev',
			$locale,
			'title', // Monograph title
			$primaryLocale,
			'title',
			$locale,
			$pressId
		);
		$searchSql = '';

		if (!empty($search)) switch ($searchField) {
			case SUBMISSION_FIELD_TITLE:
				if ($searchMatch === 'is') {
					$searchSql = ' AND LOWER(COALESCE(atl.setting_value, atpl.setting_value)) = LOWER(?)';
				} else {
					$searchSql = ' AND LOWER(COALESCE(atl.setting_value, atpl.setting_value)) LIKE LOWER(?)';
					$search = '%' . $search . '%';
				}
				$params[] = $search;
				break;
			case SUBMISSION_FIELD_AUTHOR:
				$searchSql = $this->_generateUserNameSearchSQL($search, $searchMatch, 'aa.', $params);
				break;
			case SUBMISSION_FIELD_EDITOR:
				$searchSql = $this->_generateUserNameSearchSQL($search, $searchMatch, 'ed.', $params);
				break;
			case SUBMISSION_FIELD_REVIEWER:
				$searchSql = $this->_generateUserNameSearchSQL($search, $searchMatch, 're.', $params);
				break;
			case SUBMISSION_FIELD_COPYEDITOR:
				$searchSql = $this->_generateUserNameSearchSQL($search, $searchMatch, 'ce.', $params);
				break;
			case SUBMISSION_FIELD_LAYOUTEDITOR:
				$searchSql = $this->_generateUserNameSearchSQL($search, $searchMatch, 'le.', $params);
				break;
			case SUBMISSION_FIELD_PROOFREADER:
				$searchSql = $this->_generateUserNameSearchSQL($search, $searchMatch, 'pe.', $params);
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
					$searchSql .= ' AND p.date_proofreader_completed <= ' . $this->datetimeToDB($dateTo);
				}
				break;
		}/*
		$sql = 'SELECT DISTINCT
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM
				monographs a
				INNER JOIN monograph_authors aa ON (aa.monograph_id = a.monograph_id)
				LEFT JOIN acquisitions_arrangements s ON (s.arrangement_id = a.arrangement_id)
			//	LEFT JOIN edit_assignments e ON (e.monograph_id = a.monograph_id)
				LEFT JOIN users ed ON (e.editor_id = ed.user_id)
				LEFT JOIN copyed_assignments c ON (a.monograph_id = c.monograph_id)
				LEFT JOIN users ce ON (c.copyeditor_id = ce.user_id)
				LEFT JOIN proof_assignments p ON (p.monograph_id = a.monograph_id)
				LEFT JOIN users pe ON (pe.user_id = p.proofreader_id)
				LEFT JOIN layouted_assignments l ON (l.monograph_id = a.monograph_id)
				LEFT JOIN users le ON (le.user_id = l.editor_id)
				LEFT JOIN review_assignments r ON (r.monograph_id = a.monograph_id)
				LEFT JOIN users re ON (re.user_id = r.reviewer_id AND cancelled = 0)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN monograph_settings atpl ON (a.monograph_id = atpl.monograph_id AND atpl.setting_name = ? AND atpl.locale = ?)
				LEFT JOIN monograph_settings atl ON (a.monograph_id = atl.monograph_id AND atl.setting_name = ? AND atl.locale = ?)
			WHERE
				a.press_id = ? AND a.submission_progress = 0';*/
		$sql = 'SELECT DISTINCT
				a.*

			FROM
				monographs a
			WHERE
				a.press_id = '.$pressId.' AND a.submission_progress = 0';

		// "Active" submissions have a status of STATUS_QUEUED and
		// the layout editor has not yet been acknowledged.
		// A status value of null doesn't discriminate.
/*		if ($status === true) $sql .= ' AND a.status = ' . STATUS_QUEUED;
		elseif ($status === false) $sql .= ' AND a.status <> ' . STATUS_QUEUED;

		if ($sectionId) {
			$searchSql .= ' AND a.arrangement_id = ?';
			$params[] = $sectionId;
		}

		if ($editorId) {
			$searchSql .= ' AND ed.user_id = ?';
			$params[] = $editorId;
		}

		$result =& $this->retrieveRange(
			$sql . ' ' . $searchSql . ' ORDER BY a.monograph_id ASC',
			count($params)===1?array_shift($params):$params,
			$rangeInfo
		);print_r($result);exit;
*/
$sql.=	' ORDER BY a.monograph_id ASC';
		$result =& $this->retrieveRange(
			$sql,array(),$rangeInfo
		);


		return $result;
	}

	/**
	 * FIXME Move this into somewhere common (SubmissionDAO?) as this is used in several classes.
	 */
	function _generateUserNameSearchSQL($search, $searchMatch, $prefix, &$params) {
		$first_last = $this->_dataSource->Concat($prefix.'first_name', '\' \'', $prefix.'last_name');
		$first_middle_last = $this->_dataSource->Concat($prefix.'first_name', '\' \'', $prefix.'middle_name', '\' \'', $prefix.'last_name');
		$last_comma_first = $this->_dataSource->Concat($prefix.'last_name', '\', \'', $prefix.'first_name');
		$last_comma_first_middle = $this->_dataSource->Concat($prefix.'last_name', '\', \'', $prefix.'first_name', '\' \'', $prefix.'middle_name');
		if ($searchMatch === 'is') {
			$searchSql = " AND (LOWER({$prefix}last_name) = LOWER(?) OR LOWER($first_last) = LOWER(?) OR LOWER($first_middle_last) = LOWER(?) OR LOWER($last_comma_first) = LOWER(?) OR LOWER($last_comma_first_middle) = LOWER(?))";
		} else {
			$searchSql = " AND (LOWER({$prefix}last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
			$search = '%' . $search . '%';
		}
		$params[] = $params[] = $params[] = $params[] = $params[] = $search;
		return $searchSql;
	}

	/**
	 * Helper function to retrieve copyed assignment
	 * @param monographId int
	 * @return result array
	 */
	function &getCopyedAssignment($monographId) {
		$result =& $this->retrieve(
				'SELECT * from copyed_assignments where monograph_id = ? ', $monographId
		);
		return $result;
	}

	/**
	 * Get all submissions unassigned for a press.
	 * @param $pressId int
	 * @param $sectionId int
	 * @param $editorId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getEditorSubmissionsUnassigned($pressId, $sectionId, $editorId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
		$editorSubmissions = array();

		// FIXME Does not pass $rangeInfo else we only get partial results
		$result = $this->getUnfilteredEditorSubmissions($pressId, $sectionId, $editorId, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, true);

		while (!$result->EOF) {
			$editorSubmission =& $this->_returnEditorSubmissionFromRow($result->GetRowAssoc(false));

			// used to check if editor exists for this submission
			$editAssignments =& $editorSubmission->getEditAssignments();

			if (empty($editAssignments)) {
				$editorSubmissions[] =& $editorSubmission;
			}
			unset($editorSubmission);
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		import('core.ArrayItemIterator');
		$returner =& ArrayItemIterator::fromRangeInfo($editorSubmissions, $rangeInfo);
		return $returner;
	}

	/**
	 * Get all submissions in review for a press.
	 * @param $pressId int
	 * @param $sectionId int
	 * @param $editorId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getEditorSubmissionsInReview($pressId, $sectionId, $editorId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
		$editorSubmissions = array();

		// FIXME Does not pass $rangeInfo else we only get partial results
		$result = $this->getUnfilteredEditorSubmissions($pressId, $sectionId, $editorId, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, true);

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		while (!$result->EOF) {
			$editorSubmission =& $this->_returnEditorSubmissionFromRow($result->GetRowAssoc(false));
			$monographId = $editorSubmission->getMonographId();
/*			for ($i = 1; $i <= $editorSubmission->getCurrentRound(); $i++) {
				$reviewAssignment =& $reviewAssignmentDao->getReviewAssignmentsByMonographId($monographId, $i);
				if (!empty($reviewAssignment)) {
					$editorSubmission->setReviewAssignments($reviewAssignment, $i);
				}
			}
*/
			// check if submission is still in review
			$inReview = true;
			$decisions = $editorSubmission->getDecisions();
			$decision = is_array($decisions) ? array_pop($decisions) : null;
			if (!empty($decision)) {
				$latestDecision = array_pop($decision);
				if ($latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT) {
					$inReview = false;			
				}
			}

			// used to check if editor exists for this submission
			$editAssignments =& $editorSubmission->getEditAssignments();

			if (!empty($editAssignments) && $inReview) {
				$editorSubmissions[] =& $editorSubmission;
			}
			unset($editorSubmission);
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		import('core.ArrayItemIterator');
		$returner =& ArrayItemIterator::fromRangeInfo($editorSubmissions, $rangeInfo);
		return $returner;
	}

	/**
	 * Get all submissions in editing for a press.
	 * @param $pressId int
	 * @param $sectionId int
	 * @param $editorId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getEditorSubmissionsInEditing($pressId, $sectionId, $editorId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
		$editorSubmissions = array();

		// FIXME Does not pass $rangeInfo else we only get partial results
		$result = $this->getUnfilteredEditorSubmissions($pressId, $sectionId, $editorId, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, true);

		while (!$result->EOF) {
			$editorSubmission =& $this->_returnEditorSubmissionFromRow($result->GetRowAssoc(false));
			$monographId = $editorSubmission->getMonographId();

			// get copyedit final data
			$copyedAssignment = $this->getCopyedAssignment($monographId);
			$row = $copyedAssignment->GetRowAssoc(false);
			$editorSubmission->setCopyeditorDateFinalCompleted($this->datetimeFromDB($row['date_final_completed']));

			// get layout assignment data
			$layoutAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
			$layoutAssignment =& $layoutAssignmentDao->getLayoutAssignmentByMonographId($monographId);
			$editorSubmission->setLayoutAssignment($layoutAssignment);

			// get proof assignment data
			$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
			$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
			$editorSubmission->setProofAssignment($proofAssignment);

			// check if submission is still in review
			$inEditing = false;
			$decisions = $editorSubmission->getDecisions();
			$decision = array_pop($decisions);
			if (!empty($decision)) {
				$latestDecision = array_pop($decision);
				if ($latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT) {
					$inEditing = true;	
				}
			}

			// used to check if editor exists for this submission
			$editAssignments = $editorSubmission->getEditAssignments();

			if ($inEditing && !empty($editAssignments)) {
				$editorSubmissions[] =& $editorSubmission;
			}
			unset($editorSubmission);
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		import('core.ArrayItemIterator');
		$returner =& ArrayItemIterator::fromRangeInfo($editorSubmissions, $rangeInfo);
		return $returner;
	}

	/**
	 * Get all submissions archived for a press.
	 * @param $pressId int
	 * @param $sectionId int
	 * @param $editorId int
	 * @param $searchField int Symbolic SUBMISSION_FIELD_... identifier
	 * @param $searchMatch string "is" or "contains"
	 * @param $search String to look in $searchField for
	 * @param $dateField int Symbolic SUBMISSION_FIELD_DATE_... identifier
	 * @param $dateFrom String date to search from
	 * @param $dateTo String date to search to
	 * @param $rangeInfo object
	 * @return array EditorSubmission
	 */
	function &getEditorSubmissionsArchives($pressId, $sectionId, $editorId, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
		$editorSubmissions = array();

		$result = $this->getUnfilteredEditorSubmissions($pressId, $sectionId, $editorId, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, false, $rangeInfo);
		while (!$result->EOF) {
			$editorSubmission =& $this->_returnEditorSubmissionFromRow($result->GetRowAssoc(false));
			$monographId = $editorSubmission->getMonographId();

			// get copyedit final data
			$copyedAssignment = $this->getCopyedAssignment($monographId);
			$row = $copyedAssignment->GetRowAssoc(false);
			$editorSubmission->setCopyeditorDateFinalCompleted($this->datetimeFromDB($row['date_final_completed']));

			// get layout assignment data
			$layoutAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
			$layoutAssignment =& $layoutAssignmentDao->getLayoutAssignmentByMonographId($monographId);
			$editorSubmission->setLayoutAssignment($layoutAssignment);

			// get proof assignment data
			$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
			$proofAssignment =& $proofAssignmentDao->getProofAssignmentByMonographId($monographId);
			$editorSubmission->setProofAssignment($proofAssignment);

			$editorSubmissions[] =& $editorSubmission;
			unset($editorSubmission);
			$result->MoveNext();
		}

		if (isset($rangeInfo) && $rangeInfo->isValid()) {
			import('core.VirtualArrayIterator');
			$returner = new VirtualArrayIterator($editorSubmissions, $result->MaxRecordCount(), $rangeInfo->getPage(), $rangeInfo->getCount());
		} else {
			import('core.ArrayItemIterator');
			$returner = new ArrayItemIterator($editorSubmissions);
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Function used for counting purposes for right nav bar
	 */
	function &getEditorSubmissionsCount($pressId) {

		$submissionsCount = array();
		for($i = 0; $i < 3; $i++) {
			$submissionsCount[$i] = 0;
		}

		$result =& $this->getUnfilteredEditorSubmissions($pressId);

		while (!$result->EOF) {
			$editorSubmission =& $this->_returnEditorSubmissionFromRow($result->GetRowAssoc(false));

			// check if submission is still in review
			$inReview = true;
			$notDeclined = true;
			$decisions = $editorSubmission->getDecisions();
			$decision = is_array($decisions) ? array_pop($decisions) : array();
			if (!empty($decision)) {
				$latestDecision = array_pop($decision);
				if ($latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT) {
					$inReview = false;
				}
			}

			// used to check if editor exists for this submission
			$editAssignments = $editorSubmission->getEditAssignments();

			if (empty($editAssignments)) {
				// unassigned submissions
				$submissionsCount[0] += 1;
			} else {
				if ($inReview) {
					if ($notDeclined) {
						// in review submissions
						$submissionsCount[1] += 1;
					}
				} else {
					// in editing submissions
					$submissionsCount[2] += 1;					
				}
			}
			unset($editorSubmission);
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
	 * Get the editor decisions for a review round of a monograph.
	 * @param $monographId int
	 * @param $reviewType int
	 * @param $round int
	 */
	function getEditorDecisions($monographId, $reviewType = null, $round = null) {
		$decisions = array();

		if (!isset($reviewType)) {
			$result =& $this->retrieve(
				'SELECT edit_decision_id, editor_id, decision, date_decided FROM edit_decisions WHERE monograph_id = ? ORDER BY date_decided ASC', $monographId
			);
		} else if (isset($reviewType) && !isset($round)) {
			$result =& $this->retrieve('
					SELECT edit_decision_id, editor_id, decision, date_decided
					FROM edit_decisions ed INNER JOIN review_rounds rr ON (ed.review_type = rr.review_type) 
					WHERE ed.monograph_id = ? ORDER BY date_decided ASC',
					$monographId
				);
		} else {
			$result =& $this->retrieve('
					SELECT edit_decision_id, editor_id, decision, date_decided
					FROM edit_decisions ed INNER JOIN review_rounds rr ON (ed.review_type = rr.review_type AND ed.round = rr.round) 
					WHERE ed.monograph_id = ? ORDER BY date_decided ASC',
					$monographId
				);
		}

		while (!$result->EOF) {
			$decisions[] = array('editDecisionId' => $result->fields[0], 'editorId' => $result->fields[1], 'decision' => $result->fields[2], 'dateDecided' => $this->datetimeFromDB($result->fields[3]));
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $decisions;
	}

	/**
	 * Get the editor decisions for an editor.
	 * @param $userId int
	 */
	function transferEditorDecisions($oldUserId, $newUserId) {
		$this->update(
			'UPDATE edit_decisions SET editor_id = ? WHERE editor_id = ?',
			array($newUserId, $oldUserId)
		);
	}

	/**
	 * Retrieve a list of all users in the specified role not assigned as editors to the specified monograph.
	 * @param $pressId int
	 * @param $monographId int
	 * @param $roleId int
	 * @return DAOResultFactory containing matching Users
	 */
	function &getUsersNotAssignedToMonograph($pressId, $monographId, $roleId, $searchType=null, $search=null, $searchMatch=null, $rangeInfo = null) {
		$users = array();

		$paramArray = array('interests', $monographId, $pressId, $roleId);
		$searchSql = '';

		if (isset($search)) switch ($searchType) {
			case USER_FIELD_USERID:
				$searchSql = 'AND user_id=?';
				$paramArray[] = $search;
				break;
			case USER_FIELD_FIRSTNAME:
				$searchSql = 'AND LOWER(first_name) ' . ($searchMatch=='is'?'=':'LIKE') . ' LOWER(?)';
				$paramArray[] = ($searchMatch=='is'?$search:'%' . $search . '%');
				break;
			case USER_FIELD_LASTNAME:
				$searchSql = 'AND LOWER(last_name) ' . ($searchMatch=='is'?'=':'LIKE') . ' LOWER(?)';
				$paramArray[] = ($searchMatch=='is'?$search:'%' . $search . '%');
				break;
			case USER_FIELD_USERNAME:
				$searchSql = 'AND LOWER(username) ' . ($searchMatch=='is'?'=':'LIKE') . ' LOWER(?)';
				$paramArray[] = ($searchMatch=='is'?$search:'%' . $search . '%');
				break;
			case USER_FIELD_EMAIL:
				$searchSql = 'AND LOWER(email) ' . ($searchMatch=='is'?'=':'LIKE') . ' LOWER(?)';
				$paramArray[] = ($searchMatch=='is'?$search:'%' . $search . '%');
				break;
			case USER_FIELD_INTERESTS:
				$searchSql = 'AND LOWER(s.setting_value) ' . ($searchMatch=='is'?'=':'LIKE') . ' LOWER(?)';
				$paramArray[] = ($searchMatch=='is'?$search:'%' . $search . '%');
				break;
			case USER_FIELD_INITIAL:
				$searchSql = 'AND (LOWER(last_name) LIKE LOWER(?) OR LOWER(username) LIKE LOWER(?))';
				$paramArray[] = $search . '%';
				$paramArray[] = $search . '%';
				break;
		}

		$result =& $this->retrieveRange(
			'SELECT DISTINCT
				u.*
			FROM	users u
				LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?)
				LEFT JOIN roles r ON (r.user_id = u.user_id)
				LEFT JOIN edit_assignments e ON (e.editor_id = u.user_id AND e.monograph_id = ?)
			WHERE	r.press_id = ? AND
				r.role_id = ? AND
				(e.monograph_id IS NULL) ' . $searchSql . '
			ORDER BY last_name, first_name',
			$paramArray, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
		return $returner;
	}

	/**
	 * Get the ID of the last inserted editor assignment.
	 * @return int
	 */
	function getInsertEditId() {
		return $this->getInsertId('edit_assignments', 'edit_id');
	}
}

?>
