<?php

/**
 * @file classes/monograph/PublishedMonographDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
		$params = array_merge(
			array(REALLY_BIG_NUMBER),
			$this->getFetchParameters(),
			array(
				ASSOC_TYPE_PRESS,
				(int) $pressId
			)
		);

		if ($searchText !== null) {
			$params[] = $params[] = $params[] = "%$searchText%";
		}

		$result = $this->retrieveRange(
			'SELECT	' . ($searchText !== null?'DISTINCT ':'') . '
				ps.*,
				s.*,
				COALESCE(f.seq, ?) AS order_by,
				' . $this->getFetchColumns() . '
			FROM	published_submissions ps
				JOIN submissions s ON ps.submission_id = s.submission_id
				' . $this->getFetchJoins() . '
				' . ($searchText !== null?'
					LEFT JOIN authors a ON s.submission_id = a.submission_id
					LEFT JOIN submission_settings st ON (st.submission_id = s.submission_id AND st.setting_name = \'title\')
				':'') . '
				LEFT JOIN features f ON (f.submission_id = s.submission_id AND f.assoc_type = ? AND f.assoc_id = s.context_id)
			WHERE	ps.date_published IS NOT NULL AND s.context_id = ?
				' . ($searchText !== null?' AND (st.setting_value LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ?)':'') . '
			ORDER BY order_by, ps.date_published DESC',
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
		$params = array_merge(
			$this->getFetchColumns(),
			array(ASSOC_TYPE_PRESS, (int) $pressId)
		);
		$result = $this->retrieveRange(
			'SELECT	ps.*,
				s.*,
				' . $this->getFetchColumns() . '
			FROM	published_submissions ps
				JOIN submissions s ON ps.submission_id = s.submission_id
				' . $this->getFetchJoins() . '
				JOIN features f ON (f.submission_id = s.submission_id AND f.assoc_type = ? AND f.assoc_id = s.context_id)
			WHERE	ps.date_published IS NOT NULL AND s.context_id = ?
			ORDER BY f.seq, ps.date_published',
			$params,
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
		$params = array_merge(
			$this->getFetchParameters(),
			array(ASSOC_TYPE_SERIES, (int) $seriesId)
		);

		if ($pressId) $params[] = (int) $pressId;

		$params[] = REALLY_BIG_NUMBER; // For feature sorting

		$result = $this->retrieveRange(
			'SELECT	ps.*,
				s.*,
				' . $this->getFetchColumns() . '
			FROM	published_submissions ps
				JOIN submissions s ON ps.submission_id = s.submission_id
				' . $this->getFetchJoins() . '
				LEFT JOIN features f ON (f.submission_id = s.submission_id AND f.assoc_type = ? AND f.assoc_id = se.series_id)
			WHERE	ps.date_published IS NOT NULL AND se.series_id = ?
				' . ($pressId?' AND s.context_id = ?':'' ) . '
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
		$params = array_merge(
			array(REALLY_BIG_NUMBER),
			$this->getFetchParameters(),
			array(
				(int) $categoryId, (int) $categoryId, (int) $categoryId,
				ASSOC_TYPE_CATEGORY
			)
		);

		if ($pressId) $params[] = (int) $pressId;

		$result = $this->retrieveRange(
			'SELECT	DISTINCT ps.*,
				s.*,
				COALESCE(f.seq, ?) AS order_by,
				' . $this->getFetchColumns() . '
			FROM	published_submissions ps
				JOIN submissions s ON ps.submission_id = s.submission_id
				' . $this->getFetchJoins() . '
				LEFT JOIN submission_categories sc ON (sc.submission_id = s.submission_id AND sc.category_id = ?)
				LEFT JOIN series_categories sca ON (sca.series_id = se.series_id)
				LEFT JOIN categories c ON (c.category_id = sca.category_id AND c.category_id = ?)
				LEFT JOIN features f ON (f.submission_id = s.submission_id AND f.assoc_id = ? AND f.assoc_type = ?)
			WHERE	ps.date_published IS NOT NULL AND (c.category_id IS NOT NULL OR sc.category_id IS NOT NULL)
				' . ($pressId?' AND s.context_id = ?':'' ) . '
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
		$params = $this->getFetchParameters();
		$params[] = (int) $monographId;
		if ($pressId) $params[] = (int) $pressId;

		$result = $this->retrieve(
			'SELECT	s.*,
				ps.*,
				' . $this->getFetchColumns() . '
			FROM	submissions s
				JOIN published_submissions ps ON (ps.submission_id = s.submission_id)
				' . $this->getFetchJoins() . '
			WHERE	s.submission_id = ?
				' . ($pressId?' AND s.context_id = ?':'')
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
