<?php
/**
 * @file classes/publication/maps/Schema.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Schema
 *
 * @brief Map publications to the properties defined in the publication schema
 */

namespace APP\publication\maps;

use APP\core\Application;
use APP\facades\Repo;
use APP\publication\Publication;
use PKP\db\DAORegistry;
use PKP\services\PKPSchemaService;
use PKP\submission\GenreDAO;
use PKP\submissionFile\SubmissionFile;

class Schema extends \PKP\publication\maps\Schema
{
    /** @copydoc \PKP\publication\maps\Schema::mapByProperties() */
    protected function mapByProperties(array $props, Publication $publication, bool $anonymize): array
    {
        $output = parent::mapByProperties($props, $publication, $anonymize);

        if (in_array('chapters', $props)) {
            $output['chapters'] = array_map(function ($chapter) use ($anonymize, $publication) {
                $data = $chapter->_data;
                if ($anonymize) {
                    $data['authors'] = [];
                } else {
                    $data['authors'] = Repo::author()
                        ->getCollector()
                        ->filterByChapterIds([$chapter->getId()])
                        ->filterByPublicationIds([$publication->getId()])
                        ->getMany()
                        ->map(function ($chapterAuthor) {
                            return $chapterAuthor->_data;
                        });
                }
                if ($data['doiId'] !== null) {
                    $data['doiObject'] = Repo::doi()->getSchemaMap()->summarize($data['doiObject']);
                }
                return $data;
            }, $publication->getData('chapters'));
        }

        if (in_array('publicationFormats', $props)) {
            // Get all submission files assigned to a publication format
            $submissionFiles = Repo::submissionFile()
                ->getCollector()
                ->filterBySubmissionIds([$publication->getData('submissionId')])
                ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_PROOF])
                ->getMany();

            /** @var GenreDAO $genreDao */
            $genreDao = DAORegistry::getDAO('GenreDAO');
            $genres = $genreDao->getByContextId($this->submission->getData('contextId'))->toArray();

            $publicationFormats = array_map(
                function ($publicationFormat) use ($submissionFiles, $genres) {
                    $data = $publicationFormat->_data;

                    if ($data['doiId'] !== null) {
                        $data['doiObject'] = Repo::doi()->getSchemaMap()->summarize($data['doiObject']);
                    }

                    // Get SubmissionFiles related to each format and attach
                    $formatSpecificFiles = $submissionFiles->filter(function ($submissionFile) use ($publicationFormat) {
                        return $publicationFormat->getId() === $submissionFile->getData('assocId');
                    });
                    return array_merge($data, [
                        'submissionFiles' => Repo::submissionFile()->getSchemaMap()->mapMany($formatSpecificFiles, $genres)->values()->toArray()
                    ]);
                },
                $publication->getData('publicationFormats')
            );

            // Call array_values to reset array keys so that the output will convert to an array in JSON
            $output['publicationFormats'] = array_values($publicationFormats);
        }

        if (in_array('urlPublished', $props)) {
            $output['urlPublished'] = $this->request->getDispatcher()->url(
                $this->request,
                Application::ROUTE_PAGE,
                $this->context->getData('urlPath'),
                'catalog',
                'book',
                [$this->submission->getBestId(), 'version', $publication->getId()]
            );
        }

        $locales = $this->submission->getPublicationLanguages($this->context->getSupportedSubmissionMetadataLocales());

        $output = $this->schemaService->addMissingMultilingualValues(PKPSchemaService::SCHEMA_PUBLICATION, $output, $locales);

        ksort($output);

        return $this->withExtensions($output, $publication);
    }
}
