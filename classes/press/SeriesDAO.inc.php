<?php

/**
 * @file classes/press/SeriesDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesDAO
 * @ingroup press
 * @see Series
 *
 * @brief Operations for retrieving and modifying Series objects.
 */

import ('classes.press.Series');

class SeriesDAO extends DAO {
	/**
	 * Constructor
	 */
	function SeriesDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve an series by ID.
	 * @param $seriesId int
	 * @param $pressId int optional
	 * @return Series
	 */
	function &getById($seriesId, $pressId = null) {
		$sql = 'SELECT * FROM series WHERE series_id = ?';
		$params = array((int) $seriesId);

		if ($pressId !== null) {
			$sql .= ' AND press_id = ?';
			$params[] = (int) $pressId;
		}
		$result =& $this->retrieve($sql, $params);

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
	 * @return Series
	 */
	function newDataObject() {
		return new Series();
	}

	/**
	 * Internal function to return an Series object from a row.
	 * @param $row array
	 * @return Series
	 */
	function _fromRow(&$row) {
		$series = $this->newDataObject();

		$series->setId($row['series_id']);
		$series->setPressId($row['press_id']);
		$series->setFeatured($row['featured']);

		$this->getDataObjectSettings('series_settings', 'series_id', $row['series_id'], $series);

		HookRegistry::call('SeriesDAO::_fromRow', array(&$series, &$row));

		return $series;
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
	 * @param $series object
	 */
	function updateLocaleFields(&$series) {
		$this->updateDataObjectSettings(
			'series_settings',
			$series,
			array('series_id' => $series->getId())
		);
	}

	/**
	 * Insert a new series.
	 * @param $series Series
	 */
	function insertObject(&$series) {
		$this->update(
			'INSERT INTO series
				(press_id, featured)
			VALUES
				(?, ?, ?)',
			array(
				$series->getPressId(),
				$series->getFeatured()
			)
		);

		$series->setId($this->getInsertSeriesId());
		$this->updateLocaleFields($series);
		return $series->getId();
	}

	/**
	 * Update an existing series.
	 * @param $series Series
	 */
	function updateObject($series) {
		$returner = $this->update(
			'UPDATE series
			SET	press_id = ?,
				featured = ?
			WHERE	series_id = ?',
			array(
				$series->getPressId(),
				$series->getFeatured(),
				$series->getId()
			)
		);
		$this->updateLocaleFields($series);
		return $returner;
	}

	/**
	 * Delete an series.
	 * @param $series Series
	 */
	function deleteObject(&$series) {
		return $this->deleteById($series->getId(), $series->getPressId());
	}

	/**
	 * Delete an series by ID.
	 * @param $seriesId int
	 * @param $pressId int optional
	 */
	function deleteById($seriesId, $pressId = null) {
		// Validate the $pressId, if supplied.
		if (!$this->seriesExists($seriesId, $pressId)) return false;

		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$seriesEditorsDao->deleteEditorsBySeriesId($seriesId, $pressId);

		// Remove monographs from this series
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographDao->removeMonographsFromSeries($seriesId);

		// Delete the series and settings.
		$this->update('DELETE FROM series WHERE series_id = ?', (int) $seriesId);
		$this->update('DELETE FROM series_settings WHERE series_id = ?', (int) $seriesId);
	}

	/**
	 * Delete series by press ID
	 * NOTE: This does not delete dependent entries EXCEPT from series_editors. It is intended
	 * to be called only when deleting a press.
	 * @param $pressId int
	 */
	function deleteByPressId($pressId) {
		$series =& $this->getByPressId($pressId);
		while (($series =& $series->next())) {
			$this->deleteObject($series);
			unset($series);
		}
	}

	/**
	 * Retrieve an array associating all series editor IDs with
	 * arrays containing the series they edit.
	 * @return array editorId => array(series they edit)
	 */
	function &getEditorSeries($pressId) {
		$result =& $this->retrieve(
			'SELECT	a.*,
				ae.user_id AS editor_id
			FROM	series_editors ae,
				series a
			WHERE	ae.series_id = a.series_id AND
				a.press_id = ae.press_id AND
				a.press_id = ?',
			(int) $pressId
		);

		$returner = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$series =& $this->_fromRow($row);
			if (!isset($returner[$row['editor_id']])) {
				$returner[$row['editor_id']] = array($series);
			} else {
				$returner[$row['editor_id']][] = $series;
			}
			$result->moveNext();
			unset($series);
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all series for a press.
	 * @return DAOResultFactory containing Series ordered by sequence
	 */
	function &getByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM series WHERE press_id = ?',
			(int) $pressId,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve the IDs and titles of the series for a press in an associative array.
	 * @return array
	 */
	function &getTitlesByPressId($pressId, $submittableOnly = false) {
		$seriesTitles = array();

		$seriesIterator =& $this->getByPressId($pressId, null);
		while (($series =& $seriesIterator->next())) {
			if ($submittableOnly) {
				if (!$series->getEditorRestricted()) {
					$seriesTitles[$series->getId()] = $series->getLocalizedTitle();
				}
			} else {
				$seriesTitles[$series->getId()] = $series->getLocalizedTitle();
			}
			unset($series);
		}

		return $seriesTitles;
	}

	/**
	 * Check if an series exists with the specified ID.
	 * @param $seriesId int
	 * @param $pressId int
	 * @return boolean
	 */
	function seriesExists($seriesId, $pressId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM series WHERE series_id = ? AND press_id = ?',
			array((int) $seriesId, (int) $pressId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the ID of the last inserted series.
	 * @return int
	 */
	function getInsertSeriesId() {
		return $this->getInsertId('series', 'series_id');
	}

	/**
	 * Associate a category with a series.
	 * @param $seriesId int
	 * @param $categoryId int
	 */
	function addCategory($seriesId, $categoryId) {
		$this->update(
			'INSERT INTO series_categories
				(series_id, category_id)
			VALUES
				(?, ?)',
			array(
				(int) $seriesId,
				(int) $categoryId
			)
		);
	}

	/**
	 * Unassociate a category with a series.
	 * @param $seriesId int
	 * @param $categoryId int
	 */
	function removeCategory($seriesId, $categoryId) {
		$this->update(
			'DELETE FROM series_categories WHERE series_id = ? AND category_id = ?',
			array(
				(int) $seriesId,
				(int) $categoryId
			)
		);
	}

	/**
	 * Get the categories associated with a given series.
	 * @param $seriesId int
	 * @return DAOResultFactory
	 */
	function getCategories($seriesId, $pressId = null) {
		$params = array((int) $seriesId);
		if ($pressId) $params[] = (int) $pressId;

		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$result =& $this->retrieve(
			'SELECT	c.*
			FROM	categories c,
				series_categories sc,
				series s
			WHERE	c.category_id = sc.category_id AND
				s.series_id = ? AND
			' . ($pressId?' c.press_id = s.press_id AND s.press_id = ? AND':'') . '
				s.series_id = sc.series_id',
			$params
		);

		// Delegate category creation to the category DAO.
		$returner = new DAOResultFactory($result, $categoryDao, '_fromRow');
		return $returner;
	}

	/**
	 * Get the categories not associated with a given series.
	 * @param $seriesId int
	 * @return DAOResultFactory
	 */
	function getUnassignedCategories($seriesId, $pressId = null) {
		$params = array((int) $seriesId);
		if ($pressId) $params[] = (int) $pressId;

		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$result =& $this->retrieve(
			'SELECT	c.*
			FROM	series s
				JOIN categories c ON (c.press_id = s.press_id)
				LEFT JOIN series_categories sc ON (s.series_id = sc.series_id AND sc.category_id = c.category_id)
			WHERE	s.series_id = ? AND
				' . ($pressId?' s.press_id = ? AND':'') . '
				sc.series_id IS NULL',
			$params
		);

		// Delegate category creation to the category DAO.
		$returner = new DAOResultFactory($result, $categoryDao, '_fromRow');
		return $returner;
	}

	/**
	 * Check if an series exists with the specified ID.
	 * @param $seriesId int
	 * @param $pressId int
	 * @return boolean
	 */
	function categoryAssociationExists($seriesId, $categoryId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM series_categories WHERE series_id = ? AND category_id = ?',
			array((int) $seriesId, (int) $categoryId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>
