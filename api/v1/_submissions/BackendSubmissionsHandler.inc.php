<?php

/**
 * @file api/v1/_submissions/BackendSubmissionsHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BackendSubmissionsHandler
 * @ingroup api_v1_backend
 *
 * @brief Handle API requests for backend operations.
 *
 */

import('lib.pkp.api.v1._submissions.PKPBackendSubmissionsHandler');

class BackendSubmissionsHandler extends PKPBackendSubmissionsHandler {

	/**
	 * Constructor
	 */
	public function __construct() {

		\HookRegistry::register('API::_submissions::params', array($this, 'addAppSubmissionsParams'));

		$rootPattern = '/{contextPath}/api/{version}/_submissions';
		$this->_endpoints = array(
			'POST' => array(
				array(
					'pattern' => "{$rootPattern}/saveDisplayFlags",
					'handler' => array($this, 'saveDisplayFlags'),
					'roles' => array(
						ROLE_ID_SITE_ADMIN,
						ROLE_ID_MANAGER,
					),
				),
				array(
					'pattern' => "{$rootPattern}/saveFeaturedOrder",
					'handler' => array($this, 'saveFeaturedOrder'),
					'roles' => array(
						ROLE_ID_SITE_ADMIN,
						ROLE_ID_MANAGER,
					),
				),
			),
			'PUT' => [
				[
					'pattern' => "{$rootPattern}/addToCatalog",
					'handler' => [$this, 'addToCatalog'],
					'roles' => [
						ROLE_ID_SITE_ADMIN,
						ROLE_ID_MANAGER,
					],
				],
			],
		);
		parent::__construct();
	}

	/**
	 * Add omp-specific parameters to the getMany request
	 *
	 * @param $hookName string
	 * @param $args array [
	 * 		@option $params array
	 * 		@option $slimRequest Request Slim request object
	 * 		@option $response Response object
	 * ]
	 */
	public function addAppSubmissionsParams($hookName, $args) {
		$params =& $args[0];
		$slimRequest = $args[1];
		$response = $args[2];

		$originalParams = $slimRequest->getQueryParams();

		// Bring in orderby constants
		import('lib.pkp.classes.submission.PKPSubmissionDAO');
		import('classes.submission.SubmissionDAO');

		// Add allowed order by options for OMP
		if (isset($originalParams['orderBy']) && in_array($originalParams['orderBy'], array(ORDERBY_DATE_PUBLISHED, ORDERBY_SERIES_POSITION))) {
			$params['orderBy'] = $originalParams['orderBy'];
		}

		// Add allowed order by option for featured/new releases
		if (isset($originalParams['orderByFeatured'])) {
			$params['orderByFeatured'] = true;
		}

		if (!empty($originalParams['categoryIds'])) {
			if (is_array($originalParams['categoryIds'])) {
				$params['categoryIds'] = array_map('intval', $originalParams['categoryIds']);
			} else {
				$params['categoryIds'] = array((int) $originalParams['categoryIds']);
			}
		}

		if (!empty($originalParams['seriesIds'])) {
			if (is_array($originalParams['seriesIds'])) {
				$params['seriesIds'] = array_map('intval', $originalParams['seriesIds']);
			} else {
				$params['seriesIds'] = array((int) $originalParams['seriesIds']);
			}
		}
	}

