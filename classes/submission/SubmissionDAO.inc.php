<?php

/**
 * @file classes/submission/SubmissionDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionDAO
 * @ingroup submission
 * @see Submission
 *
 * @brief Operations for retrieving and modifying Monograph objects.
 */

import('classes.submission.Submission');
import('lib.pkp.classes.submission.PKPSubmissionDAO');

define('ORDERBY_SERIES_POSITION', 'seriesPosition');

class SubmissionDAO extends PKPSubmissionDAO {

	/**
	 * Get a new data object representing the monograph.
	 * @return Submission
	 */
	public function newDataObject() {
		return new Submission();
	}

	/**
	 * Retrieve submission by public monograph ID or, failing that,
	 * internal monograph ID; public monograph ID takes precedence.
	 * @param $submissionId string
	 * @param $contextId int
	 * @return Submission|null
	 */
	function getByBestId($submissionId, $contextId) {
		$submission = $this->getByPubId('publisher-id', $submissionId, $contextId);
		if (!$submission || $submission->getData('status') !== STATUS_PUBLISHED) {
			$submission = $this->getById($submissionId);
		}
		return $submission && $submission->getData('status') === STATUS_PUBLISHED ? $submission : null;
	}

	/**
	 * @copydoc PKPSubmissionDAO::deleteById
	 */
	function deleteById($submissionId) {
		parent::deleteById($submissionId);

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

