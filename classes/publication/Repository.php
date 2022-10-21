<?php
/**
 * @file classes/publication/Repository.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class publication
 *
 * @brief Get publications and information about publications
 */

namespace APP\publication;

use Illuminate\Support\Facades\App;
use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\monograph\ChapterDAO;
use APP\notification\NotificationManager;
use APP\publicationFormat\PublicationFormatTombstoneManager;
use APP\submission\Submission;
use HookRegistry;
use PKP\core\Core;
use PKP\db\DAORegistry;
use PKP\submission\PKPSubmission;
use PKP\submissionFile\SubmissionFile;
use PKP\publication\Collector;

class Repository extends \PKP\publication\Repository
{
    /** @copydoc \PKP\submission\Repository::$schemaMap */
    public $schemaMap = maps\Schema::class;

    public function getCollector(): Collector
    {
        return App::makeWith(Collector::class, ['dao' => $this->dao]);
    }

    /** @copydoc PKP\publication\Repository::validate() */
    public function validate($publication, array $props, array $allowedLocales, string $primaryLocale): array
    {
        $errors = parent::validate($publication, $props, $allowedLocales, $primaryLocale);

        // Ensure that the specified series exists
        if (isset($props['seriesId'])) {
            $series = Application::get()->getSectionDAO()->getById($props['seriesId']);
            if (!$series) {
                $errors['seriesId'] = [__('publication.invalidSeries')];
            }
        }

        return $errors;
    }

