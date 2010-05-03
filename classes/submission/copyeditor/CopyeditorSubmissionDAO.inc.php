<?php

/**
 * @file classes/submission/copyeditor/CopyeditorSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditorSubmissionDAO
 * @ingroup submission
 * @see CopyeditorSubmission
 *
 * @brief Operations for retrieving and modifying CopyeditorSubmission objects.
 */

// $Id$


import('classes.submission.copyeditor.CopyeditorSubmission');

class CopyeditorSubmissionDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $userDao;
	var $editAssignmentDao;
	var $monographFileDao;
	var $galleyDao;
	var $monographCommentDao;

	/**
	 * Constructor.
	 */
	function CopyeditorSubmissionDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
	}

	/**
	 * Retrieve a copyeditor submission by monograph ID.
	 * @param $monographId int
	 * @return CopyeditorSubmission
	 */
	function &getCopyeditorSubmission($monographId) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieve(
			'SELECT m.*,
				e.editor_id,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM monographs m
				LEFT JOIN edit_assignments e ON (m.monograph_id = e.monograph_id)
				LEFT JOIN series aa ON (aa.series_id = m.series_id)
				LEFT JOIN series_settings stpl ON (aa.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (aa.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (aa.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (aa.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE m.monograph_id = ?',
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
	 * @return SignoffEntry
	 */
	function newDataObject() {
		return new CopyeditorSubmission();
	}

	/**
	 * Internal function to return a CopyeditorSubmission object from a row.
	 * @param $row array
	 * @return CopyeditorSubmission
	 */
	function &_fromRow(&$row) {
		$copyeditorSubmission = $this->newDataObject();

		// Monograph attributes
		$this->monographDao->_monographFromRow($copyeditorSubmission, $row);

		// Editor Assignment
		$editAssignments =& $this->editAssignmentDao->getByMonographId($row['monograph_id']);
		$copyeditorSubmission->setEditAssignments($editAssignments->toArray());

		// Comments
		$copyeditorSubmission->setMostRecentCopyeditComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_COPYEDIT, $row['monograph_id']));
		$copyeditorSubmission->setMostRecentLayoutComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_LAYOUT, $row['monograph_id']));

		// Information for Layout table access
		$copyeditorSubmission->setGalleys($this->galleyDao->getGalleysByMonograph($row['monograph_id']));

		HookRegistry::call('CopyeditorSubmissionDAO::_fromRow', array(&$copyeditorSubmission, &$row));

		return $copyeditorSubmission;
	}

	/**
	 * Get all submissions for a copyeditor of a press.
	 * @param $copyeditorId int
	 * @param $pressId int optional
	 * @param $searchField int SUBMISSION_FIELD_... constant
	 * @param $searchMatch String 'is' or 'contains' or 'startsWith'
	 * @param $search String Search string
	 * @param $dateField int SUBMISSION_FIELD_DATE_... constant
	 * @param $dateFrom int Search from timestamp
	 * @param $dateTo int Search to timestamp
	 * @return array CopyeditorSubmissions
	 */
	function &getByCopyeditorId($copyeditorId, $pressId = null, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $active = true, $rangeInfo = null) {
		$locale = Locale::getLocale();
		$primaryLocale = Locale::getPrimaryLocale();
		$params = array(
			'title', // Series title
			$primaryLocale,
			'title',
			$locale,
			'abbrev', // Series abbrev
			$primaryLocale,
			'abbrev',
			$locale,
			'title', // Monograph title
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_COPYEDITING_FINAL',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_LAYOUT',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_PROOFREADING_PROOFREADER',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_COPYEDITING_INITIAL'
		);

		if (isset($pressId)) $params[] = $pressId;
		$params[] = $copyeditorId;

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
				$first_last = $this->_dataSource->Concat('ma.first_name', '\' \'', 'ma.last_name');
				$first_middle_last = $this->_dataSource->Concat('ma.first_name', '\' \'', 'ma.middle_name', '\' \'', 'ma.last_name');
				$last_comma_first = $this->_dataSource->Concat('ma.last_name', '\', \'', 'ma.first_name');
				$last_comma_first_middle = $this->_dataSource->Concat('ma.last_name', '\', \'', 'ma.first_name', '\' \'', 'ma.middle_name');

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
					$searchSql .= ' AND scp.date_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= ' AND scp.date_completed <= ' . $this->datetimeToDB($dateTo);
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
					$searchSql .= ' AND spr.date_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= 'AND spr.date_completed <= ' . $this->datetimeToDB($dateTo);
				}
				break;
		}

		$sql = 'SELECT DISTINCT
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM
				monographs m
				INNER JOIN monograph_authors ma ON (ma.monograph_id = m.monograph_id)
				LEFT JOIN series aa ON (aa.series_id = m.series_id)
				LEFT JOIN edit_assignments e ON (e.monograph_id = m.monograph_id)
				LEFT JOIN users ed ON (e.editor_id = ed.user_id)
				LEFT JOIN series_settings stpl ON (aa.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (aa.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (aa.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (aa.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN monograph_settings atl ON (m.monograph_id = atl.monograph_id AND atl.setting_name = ?)
				LEFT JOIN signoffs scpf ON (m.monograph_id = scpf.assoc_id AND scpf.assoc_type = ? AND scpf.symbolic = ?)
				LEFT JOIN signoffs sle ON (m.monograph_id = sle.assoc_id AND sle.assoc_type = ? AND sle.symbolic = ?)
				LEFT JOIN signoffs spr ON (m.monograph_id = spr.assoc_id AND spr.assoc_type = ? AND spr.symbolic = ?)
				LEFT JOIN signoffs scpi ON (m.monograph_id = scpi.assoc_id AND scpi.assoc_type = ? AND scpi.symbolic = ?)
			WHERE ' . (isset($pressId)?'m.press_id = ? AND':'') . '
				scpi.user_id = ? AND
				(' . ($active?'':'NOT ') . ' 
					((scpi.date_notified IS NOT NULL AND scpi.date_completed IS NULL) OR 
					(scpf.date_notified IS NOT NULL AND scpf.date_completed IS NULL))
				) ';

		$result =& $this->retrieveRange(
			$sql . ' ' . $searchSql . ' ORDER BY m.monograph_id ASC',
			count($params)==1?array_shift($params):$params,
			$rangeInfo);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get count of active and complete assignments
	 * @param copyeditorId int
	 * @param pressId int
	 */
	function getSubmissionsCount($copyeditorId, $pressId) {
		$submissionsCount = array();
		$submissionsCount[0] = 0;
		$submissionsCount[1] = 0;

		$sql = 'SELECT scf.date_completed 
			FROM monographs m
			LEFT JOIN series aa ON (aa.series_id = m.series_id)
			LEFT JOIN signoffs scf ON (m.monograph_id = scf.assoc_id AND scf.assoc_type = ? AND scf.symbolic = ?)
			LEFT JOIN signoffs sci ON (m.monograph_id = sci.assoc_id AND sci.assoc_type = ? AND sci.symbolic = ?)
			WHERE m.press_id = ? AND 
				sci.user_id = ? AND 
				sci.date_notified IS NOT NULL';
					
		$result =& $this->retrieve(
					$sql, 
					array(
						ASSOC_TYPE_MONOGRAPH, 
						'SIGNOFF_COPYEDITING_FINAL', 
						ASSOC_TYPE_MONOGRAPH, 
						'SIGNOFF_COPYEDITING_INITIAL', 
						$pressId, 
						$copyeditorId
					)
				);

		while (!$result->EOF) {
			if ($result->fields['date_completed'] == null) {
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