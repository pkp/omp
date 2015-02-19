<?php

/**
 * @file classes/monograph/MonographDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	/**
	 * Constructor.
	 */
	function MonographDAO() {
		parent::SubmissionDAO();
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
	 * Internal function to return an Monograph object from a row.
	 * @param $row array
	 * @return Monograph
	 */
	function _fromRow($row) {
		$monograph = parent::_fromRow($row);

		$monograph->setSeriesId($row['series_id']);
		$monograph->setSeriesPosition($row['series_position']);
		$monograph->setSeriesAbbrev(isset($row['series_abbrev'])?$row['series_abbrev']:null);
		$monograph->setWorkType($row['edited_volume']);

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
				(locale, user_id, context_id, series_id, series_position, language, comments_to_ed, date_submitted, date_status_modified, last_modified, status, submission_progress, stage_id, pages, hide_author, comments_status, edited_volume)
				VALUES
				(?, ?, ?, ?, ?, ?, ?, %s, %s, %s, ?, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getLocale(),
				(int) $monograph->getUserId(),
				(int) $monograph->getContextId(),
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
					context_id = ?,
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
				(int) $monograph->getContextId(),
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
		// Delete monograph file directory.
		$monograph = $this->getById($monographId);
		assert(is_a($monograph, 'Submission'));

		parent::deleteById($monographId);

		// Delete chapters and assigned chapter authors.
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapters = $chapterDao->getChapters($monographId);
		while ($chapter = $chapters->next()) {
			// also removes Chapter Author associations
			$chapterDao->deleteObject($chapter);
		}

		import('lib.pkp.classes.file.SubmissionFileManager');
		$monographFileManager = new SubmissionFileManager($monograph->getPressId(), $monograph->getId());
		$monographFileManager->rmtree($monographFileManager->getBasePath());

		// Delete references to features or new releases.
		$featureDao = DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteByMonographId($monographId);

		$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
		$newReleaseDao->deleteByMonographId($monographId);

		import('classes.search.MonographSearchIndex');
		MonographSearchIndex::deleteTextIndex($monograph->getId());
	}

	/**
	 * Get all monographs for a press.
	 * @param $pressId int
	 * @return DAOResultFactory containing matching Monographs
	 */
	function getByPressId($pressId) {
		return parent::getByContextId($pressId);
	}

	/**
	 * Get unpublished monographs for a press.
	 * @param $pressId int
	 * @return DAOResultFactory containing matching Monographs
	 */
	function getUnpublishedMonographsByPressId($pressId) {
		$params = $this->_getFetchParameters();
		$params[] = (int) $pressId;

		$result = $this->retrieve(
			'SELECT	s.*, ps.date_published,
				' . $this->_getFetchColumns() . '
			FROM	submissions s
				LEFT JOIN published_submissions ps ON (s.submission_id = ps.submission_id)
				' . $this->_getFetchJoins() . '
			WHERE	s.context_id = ? AND
				(ps.submission_id IS NULL OR ps.date_published IS NULL) AND
				s.submission_progress = 0',
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
	 * Get all unassigned submissions for a context or all contexts
	 * @param $contextId mixed optional the ID of the journal to query, or an array containing possible context ids.
	 * @param $subEditorId int optional the ID of the sub editor
	 * 	whose series will be included in the results (excluding others).
	 * @param $includeDeclined boolean optional include submissions which have STATUS_DECLINED
	 * @param $includePublished boolean optional include submissions which are published
	 * @param $rangeInfo DBRangeInfo
	 * @return DAOResultFactory containing matching Submissions
	 */
	function getBySubEditorId($contextId = null, $subEditorId = null, $includeDeclined = true, $includePublished = true, $rangeInfo = null) {
		$params = $this->_getFetchParameters();
		$params[] = (int) ROLE_ID_MANAGER;
		if ($subEditorId) $params[] = (int) $subEditorId;
		if ($contextId && is_int($contextId)) $params[] = (int) $contextId;

		$result = $this->retrieveRange(
			'SELECT	s.*, ps.date_published,
				' . $this->_getFetchColumns() . '
			FROM	submissions s
				LEFT JOIN published_submissions ps ON s.submission_id = ps.submission_id
				' . $this->_getFetchJoins() . '
				LEFT JOIN stage_assignments sa ON (s.submission_id = sa.submission_id)
				LEFT JOIN user_groups g ON (sa.user_group_id = g.user_group_id AND g.role_id = ?)
				' . ($subEditorId?' JOIN series_editors se ON (se.press_id = s.context_id AND se.user_id = ? AND se.series_id = s.series_id)':'') . '
			WHERE	s.date_submitted IS NOT NULL'
			. (!$includeDeclined?' AND s.status <> ' . STATUS_DECLINED : '' )
			. (!$includePublished?' AND ps.date_published IS NULL' : '' )
			. ($contextId && !is_array($contextId)?' AND s.context_id = ?':'')
			. ($contextId && is_array($contextId)?' AND s.context_id IN  (' . join(',', array_map(array($this,'_arrayWalkIntCast'), $contextId)) . ')':'') . '
			GROUP BY s.submission_id, ps.date_published, stl.setting_value, stpl.setting_value, sal.setting_value, sapl.setting_value',
			// See bug #8557; the above are required to keep PostgreSQL happy (and s.submission_id is required logically).
			$params,
			$rangeInfo
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
			' . ($pressId?' c.press_id = s.context_id AND s.context_id = ? AND':'') . '
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
				JOIN categories c ON (c.press_id = s.context_id)
				LEFT JOIN submission_categories sc ON (s.submission_id = sc.submission_id AND sc.category_id = c.category_id)
			WHERE	s.submission_id = ? AND
				' . ($pressId?' s.context_id = ? AND':'') . '
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

	/**
	 * Return a list of extra parameters to bind to the submission fetch queries.
	 * @return array
	 */
	protected function _getFetchParameters() {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		return array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
			'abbrev', $primaryLocale, // Series abbreviation
			'abbrev', $locale, // Series abbreviation
		);
	}

	/**
	 * Return a list of extra columns to fetch during submission fetch queries.
	 * @return string
	 */
	protected function _getFetchColumns() {
		return 'COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
			COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev';
	}

	/**
	 * Return a SQL snippet of extra joins to include during fetch queries.
	 * @return string
	 */
	protected function _getFetchJoins() {
		return 'LEFT JOIN series se ON se.series_id = s.series_id
			LEFT JOIN series_settings stpl ON (se.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
			LEFT JOIN series_settings stl ON (se.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
			LEFT JOIN series_settings sapl ON (se.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
			LEFT JOIN series_settings sal ON (se.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)';
	}
}

?>
