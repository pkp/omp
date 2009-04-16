<?php

/**
 * @file classes/submission/designer/DesignerSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DesignerSubmissionDAO
 * @ingroup submission_designer
 * @see DesignerSubmission
 *
 * @brief Operations for retrieving and modifying DesignerSubmission objects.
 */

// $Id$


import('submission.designer.DesignerSubmission');

class DesignerSubmissionDAO extends DAO {
	/** Helper DAOs */
	var $monographDao;
	var $layoutDao;
	var $galleyDao;
	var $editAssignmentDao;
	var $suppFileDao;
	var $proofAssignmentDao;
	var $monographCommentDao;

	/**
	 * Constructor.
	 */
	function DesignerSubmissionDAO() {
		parent::DAO();

		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->layoutDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
//		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
//		$this->editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
//		$this->suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
//		$this->proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
//		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
	}

	/**
	 * Retrieve a layout editor submission by monograph ID.
	 * @param $monographId int
	 * @return DesignerSubmission
	 */
	function &getSubmission($monographId, $assignmentId, $pressId =  null) {
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
			$monographId,
			$assignmentId
		);
		if ($pressId) $params[] = $pressId;
		$result =& $this->retrieve(
			'SELECT
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM monographs a
				LEFT JOIN designer_assignments da ON da.monograph_id = a.monograph_id
				LEFT JOIN acquisitions_arrangements s ON s.arrangement_id = a.arrangement_id
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE a.monograph_id = ? AND da.assignment_id = ?' .
			($pressId?' AND a.press_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false), $assignmentId);
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a DesignerSubmission object from a row.
	 * @param $row array
	 * @return DesignerSubmission
	 */
	function &_fromRow(&$row, $specificAssignment = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$user =& Request::getUser();
		$submission = new DesignerSubmission();
		$this->monographDao->_monographFromRow($submission, $row);

		if ($specificAssignment == null) {
			$layoutAssignments =& $this->layoutDao->getByMonographId($row['monograph_id']);
			$layoutAssignmentSet = array();
			foreach ($layoutAssignments as $layoutAssignment) {
				if ($layoutAssignment->getDesignerId() == $user->getUserId()) {
					$layoutAssignmentSet[] =& $layoutAssignment;
				}
			}
		} else {
			$layoutAssignment =& $this->layoutDao->getById($specificAssignment);
			$layoutAssignmentSet = array($layoutAssignment);
		}

		$submission->setLayoutAssignments($layoutAssignmentSet);

		$submission->setLayoutFile($monographFileDao->getMonographFile($row['layout_file_id']));

		// Comments
//		$submission->setMostRecentLayoutComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_LAYOUT, $row['monograph_id']));
//		$submission->setMostRecentProofreadComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_PROOFREAD, $row['monograph_id']));

//		$submission->setSuppFiles($this->suppFileDao->getSuppFilesByMonograph($row['monograph_id']));

//		$submission->setGalleys($this->galleyDao->getGalleysByMonograph($row['monograph_id']));

//		$editAssignments =& $this->editAssignmentDao->getByMonographId($row['monograph_id']);
//		$submission->setEditAssignments($editAssignments->toArray());

//		$submission->setProofAssignment($this->proofAssignmentDao->getProofAssignmentByMonographId($row['monograph_id']));

		HookRegistry::call('DesignerSubmissionDAO::_returnDesignerSubmissionFromRow', array(&$submission, &$row));

		return $submission;
	}

	/**
	 * Update an existing layout editor sbusmission.
	 * @param $submission DesignerSubmission
	 */
	function updateSubmission(&$submission) {
		// Only update layout-specific data
		$layoutAssignment =& $submission->getLayoutAssignment();
		$this->layoutDao->updateLayoutAssignment($layoutAssignment);
	}

	/**
	 * Get set of layout editing assignments assigned to the specified layout editor.
	 * @param $editorId int
	 * @param $pressId int
	 * @param $searchField int SUBMISSION_FIELD_... constant
	 * @param $searchMatch String 'is' or 'contains' or 'startsWith'
	 * @param $search String Search string
	 * @param $dateField int SUBMISSION_FIELD_DATE_... constant
	 * @param $dateFrom int Search from timestamp
	 * @param $dateTo int Search to timestamp
	 * @param $active boolean true to select active assignments, false to select completed assignments
	 * @return array DesignerSubmission
	 */
	function &getSubmissions($editorId, $pressId = null, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $active = true, $rangeInfo = null) {
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
			'title',
			$editorId
		);
		if (isset($pressId)) $params[] = $pressId;

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
					$search = $search . '%';
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
				} else { // $searchMatch === 'startsWith'
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
				l.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM
				monographs a
				INNER JOIN monograph_authors aa ON (aa.monograph_id = a.monograph_id)
				INNER JOIN designer_assignments l ON (l.monograph_id = a.monograph_id)
			
				LEFT JOIN acquisitions_arrangements s ON s.arrangement_id = a.arrangement_id
				LEFT JOIN edit_assignments e ON (e.monograph_id = a.monograph_id)
				LEFT JOIN users ed ON (e.editor_id = ed.user_id)
				LEFT JOIN copyed_assignments c ON (a.monograph_id = c.monograph_id)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN monograph_settings atl ON (a.monograph_id = atl.monograph_id AND atl.setting_name = ?)
			WHERE
				l.designer_id = ? AND
				' . (isset($pressId)?'a.press_id = ? AND':'') . '
				l.date_notified IS NOT NULL';

		if ($active) {
		//	$sql .= ' AND (l.date_completed IS NULL OR p.date_layouteditor_completed IS NULL)'; 
		} else {
		//	$sql .= ' AND (l.date_completed IS NOT NULL AND p.date_layouteditor_completed IS NOT NULL)';
		}

		$result =& $this->retrieveRange(
			$sql . ' ' . $searchSql . ' ORDER BY a.monograph_id ASC',
			count($params)==1?array_shift($params):$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get count of active and complete assignments
	 * @param editorId int
	 * @param pressId int
	 */
	function getSubmissionsCount($editorId, $pressId) {
		$submissionsCount = array();
		$submissionsCount[0] = 0;
		$submissionsCount[1] = 0;

		$sql = 'SELECT	l.date_completed,
				p.date_layouteditor_completed
			FROM	monographs a
				LEFT JOIN layouted_assignments l ON (l.monograph_id = a.monograph_id)
				LEFT JOIN proof_assignments p ON (p.monograph_id = a.monograph_id)
				LEFT JOIN sections s ON (s.arrangement_id = a.arrangement_id)
			WHERE	l.editor_id = ? AND
				a.press_id = ? AND
				l.date_notified IS NOT NULL';

		$result =& $this->retrieve($sql, array($editorId, $pressId));
		while (!$result->EOF) {
			if ($result->fields['date_completed'] == null || $result->fields['date_layouteditor_completed'] == null) {
				$submissionsCount[0] += 1;
			} else {
				$submissionsCount[1] += 1;
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $submissionsCount;
	}
}

?>