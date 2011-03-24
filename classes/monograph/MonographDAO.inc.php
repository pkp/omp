<?php

/**
 * @file classes/monograph/MonographDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

	function _cacheMiss(&$cache, $id) {
		$monograph =& $this->getMonograph($id, null, false);
		$cache->setCache($id, $monograph);
		return $monograph;
	}

	function &_getCache() {
		if (!isset($this->cache)) {
			$cacheManager =& CacheManager::getManager();
			$this->cache =& $cacheManager->getObjectCache('monographs', 0, array(&$this, '_cacheMiss'));
		}
		return $this->cache;
	}

	/**
	 * Constructor.
	 */
	function MonographDAO() {
		parent::DAO();
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
	}

	/**
	 * Get a list of fields for which localized data is supported
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array(
			'title', 'cleanTitle', 'abstract', 'coverPageAltText', 'showCoverPage', 'hideCoverPageToc', 'hideCoverPageAbstract', 'originalFileName', 'fileName', 'width', 'height',
			'discipline', 'subjectClass', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor');
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
	function &getMonograph($monographId, $pressId = null, $useCache = false) {
		if ($useCache) {
			$cache =& $this->_getCache();
			$returner =& $cache->get($monographId);
			if ($returner && $pressId != null && $pressId != $returner->getPressId()) $returner = null;
			return $returner;
		}

		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$params = array(
			'title',
			$primaryLocale,
			'title',
			$locale,
			'abbrev',
			$primaryLocale,
			'abbrev',
			$locale,
			$monographId
		);
		$sql = 'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs a
				LEFT JOIN series s ON s.series_id = a.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	monograph_id = ?';
		if ($pressId !== null) {
			$sql .= ' AND a.press_id = ?';
			$params[] = $pressId;
		}

		$result =& $this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$monograph =& $this->_fromRow($result->GetRowAssoc(false));

		}


		$result->Close();
		unset($result);

		return $monograph;
	}

	/**
	 * Internal function to return an Monograph object from a row.
	 * @param $row array
	 * @return Monograph
	 */
	function &_fromRow(&$row) {
		$monograph = new Monograph();
		$this->_monographFromRow($monograph, $row);
		return $monograph;
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
		$monograph->setCurrentStageId($row['stage_id']);
		$monograph->setStatus($row['status']);
		$monograph->setSubmissionProgress($row['submission_progress']);
		$monograph->setWorkType($row['edited_volume']);

		$this->getDataObjectSettings('monograph_settings', 'monograph_id', $row['monograph_id'], $monograph);


		// set review rounds info
		$reviewRoundsInfo = $this->getReviewRoundsInfoById($row['monograph_id']);
		$monograph->setReviewRoundsInfo($reviewRoundsInfo);

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
				$monograph->getUserId(),
				$monograph->getPressId(),
				$monograph->getSeriesId() ,
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				$monograph->getStatus() === null ? 1 : $monograph->getStatus(),
				$monograph->getSubmissionProgress() === null ? 1 : $monograph->getSubmissionProgress(),
				$monograph->getCurrentRound() === null ? 1 : $monograph->getCurrentRound(),
				$monograph->getCurrentStageId() === null ? 1 : $monograph->getCurrentStageId(),
				$monograph->getPages(),
				$monograph->getHideAuthor() === null ? 0 : $monograph->getHideAuthor(),
				$monograph->getCommentsStatus() === null ? 0 : $monograph->getCommentsStatus(),
				$monograph->getWorkType()
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
				SET
					user_id = ?,
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

				WHERE monograph_id = ?',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getUserId(),
				$monograph->getSeriesId(),
				$monograph->getLanguage(),
				$monograph->getCommentsToEditor(),
				$monograph->getStatus(),
				$monograph->getPressId(),
				$monograph->getSubmissionProgress(),
				$monograph->getCurrentRound(),
				$monograph->getCurrentStageId(),
				$monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME ? 1 : 0,
				$monograph->getHideAuthor() == null ? 0 : $monograph->getHideAuthor(),


				$monograph->getId()
			)
		);
		$this->updateLocaleFields($monograph);

		$contributorMap = null;

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

		// Delete monograph files.
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFileDao->deleteAllRevisionsBySubmissionId($monographId);

		// Delete monograph file directory.
		$monograph =& $this->getMonograph($monographId);
		assert(is_a($monograph, 'Monograph'));
		if (is_a($monograph, 'Monograph')) FileManager::rmtree($monograph->getFilePath());

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$seriesEditorSubmissionDao->deleteDecisionsByMonograph($monographId);
		$seriesEditorSubmissionDao->deleteReviewRoundsByMonograph($monographId);

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignmentDao->deleteByMonographId($monographId);

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffs =& $signoffDao->getBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId);
		foreach ($signoffs as $signoff) {
			$signoffDao->deleteObject($signoff);
		}

		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$monographCommentDao->deleteMonographComments($monographId);

		$this->update('DELETE FROM monograph_settings WHERE monograph_id = ?', $monographId);
		$this->update('DELETE FROM monographs WHERE monograph_id = ?', $monographId);
	}



	/**
	 * Get all monographs for a press.
	 * @param $userId int
	 * @param $pressId int
	 * @return DAOResultFactory containing matching Monographs
	 */
	function &getMonographsByPressId($pressId) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$monographs = array();

		$result =& $this->retrieve(
			'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM	monographs a
				LEFT JOIN series s ON s.series_id = a.series_id
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE a.press_id = ?',
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$pressId
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

		while (!$monographs->eof()) {
			$monograph =& $monographs->next();
			$this->deleteMonographById($monograph->getId());
		}
	}

	/**
	 * Get all monographs for a user.
	 * @param $userId int
	 * @param $pressId int optional
	 * @return array Monographs
	 */
	function &getByUserId($userId, $pressId = null) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$params = array(
			'title',
			$primaryLocale,
			'title',
			$locale,
			'abbrev',
			$primaryLocale,
			'abbrev',
			$locale,
			$userId
		);
		if ($pressId) $params[] = $pressId;
		$monographs = array();

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

		while (!$result->EOF) {
			$monographs[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $monographs;
	}

	/**
	 * Get the ID of the press a monograph is in.
	 * FIXME: Move to PressDAO, see #6455.
	 * @param $monographId int
	 * @return int
	 */
	function getMonographPressId($monographId) {
		$result =& $this->retrieve(
			'SELECT press_id FROM monographs WHERE monograph_id = ?', $monographId
		);
		$returner = isset($result->fields[0]) ? $result->fields[0] : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Check if the specified incomplete submission exists.
	 * @param $monographId int
	 * @param $userId int
	 * @param $pressId int
	 * @return int the submission progress
	 */
	function incompleteSubmissionExists($monographId, $userId, $pressId) {
		$result =& $this->retrieve(
			'SELECT	submission_progress
			FROM	monographs
			WHERE	monograph_id = ? AND
				user_id = ? AND
				press_id = ? AND
				date_submitted IS NULL',
			array($monographId, $userId, $pressId)
		);
		$returner = isset($result->fields[0]) ? $result->fields[0] : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Change the status of the monograph.
	 * FIXME: Implement with updateMonograph().
	 * @param $monographId int
	 * @param $status int
	 */
	function changeMonographStatus($monographId, $status) {
		$this->update(
			'UPDATE monographs SET status = ? WHERE monograph_id = ?', array($status, $monographId)
		);

		$this->flushCache();
	}

	/**
	 * Remove all monographs from an series.
	 * @param $seriesId int
	 */
	function removeMonographsFromSeries($seriesId) {
		$this->update(
			'UPDATE monographs SET series_id = null WHERE series_id = ?', $seriesId
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

	function flushCache() {
		// Because both publishedMonographs and monographs are cached by
		// monograph ID, flush both caches on update.
		$cache =& $this->_getCache();
		$cache->flush();
		unset($cache);

		//TODO: flush cash of publishedMonographDAO once created
	}

	/**
	 * Get an array describing a monograph's current round for each review type
	 * FIXME: Move to ReviewRoundDAO, see #6455.
	 * FIXME: Return review round objects rather than an untyped array, see #6406.
	 * @param $monographId int
	 * @return array
	 */
	function &getReviewRoundsInfoById($monographId) {
		$returner = array();
		$result =& $this->retrieve(
			'SELECT	MAX(round) AS current_round, review_type
			FROM	review_rounds r
			WHERE	submission_id = ?
			GROUP BY review_type',
			$monographId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$row['review_type']] = $row['current_round'];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>
