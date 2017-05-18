<?php

/**
 * @file classes/services/SubmissionService.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionService
 * @ingroup services
 *
 * @brief Extends the base submission helper service class with app-specific
 *  requirements.
 */

namespace App\Services;

class SubmissionService extends PKPSubmissionService {

	/**
	 * Initialize hooks for extending PKPSubmissionService
	 */
    public function __construct() {
		parent::__construct();

		\HookRegistry::register('Submission::getSubmissionList::queryBuilder', array($this, 'getSubmissionListQueryBuilder'));
		\HookRegistry::register('Submission::listQueryBuilder::get', array($this, 'getSubmissionListQueryObject'));
		\HookRegistry::register('Submission::toArray::defaultParams', array($this, 'toArrayDefaultParams'));
		\HookRegistry::register('Submission::toArray::output', array($this, 'toArrayOutput'));
	}

	/**
	 * Add a monograph to the catalog
	 *
	 * @param Submission $submission
	 * @return bool
	 */
	public function addToCatalog($submission) {

		if (!is_a($submission, 'Submission')) {
			error_log('Attempt to add catalog entry failed because no submission could be found.');
			return false;
		}

		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publishedMonographDao = \DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($submission->getId(), null, false);
		if (!$publishedMonograph) {
			$publishedMonograph = $publishedMonographDao->newDataObject();
			$publishedMonograph->setId($submission->getId());
			$publishedMonographDao->insertObject($publishedMonograph);
		}
		$publicationFormats = \DAORegistry::getDAO('PublicationFormatDAO')
			->getBySubmissionId($submission->getId())
			->toAssociativeArray();
		$request = \Application::getRequest();

		// Update the monograph status.
		$submission->setStatus(STATUS_PUBLISHED);
		\Application::getSubmissionDao()->updateObject($submission);

		$publishedMonograph->setDatePublished(\Core::getCurrentDate());
		$publishedMonographDao->updateObject($publishedMonograph);

		$notificationMgr = new \NotificationManager();
		$notificationMgr->updateNotification(
			$request,
			array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
			null,
			ASSOC_TYPE_MONOGRAPH,
			$publishedMonograph->getId()
		);

		// Remove publication format tombstones.
		$publicationFormatTombstoneMgr = new \PublicationFormatTombstoneManager();
		$publicationFormatTombstoneMgr->deleteTombstonesByPublicationFormats($publicationFormats);

		// Update the search index for this published monograph.
		import('classes.search.MonographSearchIndex');
		\MonographSearchIndex::indexMonographMetadata($submission);

		// Log the publication event.
		import('lib.pkp.classes.log.SubmissionLog');
		\SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_METADATA_PUBLISH, 'submission.event.metadataPublished');
	}

	/**
	 * Rmove a monograph from the catalog
	 *
	 * @param Submission $submission
	 * @return bool
	 */
	public function removeFromCatalog($submission) {

		if (!is_a($submission, 'Submission')) {
			error_log('Attempt to remove catalog entry failed because no submission could be found.');
			return false;
		}

		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publishedMonographDao = \DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($submission->getId(), null, false);
		$publicationFormats = \DAORegistry::getDAO('PublicationFormatDAO')
			->getBySubmissionId($submission->getId())
			->toAssociativeArray();
		$request = \Application::getRequest();

		// Update the monograph status.
		$submission->setStatus(STATUS_QUEUED);
		\Application::getSubmissionDao()->updateObject($submission);

		// Unpublish monograph.
		$publishedMonograph->setDatePublished(null);
		$publishedMonographDao->updateObject($publishedMonograph);

		$notificationMgr = new \NotificationManager();
		$notificationMgr->updateNotification(
			$request,
			array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
			null,
			ASSOC_TYPE_MONOGRAPH,
			$publishedMonograph->getId()
		);

		// Create tombstones for each publication format.
		$publicationFormatTombstoneMgr = new \PublicationFormatTombstoneManager();
		$publicationFormatTombstoneMgr->insertTombstonesByPublicationFormats($publicationFormats, $request->getContext());

		// Log the unpublication event.
		import('lib.pkp.classes.log.SubmissionLog');
		\SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_METADATA_UNPUBLISH, 'submission.event.metadataUnpublished');
	}

	/**
	 * Run app-specific query builder methods for getSubmissionList
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option QueryBuilders\SubmissionListQueryBuilder $submissionListQB
	 *		@option int $contextId
	 *		@option array $args
	 * ]
	 *
	 * @return QueryBuilders\SubmissionListQueryBuilder
	 */
	public function getSubmissionListQueryBuilder($hookName, $args) {
		$submissionListQB =& $args[0];
		$contextId = $args[1];
		$args = $args[2];

		if (!empty($args['categoryIds'])) {
			$submissionListQB->filterByCategories($args['categoryIds']);
		}

		if (!empty($args['seriesIds'])) {
			$submissionListQB->filterBySeries($args['seriesIds']);
		}

		return $submissionListQB;
	}

	/**
	 * Add app-specific query statements to the list get query
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option object $queryObject
	 *		@option QueryBuilders\SubmissionListQueryBuilder $queryBuilder
	 * ]
	 *
	 * @return object
	 */
	public function getSubmissionListQueryObject($hookName, $args) {
		$queryObject =& $args[0];
		$queryBuilder = $args[1];

		$queryObject = $queryBuilder->appGet($queryObject);

		return true;
	}

	/**
	 * Add app-specific default params when converting a submission to an array
	 *
	 * @param $hookName string
	 * @param $args array [
	 * 		@option $defaultParams array Default param settings
	 * 		@option $params array Params requested for this conversion
	 * 		@option $submissions array Submissions to convert to array
	 * ]
	 *
	 * @return array
	 */
	public function toArrayDefaultParams($hookName, $args) {
		$defaultParams =& $args[0];
		$params = $args[1];
		$submissions = $args[2];

		$defaultParams['category'] = true;
		$defaultParams['series'] = true;

		return true;
	}

	/**
	 * Add app-specific output when converting a submission to an array
	 *
	 * @param $hookName string
	 * @param $args array [
	 * 		@option $output array All submissions converted to array
	 * 		@option $params array Params requested for this conversion
	 * 		@option $submissions array Array of Submission objects
	 * ]
	 *
	 * @return array
	 */
	public function toArrayOutput($hookName, $args) {
		$output =& $args[0];
		$params = $args[1];
		$submissions = $args[2];

		if (empty($params['category']) && empty($params['series'])) {
			return true;
		}

		// Create array of Submission objects with keys matching the $output
		// array
		$submissionObjects = array();
		foreach ($submissions as $submission) {

			if (!is_a($submission, 'Submission')) {
				error_log('Could not convert item to array because it is not a submission. ' . __LINE__);
			}

			$id = $submission->getId();
			foreach ($output as $key => $submissionArray) {
				if ($submissionArray['id'] === $id) {
					$submissionObjects[$key] = $submission;
				}
			}
		}

		foreach ($submissionObjects as $key => $submission) {

			if (!empty($params['series'])) {
				$output[$key]['series'] = array(
					'id' => $submission->getSeriesId(),
					'title' => $submission->getSeriesTitle(),
					'abbreviation' => $submission->getSeriesAbbrev(),
					'position' => $submission->getSeriesPosition(),
				);
			}

			$featureDao = \DAORegistry::getDAO('FeatureDAO');
			$output[$key]['featured'] = $featureDao->getFeaturedAll($submission->getId());

			$newReleaseDao = \DAORegistry::getDAO('NewReleaseDAO');
			$output[$key]['newRelease'] = $newReleaseDao->getNewReleaseAll($submission->getId());

			// @todo categories
		}

		return true;
	}
}
