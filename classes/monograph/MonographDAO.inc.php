<?php

/**
 * @file classes/monograph/MonographDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying Monograph objects.
 */


import('classes.monograph.Monograph');

class MonographDAO extends DAO {
	var $authorDao;
	var $cache;

	/**
	 * Constructor.
	 */
	function MonographDAO() {
		parent::DAO();
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
	}

	/**
	 * Callback for a cache miss.
	 * @param $cache Cache
	 * @param $id string
	 * @return Monograph
	 */
	function _cacheMiss(&$cache, $id) {
		$monograph =& $this->getMonograph($id, null, false);
		$cache->setCache($id, $monograph);
		return $monograph;
	}

	/**
	 * Get the monograph cache.
	 * @return Cache
	 */
	function &_getCache() {
		if (!isset($this->cache)) {
			$cacheManager =& CacheManager::getManager();
			$this->cache =& $cacheManager->getObjectCache('monographs', 0, array(&$this, '_cacheMiss'));
		}
		return $this->cache;
	}

	/**
	 * Get a list of fields for which localized data is supported
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array(
			'title', 'subtitle', 'cleanTitle', 'prefix', 'abstract', 'coverPageAltText', 'showCoverPage', 'hideCoverPageToc', 'hideCoverPageAbstract', 'originalFileName', 'fileName', 'width', 'height',
			'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor', 'rights', 'source');
	}

	/**
	 * Update the localized fields for this object.
	 * @param $monograph
	 */
	function updateLocaleFields(&$monograph) {
		$this->updateDataObjectSettings('monograph_settings', $monograph, array(
			'monograph_id' => $monograph->getId()
		));
	}

