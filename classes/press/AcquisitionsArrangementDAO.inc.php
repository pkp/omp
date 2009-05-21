<?php

/**
 * @file classes/press/AcquisitionsArrangementDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcquisitionsArrangementDAO
 * @ingroup press
 * @see AcquisitionsArrangement
 *
 * @brief Operations for retrieving and modifying AcquisitionsArrangement objects.
 */

// $Id$


import ('press.AcquisitionsArrangement');

class AcquisitionsArrangementDAO extends DAO {
	/**
	 * Retrieve an acquisitions arrangement by ID.
	 * @param $arrangementId int
	 * @return AcquisitionsArrangement
	 */
	function &getAcquisitionsArrangement($arrangementId, $pressId = null, $type = null) {
		$sql = 'SELECT * FROM acquisitions_arrangements WHERE arrangement_id = ?';
		$params = array($arrangementId);

		if ($type !== null) {
			$sql.= ' AND arrangement_type = ?';
			$params[] = $type;
		}

		if ($pressId !== null) {
			$sql .= ' AND press_id = ?';
			$params[] = $pressId;
		}
		$result =& $this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnAcquisitionsArrangementFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve an acquisitions arrangement by abbreviation.
	 * @param $arrangementAbbrev string
	 * @param $locale string optional
	 * @return AcquisitionsArrangement
	 */
	function &getAcquisitionsArrangementByAbbrev($arrangementAbbrev, $pressId, $locale = null, $type = null) {
		$sql = 'SELECT s.* FROM acquisitions_arrangements s, acquisitions_arrangements_settings l WHERE l.acquisitions_arrangements_id = s.acquisitions_arrangements_id AND l.setting_name = ? AND l.setting_value = ? AND s.press_id = ?';
		$params = array('abbrev', $arrangementAbbrev, $pressId);

		if ($type !== null) {
			$sql.= ' AND arrangement_type = ?';
			$params[] = $type;
		}
		if ($locale !== null) {
			$sql .= ' AND l.locale = ?';
			$params[] = $locale;
		}

		$result =& $this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnAcquisitionArrangementFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve an acquisitions arrangement by title.
	 * @param $arrangementTitle string
	 * @return AcquisitionsArrangement
	 */
	function &getAcquisitionsArrangementByTitle($arrangementTitle, $pressId, $locale = null) {
		$sql = 'SELECT a.* FROM acquisitions_arrangements a, acquisitions_arrangements_settings l WHERE l.arrangement_id = a.arrangement_id AND l.setting_name = ? AND l.setting_value = ? AND s.press_id = ?';
		$params = array('title', $arrangementTitle, $pressId);
		if ($locale !== null) {
			$sql .= ' AND l.locale = ?';
			$params[] = $locale;
		}

		$result =& $this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnAcquisitionsArrangementFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve an acquisitions arrangement by title and abbrev.
	 * @param $arrangementTitle string
	 * @param $arrangementAbbrev string
	 * @param $locale string optional
	 * @return AcquisitionsArrangement
	 */
	function &getAcquisitionsArrangementByTitleAndAbbrev($arrangementTitle, $arrangementAbbrev, $pressId, $locale) {
		$sql = 'SELECT a.* FROM acquisitions_arrangements a, acquisitions_arrangements_settings l1, acquisitions_arrangements_settings l2 WHERE l1.arrangement_id = a.arrangement_id AND l2.arrangement_id = a.arrangement_id AND l1.setting_name = ? AND l2.setting_name = ? AND l1.setting_value = ? AND l2.setting_value = ? AND a.press_id = ?';
		$params = array('title', 'abbrev', $arrangementTitle, $arrangementAbbrev, $pressId);
		if ($locale !== null) {
			$sql .= ' AND l1.locale = ? AND l2.locale = ?';
			$params[] = $locale;
			$params[] = $locale;
		}

		$result =& $this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnAcquisitionsArrangementFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return an AcquisitionsArrangement object from a row.
	 * @param $row array
	 * @return AcquisitionsArrangement
	 */
	function &_returnAcquisitionsArrangementFromRow(&$row) {
		$arrangement =& new AcquisitionsArrangement();
		$arrangement->setAcquisitionsArrangementId($row['arrangement_id']);
		$arrangement->setPressId($row['press_id']);
		$arrangement->setReviewFormId($row['review_form_id']);
		$arrangement->setSequence($row['seq']);
		$arrangement->setMetaIndexed($row['meta_indexed']);
		$arrangement->setEditorRestricted($row['editor_restricted']);
		$arrangement->setHideAbout($row['hide_about']);
		$arrangement->setDisableComments($row['disable_comments']);
		$arrangement->setArrangementType($row['arrangement_type']);

		$this->getDataObjectSettings('acquisitions_arrangements_settings', 'arrangement_id', $row['arrangement_id'], $arrangement);

		HookRegistry::call('AcquisitionsArrangementDAO::_returnAcquisitionsArrangementFromRow', array(&$arrangement, &$row));

		return $arrangement;
	}

	/**
	 * Get the list of fields for which data can be localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abbrev', 'policy');
	}

	/**
	 * Update the localized fields for this table
	 * @param $arrangement object
	 */
	function updateLocaleFields(&$arrangement) {
		$this->updateDataObjectSettings('acquisitions_arrangements_settings', $arrangement, array(
			'arrangement_id' => $arrangement->getAcquisitionsArrangementId()
		));
	}

	/**
	 * Insert a new acquisitions arrangement.
	 * @param $arrangement AcquisitionsArrangement
	 */	
	function insertAcquisitionsArrangement(&$arrangement) {
		$this->update(
			'INSERT INTO acquisitions_arrangements
				(press_id, review_form_id, seq, meta_indexed, editor_restricted, hide_about, disable_comments, arrangement_type)
				VALUES
				(?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				$arrangement->getPressId(),
				$arrangement->getReviewFormId(),
				$arrangement->getSequence() == null ? 0 : $arrangement->getSequence(),
				$arrangement->getMetaIndexed() ? 1 : 0,
				$arrangement->getEditorRestricted() ? 1 : 0,
				$arrangement->getHideAbout() ? 1 : 0,
				$arrangement->getDisableComments() ? 1 : 0,
				$arrangement->getArrangementType()
			)
		);

		$arrangement->setAcquisitionsArrangementId($this->getInsertAcquisitionsArrangementId());
		$this->updateLocaleFields($arrangement);
		return $arrangement->getAcquisitionsArrangementId();
	}

	/**
	 * Update an existing acquisitions arrangement.
	 * @param $arrangement AcquisitionsArrangement
	 */
	function updateAcquisitionsArrangement($arrangement) {
		$returner = $this->update(
			'UPDATE acquisitions_arrangements
				SET
					review_form_id = ?,
					seq = ?,
					meta_indexed = ?,
					editor_restricted = ?,
					hide_about = ?,
					disable_comments = ?,
					arrangement_type = ?
				WHERE arrangement_id = ?',
			array(
				$arrangement->getReviewFormId(),
				$arrangement->getSequence(),
				$arrangement->getMetaIndexed(),
				$arrangement->getEditorRestricted(),
				$arrangement->getHideAbout(),
				$arrangement->getDisableComments(),
				$arrangement->getArrangementType(),
				$arrangement->getAcquisitionsArrangementId()
			)
		);
		$this->updateLocaleFields($arrangement);
		return $returner;
	}

	/**
	 * Delete an arrangement.
	 * @param $arrangement AcquisitionsArrangement
	 */
	function deleteSeries(&$arrangement) {
		return $this->deleteById($arrangement->getAcquisitionsArrangementId(), $arrangement->getPressId());
	}

	/**
	 * Delete an arrangement by ID.
	 * @param $arrangementId int
	 * @param $pressId int optional
	 */
	function deleteById($arrangementId, $pressId = null) {
		$arrangementEditorsDao =& DAORegistry::getDAO('AcquisitionsArrangementEditorsDAO');
		$arrangementEditorsDao->deleteEditorsByAcquisitionsArrangementId($arrangementId, $pressId);

		// Remove monographs from this arrangement
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographDao->removeMonographsFromAcquisitionsArrangement($arrangementId);

		if (isset($pressId) && !$this->acquisitionsArrangementExists($arrangementId, $pressId)) return false;
		$this->update('DELETE FROM acquisitions_arrangements_settings WHERE arrangement_id = ?', array($arrangementId));
		return $this->update('DELETE FROM acquisitions_arrangements WHERE arrangement_id = ?', array($arrangementId));
	}

	/**
	 * Delete acquisitions arrangements by press ID
	 * NOTE: This does not delete dependent entries EXCEPT from acquisitions_arrangements_editors. It is intended
	 * to be called only when deleting a press.
	 * @param $pressId int
	 */
	function deleteAcquisitionsArrangementsByPress($pressId) {
		$arrangements =& $this->getPressAcquisitionsArrangements($pressId);
		while (($arrangement =& $arrangements->next())) {
			$this->deleteAcquisitionsArrangement($arrangement);
			unset($arrangement);
		}
	}

	/**
	 * Retrieve an array associating all arrangement editor IDs with 
	 * arrays containing the arrangements they edit.
	 * @return array editorId => array(acquisitions arrangements they edit)
	 */
	function &getEditorAcquisitionArrangements($pressId, $type = null) {
		$returner = array();
		$sql = 'SELECT a.*, ae.user_id AS editor_id FROM acquisitions_arrangements_editors ae, acquisitions_arrangements a WHERE ae.arrangement_id = a.arrangement_id AND a.press_id = ae.press_id AND a.press_id = ?';
		$params = array($pressId);

		if ($type !== null) {
			$params[] = $type;
			$sql.= ' AND arrangement_type = ?';
		}

		$result =& $this->retrieve($sql, $params);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$arrangement =& $this->_returnAcquisitionsArrangementFromRow($row);
			if (!isset($returner[$row['editor_id']])) {
				$returner[$row['editor_id']] = array($arrangement);
			} else {
				$returner[$row['editor_id']][] = $arrangement;
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all acquisitions arrangements for a press.
	 * @return DAOResultFactory containing AcquisitionsArrangement ordered by sequence
	 */
	function &getPressAcquisitionsArrangements($pressId, $rangeInfo = null, $type = null) {

		$sql = 'SELECT * FROM acquisitions_arrangements WHERE press_id = ?';
		$params = array($pressId);
	      
		if ($type !== null) {
			$sql.= 'AND arrangement_type = ?';
			$params[] = $type;
		}
		
		$result =& $this->retrieveRange($sql.' ORDER BY seq', $params, $rangeInfo);

		$returner = new DAOResultFactory($result, $this, '_returnAcquisitionsArrangementFromRow');
		return $returner;
	}

	/**
	 * Retrieve the IDs and titles of the arrangements for a press in an associative array.
	 * @return array
	 */
	function &getAcquisitionsArrangementsTitles($pressId, $submittableOnly = false) {
		$arrangements = array();

		$arrangementIterator =& $this->getPressAcquisitionsArrangements($pressId);
		while (($arrangement =& $arrangementIterator->next())) {
			if ($submittableOnly) {
				if (!$arrangement->getEditorRestricted()) {
					$arrangements[$arrangement->getAcquisitionsArrangementId()] = $arrangement->getAcquisitionsArrangementTitle();
				}
			} else {
				$arrangements[$arrangement->getAcquisitionsArrangementId()] = $arrangement->getAcquisitionsArrangementTitle();
			}
			unset($arrangement);
		}

		return $arrangements;
	}

	/**
	 * Check if an acquisitions arrangement exists with the specified ID.
	 * @param $arrangementId int
	 * @param $pressId int
	 * @return boolean
	 */
	function acquisitionsArrangementExists($arrangementId, $pressId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM acquisitions_arrangements WHERE arrangement_id = ? AND press_id = ?',
			array($arrangementId, $pressId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Sequentially renumber acquisition arrangements in their sequence order.
	 * @param $pressId int
	 */
	function resequenceAcquisitionsArrangements($type = SERIES_ARRANGEMENT) {
		$result =& $this->retrieve(
			'SELECT arrangement_id FROM acquisitions_arrangements WHERE arrangement_type = ? ORDER BY seq',
			array($type)
		);

		for ($i=1; !$result->EOF; $i++) {
			list($arrangementId) = $result->fields;
			$this->update(
				'UPDATE acquisitions_arrangements SET seq = ? WHERE arrangement_id = ?',
				array(
					$i,
					$arrangementId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Get the ID of the last inserted arrangement.
	 * @return int
	 */
	function getInsertAcquisitionsArrangementId() {
		return $this->getInsertId('acquisitions_arrangements', 'arrangement_id');
	}


	function updateSetting($arrangementId, $name, $value) {
		$this->update('DELETE FROM acquisitions_arrangements_settings WHERE arrangement_id = ? AND setting_name = ?',
				array($arrangementId, $name)
			);
		$this->update('INSERT INTO acquisitions_arrangements_settings
			(arrangement_id, setting_name, setting_value, setting_type)
			VALUES (?, ?, ?, ?)',
			array(
				$arrangementId, $name, $value, 'string'
			)
		);
	}
	function getSetting($arrangementId, $name) {
		$result =& $this->retrieve(
			'SELECT setting_value FROM acquisitions_arrangements_settings WHERE arrangement_id = ? AND setting_name = ?', 
			array($arrangementId, $name)
		);
		
		if (!$result->EOF) {
			$row =& $result->getRowAssoc(false);
			$value = $row['setting_value'];
		} else $value = null;

		$result->Close();
		unset($result);

		return $value;
	}

}

?>