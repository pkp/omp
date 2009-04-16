<?php

/**
 * @file classes/reviewForm/ReviewFormDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormDAO
 * @ingroup reviewForm
 * @see ReviewerForm
 *
 * @brief Operations for retrieving and modifying ReviewForm objects.
 *
 */

import ('reviewForm.ReviewForm');

class ReviewFormDAO extends DAO {

	/**
	 * Retrieve a review form by ID.
	 * @param $reviewFormId int
	 * @param $pressId int optional
	 * @return ReviewForm
	 */
	function &getById($reviewFormId, $pressId = null) {
		$params = array((int) $reviewFormId);
		if ($pressId !== null) $params[] = (int) $pressId;

		$result =& $this->retrieve (
			'SELECT	rf.review_form_id,
				rf.press_id,
				rf.seq,
				rf.is_active,
				COUNT(rac.review_id) AS complete_count,
				COUNT(rai.review_id) AS incomplete_count
			FROM	review_forms rf
				LEFT JOIN review_assignments rac ON (
					rac.review_form_id = rf.review_form_id AND
					rac.date_confirmed IS NOT NULL
				)
				LEFT JOIN review_assignments rai ON (
					rai.review_form_id = rf.review_form_id AND
					rai.date_notified IS NOT NULL AND
					rai.date_confirmed IS NULL
				)
			WHERE	rf.review_form_id = ?
				' . ($pressId!==null?' AND rf.press_id = ?':'') . '
			GROUP BY rf.press_id, rf.review_form_id, rf.seq, rf.is_active',
			$params
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
		return new ReviewForm();
	}

	/**
	 * Internal function to return a ReviewForm object from a row.
	 * @param $row array
	 * @return ReviewForm
	 */
	function &_fromRow(&$row) {
		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$reviewForm = $this->newDataObject();

		$reviewForm->setId($row['review_form_id']);
		$reviewForm->setPressId($row['press_id']);
		$reviewForm->setSequence($row['seq']);
		$reviewForm->setActive($row['is_active']);
		$reviewForm->setCompleteCount($row['complete_count']);
		$reviewForm->setIncompleteCount($row['incomplete_count']);

		$this->getDataObjectSettings('review_form_settings', 'review_form_id', $row['review_form_id'], $reviewForm);

		HookRegistry::call('ReviewFormDAO::_fromRow', array(&$reviewForm, &$row));

		return $reviewForm;
	}

	/**
	 * Check if a review form exists with the specified ID.
	 * @param $reviewFormId int
	 * @param $pressId int
	 * @return boolean
	 */
	function reviewFormExists($reviewFormId, $pressId) {
		$result = &$this->retrieve(
			'SELECT COUNT(*) FROM review_forms WHERE review_form_id = ? AND press_id = ?',
			array($reviewFormId, $pressId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the list of fields for which data can be localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'description');
	}

	/**
	 * Update the localized fields for this table
	 * @param $reviewForm object
	 */
	function updateLocaleFields(&$reviewForm) {
		$this->updateDataObjectSettings('review_form_settings', $reviewForm, array(
			'review_form_id' => $reviewForm->getId()
		));
	}

	/**
	 * Insert a new review form.
	 * @param $reviewForm ReviewForm
	 */
	function insertObject(&$reviewForm) {
		$this->update(
			'INSERT INTO review_forms
				(press_id, seq, is_active)
				VALUES
				(?, ?, ?)',
			array(
				$reviewForm->getPressId(),
				$reviewForm->getSequence() == null ? 0 : $reviewForm->getSequence(),
				$reviewForm->getActive() ? 1 : 0
			)
		);

		$reviewForm->setReviewFormId($this->getInsertReviewFormId());
		$this->updateLocaleFields($reviewForm);

		return $reviewForm->getId();
	}

	/**
	 * Update an existing review form.
	 * @param $reviewForm ReviewForm
	 */
	function updateObject(&$reviewForm) {
		$returner = $this->update(
			'UPDATE review_forms
				SET
					press_id = ?,
					seq = ?,
					is_active = ?
				WHERE review_form_id = ?',
			array(
				$reviewForm->getPressId(),
				$reviewForm->getSequence(),
				$reviewForm->getActive(),
				$reviewForm->getId()
			)
		);

		$this->updateLocaleFields($reviewForm);

		return $returner;
	}

	/**
	 * Delete a review form.
	 * @param $reviewForm reviewForm
	 */
	function deleteObject(&$reviewForm) {
		return $this->deleteById($reviewForm->getId(), $reviewForm->getPressId());
	}

	/**
	 * Delete a review form by ID.
	 * @param $reviewFormId int
	 * @param $pressId int optional
	 */
	function deleteById($reviewFormId, $pressId = null) {
		if (isset($pressId)) {
			$reviewForm =& $this->getReviewForm($reviewFormId, $pressId);
			if (!$reviewForm) return null;
			unset($reviewForm);
		}

		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		$reviewFormElementDao->deleteReviewFormElementsByReviewForm($reviewFormId);

		$this->update('DELETE FROM review_form_settings WHERE review_form_id = ?', array($reviewFormId));
		return $this->update('DELETE FROM review_forms WHERE review_form_id = ?', array($reviewFormId));
	}

	/**
	 * Delete all review forms by press ID.
	 * @param $pressId int
	 */
	function deleteByPressId($pressId) {
		$reviewForms = $this->getByPressId($pressId);

		while (!$reviewForms->eof()) {
			$reviewForm =& $reviewForms->next();
			$this->deleteById($reviewForm->getId());
		}
	}

	/**
	 * Get all review forms for a press.
	 * @param $pressId int
	 * @return DAOResultFactory containing matching ReviewForms
	 */
	function &getByPressId($pressId) {
		$result =& $this->retrieveRange(
			'SELECT	rf.review_form_id,
				rf.press_id,
				rf.seq,
				rf.is_active,
				COUNT(rac.review_id) AS complete_count,
				COUNT(rai.review_id) AS incomplete_count
			FROM	review_forms rf
				LEFT JOIN review_assignments rac ON (
					rac.review_form_id = rf.review_form_id AND
					rac.date_confirmed IS NOT NULL
				)
				LEFT JOIN review_assignments rai ON (
					rai.review_form_id = rf.review_form_id AND
					rai.date_notified IS NOT NULL AND
					rai.date_confirmed IS NULL
				)
			WHERE	rf.press_id = ?
			GROUP BY rf.press_id, rf.review_form_id, rf.seq, rf.is_active
			ORDER BY rf.seq',
			$pressId
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get active review forms for a press.
	 * @param $pressId int
	 * @param $rangeInfo object RangeInfo object (optional)
	 * @return DAOResultFactory containing matching ReviewForms
	 */
	function &getActiveByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT	rf.review_form_id,
				rf.press_id,
				rf.seq,
				rf.is_active,
				COUNT(rac.review_id) AS complete_count,
				COUNT(rai.review_id) AS incomplete_count
			FROM	review_forms rf
				LEFT JOIN review_assignments rac ON (
					rac.review_form_id = rf.review_form_id AND
					rac.date_confirmed IS NOT NULL
				)
				LEFT JOIN review_assignments rai ON (
					rai.review_form_id = rf.review_form_id AND
					rai.date_notified IS NOT NULL AND
					rai.date_confirmed IS NULL
				)
			WHERE	rf.press_id = ? AND
				rf.is_active = 1
			GROUP BY rf.press_id, rf.review_form_id, rf.seq, rf.is_active
			ORDER BY rf.seq',
			$pressId, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get used review forms for a press.
	 * @param $pressId int
	 * @param $rangeInfo object RangeInfo object (optional)
	 * @return DAOResultFactory containing matching ReviewForms
	 */
	function &getUsedByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT	rf.review_form_id,
				rf.press_id,
				rf.seq,
				rf.is_active,
				COUNT(rac.review_id) AS complete_count,
				COUNT(rai.review_id) AS incomplete_count
			FROM	review_forms rf
				LEFT JOIN review_assignments rac ON (
					rac.review_form_id = rf.review_form_id AND
					rac.date_confirmed IS NOT NULL
				)
				LEFT JOIN review_assignments rai ON (
					rai.review_form_id = rf.review_form_id AND
					rai.date_notified IS NOT NULL AND
					rai.date_confirmed IS NULL
				)
			WHERE	rf.press_id = ? AND
				rf.is_active = 1
			GROUP BY rf.press_id, rf.review_form_id, rf.seq, rf.is_active
			HAVING COUNT(rac.review_id) > 0 OR COUNT(rai.review_id) > 0
			ORDER BY rf.seq',
			$pressId, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get unused review forms for a press.
	 * @param $pressId int
	 * @param $rangeInfo object RangeInfo object (optional)
	 * @return DAOResultFactory containing matching ReviewForms
	 */
	function &getUnusedByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT	rf.review_form_id,
				rf.press_id,
				rf.seq,
				rf.is_active,
				COUNT(rac.review_id) AS complete_count,
				COUNT(rai.review_id) AS incomplete_count
			FROM	review_forms rf
				LEFT JOIN review_assignments rac ON (
					rac.review_form_id = rf.review_form_id AND
					rac.date_confirmed IS NOT NULL
				)
				LEFT JOIN review_assignments rai ON (
					rai.review_form_id = rf.review_form_id AND
					rai.date_notified IS NOT NULL AND
					rai.date_confirmed IS NULL
				)
			WHERE	rf.press_id = ?
			GROUP BY rf.press_id, rf.review_form_id, rf.seq, rf.is_active
			HAVING COUNT(rac.review_id) = 0 AND COUNT(rai.review_id) = 0
			ORDER BY rf.seq',
			$pressId, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve the IDs and titles of all review forms for a press in an associative array.
	 * @param $pressId int
	 * @param $used int
	 * @return array
	 */
	function &getTitlesByPressId($pressId, $used) {
		$reviewFormTitles = array();

		if ($used) {
			$reviewForms =& $this->getUsedByPressId($pressId);
		} else {
			$reviewForms =& $this->getUnusedByPressId($pressId);
		}
		while (($reviewForm =& $reviewForms->next())) {
			$reviewFormTitles[$reviewForm->getId()] = $reviewForm->getReviewFormTitle();
			unset($reviewForm);
		}

		return $reviewFormTitles;
	}

	/**
	 * Check if a review form exists with the specified ID.
	 * @param $reviewFormId int
	 * @param $pressId int optional
	 * @return boolean
	 */
	function unusedReviewFormExists($reviewFormId, $pressId = null) {
		$params = array((int) $reviewFormId);
		if ($pressId !== null) $params[] = (int) $pressId;

		$result =& $this->retrieve (
			'SELECT	rf.review_form_id,
				COUNT(rac.review_id) AS complete_count,
				COUNT(rai.review_id) AS incomplete_count
			FROM	review_forms rf
				LEFT JOIN review_assignments rac ON (
					rac.review_form_id = rf.review_form_id AND
					rac.date_confirmed IS NOT NULL
				)
				LEFT JOIN review_assignments rai ON (
					rai.review_form_id = rf.review_form_id AND
					rai.date_notified IS NOT NULL AND
					rai.date_confirmed IS NULL
				)
			WHERE	rf.review_form_id = ?
				' . ($pressId!==null?' AND rf.press_id = ?':'') . '
			GROUP BY rf.review_form_id
			HAVING COUNT(rac.review_id) = 0 AND COUNT(rai.review_id) = 0',
			$params
		);

		$returner = $result->RecordCount() != 0;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Sequentially renumber review form in their sequence order.
	 * @param $pressId int
	 */
	function resequenceByPressId($pressId) {
		$result =& $this->retrieve(
			'SELECT review_form_id FROM review_forms WHERE press_id = ? ORDER BY seq',
			(int) $pressId
		);

		for ($i=1; !$result->EOF; $i++) {
			list($reviewFormId) = $result->fields;
			$this->update(
				'UPDATE review_forms SET seq = ? WHERE review_form_id = ?',
				array(
					$i,
					$reviewFormId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Get the ID of the last inserted review form.
	 * @return int
	 */
	function getInsertReviewFormId() {
		return $this->getInsertId('review_forms', 'review_form_id');
	}
}

?>
