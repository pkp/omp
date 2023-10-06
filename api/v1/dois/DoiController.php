<?php

/**
 * @file api/v1/dois/DoiController.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DoiController
 *
 * @ingroup api_v1_dois
 *
 * @brief Handle API requests for DOI operations.
 *
 */

namespace APP\API\v1\dois;

use APP\facades\Repo;
use APP\monograph\ChapterDAO;
use APP\publicationFormat\PublicationFormatDAO;
use PKP\db\DAORegistry;

class DoiController extends \PKP\API\v1\dois\PKPDoiController
{
    /**
     * @copydoc PKPDoiHandler::getPubObjectHandler()
     */
    protected function getPubObjectHandler(string $type): mixed
    {
        $pubObjectHandler = match ($type) {
            Repo::doi()::TYPE_CHAPTER => DAORegistry::getDAO('ChapterDAO'),
            Repo::doi()::TYPE_SUBMISSION_FILE => Repo::submissionFile(),
            Repo::doi()::TYPE_REPRESENTATION => DAORegistry::getDAO('PublicationFormatDAO'),
            default => null
        };

        if ($pubObjectHandler !== null) {
            return $pubObjectHandler;
        }

        return parent::getPubObjectHandler($type);
    }

    /**
     * @copydoc PKPDoiHandler::getViaPubObjectHandler()
     */
    protected function getViaPubObjectHandler(mixed $pubObjectHandler, int $pubObjectId): mixed
    {
        if ($pubObjectHandler instanceof ChapterDAO) {
            return $pubObjectHandler->getChapter($pubObjectId);
        } elseif ($pubObjectHandler instanceof PublicationFormatDAO) {
            return $pubObjectHandler->getById($pubObjectId);
        }

        return parent::getViaPubObjectHandler($pubObjectHandler, $pubObjectId);
    }

    /**
     * @copydoc PKPDoiHandler::editViaPubObjectHandler()
     */
    protected function editViaPubObjectHandler(mixed $pubObjectHandler, mixed $pubObject, ?int $doiId): void
    {
        if ($pubObjectHandler instanceof ChapterDAO) {
            $pubObject->setData('doiId', $doiId);
            $pubObjectHandler->updateObject($pubObject);
            return;
        } elseif ($pubObjectHandler instanceof PublicationFormatDAO) {
            $pubObject->setData('doiId', $doiId);
            $pubObjectHandler->updateObject($pubObject);
            return;
        }
        parent::editViaPubObjectHandler($pubObjectHandler, $pubObject, $doiId);
    }
}
