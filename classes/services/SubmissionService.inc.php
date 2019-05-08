<?php

/**
 * @file classes/services/SubmissionService.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionService
 * @ingroup services
 *
 * @brief Extends the base submission helper service class with app-specific
 *  requirements.
 */

namespace APP\Services;

class SubmissionService extends \PKP\Services\PKPSubmissionService {

	/**
	 * Initialize hooks for extending PKPSubmissionService
	 */
	public function __construct() {
		\HookRegistry::register('Submission::isPublic', array($this, 'modifyIsPublic'));
		\HookRegistry::register('Submission::getMany::queryBuilder', array($this, 'modifySubmissionQueryBuilder'));
		\HookRegistry::register('Submission::getMany::queryObject', array($this, 'modifySubmissionListQueryObject'));
		\HookRegistry::register('Submission::getBackendListProperties::properties', array($this, 'modifyBackendListPropertyValues'));
		\HookRegistry::register('Submission::getProperties::values', array($this, 'modifyPropertyValues'));
	}

	/**
	 * Modify the isPublic check on a submission, based on whether it has a
	 * catalog entry.
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option boolean Is it public?
	 *		@option Submission
	 * ]
	 */
	public function modifyIsPublic($hookName, $args) {
		$isPublic =& $args[0];
		$submission = $args[1];

		if (is_a($submission, 'PublishedMonograph')) {
			$publishedMonograph = $submission;
		} else {
			$publishedMonographDao = \DAORegistry::getDAO('PublishedMonographDAO');
			$publishedMonograph = $publishedMonographDao->getBySubmissionId(
				$submission->getId(),
				$submission->getContextId()
			);
		}

		if ($publishedMonograph && $publishedMonograph->getDatePublished()) {
			$isPublic = true;
			return;
		}
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
		$publishedMonograph = $publishedMonographDao->getBySubmissionId($submission->getId(), null, false);
		if (!$publishedMonograph) {
			$publishedMonograph = $publishedMonographDao->newDataObject();
			$publishedMonograph->setId($submission->getId());
			$publishedMonographDao->insertObject($publishedMonograph);
		}
		$publicationFormats = \DAORegistry::getDAO('PublicationFormatDAO')
			->getBySubmissionId($submission->getId())
			->toAssociativeArray();
		$request = \Application::get()->getRequest();

		// Update the monograph status.
		$submission->setStatus(STATUS_PUBLISHED);
		\Application::getSubmissionDao()->updateObject($submission);

		$datePublished = $submission->getDatePublished() ? $submission->getDatePublished() : \Core::getCurrentDate();
		$publishedMonograph->setDatePublished($datePublished);
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
		$monographSearchIndex = \Application::getSubmissionSearchIndex();
		$monographSearchIndex->submissionMetadataChanged($submission);
		$monographSearchIndex->submissionChangesFinished();

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
		$publishedMonograph = $publishedMonographDao->getBySubmissionId($submission->getId(), null, false);
		$publicationFormats = \DAORegistry::getDAO('PublicationFormatDAO')
			->getBySubmissionId($submission->getId())
			->toAssociativeArray();
		$request = \Application::get()->getRequest();

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
	 *		@option QueryBuilders\SubmissionQueryBuilder $submissionListQB
	 *		@option int $contextId
	 *		@option array $args
	 * ]
	 *
	 * @return QueryBuilders\SubmissionQueryBuilder
	 */
	public function modifySubmissionQueryBuilder($hookName, $args) {
		$submissionListQB =& $args[0];
		$requestArgs = $args[1];

		if (!empty($requestArgs['categoryIds'])) {
			$submissionListQB->filterByCategories($requestArgs['categoryIds']);
		}

		if (!empty($requestArgs['seriesIds'])) {
			$submissionListQB->filterBySeries($requestArgs['seriesIds']);
		}

		if (!empty($requestArgs['orderByFeatured'])) {
			$submissionListQB->orderByFeatured();
		}

		return $submissionListQB;
	}

	/**
	 * Add app-specific query statements to the list get query
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option object $queryObject
	 *		@option QueryBuilders\SubmissionQueryBuilder $queryBuilder
	 * ]
	 *
	 * @return object
	 */
	public function modifySubmissionListQueryObject($hookName, $args) {
		$queryObject =& $args[0];
		$queryBuilder = $args[1];

		$queryObject = $queryBuilder->appGet($queryObject);

		return true;
	}

	/**
	* Add app-specific properties to submissions
	*
	* @param $hookName string Submission::getBackendListProperties::properties
	* @param $args array [
	* 		@option $props array Existing properties
	* 		@option $submission Submission The associated submission
	* 		@option $args array Request args
	* ]
	*
	* @return array
	*/
	public function modifyBackendListPropertyValues($hookName, $args) {
		$props =& $args[0];

		$props[] = 'series';
		$props[] = 'category';
		$props[] = 'featured';
		$props[] = 'newRelease';
	}

	/**
	 * Add app-specific property values to a submission
	 *
	 * @param $hookName string Submission::getProperties::values
	 * @param $args array [
	 *    @option $values array Key/value store of property values
	 * 		@option $submission Submission The associated submission
	 * 		@option $props array Requested properties
	 * 		@option $args array Request args
	 * ]
	 *
	 * @return array
	 */
	public function modifyPropertyValues($hookName, $args) {
		$values =& $args[0];
		$submission = $args[1];
		$props = $args[2];
		$propertyArgs = $args[3];
		$request = $args[3]['request'];
		$context = $request->getContext();
		$dispatcher = $request->getDispatcher();

		$publishedMonograph = null;
		if ($context) {
			$publishedMonographDao = \DAORegistry::getDAO('PublishedMonographDAO');
			$publishedMonograph = $publishedMonographDao->getByBestId($context->getId(), $submission->getId());
		}

		foreach ($props as $prop) {
			switch ($prop) {
				case 'urlPublished':
					$values[$prop] = $dispatcher->url(
						$request,
						ROUTE_PAGE,
						$context->getPath(),
						'catalog',
						'book',
						$submission->getBestId()
					);
					break;
				case 'series':
					$values[$prop] = array(
						'id' => $submission->getSeriesId(),
						'title' => $submission->getSeriesTitle(),
						'position' => $submission->getSeriesPosition(),
					);
					break;
				case 'category':
					$categoryDao = \DAORegistry::getDAO('CategoryDAO');
					$values[$prop] = $categoryDao->getBySubmissionId($submission->getId());
					break;
				case 'featured':
					$featureDao = \DAORegistry::getDAO('FeatureDAO');
					$values[$prop] = $featureDao->getFeaturedAll($submission->getId());
					break;
				case 'newRelease':
					$newReleaseDao = \DAORegistry::getDAO('NewReleaseDAO');
					$values[$prop] = $newReleaseDao->getNewReleaseAll($submission->getId());
					break;
			}
		}
	}
}
