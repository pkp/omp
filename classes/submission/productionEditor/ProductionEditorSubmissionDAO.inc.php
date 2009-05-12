<?php

/**
 * @file classes/submission/productionEditor/ProductionEditorSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionEditorSubmissionDAO
 * @ingroup submission
 * @see ProductionEditorSubmission
 *
 * @brief Operations for retrieving and modifying ProductionEditorSubmission objects.
 */

// $Id$


import('submission.productionEditor.ProductionEditorSubmission');

class ProductionEditorSubmissionDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $userDao;
	var $editAssignmentDao;
	var $monographFileDao;
	var $suppFileDao;
	var $galleyDao;
	var $monographEmailLogDao;
	var $monographCommentDao;
	var $proofAssignmentDao;

	/**
	 * Constructor.
	 */
	function ProductionEditorSubmissionDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
	//	$this->layoutAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
//		$this->monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
//		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
//		$this->proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
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
			'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev,
				sc.file_id AS layout_file_id
			FROM monographs a
				INNER JOIN signoffs sc ON (sc.assoc_type = ? AND sc.assoc_id = ? AND sc.symbolic = ?)
				LEFT JOIN acquisitions_arrangements s ON (s.arrangement_id = a.arrangement_id)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE a.press_id = ?',
			array(
				ASSOC_TYPE_MONOGRAPH,
				$monographId,
				'SIGNOFF_LAYOUT',
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$pressId
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
		$productionEditorSubmission = $this->newDataObject();

		// Monograph attributes
		$this->monographDao->_monographFromRow($productionEditorSubmission, $row);

		// Editor Assignment


//		$layoutAssignments =& $this->layoutAssignmentDao->getByMonographId($row['monograph_id']);
	//	$productionEditorSubmission->setLayoutAssignments($layoutAssignments);

		$reviewRounds =& $this->monographDao->getReviewRoundsInfoById($row['monograph_id']);

		$productionEditorSubmission->setReviewRoundsInfo($reviewRounds);

		// Files
		$productionEditorSubmission->setSubmissionFile($this->monographFileDao->getMonographFile($row['submission_file_id']));
		$productionEditorSubmission->setSuppFiles($this->suppFileDao->getSuppFilesByMonograph($row['monograph_id']));
		$productionEditorSubmission->setEditorFile($this->monographFileDao->getMonographFile($row['editor_file_id']));
		$productionEditorSubmission->setLayoutFile($this->monographFileDao->getMonographFile($row['layout_file_id']));

		// Layout Editing

		$productionEditorSubmission->setGalleys($this->galleyDao->getByMonographId($row['monograph_id']));
 
		HookRegistry::call('ProductionEditorSubmissionDAO::_fromRow', array(&$productionEditorSubmission, &$row));

		return $productionEditorSubmission;
	}

	/**
	 * Update an existing section editor submission.
	 * @param $productionEditorSubmission ProductionEditorSubmission
	 */
	function updateObject(&$productionEditorSubmission) {

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
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM monographs a
				LEFT JOIN acquisitions_arrangements s ON (s.arrangement_id = a.arrangement_id)
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
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
}

?>