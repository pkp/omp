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
	var $galleyDao;
	var $editAssignmentDao;
	var $suppFileDao;
	var $monographCommentDao;

	/**
	 * Constructor.
	 */
	function DesignerSubmissionDAO() {
		parent::DAO();

		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
//		$this->editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
//		$this->suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
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
			ASSOC_TYPE_MONOGRAPH,
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
				INNER JOIN signoffs sc ON (sc.assoc_id = a.monograph_id AND sc.assoc_type = ?)
				LEFT JOIN acquisitions_arrangements s ON s.arrangement_id = a.arrangement_id
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE a.monograph_id = ? AND sc.signoff_id = ?' .
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
	 * Construct a new data object corresponding to this DAO.
	 * @return ProductionEditorSubmission
	 */
	function newDataObject() {
		return new DesignerSubmission();
	}

	/**
	 * Internal function to return a DesignerSubmission object from a row.
	 * @param $row array
	 * @return DesignerSubmission
	 */
	function &_fromRow(&$row, $specificAssignment = null) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$user =& Request::getUser();
		$submission = $this->newDataObject();
		$this->monographDao->_monographFromRow($submission, $row);


/*		if ($specificAssignment == null) {
			$layoutAssignments =& $this->layoutDao->getByMonographId($row['monograph_id']);
			$layoutAssignmentSet = array();
			foreach ($layoutAssignments as $layoutAssignment) {
				if ($layoutAssignment->getDesignerId() == $user->getId()) {
					$layoutAssignmentSet[] =& $layoutAssignment;
				}
			}
		} else {
			$layoutAssignment =& $this->layoutDao->getById($specificAssignment);
			$layoutAssignmentSet = array($layoutAssignment);
		}
*/

		$layoutAssignment =& $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $row['monograph_id']);
		
		$submission->setLayoutAssignments($layoutAssignment);

		$submission->setLayoutFile($monographFileDao->getMonographFile($row['layout_file_id']));

		// Comments
//		$submission->setMostRecentLayoutComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_LAYOUT, $row['monograph_id']));
//		$submission->setMostRecentProofreadComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_PROOFREAD, $row['monograph_id']));

//		$submission->setSuppFiles($this->suppFileDao->getSuppFilesByMonograph($row['monograph_id']));

		$submission->setGalleys($this->galleyDao->getByMonographId($row['monograph_id']));

//		$editAssignments =& $this->editAssignmentDao->getByMonographId($row['monograph_id']);
//		$submission->setEditAssignments($editAssignments->toArray());

//		$submission->setProofAssignment($this->proofAssignmentDao->getProofAssignmentByMonographId($row['monograph_id']));

		HookRegistry::call('DesignerSubmissionDAO::_returnDesignerSubmissionFromRow', array(&$submission, &$row));

		return $submission;
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
	function &getSubmissions($designerId, $pressId = null, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $active = true, $rangeInfo = null) {
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
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_COPYEDITING_FINAL',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_LAYOUT',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_PROOFREADING_LAYOUT',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_COPYEDITING_INITIAL',
			$designerId
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
					$searchSql .= ' AND scp.date_final_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= ' AND scp.date_final_completed <= ' . $this->datetimeToDB($dateTo);
				}
				break;
			case SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE:
				if (!empty($dateFrom)) {
					$searchSql .= ' AND sle.date_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= ' AND sle.date_completed <= ' . $this->datetimeToDB($dateTo);
				}
				break;
			case SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE:
				if (!empty($dateFrom)) {
					$searchSql .= ' AND spr.date_proofreader_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= 'AND spr.date_proofreader_completed <= ' . $this->datetimeToDB($dateTo);
				}
				break;
		}

		$sql = 'SELECT DISTINCT
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM
				monographs m
				INNER JOIN monograph_authors aa ON (aa.monograph_id = m.monograph_id)
				LEFT JOIN acquisitions_arrangements s ON s.arrangement_id = m.arrangement_id
				LEFT JOIN edit_assignments e ON (e.monograph_id = m.monograph_id)
				LEFT JOIN users ed ON (e.editor_id = ed.user_id)
				LEFT JOIN copyed_assignments c ON (m.monograph_id = c.monograph_id)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN monograph_settings atl ON (m.monograph_id = atl.monograph_id AND atl.setting_name = ?)
				LEFT JOIN signoffs scpf ON (m.monograph_id = scpf.assoc_id AND scpf.assoc_type = ? AND scpf.symbolic = ?)
				LEFT JOIN signoffs sle ON (m.monograph_id = sle.assoc_id AND sle.assoc_type = ? AND sle.symbolic = ?)
				LEFT JOIN signoffs spr ON (m.monograph_id = spr.assoc_id AND spr.assoc_type = ? AND spr.symbolic = ?)
				LEFT JOIN signoffs scpi ON (m.monograph_id = scpi.assoc_id AND scpi.assoc_type = ? AND scpi.symbolic = ?)
			WHERE
				sle.user_id = ? AND
				' . (isset($pressId) ? 'm.press_id = ? AND' : '') . '
				sle.date_notified IS NOT NULL';

		if ($active) {
			$sql .= ' AND (sle.date_completed IS NULL OR spr.date_completed IS NULL)'; 
		} else {
			$sql .= ' AND (sle.date_completed IS NOT NULL OR spr.date_completed IS NOT NULL)';
		}

		$result =& $this->retrieveRange(
			$sql . ' ' . $searchSql . ' ORDER BY m.monograph_id ASC',
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
