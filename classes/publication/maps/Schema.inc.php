<?php
/**
 * @file classes/publication/maps/Schema.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class publication
 *
 * @brief Map publications to the properties defined in the publication schema
 */

namespace APP\publication\maps;

use APP\core\Application;
use APP\publication\Publication;
use PKP\db\DAORegistry;
use PKP\services\PKPSchemaService;
use APP\facades\Repo;

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
                        ->getMany(
                            Repo::author()
                                ->getCollector()
                                ->filterByChapterIds([$chapter->getId()])
                                ->filterByPublicationIds([$publication->getId()])
                        )
                        ->map(function($chapterAuthor) {
                            return $chapterAuthor->_data;
                        });
                }
                return $data;
            }, $publication->getData('chapters'));
        }

        if (in_array('publicationFormats', $props)) {
            $output['publicationFormats'] = array_map(
                function ($publicationFormat) {
                    return $publicationFormat->_data;
                },
                $publication->getData('publicationFormats')
            );
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

        $output = $this->schemaService->addMissingMultilingualValues(PKPSchemaService::SCHEMA_PUBLICATION, $output, $this->context->getSupportedSubmissionLocales());

        ksort($output);

        return $this->withExtensions($output, $publication);
    }
}