    /** @copydoc \PKP\publication\Repository::version() */
    public function version(Publication $publication): int
    {
        // Get some data about the publication being versioned before any changes are made
        $oldPublicationFormats = $publication->getData('publicationFormats');
        $oldAuthors = Repo::author()->getCollector()
            ->filterByPublicationIds([$publication->getId()])
            ->getMany();

        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
        $oldChaptersIterator = $chapterDao->getByPublicationId($publication->getId());
        $oldPublicationId = $publication->getId();
        $submissionId = $publication->getData('submissionId');

        $newId = parent::version($publication);

        $newPublication = $this->get($newId);

        // Publication Formats (and all associated objects)
        $newSubmissionFiles = [];
        foreach ($oldPublicationFormats as $oldPublicationFormat) {
            $newPublicationFormat = clone $oldPublicationFormat;
            $newPublicationFormat->setData('id', null);
            $newPublicationFormat->setData('publicationId', $newPublication->getId());
            Application::getRepresentationDAO()->insertObject($newPublicationFormat);

            // Duplicate publication format metadata
            $metadataDaos = ['IdentificationCodeDAO', 'MarketDAO', 'PublicationDateDAO', 'SalesRightsDAO'];
            foreach ($metadataDaos as $metadataDao) {
                $result = DAORegistry::getDAO($metadataDao)->getByPublicationFormatId($oldPublicationFormat->getId());
                while (!$result->eof()) {
                    $oldObject = $result->next();
                    $newObject = clone $oldObject;
                    $newObject->setData('id', null);
                    $newObject->setData('publicationFormatId', $newPublicationFormat->getId());
                    DAORegistry::getDAO($metadataDao)->insertObject($newObject);
                }
            }

            $submissionFiles = Repo::submissionFile()
                ->getCollector()
                ->filterBySubmissionIds([$submissionId])
                ->filterByAssoc(
                    Application::ASSOC_TYPE_REPRESENTATION,
                    [$oldPublicationFormat->getId()]
                )
                ->getMany();

            // Duplicate publication format files
            foreach ($submissionFiles as $submissionFile) {
                $newSubmissionFile = clone $submissionFile;
                $newSubmissionFile->setData('id', null);
                $newSubmissionFile->setData('assocId', $newPublicationFormat->getId());
                $newSubmissionFileId = Repo::submissionFile()->add($newSubmissionFile);
                $newSubmissionFile = Repo::submissionFile()->get($newSubmissionFileId);
                $newSubmissionFiles[] = $newSubmissionFile;

                $dependentFiles = Repo::submissionFile()
                    ->getCollector()
                    ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_DEPENDENT])
                    ->filterByAssoc(
                        Application::ASSOC_TYPE_SUBMISSION_FILE,
                        [$submissionFile->getId()]
                    )
                    ->includeDependentFiles()
                    ->getMany();

                foreach ($dependentFiles as $dependentFile) {
                    $newDependentFile = clone $dependentFile;
                    $newDependentFile->setData('id', null);
                    $newDependentFile->setData('assocId', $newSubmissionFile->getId());
                    Repo::submissionFile()->add($newDependentFile);
                }
            }
        }

        // Chapters (and all associated objects)
        $newAuthors = Repo::author()->getCollector()
            ->filterByPublicationIds([$newPublication->getId()])
            ->getMany();

        while ($oldChapter = $oldChaptersIterator->next()) {
            $newChapter = clone $oldChapter;
            $newChapter->setData('id', null);
            $newChapter->setData('publicationId', $newPublication->getId());
            $newChapterId = $chapterDao->insertChapter($newChapter);
            $newChapter = $chapterDao->getChapter($newChapterId);

            // Update file chapter associations for new files
            foreach ($newSubmissionFiles as $newSubmissionFile) {
                if ($newSubmissionFile->getChapterId() == $oldChapter->getId()) {
                    Repo::submissionFile()
                        ->edit(
                            $newSubmissionFile,
                            ['chapterId' => $newChapter->getId()]
                        );
                }
            }

            // We need to link new authors to chapters. To do this, we need a way to
            // link old authors to the new ones. We use seq property, which should be
            // unique for each author to determine which new author is a copy of the
            // old one. We then map the old chapter author associations to the new
            // authors.
            $oldChapterAuthors = Repo::author()->getCollector()
                ->filterByChapterIds([$oldChapter->getId()])
                ->filterByPublicationIds([$oldPublicationId])
                ->getMany();

            foreach ($newAuthors as $newAuthor) {
                foreach ($oldAuthors as $oldAuthor) {
                    if ($newAuthor->getData('seq') === $oldAuthor->getData('seq')) {
                        foreach ($oldChapterAuthors as $oldChapterAuthor) {
                            if ($oldChapterAuthor->getId() === $oldAuthor->getId()) {
                                Repo::author()->addToChapter(
                                    $newAuthor->getId(),
                                    $newChapter->getId(),
                                    $newAuthor->getId() === $newPublication->getData('primaryContactId'),
                                    $oldChapterAuthor->getData('seq')
                                );
                            }
                        }
                    }
                }
            }
        }

        return $newId;
    }

    /** @copydoc \PKP\publication\Repository::publish() */
    public function publish(Publication $publication)
    {
        HookRegistry::register('Publication::publish::before', [$this, 'addChapterLicense']);
        parent::publish($publication);

        $submission = Repo::submission()->get($publication->getData('submissionId'));

        // If this is a new current publication (the "version of record"), then
        // tombstones must be updated to reflect the new publication format entries
        // in the OAI feed
        if ($submission->getData('currentPublicationId') === $publication->getId()) {
            $context = $this->request->getContext();
            if (!$context || $context->getId() !== $submission->getData('contextId')) {
                $context = Services::get('context')->get($submission->getData('contextId'));
            }

            // Remove publication format tombstones for this publication
            $publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
            $publicationFormatTombstoneMgr->deleteTombstonesByPublicationId($publication->getId());

            // Create publication format tombstones for any other published versions
            foreach ($submission->getData('publications') as $iPublication) {
                if ($iPublication->getId() !== $publication->getId() && $iPublication->getData('status') === Submission::STATUS_PUBLISHED) {
                    $publicationFormatTombstoneMgr->insertTombstonesByPublicationId($iPublication->getId(), $context);
                }
            }
        }

        // Update notification
        $notificationMgr = new NotificationManager();
        $notificationMgr->updateNotification(
            $this->request,
            [NOTIFICATION_TYPE_APPROVE_SUBMISSION],
            null,
            ASSOC_TYPE_MONOGRAPH,
            $publication->getData('submissionId')
        );
    }

    /** @copydoc \PKP\publication\Repository::setStatusOnPublish() */
    protected function setStatusOnPublish(Publication $publication)
    {
        // If the publish date is in the future, set the status to scheduled
        $datePublished = $publication->getData('datePublished');
        if ($datePublished && strtotime($datePublished) > strtotime(Core::getCurrentDate())) {
            $publication->setData('status', Submission::STATUS_SCHEDULED);
        } else {
            $publication->setData('status', Submission::STATUS_PUBLISHED);
        }

        // If there is no publish date, set it
        if (!$publication->getData('datePublished')) {
            $publication->setData('datePublished', Core::getCurrentDate());
        }
    }

    /** @copydoc \PKP\publication\Repository::unpublish() */
    public function unpublish(Publication $publication)
    {
        parent::unpublish($publication);

        $submission = Repo::submission()->get($publication->getData('submissionId'));
        $submissionContext = Services::get('context')->get($submission->getData('contextId'));

        // Create tombstones for this publication
        $publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
        $publicationFormatTombstoneMgr->insertTombstonesByPublicationId($publication->getId(), $submissionContext);

        // Delete tombstones for the new current publication
        $currentPublication = null;
        foreach ($submission->getData('publications') as $publication) {
            if ($publication->getId() === $submission->getData('currentPublicationId')) {
                $currentPublication = $publication;
                break;
            }
        }
        if ($currentPublication && $currentPublication->getData('status') === Submission::STATUS_PUBLISHED) {
            $publicationFormatTombstoneMgr->deleteTombstonesByPublicationId($currentPublication->getId());
        }


        // Update notification
        $notificationMgr = new NotificationManager();
        $notificationMgr->updateNotification(
            $this->request,
            [NOTIFICATION_TYPE_APPROVE_SUBMISSION],
            null,
            ASSOC_TYPE_MONOGRAPH,
            $publication->getData('submissionId')
        );
    }

    /** @copydoc \PKP\publication\Repository::delete() */
    public function delete(Publication $publication)
    {
        $submission = Repo::submission()->get($publication->getData('submissionId'));
        $context = Services::get('context')->get($submission->getData('contextId'));

        // Delete Publication Formats (and all related objects)
        $publicationFormats = $publication->getData('publicationFormats');
        foreach ($publicationFormats as $publicationFormat) {
            Services::get('publicationFormat')->deleteFormat($publicationFormat, $submission, $context);
        }

        // Delete chapters and assigned chapter authors.
        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
        $chapters = $chapterDao->getByPublicationId($publication->getId());
        while ($chapter = $chapters->next()) {
            // also removes Chapter Author and file associations
            $chapterDao->deleteObject($chapter);
        }

        parent::delete($publication);
    }

    /**
     * Create all DOIs associated with the publication
     *
     * @return mixed
     */
    protected function createDois(Publication $newPublication): void
    {
        $submission = Repo::submission()->get($newPublication->getData('submissionId'));
        Repo::submission()->createDois($submission);
    }

    public function addChapterLicense(string $hookName, array $params): bool
    {
        $newPublication = $params[0];
        $publication = $params[1];
        $itsPublished = ($newPublication->getData('status') === PKPSubmission::STATUS_PUBLISHED);
        $submission = Repo::submission()->get($publication->getData('submissionId'));

        if ($itsPublished && $submission->getData('workType') === Submission::WORK_TYPE_EDITED_VOLUME) {
            if (!$newPublication->getData('chapterLicenseUrl')) {
                $newPublication->setData('chapterLicenseUrl', $newPublication->getData('licenseUrl'));
            }

            $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
            $chaptersIterator = $chapterDao->getByPublicationId($newPublication->getId());

            while ($chapter = $chaptersIterator->next()) {
                if (!$chapter->getLicenseUrl()) {
                    $chapter->setLicenseUrl($newPublication->getData('chapterLicenseUrl'));
                    $chapterDao->updateLocaleFields($chapter);
                }
            }
        }
        return false;
    }
}
