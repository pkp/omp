<?php

/**
 * @file classes/monograph/MonographDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying Monograph objects.
 */

import('classes.monograph.Monograph');
import('lib.pkp.classes.submission.PKPSubmissionDAO');

define('ORDERBY_SERIES_POSITION', 'seriesPosition');

class MonographDAO extends PKPSubmissionDAO {
	/**
	 * Get a list of fields for which localized data is supported
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array_merge(parent::getLocaleFieldNames(), array(
			'copyrightNotice',
		));
	}

	/**
	 * Get a list of additional fields that do not have
	 * dedicated accessors.
	 * @return array
	 */
	function getAdditionalFieldNames() {
		return array_merge(
			parent::getAdditionalFieldNames(), array(
				'coverImage', 'coverImageAltText',
		));
	}

	/**
	 * Internal function to return an Monograph object from a row.
	 * @param $row array
	 * @param $submissionVersion
	 * @return Monograph
	 */
	function _fromRow($row, $submissionVersion = null) {
		$monograph = parent::_fromRow($row, $submissionVersion);

		$monograph->setSeriesId($row['series_id']);
		$monograph->setSeriesPosition($row['series_position']);
		$monograph->setSeriesTitle($row['series_title']);
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
				(locale, context_id, series_id, series_position, language, date_submitted, date_status_modified, last_modified, status, submission_progress, stage_id, pages, hide_author, edited_volume, citations, submission_version)
				VALUES
				(?, ?, ?, ?, ?, %s, %s, %s, ?, ?, ?, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getLocale(),
				(int) $monograph->getContextId(),
				(int) $monograph->getSeriesId(),
				$monograph->getSeriesPosition(),
				$monograph->getLanguage(),
				$monograph->getStatus() === null ? STATUS_QUEUED : (int) $monograph->getStatus(),
				$monograph->getSubmissionProgress() === null ? 1 : (int) $monograph->getSubmissionProgress(),
				$monograph->getStageId() === null ? 1 : (int) $monograph->getStageId(),
				$monograph->getPages(),
				(int) $monograph->getHideAuthor(),
				(int) $monograph->getWorkType(),
				$monograph->getCitations(),
				$monograph->getSubmissionVersion(),
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
		$monograph->stampModified();
		$this->update(
			sprintf('UPDATE	submissions
				SET	locale = ?,
					series_id = ?,
					series_position = ?,
					language = ?,
					date_submitted = %s,
					date_status_modified = %s,
					last_modified = %s,
					status = ?,
					context_id = ?,
					submission_progress = ?,
					stage_id = ?,
					edited_volume = ?,
					hide_author = ?,
					citations = ?,
					submission_version = ?
				WHERE	submission_id = ?',
				$this->datetimeToDB($monograph->getDateSubmitted()), $this->datetimeToDB($monograph->getDateStatusModified()), $this->datetimeToDB($monograph->getLastModified())),
			array(
				$monograph->getLocale(),
				(int) $monograph->getSeriesId(),
				$monograph->getSeriesPosition(),
				$monograph->getLanguage(),
				(int) $monograph->getStatus(),
				(int) $monograph->getContextId(),
				(int) $monograph->getSubmissionProgress(),
				(int) $monograph->getStageId(),
				(int) $monograph->getWorkType(),
				(int) $monograph->getHideAuthor(),
				$monograph->getCitations(),
				$monograph->getSubmissionVersion(),
				(int) $monograph->getId(),
			)
		);
		$this->updateLocaleFields($monograph);
		$this->flushCache();
	}

	/**
	 * @copydoc PKPSubmissionDAO::deleteById
	 */
	function deleteById($submissionId) {
		parent::deleteById($submissionId);

		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
		$publishedSubmissionDao->deleteById($submissionId);

		// Delete chapters and assigned chapter authors.
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapters = $chapterDao->getBySubmissionId($submissionId);
		while ($chapter = $chapters->next()) {
			// also removes Chapter Author associations
			$chapterDao->deleteObject($chapter);
		}

		// Delete references to features or new releases.
		$featureDao = DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteByMonographId($submissionId);

		$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
		$newReleaseDao->deleteByMonographId($submissionId);

		$monographSearchIndex = Application::getSubmissionSearchIndex();
		$monographSearchIndex->deleteTextIndex($submissionId);
		$monographSearchIndex->submissionChangesFinished();
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
	 * Get unpublished submissions for a press.
	 * @param $pressId int
	 * @return DAOResultFactory containing matching Monographs
	 */
	function getUnpublishedSubmissionsByPressId($pressId) {
		$params = $this->getFetchParameters();
		$params[] = (int) $pressId;

		$result = $this->retrieve(
			'SELECT	s.*, ps.date_published,
				' . $this->getFetchColumns() . '
			FROM	submissions s
				LEFT JOIN published_submissions ps ON (s.submission_id = ps.submission_id) and (ps.published_submission_version = s.submission_version) and ps.is_current_submission_version = 1
				' . $this->getFetchJoins() . '
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
	 * Unassociate a category with a submission.
	 * @param $submissionId int
	 * @param $categoryId int
	 */
	public function removeCategory($submissionId, $categoryId) {
		// If any new release or feature object is associated
		// with this category delete them.
		$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /* @var $newReleaseDao NewReleaseDAO */
		$newReleaseDao->deleteNewRelease($submissionId, ASSOC_TYPE_CATEGORY, $categoryId);

		$featureDao = DAORegistry::getDAO('FeatureDAO'); /* @var $featureDao FeatureDAO */
		$featureDao->deleteFeature($submissionId, ASSOC_TYPE_CATEGORY, $categoryId);

		return parent::removeCategory($submissionId, $categoryId);
	}

	/**
	 * @copydoc PKPSubmissionDAO::getFetchParameters()
	 */
	protected function getFetchParameters() {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		return array(
			'title', $primaryLocale, // Series title
			'title', $locale, // Series title
		);
	}

	/**
	 * @copydoc PKPSubmissionDAO::getFetchColumns()
	 */
	protected function getFetchColumns() {
		return 'COALESCE(stl.setting_value, stpl.setting_value) AS series_title';
	}

	/**
	 * @copydoc PKPSubmissionDAO::getFetchJoins()
	 */
	protected function getFetchJoins() {
		return 'LEFT JOIN series se ON se.series_id = s.series_id
			LEFT JOIN series_settings stpl ON (se.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
			LEFT JOIN series_settings stl ON (se.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)';
	}

	/**
	 * @copydoc PKPSubmissionDAO::getSubEditorJoin()
 	 */
	protected function getSubEditorJoin() {
		return 'JOIN series_editors se ON (se.press_id = s.context_id AND se.user_id = ? AND se.series_id = s.series_id)';
	}

	/**
	 * @copydoc PKPSubmissionDAO::getGroupByColumns()
	 */
	protected function getGroupByColumns() {
		return 's.submission_id, ps.date_published, stl.setting_value, stpl.setting_value';
	}

	/**
	 * @copydoc PKPSubmissionDAO::getCompletionConditions()
	 */
	protected function getCompletionConditions($completed) {
		return ' ps.date_published IS ' . ($completed?'NOT ':'') . 'NULL ';
	}

	/**
	 *
	 * @param  $submissionId
	 *
	 * @return void
	 */
	function newVersion($submissionId) {
		parent::newVersion($submissionId);
	}

	function versioningRelatedEntityDaos() {
		return array_merge(
			parent::versioningRelatedEntityDaos(),
			array('PublicationFormatDAO', 'ChapterDAO')
		);
	}

	/**
	 * Map a column heading value to a database value for sorting
	 * @param $sortBy string
	 * @return string
	 */
	public function getSortMapping($sortBy) {
		switch ($sortBy) {
			case ORDERBY_SERIES_POSITION:
				return 's.series_position';
		}
		return parent::getSortMapping($sortBy);
	}

	/**
	 * Get possible sort options.
	 * @return array
	 */
	public function getSortSelectOptions() {
		return array_merge(parent::getSortSelectOptions(), array(
			$this->getSortOption(ORDERBY_SERIES_POSITION, SORT_DIRECTION_ASC) => __('catalog.sortBy.seriesPositionAsc'),
			$this->getSortOption(ORDERBY_SERIES_POSITION, SORT_DIRECTION_DESC) => __('catalog.sortBy.seriesPositionDesc'),
		));
	}
}

