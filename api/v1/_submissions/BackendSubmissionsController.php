<?php

/**
 * @file api/v1/_submissions/BackendSubmissionsController.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BackendSubmissionsController
 *
 * @ingroup api_v1__submission
 *
 * @brief Handle API requests for backend operations.
 *
 */

namespace APP\API\v1\_submissions;

use APP\core\Application;
use APP\facades\Repo;
use APP\press\FeatureDAO;
use APP\press\NewReleaseDAO;
use APP\publication\Publication;
use APP\submission\Collector;
use APP\submission\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use PKP\db\DAORegistry;
use PKP\security\Role;

class BackendSubmissionsController extends \PKP\API\v1\_submissions\PKPBackendSubmissionsController
{
    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        parent::getGroupRoutes();

        Route::middleware([
            self::roleAuthorizer([
                Role::ROLE_ID_SITE_ADMIN,
                Role::ROLE_ID_MANAGER,
            ]),
        ])->group(function () {

            Route::post('saveDisplayFlags', $this->saveDisplayFlags(...))
                ->name('_submission.displayFlag.save');

            Route::post('saveFeaturedOrder', $this->saveFeaturedOrder(...))
                ->name('_submission.featuredOrder.save');

            Route::put('addToCatalog', $this->addToCatalog(...))
                ->name('_submission.catalog.addTo');
        });
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
                array_map(intval(...), paramToArray($queryParams['seriesIds']))
            );
        }

        return $collector;
    }

    /**
     * Save changes to a submission's featured or new release flags
     */
    public function saveDisplayFlags(Request $illuminateRequest): JsonResponse
    {
        $params = $illuminateRequest->input();

        $submissionId = isset($params['submissionId']) ? (int) $params['submissionId'] : null;

        if (empty($submissionId)) {
            return response()->json([
                'error' => __('api.submissions.400.missingRequired'),
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var FeatureDAO */
        $featureDao = DAORegistry::getDAO('FeatureDAO');
        $featureDao->deleteByMonographId($submissionId);
        if (!empty($params['featured'])) {
            foreach ($params['featured'] as $feature) {
                $featureDao->insertFeature($submissionId, $feature['assoc_type'], $feature['assoc_id'], $feature['seq']);
                $featureDao->resequenceByAssoc($feature['assoc_type'], $feature['assoc_id']);
            }
        }
        /** @var NewReleaseDAO */
        $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
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

        return response()->json($output, Response::HTTP_OK);
    }

    /**
     * Save changes to the sequence of featured items in the catalog, series or
     * category.
     */
    public function saveFeaturedOrder(Request $illuminateRequest): JsonResponse
    {
        $params = $illuminateRequest->input();

        $assocType = isset($params['assocType']) && in_array($params['assocType'], [Application::ASSOC_TYPE_PRESS, Application::ASSOC_TYPE_CATEGORY, Application::ASSOC_TYPE_SERIES]) ? (int) $params['assocType'] : null;
        $assocId = isset($params['assocId']) ? (int) $params['assocId'] : null;

        if (empty($assocType) || empty($assocId)) {
            return response()->json([
                'error' => __('api.submissions.400.missingRequired'),
            ], Response::HTTP_BAD_REQUEST);
        }
        /** @var FeatureDAO */
        $featureDao = DAORegistry::getDAO('FeatureDAO');
        if (!empty($params['featured'])) {
            foreach ($params['featured'] as $feature) {
                $featureDao->setSequencePosition($feature['id'], $assocType, $assocId, $feature['seq']);
            }
        }

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Add one or more submissions to the catalog
     */
    public function addToCatalog(Request $illuminateRequest): JsonResponse
    {
        $params = $illuminateRequest->input();

        if (empty($params['submissionIds'])) {
            return response()->json([
                'error' => __('api.submissions.400.submissionIdsRequired'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $submissionIds = array_map(intval(...), (array) $params['submissionIds']);

        if (empty($submissionIds)) {
            return response()->json([
                'error' => __('api.submissions.400.submissionIdsRequired'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $supportedMetadataLocales = $this->getRequest()->getContext()->getSupportedSubmissionMetadataLocales();

        $validPublications = [];
        foreach ($submissionIds as $submissionId) {
            $submission = Repo::submission()->get($submissionId);

            if (!$submission) {
                return response()->json([
                    'error' => __('api.submissions.400.submissionsNotFound'),
                ], Response::HTTP_NOT_FOUND);
            }

            $publication = $submission->getCurrentPublication();

            if ($publication->getData('status') === Publication::STATUS_PUBLISHED) {
                continue;
            }
            $allowedLocales = $submission->getPublicationLanguages($supportedMetadataLocales);
            $errors = Repo::publication()->validatePublish($publication, $submission, $allowedLocales, $submission->getData('locale'));
            if (!empty($errors)) {
                return response()->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $validPublications[] = $publication;
        }

        foreach ($validPublications as $validPublication) {
            Repo::publication()->publish($validPublication);
        }

        return response()->json([], Response::HTTP_OK);
    }
}
