<?php
/**
 * @file classes/submission/maps/Schema.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Schema
 *
 * @brief Map submissions to the properties defined in the submission schema
 */

namespace APP\submission\maps;

use APP\core\Application;
use APP\press\FeatureDAO;
use APP\press\NewReleaseDAO;
use APP\submission\Submission;
use PKP\db\DAORegistry;

class Schema extends \PKP\submission\maps\Schema
{
    /** @copydoc \PKP\submission\maps\Schema::getSubmissionsListProps() */
    protected function getSubmissionsListProps(): array
    {
        $props = parent::getSubmissionsListProps();
        $props[] = 'series';
        $props[] = 'category';
        $props[] = 'featured';
        $props[] = 'newRelease';

        return $props;
    }

    /** @copydoc \PKP\submission\maps\Schema::mapByProperties() */
    protected function mapByProperties(array $props, Submission $submission): array
    {
        $output = parent::mapByProperties($props, $submission);

        if (in_array('urlPublished', $props)) {
            $output['urlPublished'] = $this->request->getDispatcher()->url(
                $this->request,
                Application::ROUTE_PAGE,
                $this->context->getPath(),
                'catalog',
                'book',
                $submission->getBestId()
            );
        }

        if (in_array('featured', $props)) {
            $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
            $output['featured'] = $featureDao->getFeaturedAll($submission->getId());
        }

        if (in_array('newRelease', $props)) {
            $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
            $output['newRelease'] = $newReleaseDao->getNewReleaseAll($submission->getId());
        }

        $locales = $this->context->getSupportedSubmissionMetaDataLocales();

        if (!in_array($submissionLocale = $submission->getData('locale'), $locales)) {
            $locales[] = $submissionLocale;
        }

        $output = $this->schemaService->addMissingMultilingualValues($this->schemaService::SCHEMA_SUBMISSION, $output, $locales);

        ksort($output);

        return $this->withExtensions($output, $submission);
    }
}
