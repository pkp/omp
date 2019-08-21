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
		\HookRegistry::register('Submission::getMany::queryBuilder', array($this, 'modifySubmissionQueryBuilder'));
		\HookRegistry::register('Submission::getMany::queryObject', array($this, 'modifySubmissionQueryObject'));
		\HookRegistry::register('Submission::getBackendListProperties::properties', array($this, 'modifyBackendListPropertyValues'));
		\HookRegistry::register('Submission::getProperties::values', array($this, 'modifyPropertyValues'));
	}

	/**
	 * Run app-specific query builder methods for getSubmissionList
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option QueryBuilders\SubmissionQueryBuilder $submissionQB
	 *		@option int $contextId
	 *		@option array $args
	 * ]
	 *
	 * @return QueryBuilders\SubmissionQueryBuilder
	 */
	public function modifySubmissionQueryBuilder($hookName, $args) {
		$submissionQB =& $args[0];
		$requestArgs = $args[1];

		if (!empty($requestArgs['categoryIds'])) {
			$submissionQB->filterByCategories($requestArgs['categoryIds']);
		}

		if (!empty($requestArgs['seriesIds'])) {
			$submissionQB->filterBySeries($requestArgs['seriesIds']);
		}

		if (!empty($requestArgs['orderByFeatured'])) {
			$submissionQB->orderByFeatured();
		}

		return $submissionQB;
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
	public function modifySubmissionQueryObject($hookName, $args) {
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
