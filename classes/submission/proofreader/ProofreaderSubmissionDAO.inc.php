<?php

/**
 * @file classes/submission/proofreader/ProofreaderSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofreaderSubmissionDAO
 * @ingroup submission_proofreader
 * @see ProofreaderSubmission
 *
 * @brief Operations for retrieving and modifying ProofreaderSubmission objects.
 */



/* FIXME #5557: We need a general code cleanup here (remove useless functions), and to integrate with monograph_stage_assignments table */

class ProofreaderSubmissionDAO extends DAO {
	/** Helper DAOs */
	var $monographDao;
	var $monographCommentDao;
	var $galleyDao;

	/**
	 * Constructor.
	 */
	function ProofreaderSubmissionDAO() {
		parent::DAO();

		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
	}

	/**
	 * Retrieve a proofreader submission by monograph ID.
	 * @param $monographId int
	 * @return ProofreaderSubmission
	 */
	function &getSubmission($monographId, $pressId = null) {
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
			$monographId
		);
		if ($pressId) $params[] = $pressId;

		$result =& $this->retrieve(
			'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_arrangment_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_arrangment_abbrev
			FROM monographs a
				LEFT JOIN series_arrangments aa ON aa.series_arrangment_id = a.series_arrangment_id
				LEFT JOIN series_arrangment_settings stpl ON (aa.series_arrangment_id = stpl.series_arrangment_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_arrangment_settings stl ON (aa.series_arrangment_id = stl.series_arrangment_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_arrangment_settings sapl ON (aa.series_arrangment_id = sapl.series_arrangment_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_arrangment_settings sal ON (aa.series_arrangment_id = sal.series_arrangment_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	monograph_id = ?' .
				($pressId?' AND a.press_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnSubmissionFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a ProofreaderSubmission object from a row.
	 * @param $row array
	 * @return ProofreaderSubmission
	 */
	function &_returnSubmissionFromRow(&$row) {
		$submission = new ProofreaderSubmission();
		$this->monographDao->_monographFromRow($submission, $row);
		$submission->setMostRecentProofreadComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_PROOFREAD, $row['monograph_id']));

		// Editor Assignment
		// FIXME #5557: Ensure compatibility with monograph stage assignment DAO
		$editAssignments =& $this->editAssignmentDao->getEditAssignmentsByMonographId($row['monograph_id']);
		$submission->setEditAssignments($editAssignments->toArray());

		// Layout reference information
		$submission->setGalleys($this->galleyDao->getGalleysByMonograph($row['monograph_id']));

		$submission->setMostRecentLayoutComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_LAYOUT, $row['monograph_id']));

		HookRegistry::call('ProofreaderSubmissionDAO::_returnProofreaderSubmissionFromRow', array(&$submission, &$row));

		return $submission;
	}

	/**
	 * Get set of proofreader assignments assigned to the specified proofreader.
	 * @param $proofreaderId int
	 * @param $pressId int optional
	 * @param $searchField int SUBMISSION_FIELD_... constant
	 * @param $searchMatch String 'is' or 'contains' or 'startsWith'
	 * @param $search String Search string
	 * @param $dateField int SUBMISSION_FIELD_DATE_... constant
	 * @param $dateFrom int Search from timestamp
	 * @param $dateTo int Search to timestamp
	 * @param $active boolean true to select active assignments, false to select completed assignments
	 * @return array ProofreaderSubmission
	 */
	function &getSubmissions($proofreaderId, $pressId = null, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $active = true, $rangeInfo = null, $sortBy = null, $sortDirection = 'ASC') {
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
			'SIGNOFF_PROOFREADING_PROOFREADER',
			ASSOC_TYPE_MONOGRAPH,
			'SIGNOFF_COPYEDITING_INITIAL',
			$proofreaderId
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
				a.*,
				spr.date_notified AS date_assigned,
				spr.date_completed AS date_completed,
				atl.setting_value AS submission_title,
				aap.last_name AS author_name,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_arrangment_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_arrangment_abbrev
			FROM	monographs a
				LEFT JOIN authors aa ON (aa.submission_id = a.monograph_id)
				LEFT JOIN authors aap ON (aap.submission_id = a.monograph_id AND aap.primary_contact = 1)
				LEFT JOIN series_arrangments aa ON aa.series_arrangment_id = a.series_arrangment_id
				LEFT JOIN edit_assignments e ON (e.monograph_id = a.monograph_id)
				LEFT JOIN users ed ON (e.editor_id = ed.user_id)
				LEFT JOIN series_arrangment_settings stpl ON (aa.series_arrangment_id = stpl.series_arrangment_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_arrangment_settings stl ON (aa.series_arrangment_id = stl.series_arrangment_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_arrangment_settings sapl ON (aa.series_arrangment_id = sapl.series_arrangment_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_arrangment_settings sal ON (aa.series_arrangment_id = sal.series_arrangment_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN monograph_settings atl ON (a.monograph_id = atl.monograph_id AND atl.setting_name = ?)
				LEFT JOIN signoffs scpf ON (a.monograph_id = scpf.assoc_id AND scpf.assoc_type = ? AND scpf.symbolic = ?)
				LEFT JOIN signoffs sle ON (a.monograph_id = sle.assoc_id AND sle.assoc_type = ? AND sle.symbolic = ?)
				LEFT JOIN signoffs spr ON (a.monograph_id = spr.assoc_id AND spr.assoc_type = ? AND spr.symbolic = ?)
				LEFT JOIN signoffs scpi ON (a.monograph_id = scpi.assoc_id AND scpi.assoc_type = ? AND scpi.symbolic = ?)
			WHERE
				spr.user_id = ? AND
				' . (isset($pressId)?'a.press_id = ? AND':'') . '
				spr.date_notified IS NOT NULL';

		if ($active) {
			$sql .= ' AND spr.date_completed IS NULL';
		} else {
			$sql .= ' AND spr.date_completed IS NOT NULL';
		}

		$result =& $this->retrieveRange($sql . ' ' . $searchSql . ($sortBy?(' ORDER BY ' . $sortBy . ' ' . $sortDirection) : ''), $params, $rangeInfo);

		$returner = new DAOResultFactory ($result, $this, '_returnSubmissionFromRow');
		return $returner;
	}

	/**
	 * Get count of active and complete assignments
	 * @param proofreaderId int
	 * @param pressId int
	 */
	function getSubmissionsCount($proofreaderId, $pressId) {
		$submissionsCount = array();
		$submissionsCount[0] = 0;
		$submissionsCount[1] = 0;

		$sql = 'SELECT
					spp.date_completed
				FROM
					monographs a
					LEFT JOIN signoffs spp ON (a.monograph_id = spp.assoc_id AND spp.assoc_type = ? AND spp.symbolic = ?)
					LEFT JOIN series_arrangments aa ON aa.series_arrangment_id = a.series_arrangment_id
				WHERE
					spp.user_id = ? AND a.press_id = ? AND spp.date_notified IS NOT NULL';

		$result =& $this->retrieve($sql, array(ASSOC_TYPE_MONOGRAPH, 'SIGNOFF_PROOFREADING_PROOFREADER', $proofreaderId, $pressId));

		while (!$result->EOF) {
			if ($result->fields['date_completed'] == null) {
				$submissionsCount[0] += 1;
			} else {
				$submissionsCount[1] += 1;
			}
			$result->moveNext();
		}

		return $submissionsCount;
	}

	/**
	 * Map a column heading value to a database value for sorting
	 * @param string
	 * @return string
	 */
	function getSortMapping($heading) {
		switch ($heading) {
			case 'id': return 'a.monograph_id';
			case 'assignDate': return 'date_assigned';
			case 'dateCompleted': return 'date_completed';
			case 'series_arrangment': return 'series_arrangment_abbrev';
			case 'authors': return 'author_name';
			case 'title': return 'submission_title';
			case 'status': return 'a.status';
			default: return null;
		}
	}
}

?>
