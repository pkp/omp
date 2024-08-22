<?php

/**
 * @file api/v1/_dois/BackendDoiHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BackendDoiHandler
 *
 * @ingroup api_v1_backend
 *
 * @brief Handle API requests for backend operations.
 *
 */

namespace APP\API\v1\_dois;

use APP\facades\Repo;
use Exception;
use PKP\core\APIResponse;
use PKP\db\DAORegistry;
use PKP\security\Role;
use PKP\submission\GenreDAO;
use Slim\Http\Request as SlimRequest;

class BackendDoiHandler extends \PKP\API\v1\_dois\PKPBackendDoiHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_handlerPath = '_dois';
        $this->_endpoints = array_merge_recursive($this->_endpoints, [
            'PUT' => [
                [
                    'pattern' => $this->getEndpointPattern() . "/chapters/{chapterId:\d+}",
                    'handler' => [$this, 'editChapter'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
                [
                    'pattern' => $this->getEndpointPattern() . "/publicationFormats/{publicationFormatId:\d+}",
                    'handler' => [$this, 'editPublicationFormat'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
                [
                    'pattern' => $this->getEndpointPattern() . "/submissionFiles/{submissionFileId:\d+}",
                    'handler' => [$this, 'editSubmissionFile'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ]
            ]
        ]);
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function editChapter(SlimRequest $slimRequest, APIResponse $response, array $args): \Slim\Http\Response
    {
        $context = $this->getRequest()->getContext();

        /** @var \APP\monograph\ChapterDAO $chapterDao */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        $chapter = $chapterDao->getChapter($args['chapterId']);
        if (!$chapter) {
            return $response->withStatus(404)->withJsonError('api.404.resourceNotFound');
        }
        if (!$chapter->isPageEnabled() && empty($chapter->getDoi())) {
            return $response->withStatus(403)->withJsonError('api.dois.403.editItemDoiCantBeAssigned');
        }

        $publication = Repo::publication()->get($chapter->getData('publicationId'));
        $submission = Repo::submission()->get($publication->getData('submissionId'));
        if ($submission->getData('contextId') !== $context->getId()) {
            return $response->withStatus(403)->withJsonError('api.dois.403.editItemOutOfContext');
        }

        $doiId = $slimRequest->getParsedBody()['doiId'];
        $doi = Repo::doi()->get((int) $doiId);
        if (!$doi) {
            return $response->withStatus(404)->withJsonError('api.dois.404.doiNotFound');
        }

        $chapter->setData('doiId', $doi->getId());
        $chapterDao->updateObject($chapter);
        return $response->withStatus(200);
    }

    /**
     * @throws Exception
     */
    public function editPublicationFormat(SlimRequest $slimRequest, APIResponse $response, array $args): \Slim\Http\Response
    {
        $context = $this->getRequest()->getContext();

        /** @var \APP\publicationFormat\PublicationFormatDAO $publicationFormatDao */
        $publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
        $publicationFormat = $publicationFormatDao->getById($args['publicationFormatId']);

        $publication = Repo::publication()->get($publicationFormat->getData('publicationId'));
        $submission = Repo::submission()->get($publication->getData('submissionId'));

        if ($submission->getData('contextId') !== $context->getId()) {
            return $response->withStatus(403)->withJsonError('api.dois.403.editItemOutOfContext');
        }

        $doiId = $slimRequest->getParsedBody()['doiId'];
        $doi = Repo::doi()->get((int) $doiId);
        if (!$doi) {
            return $response->withStatus(404)->withJsonError('api.dois.404.doiNotFound');
        }

        $publicationFormat->setData('doiId', $doi->getId());
        $publicationFormatDao->updateObject($publicationFormat);
        return $response->withStatus(200);
    }

    /**
     * @throws Exception
     */
    public function editSubmissionFile(SlimRequest $slimRequest, APIResponse $response, array $args): \Slim\Http\Response
    {
        $context = $this->getRequest()->getContext();

        $submissionFile = Repo::submissionFile()->get($args['submissionFileId']);
        if (!$submissionFile) {
            return $response->withStatus(404)->withJsonError('api.404.resourceNotFound');
        }

        $submission = Repo::submission()->get($submissionFile->getData('submissionId'));
        if ($submission->getData('contextId') !== $context->getId()) {
            return $response->withStatus(403)->withJsonError('api.dois.403.editItemOutOfContext');
        }

        $params = $this->convertStringsToSchema(\PKP\services\PKPSchemaService::SCHEMA_SUBMISSION_FILE, $slimRequest->getParsedBody());

        $doi = Repo::doi()->get((int) $params['doiId']);
        if (!$doi) {
            return $response->withStatus(404)->withJsonError('api.dois.404.doiNotFound');
        }

        Repo::submissionFile()->edit($submissionFile, ['doiId' => $doi->getId()]);
        $submissionFile = Repo::submissionFile()->get($submissionFile->getId());

        /** @var GenreDAO */
        $genreDao = DAORegistry::getDAO('GenreDAO');
        $genres = $genreDao->getByContextId($submission->getData('contextId'))->toArray();

        return $response->withJson(Repo::submissionFile()->getSchemaMap()->map($submissionFile, $genres));
    }
}