	/**
	 * Save changes to a submission's featured or new release flags
	 *
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param $args array {
	 * 		@option array featured Optional. Featured flags with assoc type, id
	 *		  and seq values.
	 * 		@option array newRelease Optional. New release flags assoc type, id
	 *		  and seq values.
	 * }
	 *
	 * @return Response
	 */
	public function saveDisplayFlags($slimRequest, $response, $args) {
		$params = $slimRequest->getParsedBody();

		$submissionId = isset($params['submissionId']) ?  (int) $params['submissionId'] : null;

		if (empty($submissionId)) {
			return $response->withStatus(400)->withJsonError('api.submissions.400.missingRequired');
		}

		$featureDao = \DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteByMonographId($submissionId);
		if (!empty($params['featured'])) {
			foreach($params['featured'] as $feature) {
				$featureDao->insertFeature($submissionId, $feature['assoc_type'], $feature['assoc_id'], $feature['seq']);
				$featureDao->resequenceByAssoc($feature['assoc_type'], $feature['assoc_id']);
			}
		}

		$newReleaseDao = \DAORegistry::getDAO('NewReleaseDAO');
		$newReleaseDao->deleteByMonographId($submissionId);
		if (!empty($params['newRelease'])) {
			foreach($params['newRelease'] as $newRelease) {
				$newReleaseDao->insertNewRelease($submissionId, $newRelease['assoc_type'], $newRelease['assoc_id']);
			}
		}

		$output = array(
			'featured' => $featureDao->getFeaturedAll($submissionId),
			'newRelease' => $newReleaseDao->getNewReleaseAll($submissionId),
		);

		return $response->withJson($output);
	}

	/**
	 * Save changes to the sequence of featured items in the catalog, series or
	 * category.
	 *
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param $args array {
	 * 		@option int assocType Whether these featured items are for a
	 *			press, category or series. Values: ASSOC_TYPE_*
	 * 		@option int assocId The press, category or series id
	 *		@option array featured List of assoc arrays with submission ids and
	 *			seq value.
	 *		@option bool append Whether to replace or append the features to
	 *			the existing features for this assoc type and id. Default: false
	 * }
	 *
	 * @return Response
	 */
	public function saveFeaturedOrder($slimRequest, $response, $args) {
		$params = $slimRequest->getParsedBody();

		$assocType = isset($params['assocType']) && in_array($params['assocType'], array(ASSOC_TYPE_PRESS, ASSOC_TYPE_CATEGORY, ASSOC_TYPE_SERIES)) ?  (int) $params['assocType'] : null;
		$assocId = isset($params['assocId']) ?  (int) $params['assocId'] : null;

		if (empty($assocType) || empty($assocId)) {
			return $response->withStatus(400)->withJsonError('api.submissions.400.missingRequired');
		}

		$featureDao = \DAORegistry::getDAO('FeatureDAO');
		if (!empty($params['featured'])) {
			foreach($params['featured'] as $feature) {
				$featureDao->setSequencePosition($feature['id'], $assocType, $assocId, $feature['seq']);
			}
		}

		return $response->withJson(true);
	}

	/**
	 * Add one or more submissions to the catalog
	 *
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 *
	 * @return Response
	 */
	public function addToCatalog($slimRequest, $response, $args) {
		$params = $slimRequest->getParsedBody();

		if (empty($params['submissionIds'])) {
			return $response->withStatus(400)->withJsonError('api.submissions.400.submissionIdsRequired');
		}

		$submissionIds = array_map('intval', (array) $params['submissionIds']);

		if (empty($submissionIds)) {
			return $response->withStatus(400)->withJsonError('api.submissions.400.submissionIdsRequired');
		}


		$primaryLocale = $this->getRequest()->getContext()->getPrimaryLocale();
		$allowedLocales = $this->getRequest()->getContext()->getSupportedFormLocales();

		$validPublications = [];
		foreach ($submissionIds as $submissionId) {
			$submission = Services::get('submission')->get($submissionId);
			if (!$submission) {
				return $response->withStatus(400)->withJsonError('api.submissions.400.submissionsNotFound');
			}
			$publication = $submission->getCurrentPublication();
			if ($publication->getData('status') === STATUS_PUBLISHED) {
				continue;
			}
			$errors = Services::get('publication')->validatePublish($publication, $submission, $allowedLocales, $primaryLocale);
			if (!empty($errors)) {
				return $response->withStatus(400)->withJson($errors);
			}
			$validPublications[] = $publication;
		}

		foreach($validPublications as $validPublication) {
			Services::get('publication')->publish($validPublication);
		}

		return $response->withJson(true);
	}
}
