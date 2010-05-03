<?php

/**
 * @file classes/submission/designer/DesignerSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DesignerSubmissionDAO
 * @ingroup submission_designer
 * @see DesignerSubmission
 *
 * @brief Operations for retrieving and modifying DesignerSubmission objects.
 */

// $Id$


import('classes.submission.designer.DesignerSubmission');

class DesignerSubmissionDAO extends DAO {
	/** Helper DAOs */
	var $monographDao;
	var $galleyDao;
	var $productionAssignmentDao;

	/**
	 * Constructor.
	 */
	function DesignerSubmissionDAO() {
		parent::DAO();

		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$this->productionAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');
	}

	/**
	 * Retrieve a layout editor submission by monograph ID.
	 * @param $monographId int
	 * @return DesignerSubmission
	 */
	function &getSubmission($monographId, $pressId =  null) {
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
			'SELECT
				m.*,
				COALESCE(atl.setting_value, atpl.setting_value) AS series_title,
				COALESCE(aal.setting_value, aapl.setting_value) AS series_abbrev
			FROM monographs m
				LEFT JOIN series aa ON (aa.series_id = m.series_id)
				LEFT JOIN series_settings atpl ON (aa.series_id = atpl.series_id AND atpl.setting_name = ? AND atpl.locale = ?)
				LEFT JOIN series_settings atl ON (aa.series_id = atl.series_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN series_settings aapl ON (aa.series_id = aapl.series_id AND aapl.setting_name = ? AND aapl.locale = ?)
				LEFT JOIN series_settings aal ON (aa.series_id = aal.series_id AND aal.setting_name = ? AND aal.locale = ?)
			WHERE m.monograph_id = ?' .
			($pressId?' AND m.press_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow( $result->GetRowAssoc(false), $assignmentId);
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

		$submission->setProductionAssignments($this->productionAssignmentDao->getByMonographId($row['monograph_id'], $user->getId()));
		$layoutAssignment =& $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $row['monograph_id']);
		$submission->setLayoutAssignment($layoutAssignment);
		$submission->setLayoutFile($monographFileDao->getMonographFile($layoutAssignment->getFileId()));
		$submission->setGalleys($this->galleyDao->getByMonographId($row['monograph_id'], $user->getId()));

		HookRegistry::call('DesignerSubmissionDAO::_fromRow', array(&$submission, &$row));

		return $submission;
	}

	/**
	 * Get set of layout/design assignments assigned to the specified designer.
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
			ASSOC_TYPE_PRODUCTION_ASSIGNMENT,
			'PRODUCTION_DESIGN',
			$designerId
		);
		if (isset($pressId)) $params[] = $pressId;

		$searchSql = '';

		if (!empty($search)) switch ($searchField) {
			case SUBMISSION_FIELD_TITLE:
				if ($searchMatch === 'is') {
					$searchSql = ' AND LOWER(mtl.setting_value) = LOWER(?)';
				} elseif ($searchMatch === 'contains') {
					$searchSql = ' AND LOWER(mtl.setting_value) LIKE LOWER(?)';
					$search = '%' . $search . '%';
				} else { // $searchMatch === 'startsWith'
					$searchSql = ' AND LOWER(mtl.setting_value) LIKE LOWER(?)';
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
					$searchSql = " AND (LOWER(ma.last_name) = LOWER(?) OR LOWER($first_last) = LOWER(?) OR LOWER($first_middle_last) = LOWER(?) OR LOWER($last_comma_first) = LOWER(?) OR LOWER($last_comma_first_middle) = LOWER(?))";
				} elseif ($searchMatch === 'contains') {
					$searchSql = " AND (LOWER(ma.last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
					$search = '%' . $search . '%';
				} else { // $searchMatch === 'startsWith'
					$searchSql = " AND (LOWER(ma.last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
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
			case SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE:
				if (!empty($dateFrom)) {
					$searchSql .= ' AND da.date_completed >= ' . $this->datetimeToDB($dateFrom);
				}
				if (!empty($dateTo)) {
					$searchSql .= ' AND da.date_completed <= ' . $this->datetimeToDB($dateTo);
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
				COALESCE(atl.setting_value, atpl.setting_value) AS series_title,
				COALESCE(aal.setting_value, aapl.setting_value) AS series_abbrev
			FROM
				monographs m
				INNER JOIN monograph_authors ma ON (ma.monograph_id = m.monograph_id)
				LEFT JOIN series s ON (s.series_id = m.series_id)
				LEFT JOIN series_settings atpl ON (s.series_id = atpl.series_id AND atpl.setting_name = ? AND atpl.locale = ?)
				LEFT JOIN series_settings atl ON (s.series_id = atl.series_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN series_settings aapl ON (s.series_id = aapl.series_id AND aapl.setting_name = ? AND aapl.locale = ?)
				LEFT JOIN series_settings aal ON (s.series_id = aal.series_id AND aal.setting_name = ? AND aal.locale = ?)
				LEFT JOIN monograph_settings mtl ON (m.monograph_id = mtl.monograph_id AND mtl.setting_name = ?)
				INNER JOIN production_assignments pa ON (pa.monograph_id = m.monograph_id)
				LEFT JOIN signoffs da ON (da.assoc_type = ? AND da.symbolic = ? AND da.assoc_id = pa.assignment_id)
			WHERE
				da.user_id = ? AND
				' . (isset($pressId) ? 'm.press_id = ? AND' : '') . '
				da.date_notified IS NOT NULL';

		if ($active) {
			$sql .= ' AND (da.date_acknowledged IS NULL)'; 
		} else {
			$sql .= ' AND (da.date_acknowledged IS NOT NULL)';
		}

		$result =& $this->retrieveRange(
			$sql . ' ' . $searchSql . ' ORDER BY m.monograph_id ASC',
			count($params)==1?array_shift($params):$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}
}

?>
