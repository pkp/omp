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

use APP\facades\Repo;
use APP\submission\Collector;
use APP\submission\Submission;
use PKP\plugins\HookRegistry;

use PKP\security\Role;

class BackendSubmissionsHandler extends PKPBackendSubmissionsHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        HookRegistry::register('API::_submissions::params', [$this, 'addAppSubmissionsParams']);

        $rootPattern = '/{contextPath}/api/{version}/_submissions';
        $this->_endpoints = [
            'POST' => [
                [
                    'pattern' => "{$rootPattern}/saveDisplayFlags",
                    'handler' => [$this, 'saveDisplayFlags'],
                    'roles' => [
                        Role::ROLE_ID_SITE_ADMIN,
                        Role::ROLE_ID_MANAGER,
                    ],
                ],
                [
                    'pattern' => "{$rootPattern}/saveFeaturedOrder",
                    'handler' => [$this, 'saveFeaturedOrder'],
                    'roles' => [
                        Role::ROLE_ID_SITE_ADMIN,
                        Role::ROLE_ID_MANAGER,
                    ],
                ],
            ],
            'PUT' => [
                [
                    'pattern' => "{$rootPattern}/addToCatalog",
                    'handler' => [$this, 'addToCatalog'],
                    'roles' => [
                        Role::ROLE_ID_SITE_ADMIN,
                        Role::ROLE_ID_MANAGER,
                    ],
                ],
            ],
        ];
        parent::__construct();
    }

    /**
     * Configure a submission Collector based on the query params
     */
    protected function getSubmissionCollector(array $queryParams): Collector
    {
        $collector = parent::getSubmissionCollector($queryParams);

        // Add allowed order by options for OMP
        if (isset($queryParams['orderBy']) && $queryParams['orderBy'] === Collector::ORDERBY_SERIES_POSITION) {
            $direction = isset($queryParams['orderDirection']) && $queryParams['orderDirection'] === $collector::ORDER_DIR_ASC
                ? $collector::ORDER_DIR_ASC
                : $collector::ORDER_DIR_DESC;
            $collector->orderBy(Collector::ORDERBY_SERIES_POSITION, $direction);
        }

        // Add allowed order by option for featured/new releases
        if (!empty($queryParams['orderByFeatured'])) {
            $collector->orderByFeatured();
        }

        if (isset($queryParams['seriesIds'])) {
            $collector->filterBySeriesIds(
                array_map('intval', $this->paramToArray($queryParams['seriesIds']))
            );
        }

        return $collector;
    }

    /**
     * Save changes to a submission's featured or new release flags
     *
     * @param Request $slimRequest Slim request object
     * @param Response $response object
     * @param array $args {
     * 		@option array featured Optional. Featured flags with assoc type, id
     *		  and seq values.
     * 		@option array newRelease Optional. New release flags assoc type, id
     *		  and seq values.
     * }
     *
     * @return Response
     */
    public function saveDisplayFlags($slimRequest, $response, $args)
    {
        $params = $slimRequest->getParsedBody();

        $submissionId = isset($params['submissionId']) ? (int) $params['submissionId'] : null;

        if (empty($submissionId)) {
            return $response->withStatus(400)->withJsonError('api.submissions.400.missingRequired');
        }

        $featureDao = \DAORegistry::getDAO('FeatureDAO');
        $featureDao->deleteByMonographId($submissionId);
        if (!empty($params['featured'])) {
            foreach ($params['featured'] as $feature) {
                $featureDao->insertFeature($submissionId, $feature['assoc_type'], $feature['assoc_id'], $feature['seq']);
            }
        }

        $newReleaseDao = \DAORegistry::getDAO('NewReleaseDAO');
        $newReleaseDao->deleteByMonographId($submissionId);
        if (!empty($params['newRelease'])) {
            foreach ($params['newRelease'] as $newRelease) {
                $newReleaseDao->insertNewRelease($submissionId, $newRelease['assoc_type'], $newRelease['assoc_id']);
            }
        }

        $output = [
            'featured' => $featureDao->getFeaturedAll($submissionId),
            'newRelease' => $newReleaseDao->getNewReleaseAll($submissionId),
        ];

        return $response->withJson($output);
    }

    /**
     * Save changes to the sequence of featured items in the catalog, series or
     * category.
     *
     * @param Request $slimRequest Slim request object
     * @param Response $response object
     * @param array $args {
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
    public function saveFeaturedOrder($slimRequest, $response, $args)
    {
        $params = $slimRequest->getParsedBody();

        $assocType = isset($params['assocType']) && in_array($params['assocType'], [ASSOC_TYPE_PRESS, ASSOC_TYPE_CATEGORY, ASSOC_TYPE_SERIES]) ? (int) $params['assocType'] : null;
        $assocId = isset($params['assocId']) ? (int) $params['assocId'] : null;

        if (empty($assocType) || empty($assocId)) {
            return $response->withStatus(400)->withJsonError('api.submissions.400.missingRequired');
        }

        $featureDao = \DAORegistry::getDAO('FeatureDAO');
        $featureDao->deleteByAssoc($assocType, $assocId);
        if (!empty($params['featured'])) {
            foreach ($params['featured'] as $feature) {
                $featureDao->insertFeature($feature['id'], $assocType, $assocId, $feature['seq']);
            }
        }

        return $response->withJson(true);
    }

    /**
     * Add one or more submissions to the catalog
     *
     * @param Request $slimRequest Slim request object
     * @param Response $response object
     *
     * @return Response
     */
    public function addToCatalog($slimRequest, $response, $args)
    {
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
            $submission = Repo::submission()->get($submissionId);
            if (!$submission) {
                return $response->withStatus(400)->withJsonError('api.submissions.400.submissionsNotFound');
            }
            $publication = $submission->getCurrentPublication();
            if ($publication->getData('status') === Submission::STATUS_PUBLISHED) {
                continue;
            }
            $errors = Repo::publication()->validatePublish($publication, $submission, $allowedLocales, $primaryLocale);
            if (!empty($errors)) {
                return $response->withStatus(400)->withJson($errors);
            }
            $validPublications[] = $publication;
        }

        foreach ($validPublications as $validPublication) {
            Repo::publication()->publish($validPublication);
        }

        return $response->withJson(true);
    }
}
