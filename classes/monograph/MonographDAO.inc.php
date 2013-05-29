<?php

/**
 * @file classes/monograph/MonographDAO.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying Monograph objects.
 */

import('classes.monograph.Monograph');
import('lib.pkp.classes.submission.SubmissionDAO');

class MonographDAO extends SubmissionDAO {
	var $cache;

	/**
	 * Constructor.
	 */
	function MonographDAO() {
		parent::SubmissionDAO();
	}

	/**
	 * Callback for a cache miss.
	 * @param $cache Cache
	 * @param $id string
	 * @return Monograph
	 */
	function _cacheMiss($cache, $id) {
		$monograph = $this->getMonograph($id, null, false);
		$cache->setCache($id, $monograph);
		return $monograph;
	}

	/**
	 * Get the monograph cache.
	 * @return Cache
	 */
	function _getCache() {
		if (!isset($this->cache)) {
			$cacheManager = CacheManager::getManager();
			$this->cache = $cacheManager->getObjectCache('submissions', 0, array(&$this, '_cacheMiss'));
		}
		return $this->cache;
	}

	/**
	 * Get a list of fields for which localized data is supported
	 * @return array
	 */
	function getLocaleFieldNames() {
		return parent::getLocaleFieldNames() + array(
			'copyrightNotice',
		);
	}

	/**
	 * Update the localized fields for this object.
	 * @param $monograph
	 */
	function updateLocaleFields($monograph) {
		$this->updateDataObjectSettings('submission_settings', $monograph, array(
			'submission_id' => $monograph->getId()
		));
	}

