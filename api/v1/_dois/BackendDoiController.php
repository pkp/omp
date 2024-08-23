<?php

/**
 * @file api/v1/_dois/BackendDoiController.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BackendDoiController
 *
 * @ingroup api_v1_backend
 *
 * @brief Handle API requests for backend operations.
 *
 */

namespace APP\API\v1\_dois;

use APP\facades\Repo;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use PKP\db\DAORegistry;
use PKP\submission\GenreDAO;

class BackendDoiController extends \PKP\API\v1\_dois\PKPBackendDoiController
{
    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        parent::getGroupRoutes();

        Route::put('chapters/{chapterId}', $this->editChapter(...))
            ->name('_doi.backend.chapter.edit')
            ->whereNumber('chapterId');

        Route::put('publicationFormats/{publicationFormatId}', $this->editPublicationFormat(...))
            ->name('_doi.backend.publication.format.edit')
            ->whereNumber('publicationFormatId');

        Route::put('submissionFiles/{submissionFileId}', $this->editSubmissionFile(...))
            ->name('_doi.backend.submission.file.edit')
            ->whereNumber('submissionFileId');
    }

    /**
     * Edit chapter DOI
     *
     * @throws Exception
     */
    public function editChapter(Request $illuminateRequest): JsonResponse
    {
        $context = $this->getRequest()->getContext();

        /** @var \APP\monograph\ChapterDAO $chapterDao */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $chapter = $chapterDao->getChapter((int) $illuminateRequest->route('chapterId'));
        if (!$chapter) {
            return response()->json([
                'error' => __('api.404.resourceNotFound'),
            ], Response::HTTP_NOT_FOUND);
        }
        if (!$chapter->isPageEnabled() && empty($chapter->getDoi())) {
            return response()->json([
                'error' => __('api.dois.403.editItemDoiCantBeAssigned'),
            ], Response::HTTP_FORBIDDEN);
        }

        $publication = Repo::publication()->get($chapter->getData('publicationId'));
        $submission = Repo::submission()->get($publication->getData('submissionId'));
        if ($submission->getData('contextId') !== $context->getId()) {
            return response()->json([
                'error' => __('api.dois.403.editItemOutOfContext'),
            ], Response::HTTP_FORBIDDEN);
        }

        $doiId = $illuminateRequest->input()['doiId'];
        $doi = Repo::doi()->get((int) $doiId);
        if (!$doi) {
            return response()->json([
                'error' => __('api.dois.404.doiNotFound'),
            ], Response::HTTP_NOT_FOUND);
        }

        $chapter->setData('doiId', $doi->getId());
        $chapterDao->updateObject($chapter);

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Edit publication format DOI
     *
     * @throws Exception
     */
    public function editPublicationFormat(Request $illuminateRequest): JsonResponse
    {
        $context = $this->getRequest()->getContext();

        /** @var \APP\publicationFormat\PublicationFormatDAO $publicationFormatDao */
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
        $publicationFormat = $publicationFormatDao->getById($illuminateRequest->route('publicationFormatId'));

        $publication = Repo::publication()->get($publicationFormat->getData('publicationId'));
        $submission = Repo::submission()->get($publication->getData('submissionId'));

        if ($submission->getData('contextId') !== $context->getId()) {
            return response()->json([
                'error' => __('api.dois.403.editItemOutOfContext'),
            ], Response::HTTP_FORBIDDEN);
        }

        $doiId = $illuminateRequest->input()['doiId'];
        $doi = Repo::doi()->get((int) $doiId);
        if (!$doi) {
            return response()->json([
                'error' => __('api.dois.404.doiNotFound'),
            ], Response::HTTP_NOT_FOUND);
        }

        $publicationFormat->setData('doiId', $doi->getId());
        $publicationFormatDao->updateObject($publicationFormat);

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Edit submission file DOI
     *
     * @throws Exception
     */
    public function editSubmissionFile(Request $illuminateRequest): JsonResponse
    {
        $context = $this->getRequest()->getContext();

        $submissionFile = Repo::submissionFile()->get($illuminateRequest->route('submissionFileId'));
        if (!$submissionFile) {
            return response()->json([
                'error' => __('api.404.resourceNotFound'),
            ], Response::HTTP_NOT_FOUND);
        }

        $submission = Repo::submission()->get($submissionFile->getData('submissionId'));
        if ($submission->getData('contextId') !== $context->getId()) {
            return response()->json([
                'error' => __('api.dois.403.editItemOutOfContext'),
            ], Response::HTTP_FORBIDDEN);
        }

        $params = $this->convertStringsToSchema(
            \PKP\services\PKPSchemaService::SCHEMA_SUBMISSION_FILE,
            $illuminateRequest->input()
        );

        $doi = Repo::doi()->get((int) $params['doiId']);
        if (!$doi) {
            return response()->json([
                'error' => __('api.dois.404.doiNotFound'),
            ], Response::HTTP_NOT_FOUND);
        }

        Repo::submissionFile()->edit($submissionFile, ['doiId' => $doi->getId()]);
        $submissionFile = Repo::submissionFile()->get($submissionFile->getId());

        /** @var GenreDAO */
        $genreDao = DAORegistry::getDAO('GenreDAO');
        $genres = $genreDao->getByContextId($submission->getData('contextId'))->toArray();

        return response()->json(
            Repo::submissionFile()->getSchemaMap()->map($submissionFile, $genres),
            Response::HTTP_OK
        );
    }
}
