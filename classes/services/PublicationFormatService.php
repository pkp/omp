<?php
/**
 * @file classes/services/PublicationFormatService.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatService
 *
 * @ingroup services
 *
 * @brief A service class with methods to handle publication formats
 */

namespace APP\services;

use APP\core\Application;
use APP\facades\Repo;
use APP\log\event\SubmissionEventLogEntry;
use APP\press\Press;
use APP\publicationFormat\IdentificationCodeDAO;
use APP\publicationFormat\MarketDAO;
use APP\publicationFormat\PublicationDateDAO;
use APP\publicationFormat\PublicationFormat;
use APP\publicationFormat\SalesRightsDAO;
use APP\submission\Submission;
use PKP\core\Core;
use PKP\core\PKPApplication;
use PKP\db\DAORegistry;

class PublicationFormatService
{
    /**
     * Delete a publication format
     *
     * @param PublicationFormat $publicationFormat
     * @param Submission $submission
     * @param Press $context
     */
    public function deleteFormat($publicationFormat, $submission, $context)
    {
        Application::getRepresentationDAO()->deleteById($publicationFormat->getId());

        // Delete publication format metadata
        $metadataDaos = ['IdentificationCodeDAO', 'MarketDAO', 'PublicationDateDAO', 'SalesRightsDAO'];
        foreach ($metadataDaos as $metadataDao) {
            /** @var IdentificationCodeDAO|MarketDAO|PublicationDateDAO|SalesRightsDAO */
            $dao = DAORegistry::getDAO($metadataDao);
            $result = $dao->getByPublicationFormatId($publicationFormat->getId());
            while (!$result->eof()) {
                $object = $result->next();
                $dao->deleteObject($object);
            }
        }

        $submissionFiles = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$submission->getId()])
            ->filterByAssoc(
                Application::ASSOC_TYPE_REPRESENTATION,
                [$publicationFormat->getId()]
            )
            ->getMany();

        // Delete submission files for this publication format
        foreach ($submissionFiles as $submissionFile) {
            Repo::submissionFile()->delete($submissionFile);
        }

        // Log the deletion of the format.
        $eventLog = Repo::eventLog()->newDataObject([
            'assocType' => PKPApplication::ASSOC_TYPE_SUBMISSION,
            'assocId' => $submission->getId(),
            'eventType' => SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_REMOVE,
            'userId' => Application::get()->getRequest()->getUser()?->getId(),
            'message' => 'submission.event.publicationFormatRemoved',
            'isTranslated' => false,
            'dateLogged' => Core::getCurrentDate(),
            'publicationFormatName' => $publicationFormat->getData('name') // formatName
        ]);
        Repo::eventLog()->add($eventLog);
    }
}