	/**
	 * Retrieve Monograph by monograph id
	 * @param $monographId int
	 * @param $pressId int optional
	 * @param $useCache boolean optional
	 * @return Monograph
	 */
	function getById($monographId, $pressId = null, $useCache = false) {
		if ($useCache) {
			$cache = $this->_getCache();
			$returner = $cache->get($monographId);
			if ($returner && $pressId != null && $pressId != $returner->getPressId()) $returner = null;
			return $returner;
		}

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
			'SELECT	m.*, ps.date_published,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	submissions m
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN published_submissions ps ON (m.submission_id = ps.submission_id)
			WHERE	m.submission_id = ?
				' . ($pressId?' AND m.press_id = ?':''),
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
	 * Internal function to return an Monograph object from a row.
	 * @param $row array
	 * @return Monograph
	 */
	function _fromRow($row) {
		$monograph = parent::_fromRow($row);

		$monograph->setId($row['submission_id']);
		$monograph->setPressId($row['press_id']);
		$monograph->setSeriesId($row['series_id']);
		$monograph->setSeriesPosition($row['series_position']);
		$monograph->setSeriesAbbrev(isset($row['series_abbrev'])?$row['series_abbrev']:null);
		$monograph->setWorkType($row['edited_volume']);

		$this->getDataObjectSettings('submission_settings', 'submission_id', $monograph->getId(), $monograph);

		HookRegistry::call('MonographDAO::_fromRow', array(&$monograph, &$row));

		return $monograph;
	}

	/**
	 * Get a new data object representing the monograph.
	 * @return Monograph
	 */
	function newDataObject() {
		return new Monograph();
	}

	/**
	 * inserts a new monograph into submissions table
	 * @param Monograph object
	 * @return Monograph Id int
	 */
	function insertObject($monograph) {
		$monograph->stampModified();
		$this->update(
			sprintf('INSERT INTO submissions
				(locale, user_id, press_id, series_id, series_position, language, comments_to_ed, date_submitted, date_status_modified, last_modified, status, submission_progress, stage_id, pages, hide_author, comments_status, edited_volume)
				VALUES
				(?, ?, ?, ?, ?, ?, ?, %s, %s, %s, ?, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getLocale(),
				(int) $monograph->getUserId(),
				(int) $monograph->getPressId(),
				(int) $monograph->getSeriesId(),
				$monograph->getSeriesPosition(),
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				$monograph->getStatus() === null ? STATUS_QUEUED : (int) $monograph->getStatus(),
				$monograph->getSubmissionProgress() === null ? 1 : (int) $monograph->getSubmissionProgress(),
				$monograph->getStageId() === null ? 1 : (int) $monograph->getStageId(),
				$monograph->getPages(),
				(int) $monograph->getHideAuthor(),
				(int) $monograph->getCommentsStatus(),
				(int) $monograph->getWorkType(),
			)
		);

		$monograph->setId($this->getInsertId());
		$this->updateLocaleFields($monograph);

		return $monograph->getId();
	}

	/**
	 * updates a monograph
	 * @param Monograph object
	 */
	function updateObject($monograph) {
		$this->update(
			sprintf('UPDATE submissions
				SET	user_id = ?,
					series_id = ?,
					series_position = ?,
					language = ?,
					comments_to_ed = ?,
					date_submitted = %s,
					date_status_modified = %s,
					last_modified = %s,
					status = ?,
					press_id = ?,
					submission_progress = ?,
					stage_id = ?,
					edited_volume = ?,
					hide_author = ?

				WHERE	submission_id = ?',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				(int) $monograph->getUserId(),
				(int) $monograph->getSeriesId(),
				$monograph->getSeriesPosition(),
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				(int) $monograph->getStatus(),
				(int) $monograph->getPressId(),
				(int) $monograph->getSubmissionProgress(),
				(int) $monograph->getStageId(),
				(int) $monograph->getWorkType(),
				(int) $monograph->getHideAuthor(),
				(int) $monograph->getId()
			)
		);
		$this->updateLocaleFields($monograph);
		$this->flushCache();
	}

	/**
	 * Delete an monograph by ID.
	 * @param $monographId int
	 */
	function deleteById($monographId) {
		parent::deleteById($monographId);

		// Delete chapters and assigned chapter authors.
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapters = $chapterDao->getChapters($monographId);
		while ($chapter = $chapters->next()) {
			// also removes Chapter Author associations
			$chapterDao->deleteObject($chapter);
		}

		// Delete monograph file directory.
		$monograph = $this->getById($monographId);
		assert(is_a($monograph, 'Submission'));

		import('lib.pkp.classes.file.SubmissionFileManager');
		$monographFileManager = new SubmissionFileManager($monograph->getPressId(), $monograph->getId());
		$monographFileManager->rmtree($monographFileManager->getBasePath());

		// Delete references to features or new releases.
		$featureDao = DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteByMonographId($monographId);

		$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
		$newReleaseDao->deleteByMonographId($monographId);

		$this->update('DELETE FROM submission_settings WHERE submission_id = ?', (int) $monographId);
		$this->update('DELETE FROM submissions WHERE submission_id = ?', (int) $monographId);
	}

	/**
	 * Get all monographs for a press.
	 * @param $pressId int
	 * @return DAOResultFactory containing matching Monographs
	 */
	function getByPressId($pressId) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$result = $this->retrieve(
			'SELECT	m.*, ps.date_published,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	submissions m
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN published_submissions ps ON (m.submission_id = ps.submission_id)
			WHERE	m.press_id = ?',
			array(
				'title', $primaryLocale, // Series title
				'title', $locale, // Series title
				'abbrev', $primaryLocale, // Series abbreviation
				'abbrev', $locale, // Series abbreviation
				(int) $pressId
			)
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Get unpublished monographs for a press.
	 * @param $pressId int
	 * @return DAOResultFactory containing matching Monographs
	 */
	function getUnpublishedMonographsByPressId($pressId) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$result = $this->retrieve(
			'SELECT	m.*, ps.date_published,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	submissions m
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN published_submissions ps ON (m.submission_id = ps.submission_id)
			WHERE	m.press_id = ? AND
				(ps.submission_id IS NULL OR ps.date_published IS NULL) AND
				m.submission_progress = 0',
			array(
				'title', $primaryLocale, // Series title
				'title', $locale, // Series title
				'abbrev', $primaryLocale, // Series abbreviation
				'abbrev', $locale, // Series abbreviation
				(int) $pressId
			)
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Delete all monographs by press ID.
	 * @param $pressId int
	 */
	function deleteByPressId($pressId) {
		$monographs = $this->getByPressId($pressId);
		import('classes.search.MonographSearchIndex');
		while ($monograph = $monographs->next()) {
			if ($monograph->getDatePublished()) {
				MonographSearchIndex::deleteTextIndex($monograph->getId());
			}
			$this->deleteById($monograph->getId());
		}
	}

	/**
	 * Get all monographs for a user.
	 * @param $userId int
	 * @param $pressId int optional
	 * @return array Monographs
	 */
	function getByUserId($userId, $pressId = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		$params = array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			(int) $userId
		);
		if ($pressId) $params[] = (int) $pressId;

		$result = $this->retrieve(
			'SELECT	m.*, ps.date_published,
				COALESCE(atl.setting_value, atpl.setting_value) AS series_title,
				COALESCE(aal.setting_value, aapl.setting_value) AS series_abbrev
			FROM	submissions m
				LEFT JOIN series aa ON (aa.series_id = m.series_id)
				LEFT JOIN series_settings atpl ON (aa.series_id = atpl.series_id AND atpl.setting_name = ? AND atpl.locale = ?)
				LEFT JOIN series_settings atl ON (aa.series_id = atl.series_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN series_settings aapl ON (aa.series_id = aapl.series_id AND aapl.setting_name = ? AND aapl.locale = ?)
				LEFT JOIN series_settings aal ON (aa.series_id = aal.series_id AND aal.setting_name = ? AND aal.locale = ?)
				LEFT JOIN published_submissions ps ON (m.submission_id = ps.submission_id)
			WHERE	m.user_id = ?' .
				(isset($pressId)?' AND m.press_id = ?':''),
			$params
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Remove all monographs from an series.
	 * @param $seriesId int
	 */
	function removeMonographsFromSeries($seriesId) {
		$this->update(
			'UPDATE submissions SET series_id = null WHERE series_id = ?',
			(int) $seriesId
		);

		$this->flushCache();
	}

	/**
	 * Get the ID of the last inserted monograph.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('submissions', 'submission_id');
	}

	/**
	 * Flush the monograph cache.
	 */
	function flushCache() {
		// Because both published_submissions and submissions are
		// cached by submission ID, flush both caches on update.
		$cache = $this->_getCache();
		$cache->flush();
		unset($cache);

		//TODO: flush cache of PublishedMonographDAO once created
	}

	/**
	 * Get all unassigned submissions for a context or all contexts
	 * @param $pressId int optional the ID of the press to query.
	 * @param $subEditorId int optional the ID of the sub editor
	 * 	whose series will be included in the results (excluding others).
	 * @return DAOResultFactory containing matching Submissions
	 */
	function getBySubEditorId($pressId = null, $subEditorId = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$params = array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			(int) ROLE_ID_MANAGER
		);
		if ($subEditorId) $params[] = (int) $subEditorId;
		if ($pressId) $params[] = (int) $pressId;

		$result = $this->retrieve(
			'SELECT	m.*, ps.date_published
			FROM	submissions m
				LEFT JOIN published_submissions ps ON m.submission_id = ps.submission_id
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN stage_assignments sa ON (m.submission_id = sa.submission_id)
				LEFT JOIN user_groups g ON (sa.user_group_id = g.user_group_id AND g.role_id = ?)
				' . ($subEditorId?' JOIN series_editors se ON (se.press_id = m.press_id AND se.user_id = ? AND se.series_id = m.series_id)':'') . '
			WHERE	m.date_submitted IS NOT NULL
				' . ($pressId?' AND m.press_id = ?':'') . '
			GROUP BY m.submission_id',
			$params
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	/**
	 * Associate a category with a monograph.
	 * @param $monographId int
	 * @param $categoryId int
	 */
	function addCategory($monographId, $categoryId) {
		$this->update(
			'INSERT INTO submission_categories
				(submission_id, category_id)
			VALUES
				(?, ?)',
			array(
				(int) $monographId,
				(int) $categoryId
			)
		);
	}

	/**
	 * Unassociate a category with a monograph.
	 * @param $monographId int
	 * @param $categoryId int
	 */
	function removeCategory($monographId, $categoryId) {
		$this->update(
			'DELETE FROM submission_categories WHERE submission_id = ? AND category_id = ?',
			array(
				(int) $monographId,
				(int) $categoryId
			)
		);

		// If any new release or feature object is associated
		// with this category delete them.
		$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /* @var $newReleaseDao NewReleaseDAO */
		$newReleaseDao->deleteNewRelease($monographId, ASSOC_TYPE_CATEGORY, $categoryId);

		$featureDao = DAORegistry::getDAO('FeatureDAO'); /* @var $featureDao FeatureDAO */
		$featureDao->deleteFeature($monographId, ASSOC_TYPE_CATEGORY, $categoryId);
	}

	/**
	 * Unassociate all categories.
	 * @param $monographId int
	 */
	function removeCategories($monographId) {
		$this->update(
			'DELETE FROM submission_categories WHERE submission_id = ?',
			(int) $monographId
		);
	}

	/**
	 * Get the categories associated with a given monograph.
	 * @param $monographId int
	 * @return DAOResultFactory
	 */
	function getCategories($monographId, $pressId = null) {
		$params = array((int) $monographId);
		if ($pressId) $params[] = (int) $pressId;

		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$result = $this->retrieve(
			'SELECT	c.*
			FROM	categories c,
				submission_categories sc,
				submissions s
			WHERE	c.category_id = sc.category_id AND
				s.submission_id = ? AND
			' . ($pressId?' c.press_id = s.press_id AND s.press_id = ? AND':'') . '
				s.submission_id = sc.submission_id',
			$params
		);

		// Delegate category creation to the category DAO.
		return new DAOResultFactory($result, $categoryDao, '_fromRow');
	}

	/**
	 * Get the categories not associated with a given monograph.
	 * @param $monographId int
	 * @return DAOResultFactory
	 */
	function getUnassignedCategories($monographId, $pressId = null) {
		$params = array((int) $monographId);
		if ($pressId) $params[] = (int) $pressId;

		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		// The strange ORDER BY clause is to return subcategories
		// immediately after their parent category's entry.
		$result = $this->retrieve(
			'SELECT	c.*
			FROM	submissions s
				JOIN categories c ON (c.press_id = s.press_id)
				LEFT JOIN submission_categories sc ON (s.submission_id = sc.submission_id AND sc.category_id = c.category_id)
			WHERE	s.submission_id = ? AND
				' . ($pressId?' s.press_id = ? AND':'') . '
				sc.submission_id IS NULL
			ORDER BY CASE WHEN c.parent_id = 0 THEN c.category_id * 2 ELSE (c.parent_id * 2) + 1 END ASC',
			$params
		);

		// Delegate category creation to the category DAO.
		return new DAOResultFactory($result, $categoryDao, '_fromRow');
	}

	/**
	 * Check if an monograph exists with the specified ID.
	 * @param $monographId int
	 * @param $pressId int
	 * @return boolean
	 */
	function categoryAssociationExists($monographId, $categoryId) {
		$result = $this->retrieve(
			'SELECT COUNT(*) FROM submission_categories WHERE submission_id = ? AND category_id = ?',
			array((int) $monographId, (int) $categoryId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		return $returner;
	}
}

?>
