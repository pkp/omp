<?php

/**
 * @file classes/monograph/PublishedMonographDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	function &getByPressId($pressId, $searchText = null, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$params = array(
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

		$params[] = REALLY_BIG_NUMBER; // For feature sorting

		$result =& $this->retrieveRange(
			'SELECT	' . ($searchText !== null?'DISTINCT ':'') . '
				pm.*,
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	published_monographs pm
				JOIN monographs m ON pm.monograph_id = m.monograph_id
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				' . ($searchText !== null?'
					LEFT JOIN authors a ON m.monograph_id = a.submission_id
					LEFT JOIN monograph_settings mt ON (mt.monograph_id = m.monograph_id AND mt.setting_name = \'title\')
				':'') . '
				LEFT JOIN features f ON (f.monograph_id = m.monograph_id AND f.assoc_type = ? AND f.assoc_id = m.press_id)
			WHERE	m.press_id = ?
				' . ($searchText !== null?' AND (mt.setting_value LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ?)':'') . '
			ORDER BY COALESCE(f.seq, ?), pm.date_published',
			$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve new releases, i.e. content released within a window of time
	 * (defined by $windowSeconds) or content that is featured as a new
	 * release.
	 * @param $pressId int
	 * @param $windowSeconds Number of seconds old a submission should be
	 * 	in order to be considered a new release. (Default 2 weeks.)
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory
	 */
	function &getNewReleases($pressId, $windowSeconds = null, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		if ($windowSeconds === null) {
			// Two weeks.
			$windowSeconds = 60 * 60 * 24 * 7 * 2;
		}

		$result =& $this->retrieveRange(
			sprintf(
				'SELECT	pm.*,
					m.*,
					COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
					COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
				FROM	published_monographs pm
					JOIN monographs m ON pm.monograph_id = m.monograph_id
					LEFT JOIN series s ON s.series_id = m.series_id
					LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
					LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
					LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
					LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
					LEFT JOIN features f ON (f.monograph_id = m.monograph_id AND f.assoc_type = ? AND f.assoc_id = m.press_id)
				WHERE	m.press_id = ? AND
					(f.monograph_id IS NOT NULL OR pm.date_published >= %s)
				ORDER BY COALESCE(f.seq, ?), pm.date_published',
				$this->datetimeToDB(time() - $windowSeconds)
			),
			array(
				'title', $primaryLocale, // Series title
				'title', $locale, // Series title
				'abbrev', $primaryLocale, // Series abbreviation
				'abbrev', $locale, // Series abbreviation
				ASSOC_TYPE_NEW_RELEASE,
				(int) $pressId,
				REALLY_BIG_NUMBER // For feature sorting
			),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve featured monographs for the press homepage.
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory
	 */
	function &getPressFeatures($pressId, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$result =& $this->retrieveRange(
			'SELECT	pm.*,
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	published_monographs pm
				JOIN monographs m ON pm.monograph_id = m.monograph_id
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				JOIN features f ON (f.monograph_id = m.monograph_id AND f.assoc_type = ? AND f.assoc_id = m.press_id)
			WHERE	m.press_id = ?
			ORDER BY f.seq, pm.date_published',
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

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve all published monographs in a series.
	 * @param $seriesId int
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory
	 */
	function &getBySeriesId($seriesId, $pressId = null, $rangeInfo = null) {
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

		$result =& $this->retrieveRange(
			'SELECT	pm.*,
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	published_monographs pm
				JOIN monographs m ON pm.monograph_id = m.monograph_id
				JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN features f ON (f.monograph_id = m.monograph_id AND f.assoc_type = ? AND f.assoc_id = s.series_id)
			WHERE	s.series_id = ?
				' . ($pressId?' AND m.press_id = ?':'' ) . '
			ORDER BY COALESCE(f.seq, ?) ASC, pm.date_published',
			$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve all published monographs in a category.
	 * @param $categoryId int
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory
	 */
	function &getByCategoryId($categoryId, $pressId = null, $rangeInfo = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$params = array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			(int) $categoryId, (int) $categoryId, (int) $categoryId,
			ASSOC_TYPE_SERIES
		);

		if ($pressId) $params[] = (int) $pressId;

		$params[] = REALLY_BIG_NUMBER; // For feature sorting

		$result =& $this->retrieveRange(
			'SELECT	DISTINCT pm.*,
				m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	published_monographs pm
				JOIN monographs m ON pm.monograph_id = m.monograph_id
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN monograph_categories mc ON (mc.monograph_id = m.monograph_id AND mc.category_id = ?)
				LEFT JOIN series_categories sca ON (sca.series_id = s.series_id)
				LEFT JOIN categories sc ON (sc.category_id = sca.category_id AND sc.category_id = ?)
				LEFT JOIN features f ON (f.monograph_id = m.monograph_id AND f.assoc_type = ? AND f.assoc_id = ?)
			WHERE	(sc.category_id IS NOT NULL OR mc.category_id IS NOT NULL)
				' . ($pressId?' AND m.press_id = ?':'' ) . '
			ORDER BY COALESCE(f.seq, ?), pm.date_published',
			$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve Published Monograph by monograph id
	 * @param $monographId int
	 * @param $pressId int
	 * @return PublishedMonograph object
	 */
	function &getById($monographId, $pressId = null) {
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

		$result =& $this->retrieve(
			'SELECT	m.*,
				pm.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs m
				JOIN published_monographs pm ON (pm.monograph_id = m.monograph_id)
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	m.monograph_id = ?
				' . ($pressId?' AND m.press_id = ?':''),
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
	 * Generate and return a new data object.
	 * @return PublishedMonograph
	 */
	function newDataObject() {
		return new PublishedMonograph();
	}

	/**
	 * Creates and returns a published monograph object from a row
	 * @param $row array
	 * @param $callHooks boolean Whether or not to call hooks
	 * @return PublishedMonograph object
	 */
	function &_fromRow($row, $callHooks = true) {
		// Get the PublishedMonograph object, populated with Monograph data
		$publishedMonograph =& parent::_fromRow($row, $callHooks);

		// Add the additional PublishedMonograph data
		$publishedMonograph->setPubId($row['pub_id']); // Deprecated
		$publishedMonograph->setDatePublished($this->datetimeFromDB($row['date_published']));
		$publishedMonograph->setSeq($row['seq']);

		$this->getDataObjectSettings('published_monograph_settings', 'pub_id', $row['pub_id'], $publishedMonograph);

		if ($callHooks) HookRegistry::call('PublishedMonographDAO::_fromRow', array(&$publishedMonograph, &$row));
		return $publishedMonograph;
	}


	/**
	 * Inserts a new published monograph into published_monographs table
	 * @param PublishedMonograph object
	 */
	function insertObject(&$publishedMonograph) {
		$this->update(
			sprintf('INSERT INTO published_monographs
				(monograph_id, date_published, seq)
				VALUES
				(?, %s, ?)',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				(int) $publishedMonograph->getId(),
				(int) $publishedMonograph->getSeq()
			)
		);
	}

	/**
	 * Removes an published monograph by monograph id
	 * @param monographId int
	 */
	function deleteById($monographId) {
		$this->update(
			'DELETE FROM published_monographs WHERE monograph_id = ?',
			(int) $monographId
		);
	}

	/**
	 * Update a published monograph
	 * @param PublishedMonograph object
	 */
	function updateObject($publishedMonograph) {
		$this->update(
			sprintf('UPDATE	published_monographs
				SET	date_published = %s,
					seq = ?
				WHERE	monograph_id = ?',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				(int) $publishedMonograph->getSeq(),
				(int) $publishedMonograph->getId()
			)
		);
	}

	/**
	 * Get a list of fields for which we store localized data
	 * @return array
	 */
	function getLocaleFieldNames() {
		// return empty, since the localized submission information is in the monograph's metadata.
		// this is for published_monograph information.
		return array();
	}

	/**
	 * Get a list of fields for which we store (non-localized) data
	 * @return array
	 */
	function getAdditionalFieldNames() {
		return array(
				'audience', 'audienceRangeQualifier', 'audienceRangeFrom', 'audienceRangeTo', 'audienceRangeExact');
	}

	/**
	 * Update the localized fields for this object.
	 * @param $publishedMonograph
	 */
	function updateLocaleFields(&$publishedMonograph) {
		$this->updateDataObjectSettings('published_monograph_settings', $publishedMonograph, array(
				'pub_id' => $publishedMonograph->getPubId()
		));
	}
}

?>
