<?php

/**
 * @file classes/monograph/PublishedMonographDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
import('lib.pkp.classes.core.ArrayItemIterator');

class PublishedMonographDAO extends MonographDAO {
	/**
	 * Retrieve all published monographs in a press.
	 * @param $pressId int The monograhps press id.
	 * @param $searchText string optional Search text for title and authors.
	 * @param $rangeInfo DBResultRange optional Object with result range information.
	 * @param $sortBy int optional Sort monographs by passed column option.
	 * @param $sortDirection int optional Sort monographs by passed direction.
	 * @param $featuredOnly boolean optional Whether the monographs are featured on press or not.
	 * @param $newReleasedOnly boolean optional Whether the monographs are marked as new releases on press or not.
	 * @return ItemIterator Iterator for monograph objects.
	 */
	function getByPressId($pressId, $searchText = null, $rangeInfo = null, $sortBy = null, $sortDirection = null, $featuredOnly = false, $newReleasedOnly = false) {
		return $this->_getByAssoc($pressId, ASSOC_TYPE_PRESS, $pressId, $searchText, $rangeInfo, $sortBy, $sortDirection, $featuredOnly, $newReleasedOnly);
	}


	/**
	 * Retrieve all published monographs associated with the passed series id.
	 * @param $seriesId int The series id monographs are associated with.
	 * @param $pressId int The monograhps press id.
	 * @param $searchText string optional Search text for title and authors.
	 * @param $rangeInfo DBResultRange optional Object with result range information.
	 * @param $sortBy int optional Sort monographs by passed column option.
	 * @param $sortDirection int optional Sort monographs by passed direction.
	 * @param $featuredOnly boolean optional Whether the monographs are featured on series or not.
	 * @param $newReleasedOnly boolean optional Whether the monographs are marked as new releases on series or not.
	 * @return ItemIterator Iterator for monograph objects.
	 */
	function getBySeriesId($seriesId, $pressId = null, $searchText = null, $rangeInfo = null, $sortBy = null, $sortDirection = null, $featuredOnly = false, $newReleasedOnly = false) {
		return $this->_getByAssoc($pressId, ASSOC_TYPE_SERIES, $seriesId, $searchText, $rangeInfo, $sortBy, $sortDirection, $featuredOnly, $newReleasedOnly);
	}

	/**
	 * Retrieve all published monographs associated with the passed category id.
	 * @param $seriesId int The category id monographs are associated with.
	 * @param $pressId int The monograhps press id.
	 * @param $searchText string optional Search text for title and authors.
	 * @param $rangeInfo DBResultRange optional Object with result range information.
	 * @param $sortBy int optional Sort monographs by passed column option.
	 * @param $sortDirection int optional Sort monographs by passed direction.
	 * @param $featuredOnly boolean optional Whether the monographs are featured on category or not.
	 * @param $newReleasedOnly boolean optional Whether the monographs are marked as new releases on category or not.
	 * @return ItemIterator Iterator for monograph objects.
	 */
	function getByCategoryId($categoryId, $pressId = null, $searchText = null, $rangeInfo = null, $sortBy = null, $sortDirection = null, $featuredOnly = false, $newReleasedOnly = false) {
		return $this->_getByAssoc($pressId, ASSOC_TYPE_CATEGORY, $categoryId, $searchText, $rangeInfo, $sortBy, $sortDirection, $featuredOnly, $newReleasedOnly);
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
				JOIN submissions s ON ps.submission_id = s.submission_id AND ps.published_submission_version = s.submission_version
				' . $this->getFetchJoins() . '
				JOIN features f ON (f.submission_id = s.submission_id AND f.assoc_type = ? AND f.assoc_id = s.context_id)
			WHERE	ps.date_published IS NOT NULL AND s.context_id = ?
			AND ps.is_current_submission_version = 1
			ORDER BY f.seq, ps.date_published',
			$params,
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Retrieve Published Monograph by monograph id
	 * @param $monographId int
	 * @param $pressId int
	 * @param $metadataApprovedOnly boolean
	 * @return PublishedMonograph object
	 */
	function getBySubmissionId($monographId, $pressId = null, $metadataApprovedOnly = true, $submissionVersion = null) {
		$params = $this->getFetchParameters();
		$params[] = (int) $monographId;
		if ($submissionVersion) {
			$params[] = (int) $submissionVersion;
    }

		if ($pressId) $params[] = (int) $pressId;

		$result = $this->retrieve(
			'SELECT	s.*,
				ps.*,
				' . $this->getFetchColumns() . '
			FROM	submissions s
				JOIN published_submissions ps ON (ps.submission_id = s.submission_id)'
				. $this->getFetchJoins() . '
			WHERE	s.submission_id = ?'
			. ($submissionVersion ? ' AND ps.published_submission_version = ? ' : ' AND ps.is_current_submission_version = 1')
			. ($pressId?' AND s.context_id = ?':'')
			. ($metadataApprovedOnly?' AND ps.date_published IS NOT NULL':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false), $submissionVersion);
		}

		$result->Close();
		return $returner;
	}

	/**
	 * Find published monographs by querying monograph settings.
	 * @param $settingName string
	 * @param $settingValue mixed
	 * @param $pressId int optional
	 * @return array The monographs identified by setting.
	 */
	function getBySetting($settingName, $settingValue, $pressId = null, $submissionVersion = null) {
		$params = $this->getFetchParameters();
		$params[] = $settingName;

		$sql = 'SELECT	ps.*,
				s.*,
				' . $this->getFetchColumns() . '
			FROM	published_submissions ps
				JOIN submissions s ON (ps.submission_id = s.submission_id)'
				. $this->getFetchJoins();

		if (is_null($settingValue)) {
			if ($submissionVersion) {
        $params[] = (int) $submissionVersion;
			}
			$sql .= 'LEFT JOIN submission_settings sst ON s.submission_id = sst.submission_id AND sst.setting_name = ?
				WHERE	(sst.setting_value IS NULL OR sst.setting_value = \'\') '
				. ($submissionVersion ? ' AND ps.published_submission_version = ? ' : ' AND is_current_submission_version = 1');
		} else {
			$params[] = (string) $settingValue;
			if ($submissionVersion) {
        $params[] = (int) $submissionVersion;
			}
			$sql .= 'INNER JOIN submission_settings sst ON s.submission_id = sst.submission_id
				WHERE	sst.setting_name = ? AND sst.setting_value = ? '
				. ($submissionVersion ? ' AND ps.published_submission_version = ? ' : ' AND is_current_submission_version = 1');
		}
		if ($pressId) {
			$params[] = (int) $pressId;
			$sql .= ' AND s.context_id = ?';
		}
		$sql .= ' ORDER BY s.submission_id';
		$result = $this->retrieve($sql, $params);

		$publishedMonographs = array();
		while (!$result->EOF) {
			$publishedMonographs[] = $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}
		$result->Close();

		return $publishedMonographs;
	}

	/**
	 * Retrieve published monograph by public monograph ID
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @param $pubId string
	 * @param $pressId int
	 * @return PublishedMonograph|null
	 */
	function getByPubId($pubIdType, $pubId, $pressId = null) {
		$publishedMonograph = null;
		if (!empty($pubId)) {
			$publishedMonographs = $this->getBySetting('pub-id::'.$pubIdType, $pubId, $pressId);
			if (!empty($publishedMonographs)) {
				assert(count($publishedMonographs) == 1);
				$publishedMonograph = $publishedMonographs[0];
			}
		}
		return $publishedMonograph;
	}

	/**
	 * Retrieve published monograph by public monograph ID or, failing that,
	 * internal monograph ID; public monograph ID takes precedence.
	 * @param $monographId string
	 * @param $pressId int
	 * @return PublishedMonograph|null
	 */
	function getByBestId($monographId, $pressId, $submissionVersion = null) {
		$publishedMonograph = null;
		if ($monographId != '') $publishedMonograph = $this->getByPubId('publisher-id', $monographId, $pressId);
		if (!isset($publishedMonograph) && ctype_digit("$monographId")) $publishedMonograph = $this->getBySubmissionId((int) $monographId, $pressId, true, $submissionVersion);
		return $publishedMonograph;
	}

	/**
	 * Generate and return a new data object.
	 * @return PublishedMonograph
	 */
	function newDataObject() {
		return new PublishedMonograph();
	}

	/**
	 * Inserts a new published monograph into published_submissions table
	 * @param PublishedMonograph object
	 */
	function insertObject($publishedMonograph) {

		$this->update(
			sprintf('INSERT INTO published_submissions
				(submission_id, date_published, audience, audience_range_qualifier, audience_range_from, audience_range_to, audience_range_exact, cover_image, published_submission_version, is_current_submission_version)
				VALUES
				(?, %s, ?, ?, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				(int) $publishedMonograph->getId(),
				$publishedMonograph->getAudience(),
				$publishedMonograph->getAudienceRangeQualifier(),
				$publishedMonograph->getAudienceRangeFrom(),
				$publishedMonograph->getAudienceRangeTo(),
				$publishedMonograph->getAudienceRangeExact(),
				serialize($publishedMonograph->getCoverImage() ? $publishedMonograph->getCoverImage() : array()),
				$publishedMonograph->getSubmissionVersion(),
				$publishedMonograph->getIsCurrentSubmissionVersion(),
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
					cover_image = ?,
					is_current_submission_version = ?
				WHERE	submission_id = ? AND published_submission_version = ?',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				$publishedMonograph->getAudience(),
				$publishedMonograph->getAudienceRangeQualifier(),
				$publishedMonograph->getAudienceRangeFrom(),
				$publishedMonograph->getAudienceRangeTo(),
				$publishedMonograph->getAudienceRangeExact(),
				serialize($publishedMonograph->getCoverImage() ? $publishedMonograph->getCoverImage() : array()),
				(int) $publishedMonograph->getIsCurrentSubmissionVersion(),
				(int) $publishedMonograph->getId(),
				(int) $publishedMonograph->getSubmissionVersion(),
			)
		);
	}

	/**
	 * Creates and returns a published monograph object from a row
	 * @param $row array
	 * @return PublishedMonograph object
	 */
	function _fromRow($row, $submissionVersion = null) {
		// Get the PublishedMonograph object, populated with Monograph data
		$publishedMonograph = parent::_fromRow($row, $submissionVersion);

		// Add the additional PublishedMonograph data
		$publishedMonograph->setDatePublished($this->datetimeFromDB($row['date_published']));
		$publishedMonograph->setAudience($row['audience']);
		$publishedMonograph->setAudienceRangeQualifier($row['audience_range_qualifier']);
		$publishedMonograph->setAudienceRangeFrom($row['audience_range_from']);
		$publishedMonograph->setAudienceRangeTo($row['audience_range_to']);
		$publishedMonograph->setAudienceRangeExact($row['audience_range_exact']);
		$publishedMonograph->setCoverImage(unserialize($row['cover_image']));
		$publishedMonograph->setSubmissionVersion($row['published_submission_version']);
		$publishedMonograph->setCurrentSubmissionVersion($row['published_submission_version']);
		$publishedMonograph->setIsCurrentSubmissionVersion($row['is_current_submission_version']);

		HookRegistry::call('PublishedMonographDAO::_fromRow', array(&$publishedMonograph, &$row));
		$this->getDataObjectSettings('submission_settings', 'submission_id', $publishedMonograph->getId(), $publishedMonograph, $publishedMonograph->getSubmissionVersion());

		return $publishedMonograph;
	}


	//
	// Private helper methods.
	//
	/**
	 * Retrieve all published monographs by associated object.
	 * @param $pressId int The monograhps press id.
	 * @param $assocType int The associated object type.
	 * @param $assocId int The associated object id.
	 * @param $searchText string optional Search text for title and authors.
	 * @param $rangeInfo DBResultRange optional Object with result range information.
	 * @param $sortBy int optional Sort monographs by passed column option.
	 * @param $sortDirection int optional Sort monographs by passed direction.
	 * @param $featuredOnly boolean optional Whether the monographs are featured on passed associated object or not.
	 * @param $newReleasedOnly boolean optional Whether the monographs are marked as new releases on associated object or not.
	 * @return ItemIterator Iterator for monograph objects.
	 */
	private function _getByAssoc($pressId, $assocType, $assocId, $searchText = null, $rangeInfo = null, $sortBy = null, $sortDirection = null, $featuredOnly = false, $newReleasedOnly = false) {
		// Cast parameters.
		$pressId = (int) $pressId;
		$assocType = (int) $assocType;
		$assocId = (int) $assocId;
		$featuredOnly = (boolean) $featuredOnly;
		$newReleasedOnly = (boolean) $newReleasedOnly;

		// If no associated object is passed, return.
		if (!$assocId || !$assocType) {
			$theArray = array();
			return new ArrayItemIterator($theArray);
		} else {
			// Check if the associated object exists.
			switch ($assocType) {
				case ASSOC_TYPE_PRESS:
					$assocObject = DAORegistry::getDAO('PressDAO')->getById($assocId);
					break;
				case ASSOC_TYPE_SERIES:
					$assocObject = DAORegistry::getDAO('SeriesDAO')->getById($assocId);
					break;
				case ASSOC_TYPE_CATEGORY:
					$assocObject = DAORegistry::getDAO('CategoryDAO')->getById($assocId);
					break;
				default:
					$assocObject = null;
			}
			if (!$assocObject) {
				$theArray = array();
				return new ArrayItemIterator($theArray);
			}
		}

		// If no sort by options passed, sort by the passed associated object default.
		if ($assocType && $assocId && (!$sortBy || !$sortDirection)) {
			$sortOption = null;
			switch ($assocType) {
				case ASSOC_TYPE_PRESS:
					$sortOption = $assocObject->getSetting('catalogSortOption') ? $assocObject->getSetting('catalogSortOption') : $this->getDefaultSortOption();
					break;
				case ASSOC_TYPE_SERIES:
				case ASSOC_TYPE_CATEGORY:
					$sortOption = $assocObject->getSortOption() ? $assocObject->getSortOption() : $this->getDefaultSortOption();
					break;
			}
			$sortBy = $this->getSortBy($sortOption);
			$sortDirection = $this->getSortDirection($sortOption);
		}

		$params = array_merge(
			array(REALLY_BIG_NUMBER),
			$this->getFetchParameters(),
			array(
				$assocType,
				$assocId,
				$assocType,
				$assocId,
				$pressId
			)
		);

		if ($searchText !== null) {
			$params[] = $params[] = $params[] = "%$searchText%";
		}

		if ($featuredOnly) {
			$params[] = $assocType;
			$params[] = $assocId;
		}

		if ($newReleasedOnly) {
			$params[] = $assocType;
			$params[] = $assocId;
		}

		$result = $this->retrieveRange(
			'SELECT	' . ($searchText !== null?'DISTINCT ':'') . '
				ps.*,
				s.*,
				COALESCE(f.seq, ?) AS order_by,
				' . $this->getFetchColumns() . '
			FROM	published_submissions ps
				JOIN submissions s ON ps.submission_id = s.submission_id AND ps.published_submission_version = s.submission_version
				' . $this->getFetchJoins() . '
				' . ($searchText !== null?'
					LEFT JOIN authors au ON (s.submission_id = au.submission_id)
					LEFT JOIN author_settings asgs ON (asgs.author_id = au.author_id AND asgs.setting_name = \''.IDENTITY_SETTING_GIVENNAME.'\')
					LEFT JOIN author_settings asfs ON (asfs.author_id = au.author_id AND asfs.setting_name = \''.IDENTITY_SETTING_FAMILYNAME.'\')
				':'') . '
				' . ($searchText !== null || $sortBy == ORDERBY_TITLE?'
					LEFT JOIN submission_settings st ON (st.submission_id = s.submission_id AND st.setting_name = \'title\')
				':'') . '
				' . ($assocType == ASSOC_TYPE_CATEGORY?'
					LEFT JOIN submission_categories sc ON (sc.submission_id = s.submission_id AND sc.category_id = ' . $assocId . ')
					LEFT JOIN series_categories sca ON (sca.series_id = se.series_id)
					LEFT JOIN categories c ON (c.category_id = sca.category_id AND c.category_id = ' . $assocId . ')
				':'') . '
				LEFT JOIN features f ON (f.submission_id = s.submission_id AND f.assoc_type = ? AND f.assoc_id = ?)
				LEFT JOIN new_releases nr ON (nr.submission_id = s.submission_id AND nr.assoc_type = ? AND nr.assoc_id = ?)
			WHERE	ps.date_published IS NOT NULL AND s.context_id = ? AND ps.is_current_submission_version = 1 
				' . ($searchText !== null?' AND (st.setting_value LIKE ? OR asgs.setting_value LIKE ? OR asfs.setting_value LIKE ?)':'') . '
				' . ($assocType == ASSOC_TYPE_CATEGORY?' AND (c.category_id IS NOT NULL OR sc.category_id IS NOT NULL)':'') . '
				' . ($assocType == ASSOC_TYPE_SERIES?' AND se.series_id = ' . $assocId:'') . '
				' . ($featuredOnly?' AND (f.assoc_type = ? AND f.assoc_id = ?)':'') . '
				' . ($newReleasedOnly?' AND (nr.assoc_type = ? AND nr.assoc_id = ?)':'') . '
			ORDER BY order_by, '. $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection),
			$params,
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	function getMasterTableName() {
		return 'published_submissions';
	}
}
