<?php
/**
 * @file classes/publication/Repository.inc.php
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

use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\notification\NotificationManager;
use APP\submission\Submission;
use PKP\core\Core;
use PKP\db\DAORegistry;
use PKP\submissionFile\SubmissionFile;

class Repository extends \PKP\publication\Repository
{
    /** @copydoc \PKP\submission\Repository::$schemaMap */
    public $schemaMap = maps\Schema::class;

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

    /** @copydoc \PKP\publication\Repository::add() */
    public function add(Publication $publication): int
    {
        $id = parent::add($publication);

        $publication = $this->get($id);

        // Create a thumbnail for the cover image
        if ($publication->getData('coverImage')) {
            $submission = Repo::submission()->get($publication->getData('submissionId'));
            $submissionContext = $this->request->getContext();
            if ($submissionContext->getId() !== $submission->getData('contextId')) {
                $submissionContext = Services::get('context')->get($submission->getData('contextId'));
            }

            $supportedLocales = $submissionContext->getSupportedSubmissionLocales();
            foreach ($supportedLocales as $localeKey) {
                if (!array_key_exists($localeKey, $publication->getData('coverImage'))) {
                    continue;
                }

                $publicFileManager = new PublicFileManager();
                $coverImage = $publication->getData('coverImage', $localeKey);
                $coverImageFilePath = $publicFileManager->getContextFilesPath($submissionContext->getId()) . '/' . $coverImage['uploadName'];
                $this->makeThumbnail(
                    $coverImageFilePath,
                    $this->getThumbnailFileName($coverImage['uploadName']),
                    $submissionContext->getData('coverThumbnailsMaxWidth'),
                    $submissionContext->getData('coverThumbnailsMaxHeight')
                );
            }
        }

        return $id;
    }

    /** @copydoc \PKP\publication\Repository::version() */
    public function version(Publication $publication): int
    {
        // Get some data about the publication being versioned before any changes are made
        $oldPublicationFormats = $publication->getData('publicationFormats');
        $oldAuthors = Repo::author()->getMany(
            Repo::author()
                ->getCollector()
                ->filterByPublicationIds([$publication->getId()])
        );
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

            $collector = Repo::submissionFiles()
                ->getCollector()
                ->filterBySubmissionIds([$submissionId])
                ->filterByAssoc(
                    Application::ASSOC_TYPE_REPRESENTATION,
                    [$oldPublicationFormat->getId()]
                );

            // Duplicate publication format files
            $submissionFiles = Repo::submissionFiles()->getMany($collector);
            foreach ($submissionFiles as $submissionFile) {
                $newSubmissionFile = clone $submissionFile;
                $newSubmissionFile->setData('id', null);
                $newSubmissionFile->setData('assocId', $newPublicationFormat->getId());
                $newSubmissionFileId = Repo::submissionFiles()->add($newSubmissionFile);
                $newSubmissionFile = Repo::submissionFiles()->get($newSubmissionFileId);
                $newSubmissionFiles[] = $newSubmissionFile;

                $collector = Repo::submissionFiles()
                    ->getCollector()
                    ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_DEPENDENT])
                    ->filterByAssoc(
                        Application::ASSOC_TYPE_SUBMISSION_FILE,
                        [$submissionFile->getId()]
                    )
                    ->includeDependentFiles();

                $dependentFiles = Repo::submissionFiles()->getMany($collector);
                foreach ($dependentFiles as $dependentFile) {
                    $newDependentFile = clone $dependentFile;
                    $newDependentFile->setData('id', null);
                    $newDependentFile->setData('assocId', $newSubmissionFile->getId());
                    Repo::submissionFiles()->add($newDependentFile);
                }
            }
        }

        // Chapters (and all associated objects)
        $newAuthors = Repo::author()->getMany(
            Repo::author()
                ->getCollector()
                ->filterByPublicationIds([$newPublication->getId()])
        );
        while ($oldChapter = $oldChaptersIterator->next()) {
            $newChapter = clone $oldChapter;
            $newChapter->setData('id', null);
            $newChapter->setData('publicationId', $newPublication->getId());
            $newChapterId = $chapterDao->insertChapter($newChapter);
            $newChapter = $chapterDao->getChapter($newChapterId);

            // Update file chapter associations for new files
            foreach ($newSubmissionFiles as $newSubmissionFile) {
                if ($newSubmissionFile->getChapterId() == $oldChapter->getId()) {
                    Repo::submissionFiles()
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
            $oldChapterAuthors = Repo::author()->getMany(
                Repo::author()
                    ->getCollector()
                    ->filterByChapterIds([$oldChapter->getId()])
                    ->filterByPublicationIds([$oldPublicationId])
            );

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

    /** @copydoc \PKP\publication\Repository::edit() */
    public function edit(Publication $publication, array $params)
    {
        $oldCoverImage = $publication->getData('coverImage');

        parent::edit($publication, $params);

        // Create or delete the thumbnail of a cover image
        if (array_key_exists('coverImage', $params)) {
            $publicFileManager = new PublicFileManager();
            $submission = Repo::submission()->get($publication->getData('submissionId'));

            // Get the submission context
            $submissionContext = $this->request->getContext();
            if ($submissionContext->getId() !== $submission->getData('contextId')) {
                $submissionContext = Services::get('context')->get($submission->getData('contextId'));
            }

            foreach ($params['coverImage'] as $localeKey => $newCoverImage) {

                // Delete the thumbnail if the cover image has been deleted
                if (is_null($newCoverImage)) {
                    if (empty($oldCoverImage[$localeKey])) {
                        continue;
                    }

                    $coverImageFilePath = $publicFileManager->getContextFilesPath($submission->getData('contextId')) . '/' . $oldCoverImage[$localeKey]['uploadName'];
                    if (!file_exists($coverImageFilePath)) {
                        $publicFileManager->removeContextFile($submission->getData('contextId'), $this->getThumbnailFileName($oldCoverImage[$localeKey]['uploadName']));
                    }

                    // Otherwise generate a new thumbnail if a cover image exists
                } elseif (!empty($newCoverImage)) {
                    $coverImageFilePath = $publicFileManager->getContextFilesPath($submission->getData('contextId')) . '/' . $newCoverImage['uploadName'];
                    $this->makeThumbnail(
                        $coverImageFilePath,
                        $this->getThumbnailFileName($newCoverImage['uploadName']),
                        $submissionContext->getData('coverThumbnailsMaxWidth'),
                        $submissionContext->getData('coverThumbnailsMaxHeight')
                    );
                }
            }
        }
    }

    /** @copydoc \PKP\publication\Repository::publish() */
    public function publish(Publication $publication)
    {
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
            import('classes.publicationFormat.PublicationFormatTombstoneManager');
            $publicationFormatTombstoneMgr = new \PublicationFormatTombstoneManager();
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
        import('classes.publicationFormat.PublicationFormatTombstoneManager');
        $publicationFormatTombstoneMgr = new \PublicationFormatTombstoneManager();
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
     * Derive a thumbnail filename from the cover image filename
     *
     * book_1_1_cover.png --> book_1_1_cover_t.png
     *
     * @param string $fileName
     *
     * @return string The thumbnail filename
     */
    public function getThumbnailFileName($fileName)
    {
        $pathInfo = pathinfo($fileName);
        return $pathInfo['filename'] . '_t.' . $pathInfo['extension'];
    }

    /**
     * Generate a thumbnail of an image
     *
     * @param string $filePath The full path and name of the file
     * @param int $maxWidth The maximum allowed width of the thumbnail
     * @param int $maxHeight The maximum allowed height of the thumbnail
     */
    public function makeThumbnail($filePath, $thumbFileName, $maxWidth, $maxHeight)
    {
        $pathParts = pathinfo($filePath);
        $thumbFilePath = $pathParts['dirname'] . '/' . $thumbFileName;

        $cover = null;
        switch ($pathParts['extension']) {
            case 'jpg': $cover = imagecreatefromjpeg($filePath); break;
            case 'png': $cover = imagecreatefrompng($filePath); break;
            case 'gif': $cover = imagecreatefromgif($filePath); break;
            case 'webp': $cover = imagecreatefromwebp($filePath); break;
            case 'svg': $cover = copy($filePath, $thumbFilePath); break;
        }
        if (!isset($cover)) {
            throw new \Exception('Can not build thumbnail because the file was not found or the file extension was not recognized.');
        }

        if ($pathParts['extension'] != 'svg') {

            // Calculate the scaling ratio for each dimension.
            $originalSizeArray = getimagesize($filePath);
            $xRatio = min(1, $maxWidth / $originalSizeArray[0]);
            $yRatio = min(1, $maxHeight / $originalSizeArray[1]);

            // Choose the smallest ratio and create the target.
            $ratio = min($xRatio, $yRatio);

            $thumbWidth = round($ratio * $originalSizeArray[0]);
            $thumbHeight = round($ratio * $originalSizeArray[1]);
            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagecopyresampled($thumb, $cover, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalSizeArray[0], $originalSizeArray[1]);

            switch ($pathParts['extension']) {
                case 'jpg': imagejpeg($thumb, $pathParts['dirname'] . '/' . $thumbFileName); break;
                case 'png': imagepng($thumb, $pathParts['dirname'] . '/' . $thumbFileName); break;
                case 'gif': imagegif($thumb, $pathParts['dirname'] . '/' . $thumbFileName); break;
                case 'webp': imagewebp($thumb, $thumbFilePath); break;
            }

            imagedestroy($thumb);
        }
    }
}
