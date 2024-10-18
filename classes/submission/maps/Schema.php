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
use Illuminate\Support\Collection;
use PKP\db\DAORegistry;
use PKP\submission\reviewRound\ReviewRound;
use PKP\submission\reviewRound\ReviewRoundDAO;

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
    protected function mapByProperties(array $props, Submission $submission, bool|Collection $anonymizeReviews = false): array
    {
        $output = parent::mapByProperties(array_diff($props, ['recommendationsIn', 'reviewersNotAssigned', 'reviewRounds', 'revisionsRequested', 'revisionsSubmitted']), $submission, $anonymizeReviews);

        $locales = $this->context->getSupportedSubmissionMetaDataLocales();

        if (!in_array($submissionLocale = $submission->getData('locale'), $locales)) {
            $locales[] = $submissionLocale;
        }

        $reviewRounds = $this->getReviewRoundsFromSubmission($submission);
        $currentReviewRound = $reviewRounds->flatten()->sort()->last(); /** @var ReviewRound|null $currentReviewRound */

        foreach ($props as $prop) {
            switch ($prop) {
                case 'recommendationsIn':
                    $output[$prop] = $currentReviewRound ? $this->areRecommendationsIn($currentReviewRound, $this->stageAssignments) : null;
                    break;
                case 'reviewersNotAssigned':
                    $output[$prop] = $currentReviewRound && $this->reviewAssignments->count() >= intval($this->context->getData('numReviewersPerSubmission'));
                    break;
                case 'reviewRounds':
                    $output[$prop] = $this->getPropertyReviewRounds($reviewRounds->flatten());
                    break;
                case 'revisionsRequested':
                    $output[$prop] = $currentReviewRound && $currentReviewRound->getData('status') == ReviewRound::REVIEW_ROUND_STATUS_REVISIONS_REQUESTED;
                    break;
                case 'revisionsSubmitted':
                    $output[$prop] = $currentReviewRound && $currentReviewRound->getData('status') == ReviewRound::REVIEW_ROUND_STATUS_REVISIONS_SUBMITTED;
                    break;
                case 'featured':
                    $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
                    $output['featured'] = $featureDao->getFeaturedAll($submission->getId());
                    break;
                case 'newRelease':
                    $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
                    $output['newRelease'] = $newReleaseDao->getNewReleaseAll($submission->getId());
                    break;
                case 'urlPublished':
                    $output['urlPublished'] = $this->request->getDispatcher()->url(
                        $this->request,
                        Application::ROUTE_PAGE,
                        $this->context->getPath(),
                        'catalog',
                        'book',
                        [$submission->getBestId()]
                    );
                    break;
            }
        }


        $output = $this->schemaService->addMissingMultilingualValues($this->schemaService::SCHEMA_SUBMISSION, $output, $locales);

        ksort($output);

        return $this->withExtensions($output, $submission);
    }

    /** @copydoc \PKP\submission\maps\Schema::getReviewRoundsFromSubmission*/
    protected function getReviewRoundsFromSubmission(Submission $submission): Collection
    {
        $reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO'); /** @var ReviewRoundDAO $reviewRoundDao */
        return collect($reviewRoundDao->getBySubmissionId($submission->getId())->toIterator())
            ->groupBy(fn (ReviewRound $reviewRound) => $reviewRound->getData('round'));
    }
}
