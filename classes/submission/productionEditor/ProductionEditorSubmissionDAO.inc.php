<?php

/**
 * @file classes/submission/productionEditor/ProductionEditorSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionEditorSubmissionDAO
 * @ingroup submission
 * @see ProductionEditorSubmission
 *
 * @brief Operations for retrieving and modifying ProductionEditorSubmission objects.
 */

// $Id$


import('classes.submission.productionEditor.ProductionEditorSubmission');

class ProductionEditorSubmissionDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $userDao;
	var $productionAssignmentDao;
	var $monographFileDao;
	var $galleyDao;

	/**
	 * Constructor.
	 */
	function ProductionEditorSubmissionDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->productionAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
	}

	/**
	 * Get all submissions assigned to a production editor.
	 * @param $productionEditorId
	 * @param $pressId
	 * @return DAOResultFactory continaing ProductionEditorSubmissions
	 */
	function &getById($monographId, $pressId) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(aal.setting_value, aapl.setting_value) AS series_abbrev
			FROM	monographs m
				LEFT JOIN series s ON (s.series_id = m.series_id)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings aapl ON (s.series_id = aapl.series_id AND aapl.setting_name = ? AND aapl.locale = ?)
				LEFT JOIN series_settings aal ON (s.series_id = aal.series_id AND aal.setting_name = ? AND aal.locale = ?)
			WHERE	m.monograph_id = ? AND
				m.press_id = ?',
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				(int) $monographId,
				(int) $pressId
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
	 * @return ProductionEditorSubmission
	 */
	function newDataObject() {
		return new ProductionEditorSubmission();
	}

	/**
	 * Internal function to return a ProductionEditorSubmission object from a row.
	 * @param $row array
	 * @return ProductionEditorSubmission
	 */
	function &_fromRow(&$row) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$productionEditorSubmission = $this->newDataObject();

		$productionEditorSubmission->setProductionAssignments($this->productionAssignmentDao->getByMonographId($row['monograph_id']));

		// Monograph attributes
		$this->monographDao->_monographFromRow($productionEditorSubmission, $row);

		// Editor Assignment
		$reviewRounds =& $this->monographDao->getReviewRoundsInfoById($row['monograph_id']);

		$productionEditorSubmission->setReviewRoundsInfo($reviewRounds);

		// Files
		$productionEditorSubmission->setSubmissionFile($this->monographFileDao->getMonographFile($row['submission_file_id']));

		$productionEditorSubmission->setEditorFile($this->monographFileDao->getMonographFile($row['editor_file_id']));

		$layoutSignoff =& $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $row['monograph_id']);
		$productionEditorSubmission->setLayoutFile($this->monographFileDao->getMonographFile($layoutSignoff->getFileId()));

		// Layout Editing
		$productionEditorSubmission->setGalleys($this->galleyDao->getByMonographId($row['monograph_id']));

		HookRegistry::call('ProductionEditorSubmissionDAO::_fromRow', array(&$productionEditorSubmission, &$row));

		return $productionEditorSubmission;
	}

	/**
	 * Get all submissions assigned to a production editor.
	 * @param $productionEditorId
	 * @param $pressId
	 * @return DAOResultFactory continaing ProductionEditorSubmissions
	 */
	function &getProductionEditorSubmissions($productionEditorId, $pressId, $active = true, $rangeInfo = null) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieveRange(
			'SELECT	a.*,
				COALESCE(atl.setting_value, atpl.setting_value) AS series_title,
				COALESCE(aal.setting_value, aapl.setting_value) AS series_abbrev
			FROM monographs a
				LEFT JOIN series s ON (s.series_id = a.series_id)
				LEFT JOIN series_settings atpl ON (s.series_id = atpl.series_id AND atpl.setting_name = ? AND atpl.locale = ?)
				LEFT JOIN series_settings atl ON (s.series_id = atl.series_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN series_settings aapl ON (s.series_id = aapl.series_id AND aapl.setting_name = ? AND aapl.locale = ?)
				LEFT JOIN series_settings aal ON (s.series_id = aal.series_id AND aal.setting_name = ? AND aal.locale = ?)
			WHERE	a.user_id = ? AND a.press_id = ? AND ' .
			($active?'a.status = 1':'(a.status <> 1 AND a.submission_progress = 0)'),
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$productionEditorId,
				$pressId
			),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve a list of all designers along with information about their current status with respect to a monograph's production.
	 * @param $pressId int
	 * @param $monographId int
	 * @param $searchType int USER_FIELD_...
	 * @param $search string
	 * @param $searchMatch string "is" or "contains" or "startsWith"
	 * @param $rangeInfo RangeInfo optional
	 * @return DAOResultFactory containing matching Users
	 */
	function &getDesignersForMonograph($pressId, $monographId, $searchType = null, $search = null, $searchMatch = null, $rangeInfo = null) {
		$paramArray = array($pressId, RoleDAO::getRoleIdFromPath('designer'), ASSOC_TYPE_DESIGN_ASSIGNMENT, $monographId);
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
			'SELECT DISTINCT u.*, pa.monograph_id AS assigned
			FROM users u
			LEFT JOIN roles r ON (r.press_id = ? AND r.user_id = u.user_id AND r.role_id = ?)
			LEFT JOIN signoffs s ON (s.user_id = u.user_id AND s.assoc_type = ?)
			LEFT JOIN production_assignments pa ON (s.assoc_id = pa.assignment_id)
			WHERE pa.monograph_id = ? OR pa.monograph_id IS NULL ' . $searchSql . '
			ORDER BY last_name, first_name',
			$paramArray, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnUserFromRow');
		return $returner;
	}

	function &_returnUserFromRow(&$row) { // FIXME
		$user =& $this->userDao->_returnUserFromRowWithData($row);
		$user->setData('isAssignedDesigner', $row['assigned']);

		HookRegistry::call('SeriesEditorSubmissionDAO::_returnReviewerUserFromRow', array(&$designer, &$row));

		return $user;
	}

}

?>