	/**
	 * Retrieve Monograph by monograph id
	 * @param $monographId int
	 * @param $pressId int optional
	 * @param $useCache boolean optional
	 * @return Monograph
	 */
	function &getById($monographId, $pressId = null, $useCache = false) {
		if ($useCache) {
			$cache =& $this->_getCache();
			$returner =& $cache->get($monographId);
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

		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs m
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
	 * Internal function to return an Monograph object from a row.
	 * @param $row array
	 * @return Monograph
	 */
	function &_fromRow(&$row) {
		$monograph = $this->newDataObject();
		$this->_monographFromRow($monograph, $row);
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
	 * Internal function to fill in the passed monograph object from the row.
	 * @param $monograph Monograph output monograph
	 * @param $row array input row
	 */
	function _monographFromRow(&$monograph, &$row) {
		$monograph->setId($row['monograph_id']);
		$monograph->setLocale($row['locale']);
		$monograph->setUserId($row['user_id']);
		$monograph->setPressId($row['press_id']);
		$monograph->setStatus($row['status']);
		$monograph->setSeriesId($row['series_id']);
		$monograph->setSeriesAbbrev(isset($row['series_abbrev'])?$row['series_abbrev']:null);
		$monograph->setLanguage($row['language']);
		$monograph->setCommentsToEditor($row['comments_to_ed']);
		$monograph->setDateSubmitted($row['date_submitted']);
		$monograph->setDateStatusModified($this->datetimeFromDB($row['date_status_modified']));
		$monograph->setLastModified($this->datetimeFromDB($row['last_modified']));
		$monograph->setCurrentRound($row['current_round']);
		$monograph->setStageId($row['stage_id']);
		$monograph->setStatus($row['status']);
		$monograph->setSubmissionProgress($row['submission_progress']);
		$monograph->setWorkType($row['edited_volume']);

		$this->getDataObjectSettings('monograph_settings', 'monograph_id', $row['monograph_id'], $monograph);

		HookRegistry::call('MonographDAO::_monographFromRow', array(&$monograph, &$row));
	}

	/**
	 * inserts a new monograph into monographs table
	 * @param Monograph object
	 * @return Monograph Id int
	 */
	function insertMonograph(&$monograph) {
		$monograph->stampModified();
		$this->update(
			sprintf('INSERT INTO monographs
				(locale, user_id, press_id, series_id, language, comments_to_ed, date_submitted, date_status_modified, last_modified, status, submission_progress, current_round, stage_id, pages, hide_author, comments_status, edited_volume)
				VALUES
				(?, ?, ?, ?, ?, ?, %s, %s, %s, ?, ?, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getLocale(),
				(int) $monograph->getUserId(),
				(int) $monograph->getPressId(),
				(int) $monograph->getSeriesId(),
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				$monograph->getStatus() === null ? STATUS_QUEUED : (int) $monograph->getStatus(),
				$monograph->getSubmissionProgress() === null ? 1 : (int) $monograph->getSubmissionProgress(),
				$monograph->getCurrentRound() === null ? 1 : (int) $monograph->getCurrentRound(),
				$monograph->getStageId() === null ? 1 : (int) $monograph->getStageId(),
				$monograph->getPages(),
				(int) $monograph->getHideAuthor(),
				(int) $monograph->getCommentsStatus(),
				(int) $monograph->getWorkType()
			)
		);

		$monograph->setId($this->getInsertMonographId());
		$this->updateLocaleFields($monograph);

		return $monograph->getId();
	}

	/**
	 * updates a monograph
	 * @param Monograph object
	 */
	function updateMonograph($monograph) {
		$this->update(
			sprintf('UPDATE monographs
				SET	user_id = ?,
					series_id = ?,
					language = ?,
					comments_to_ed = ?,
					date_submitted = %s,
					date_status_modified = %s,
					last_modified = %s,
					status = ?,
					press_id = ?,
					submission_progress = ?,
					current_round = ?,
					stage_id = ?,
					edited_volume = ?,
					hide_author = ?

				WHERE	monograph_id = ?',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				(int) $monograph->getUserId(),
				(int) $monograph->getSeriesId(),
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				(int) $monograph->getStatus(),
				(int) $monograph->getPressId(),
				(int) $monograph->getSubmissionProgress(),
				(int) $monograph->getCurrentRound(),
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
	 * Delete monograph by id.
	 * @param $monograph object Monograph
	 */
	function deleteMonograph(&$monograph) {
		return $this->deleteMonographById($monograph->getId());
	}

	/**
	 * Delete an monograph by ID.
	 * @param $monographId int
	 */
	function deleteMonographById($monographId) {
		$this->authorDao->deleteAuthorsByMonograph($monographId);

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmissionDao->deleteDecisionsByMonograph($monographId);
		$seriesEditorSubmissionDao->deleteReviewRoundsByMonograph($monographId);

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignmentDao->deleteBySubmissionId($monographId);

		// Delete chapters and assigned chapter authors.
		$chapterDao =& DAORegistry::getDAO('ChapterDAO');
		$chapters =& $chapterDao->getChapters($monographId);
		while ($chapter =& $chapters->next()) {
			// also removes Chapter Author associations
			$chapterDao->deleteObject($chapter);
		}

		// Delete controlled vocab lists assigned to this Monograph
		$monographKeywordDao =& DAORegistry::getDAO('MonographKeywordDAO');
		$monographKeywordVocab =& $monographKeywordDao->getBySymbolic(CONTROLLED_VOCAB_MONOGRAPH_KEYWORD, ASSOC_TYPE_MONOGRAPH, $monographId);
		$monographKeywordDao->deleteObject($monographKeywordVocab);

		$monographDisciplineDao =& DAORegistry::getDAO('MonographDisciplineDAO');
		$monographDisciplineVocab =& $monographDisciplineDao->getBySymbolic(CONTROLLED_VOCAB_MONOGRAPH_DISCIPLINE, ASSOC_TYPE_MONOGRAPH, $monographId);
		$monographDisciplineDao->deleteObject($monographDisciplineVocab);

		$monographAgencyDao =& DAORegistry::getDAO('MonographAgencyDAO');
		$monographAgencyVocab =& $monographAgencyDao->getBySymbolic(CONTROLLED_VOCAB_MONOGRAPH_AGENCY, ASSOC_TYPE_MONOGRAPH, $monographId);
		$monographAgencyDao->deleteObject($monographAgencyVocab);

		$monographLanguageDao =& DAORegistry::getDAO('MonographLanguageDAO');
		$monographLanguageVocab =& $monographLanguageDao->getBySymbolic(CONTROLLED_VOCAB_MONOGRAPH_LANGUAGE, ASSOC_TYPE_MONOGRAPH, $monographId);
		$monographLanguageDao->deleteObject($monographLanguageVocab);

		$monographSubjectDao =& DAORegistry::getDAO('MonographSubjectDAO');
		$monographSubjectVocab =& $monographSubjectDao->getBySymbolic(CONTROLLED_VOCAB_MONOGRAPH_SUBJECT, ASSOC_TYPE_MONOGRAPH, $monographId);
		$monographSubjecDao->deleteObject($monographSubjectVocab);

		// Signoff DAOs
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$monographFileSignoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');

		// Delete Signoffs associated with a monogrpah file of this monograph.
		$monographFileSignoffs = $monographFileSignoffDao->getAllByMonograph($monographId);
		while ($signoff =& $monographFileSignoffs->next()) {
			$signoffDao->deleteObject($signoff);
			unset($signoff);
		}

		// Delete the Signoffs associated with the monograph itself.
		$monographSignoffs =& $signoffDao->getAllByAssocType(ASSOC_TYPE_MONOGRAPH, $monographId);
		while ($signoff =& $monographSignoffs->next()) {
			$signoffDao->deleteObject($signoff);
			unset($signoff);
		}

		// Delete the stage assignments.
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
		$stageAssignments =& $stageAssignmentDao->getBySubmissionAndStageId($monographId);
		while ($stageAssignment =& $stageAssignments->next()) {
			$stageAssignmentDao->deleteObject($stageAssignment);
			unset($stageAssignment);
		}

		// N.B. Files must be deleted after signoffs to identify monograph file signoffs.
		// Delete monograph files.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFileDao->deleteAllRevisionsBySubmissionId($monographId);

		// Delete monograph file directory.
		$monograph =& $this->getMonograph($monographId);
		assert(is_a($monograph, 'Monograph'));

		import('classes.file.MonographFileManager');
		$monographFileManager = new FileManager($monograph->getPressId(), $monograph->getId());
		$monographFileManager->rmtree($monographFileManager->getBasePath());

		// Delete any comments.
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$monographCommentDao->deleteMonographComments($monographId);

		$this->update('DELETE FROM monograph_settings WHERE monograph_id = ?', (int) $monographId);
		$this->update('DELETE FROM monographs WHERE monograph_id = ?', (int) $monographId);
	}

	/**
	 * Get all monographs for a press.
	 * @param $pressId int
	 * @return DAOResultFactory containing matching Monographs
	 */
	function &getMonographsByPressId($pressId) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs m
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	m.press_id = ?',
			array(
				'title', $primaryLocale, // Series title
				'title', $locale, // Series title
				'abbrev', $primaryLocale, // Series abbreviation
				'abbrev', $locale, // Series abbreviation
				(int) $pressId
			)
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get unpublished monographs for a press.
	 * @param $pressId int
	 * @return DAOResultFactory containing matching Monographs
	 */
	function &getUnpublishedMonographsByPressId($pressId) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs m
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN published_monographs pm ON (m.monograph_id = pm.monograph_id)
			WHERE	m.press_id = ? AND
				pm.monograph_id IS NULL AND
				m.submission_progress = 0',
			array(
				'title', $primaryLocale, // Series title
				'title', $locale, // Series title
				'abbrev', $primaryLocale, // Series abbreviation
				'abbrev', $locale, // Series abbreviation
				(int) $pressId
			)
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Delete all monographs by press ID.
	 * @param $pressId int
	 */
	function deleteMonographsByPressId($pressId) {
		$monographs = $this->getMonographsByPressId($pressId);

		while ($monograph =& $monographs->next()) {
			$this->deleteMonographById($monograph->getId());
			unset($monograph);
		}
	}

	/**
	 * Get all monographs for a user.
	 * @param $userId int
	 * @param $pressId int optional
	 * @return array Monographs
	 */
	function &getByUserId($userId, $pressId = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		$params = array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			(int) $userId
		);
		if ($pressId) $params[] = $pressId;

		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(atl.setting_value, atpl.setting_value) AS series_title,
				COALESCE(aal.setting_value, aapl.setting_value) AS series_abbrev
			FROM	monographs m
				LEFT JOIN series aa ON (aa.series_id = m.series_id)
				LEFT JOIN series_settings atpl ON (aa.series_id = atpl.series_id AND atpl.setting_name = ? AND atpl.locale = ?)
				LEFT JOIN series_settings atl ON (aa.series_id = atl.series_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN series_settings aapl ON (aa.series_id = aapl.series_id AND aapl.setting_name = ? AND aapl.locale = ?)
				LEFT JOIN series_settings aal ON (aa.series_id = aal.series_id AND aal.setting_name = ? AND aal.locale = ?)
			WHERE	m.user_id = ?' .
				(isset($pressId)?' AND m.press_id = ?':''),
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Remove all monographs from an series.
	 * @param $seriesId int
	 */
	function removeMonographsFromSeries($seriesId) {
		$this->update(
			'UPDATE monographs SET series_id = null WHERE series_id = ?',
			(int) $seriesId
		);

		$this->flushCache();
	}

	/**
	 * Get the ID of the last inserted monograph.
	 * @return int
	 */
	function getInsertMonographId() {
		return $this->getInsertId('monographs', 'monograph_id');
	}

	/**
	 * Flush the monograph cache.
	 */
	function flushCache() {
		// Because both publishedMonographs and monographs are cached by
		// monograph ID, flush both caches on update.
		$cache =& $this->_getCache();
		$cache->flush();
		unset($cache);

		//TODO: flush cache of publishedMonographDAO once created
	}

	/**
	 * Get all unassigned monographs for a press or all presses
	 * @param $pressId int optional the ID of the press to query.
	 * @param $seriesEditorId int optional the ID of the series editor
	 * 	whose series will be included in the results (excluding others).
	 * @return DAOResultFactory containing matching Monographs
	 */
	function &getUnassignedMonographs($pressId = null, $seriesEditorId = null) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$params = array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
			(int) ROLE_ID_SERIES_EDITOR
		);
		if ($seriesEditorId) $params[] = (int) $seriesEditorId;
		if ($pressId) $params[] = (int) $pressId;

		$result =& $this->retrieve(
			'SELECT	m.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs m
				LEFT JOIN series s ON s.series_id = m.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
				LEFT JOIN stage_assignments sa ON (m.monograph_id = sa.submission_id)
				LEFT JOIN user_groups g ON (sa.user_group_id = g.user_group_id AND g.role_id = ?)
				' . ($seriesEditorId?' JOIN series_editors se ON (se.press_id = m.press_id AND se.user_id = ? AND se.series_id = m.series_id)':'') . '
			WHERE	m.date_submitted IS NOT NULL AND
				g.user_group_id IS NULL
				' . ($pressId?' AND m.press_id = ?':''),
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Associate a category with a monograph.
	 * @param $monographId int
	 * @param $categoryId int
	 */
	function addCategory($monographId, $categoryId) {
		$this->update(
			'INSERT INTO monograph_categories
				(monograph_id, category_id)
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
			'DELETE FROM monograph_categories WHERE monograph_id = ? AND category_id = ?',
			array(
				(int) $monographId,
				(int) $categoryId
			)
		);
	}

	/**
	 * Unassociate all categories.
	 * @param $monographId int
	 */
	function removeCategories($monographId) {
		$this->update(
			'DELETE FROM monograph_categories WHERE monograph_id = ?',
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

		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$result =& $this->retrieve(
			'SELECT	c.*
			FROM	categories c,
				monograph_categories mc,
				monographs m
			WHERE	c.category_id = mc.category_id AND
				m.monograph_id = ? AND
			' . ($pressId?' c.press_id = m.press_id AND m.press_id = ? AND':'') . '
				m.monograph_id = mc.monograph_id',
			$params
		);

		// Delegate category creation to the category DAO.
		$returner = new DAOResultFactory($result, $categoryDao, '_fromRow');
		return $returner;
	}

	/**
	 * Get the categories not associated with a given monograph.
	 * @param $monographId int
	 * @return DAOResultFactory
	 */
	function getUnassignedCategories($monographId, $pressId = null) {
		$params = array((int) $monographId);
		if ($pressId) $params[] = (int) $pressId;

		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		// The strange ORDER BY clause is to return subcategories
		// immediately after their parent category's entry.
		$result =& $this->retrieve(
			'SELECT	c.*
			FROM	monographs m
				JOIN categories c ON (c.press_id = m.press_id)
				LEFT JOIN monograph_categories mc ON (m.monograph_id = mc.monograph_id AND mc.category_id = c.category_id)
			WHERE	m.monograph_id = ? AND
				' . ($pressId?' m.press_id = ? AND':'') . '
				mc.monograph_id IS NULL
			ORDER BY CASE WHEN c.parent_id = 0 THEN c.category_id * 2 ELSE (c.parent_id * 2) + 1 END ASC',
			$params
		);

		// Delegate category creation to the category DAO.
		$returner = new DAOResultFactory($result, $categoryDao, '_fromRow');
		return $returner;
	}

	/**
	 * Check if an monograph exists with the specified ID.
	 * @param $monographId int
	 * @param $pressId int
	 * @return boolean
	 */
	function categoryAssociationExists($monographId, $categoryId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM monograph_categories WHERE monograph_id = ? AND category_id = ?',
			array((int) $monographId, (int) $categoryId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>
