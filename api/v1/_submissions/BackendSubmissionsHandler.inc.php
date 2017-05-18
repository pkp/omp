<?php

/**
 * @file api/v1/_submissions/BackendSubmissionsHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
			),
		);
		parent::__construct();
	}

	/**
	 * Add omp-specific parameters to the getSubmissions request
	 *
	 * @param $params array
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 *
	 * @return array
	 */
	public function addAppSubmissionsParams($params, $slimRequest, $response) {

		$originalParams = $slimRequest->getQueryParams();

		// Add allowed order by option for featured/new releases
		if (isset($originalParams['orderBy']) && $originalParams['orderBy'] === 'isFeatured') {
			$params['orderBy'] = 'isFeatured';
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

		return $params;
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

		if (!\Application::getRequest()->checkCSRF()) {
			return $response->withStatus(403)->withJsonError('api.submissions.403.csrfTokenFailure');
		}

		$submissionId = isset($params['submissionId']) ?  (int) $params['submissionId'] : null;

		if (empty($submissionId)) {
			return $response->withStatus(400)->withJsonError('api.submissions.400.missingRequired');
		}

		$featureDao = \DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteByMonographId($submissionId);
		if (!empty($params['featured'])) {
			foreach($params['featured'] as $feature) {
				$featureDao->insertFeature($submissionId, $feature['assoc_type'], $feature['assoc_id'], $feature['seq']);
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
}
