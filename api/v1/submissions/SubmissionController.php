<?php

/**
 * @file api/v1/submissions/SubmissionController.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionController
 *
 * @ingroup api_v1_submission
 *
 * @brief Handle API requests for submission operations.
 *
 */

namespace APP\API\v1\submissions;

use APP\components\forms\publication\CatalogEntryForm;
use APP\components\forms\publication\PublicationLicenseForm;
use APP\components\forms\submission\AudienceForm;
use APP\components\forms\submission\PublicationDatesForm;
use APP\file\PublicFileManager;
use APP\publication\Publication;
use APP\submission\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use PKP\API\v1\submissions\PKPSubmissionController;
use PKP\context\Context;
use PKP\core\PKPApplication;
use PKP\security\Role;
use PKP\userGroup\UserGroup;

class SubmissionController extends PKPSubmissionController
{
    public function __construct()
    {
        array_push($this->requiresSubmissionAccess, 'getAudienceForm', 'getCatalogEntryForm', 'getPublicationDatesForm');
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        parent::getGroupRoutes();

        Route::middleware([
            self::roleAuthorizer([Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_ASSISTANT]),
        ])->group(function () {
            Route::prefix('{submissionId}/publications/{publicationId}/_components')->group(function () {
                Route::get('audience', $this->getAudienceForm(...))->name('submission.publication._components.audience');
                Route::get('catalogEntry', $this->getCatalogEntryForm(...))->name('submission.publication._components.catalogEntry');
                Route::get('publicationDates', $this->getPublicationDatesForm(...))->name('submission.publication._components.publicationDates');
                Route::get('permissionDisclosure', $this->getPublicationLicenseForm(...))->name('submission.publication._components.permissionDisclosure');
            })->whereNumber(['submissionId', 'publicationId']);
        });
    }

    /**
     * Get AudienceForm form component
     */
    protected function getAudienceForm(Request $illuminateRequest): JsonResponse
    {
        $data = $this->getSubmissionAndPublicationData($illuminateRequest);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], $data['status']);
        }

        $request = $this->getRequest();
        $submission = $data['submission']; /** @var Submission $submission */
        $context = $data['context']; /** @var Context $context */

        $submissionApiUrl = $request->getDispatcher()->url($request, PKPApplication::ROUTE_API, $context->getData('urlPath'), 'submissions/' . $submission->getId());
        $audienceForm = new AudienceForm($submissionApiUrl, $submission);

        return response()->json($audienceForm->getConfig(), Response::HTTP_OK);
    }


    /**
     * Get CatalogEntryForm form component
     */
    protected function getCatalogEntryForm(Request $illuminateRequest): JsonResponse
    {
        $data = $this->getSubmissionAndPublicationData($illuminateRequest);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], $data['status']);
        }

        $request = $this->getRequest();

        $submission = $data['submission']; /** @var Submission $submission */
        $publication = $data['publication']; /** @var Publication $publication */
        $context = $data['context']; /** @var Context $context */
        $locales = $this->getPublicationFormLocales($context, $submission);
        $publicationApiUrl = $data['publicationApiUrl']; /** @var String $publicationApiUrl */
        $temporaryFileApiUrl = $request->getDispatcher()->url($request, PKPApplication::ROUTE_API, $context->getData('urlPath'), 'temporaryFiles');

        $publicFileManager = new PublicFileManager();
        $baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId());

        $catalogEntryForm = new CatalogEntryForm($publicationApiUrl, $locales, $publication, $submission, $baseUrl, $temporaryFileApiUrl);
        $submissionLocale = $submission->getData('locale');

        return response()->json($this->getLocalizedForm($catalogEntryForm, $submissionLocale, $locales), Response::HTTP_OK);
    }

    /**
     * Get PublicationDatesForm form component
     */
    protected function getPublicationDatesForm(Request $illuminateRequest): JsonResponse
    {
        $data = $this->getSubmissionAndPublicationData($illuminateRequest);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], $data['status']);
        }

        $request = $this->getRequest();

        $submission = $data['submission']; /** @var Submission $submission */
        $context = $data['context']; /** @var Context $context */
        $submissionApiUrl = $request->getDispatcher()->url($request, PKPApplication::ROUTE_API, $context->getData('urlPath'), 'submissions/' . $submission->getId());

        $publicationDatesForm = new PublicationDatesForm($submissionApiUrl, $submission);

        return response()->json($publicationDatesForm->getConfig());
    }

    /**
     * Get PublicationLicenseForm form component
     */
    protected function getPublicationLicenseForm(Request $illuminateRequest): JsonResponse
    {
        $data = $this->getSubmissionAndPublicationData($illuminateRequest);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], $data['status']);
        }

        $submission = $data['submission']; /** @var Submission $submission */
        $context = $data['context']; /** @var Context $context */
        $publication = $data['publication']; /** @var Publication $publication */
        $publicationApiUrl = $data['publicationApiUrl']; /** @var String $publicationApiUrl */

        $locales = $this->getPublicationFormLocales($context, $submission);
        $authorUserGroups = UserGroup::withRoleIds([Role::ROLE_ID_AUTHOR])
            ->withContextIds([$submission->getData('contextId')])
            ->get();

        $publicationLicenseForm = new PublicationLicenseForm($publicationApiUrl, $locales, $publication, $context, $authorUserGroups);
        $submissionLocale = $submission->getData('locale');

        return response()->json($this->getLocalizedForm($publicationLicenseForm, $submissionLocale, $locales), Response::HTTP_OK);
    }
}
