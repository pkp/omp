<?php

/**
 * @file classes/monograph/PublishedMonographDAO.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedMonographDAO
 * @ingroup monograph
 * @see PublishedMonograph
 *
 * @brief Operations for retrieving and modifying PublishedMonograph objects.
 */

import('classes.monograph.PublishedMonograph');
import('classes.monograph.MonographDAO');

class PublishedMonographDAO extends MonographDAO {
 	/**
	 * Constructor.
	 */
	function PublishedMonographDAO() {
		parent::MonographDAO();
	}

	/**
	 * Retrieve all published monographs in a press.
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory
	 */
	function getByPressId($pressId, $searchText = null, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$params = array(
			REALLY_BIG_NUMBER,
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			ASSOC_TYPE_PRESS,
			(int) $pressId
		);

		if ($searchText !== null) {
			$params[] = $params[] = $params[] = "%$searchText%";
		}

		$result = $this->retrieveRange(
			'SELECT	' . ($searchText !== null?'DISTINCT ':'') . '
				ps.*,
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev,
				COALESCE(f.seq, ?) AS order_by
			FROM	published_submissions ps
				JOIN submissions m ON ps.submission_id = m.submission_id
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				' . ($searchText !== null?'
					LEFT JOIN authors a ON m.submission_id = a.submission_id
					LEFT JOIN submission_settings mt ON (mt.submission_id = m.submission_id AND mt.setting_name = \'title\')
				':'') . '
				LEFT JOIN features f ON (f.submission_id = m.submission_id AND f.assoc_type = ? AND f.assoc_id = m.press_id)
			WHERE	ps.date_published IS NOT NULL AND m.press_id = ?
				' . ($searchText !== null?' AND (mt.setting_value LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ?)':'') . '
			ORDER BY order_by, ps.date_published',
			$params,
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieve featured monographs for the press homepage.
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory
	 */
	function getPressFeatures($pressId, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$result = $this->retrieveRange(
			'SELECT	ps.*,
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	published_submissions ps
				JOIN submissions m ON ps.submission_id = m.submission_id
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				JOIN features f ON (f.submission_id = m.submission_id AND f.assoc_type = ? AND f.assoc_id = m.press_id)
			WHERE	ps.date_published IS NOT NULL AND m.press_id = ?
			ORDER BY f.seq, ps.date_published',
			array(
				'title', $primaryLocale, // Series title
				'title', $locale, // Series title
				'abbrev', $primaryLocale, // Series abbreviation
				'abbrev', $locale, // Series abbreviation
				ASSOC_TYPE_PRESS,
				(int) $pressId
			),
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieve all published monographs in a series.
	 * @param $seriesId int
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory
	 */
	function getBySeriesId($seriesId, $pressId = null, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$params = array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			ASSOC_TYPE_SERIES,
			(int) $seriesId
		);

		if ($pressId) $params[] = (int) $pressId;

		$params[] = REALLY_BIG_NUMBER; // For feature sorting

		$result = $this->retrieveRange(
			'SELECT	ps.*,
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	published_submissions ps
				JOIN submissions m ON ps.submission_id = m.submission_id
				JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN features f ON (f.submission_id = m.submission_id AND f.assoc_type = ? AND f.assoc_id = s.series_id)
			WHERE	ps.date_published IS NOT NULL AND s.series_id = ?
				' . ($pressId?' AND m.press_id = ?':'' ) . '
			ORDER BY COALESCE(f.seq, ?) ASC, ps.date_published',
			$params,
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieve all published monographs in a category.
	 * @param $categoryId int
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory
	 */
	function getByCategoryId($categoryId, $pressId = null, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$params = array(
			REALLY_BIG_NUMBER,
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			(int) $categoryId, (int) $categoryId, (int) $categoryId,
			ASSOC_TYPE_SERIES
		);

		if ($pressId) $params[] = (int) $pressId;

		$result = $this->retrieveRange(
			'SELECT	DISTINCT ps.*,
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev,
				COALESCE(f.seq, ?) AS order_by
			FROM	published_submissions ps
				JOIN submissions m ON ps.submission_id = m.submission_id
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN submission_categories mc ON (mc.submission_id = m.submission_id AND mc.category_id = ?)
				LEFT JOIN series_categories sca ON (sca.series_id = s.series_id)
				LEFT JOIN categories sc ON (sc.category_id = sca.category_id AND sc.category_id = ?)
				LEFT JOIN features f ON (f.submission_id = m.submission_id AND f.assoc_type = ? AND f.assoc_id = ?)
			WHERE	ps.date_published IS NOT NULL AND (sc.category_id IS NOT NULL OR mc.category_id IS NOT NULL)
				' . ($pressId?' AND m.press_id = ?':'' ) . '
			ORDER BY order_by, ps.date_published',
			$params,
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieve Published Monograph by monograph id
	 * @param $monographId int
	 * @param $pressId int
	 * @return PublishedMonograph object
	 */
	function getById($monographId, $pressId = null, $metadataApprovedOnly = true) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		$params = array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			(int) $monographId
		);
		if ($pressId) $params[] = (int) $pressId;

		$result = $this->retrieve(
			'SELECT	m.*,
				ps.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	submissions m
				JOIN published_submissions ps ON (ps.submission_id = m.submission_id)
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	m.submission_id = ?
				' . ($pressId?' AND m.press_id = ?':'')
				. ($metadataApprovedOnly?' AND ps.date_published IS NOT NULL':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		return $returner;
	}

	/**
	 * Generate and return a new data object.
	 * @return PublishedMonograph
	 */
	function newDataObject() {
		return new PublishedMonograph();
	}

	/**
	 * Creates and returns a published monograph object from a row
	 * @param $row array
	 * @return PublishedMonograph object
	 */
	function _fromRow($row) {
		// Get the PublishedMonograph object, populated with Monograph data
		$publishedMonograph = parent::_fromRow($row);

		// Add the additional PublishedMonograph data
		$publishedMonograph->setDatePublished($this->datetimeFromDB($row['date_published']));
		$publishedMonograph->setAudience($row['audience']);
		$publishedMonograph->setAudienceRangeQualifier($row['audience_range_qualifier']);
		$publishedMonograph->setAudienceRangeFrom($row['audience_range_from']);
		$publishedMonograph->setAudienceRangeTo($row['audience_range_to']);
		$publishedMonograph->setAudienceRangeExact($row['audience_range_exact']);
		$publishedMonograph->setCoverImage(unserialize($row['cover_image']));

		HookRegistry::call('PublishedMonographDAO::_fromRow', array(&$publishedMonograph, &$row));
		return $publishedMonograph;
	}


	/**
	 * Inserts a new published monograph into published_submissions table
	 * @param PublishedMonograph object
	 */
	function insertObject($publishedMonograph) {

		$this->update(
			sprintf('INSERT INTO published_submissions
				(submission_id, date_published, audience, audience_range_qualifier, audience_range_from, audience_range_to, audience_range_exact, cover_image)
				VALUES
				(?, %s, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				(int) $publishedMonograph->getId(),
				$publishedMonograph->getAudience(),
				$publishedMonograph->getAudienceRangeQualifier(),
				$publishedMonograph->getAudienceRangeFrom(),
				$publishedMonograph->getAudienceRangeTo(),
				$publishedMonograph->getAudienceRangeExact(),
				serialize($publishedMonograph->getCoverImage() ? $publishedMonograph->getCoverImage() : array()),
			)
		);
	}

	/**
	 * Removes an published monograph by monograph id
	 * @param monographId int
	 */
	function deleteById($monographId) {
		$this->update(
			'DELETE FROM published_submissions WHERE submission_id = ?',
			(int) $monographId
		);
	}

	/**
	 * Update a published monograph
	 * @param PublishedMonograph object
	 */
	function updateObject($publishedMonograph) {
		$this->update(
			sprintf('UPDATE	published_submissions
				SET	date_published = %s,
					audience = ?,
					audience_range_qualifier = ?,
					audience_range_from = ?,
					audience_range_to = ?,
					audience_range_exact = ?,
					cover_image = ?
				WHERE	submission_id = ?',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				$publishedMonograph->getAudience(),
				$publishedMonograph->getAudienceRangeQualifier(),
				$publishedMonograph->getAudienceRangeFrom(),
				$publishedMonograph->getAudienceRangeTo(),
				$publishedMonograph->getAudienceRangeExact(),
				serialize($publishedMonograph->getCoverImage() ? $publishedMonograph->getCoverImage() : array()),
				(int) $publishedMonograph->getId()
			)
		);
	}
}

?>
