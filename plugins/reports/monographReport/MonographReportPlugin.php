<?php

/**
 * @file plugins/reports/monographReport/MonographReportPlugin.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographReportPlugin
 * @ingroup plugins_reports_monographReport
 *
 * @brief The monograph report plugin will output a .csv file containing basic
 * information (title, DOI, etc.) from all monographs
 */

namespace APP\plugins\reports\monographReport;

use APP\author\Author;
use APP\core\Application;
use APP\decision\Decision;
use APP\facades\Repo;
use APP\press\Press;
use APP\press\Series;
use APP\press\SeriesDAO;
use APP\publicationFormat\IdentificationCode;
use APP\publicationFormat\PublicationFormat;
use APP\submission\Submission;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Collection;
use IteratorAggregate;
use PKP\category\Category;
use PKP\core\PKPString;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\plugins\ReportPlugin;
use PKP\security\Role;
use PKP\stageAssignment\StageAssignment;
use PKP\stageAssignment\StageAssignmentDAO;
use PKP\submission\SubmissionAgencyDAO;
use PKP\submission\SubmissionDisciplineDAO;
use PKP\submission\SubmissionKeywordDAO;
use PKP\submission\SubmissionSubjectDAO;
use PKP\user\User;
use PKP\userGroup\UserGroup;
use SplFileObject;
use Traversable;

class MonographReportPlugin extends ReportPlugin implements IteratorAggregate
{
    private int $maxAuthors;
    private int $maxEditors;
    private int $maxDecisions;
    private array $dataSet;

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();
        return $success;
    }

    /**
     * @copydoc Plugin::getName()
     */
    public function getName(): string
    {
        return substr(static::class, strlen(__NAMESPACE__) + 1);
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName(): string
    {
        return __('plugins.reports.monographReport.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription(): string
    {
        return __('plugins.reports.monographReport.description');
    }

    /**
     * @copydoc ReportPlugin::display()
     */
    public function display($args, $request): void
    {
        $press = $request->getContext();
        if (!$press) {
            throw new Exception('The monograph report requires a context');
        }

        $output = $this->createOutputStream($press);

        // Display the data rows.
        foreach ($this as $row) {
            $output->fputcsv($row);
        }
    }

    /**
     * Retrieves a row generator
     */
    public function getIterator(): Traversable
    {
        $this->buildDataset();
        yield $this->buildHeaders();
        foreach ($this->dataSet as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                match ($key) {
                    'authors' => array_push($data, ...$this->transformAuthors($value)),
                    'editors' => array_push($data, ...$this->transformEditors($value)),
                    default => $data[] = $value
                };
            }
            yield $data;
        }
    }

    /**
     * Retrieves the stage label
     */
    public function getStageLabel(int $stageId): string
    {
        return match ($stageId) {
            WORKFLOW_STAGE_ID_SUBMISSION => __('submission.submission'),
            WORKFLOW_STAGE_ID_INTERNAL_REVIEW => __('workflow.review.internalReview'),
            WORKFLOW_STAGE_ID_EXTERNAL_REVIEW => __('submission.review'),
            WORKFLOW_STAGE_ID_EDITING => __('submission.copyediting'),
            WORKFLOW_STAGE_ID_PRODUCTION => __('submission.production'),
            default => ''
        };
    }

    /**
     * Retrieves the decision message
     */
    private function getDecisionMessage(int $decision): string
    {
        return match ($decision) {
            Decision::INTERNAL_REVIEW => __('editor.submission.decision.sendInternalReview'),
            Decision::ACCEPT => __('editor.submission.decision.accept'),
            Decision::EXTERNAL_REVIEW => __('editor.submission.decision.sendExternalReview'),
            Decision::PENDING_REVISIONS => __('editor.submission.decision.requestRevisions'),
            Decision::RESUBMIT => __('editor.submission.decision.resubmit'),
            Decision::DECLINE => __('editor.submission.decision.decline'),
            Decision::SEND_TO_PRODUCTION => __('editor.submission.decision.sendToProduction'),
            Decision::INITIAL_DECLINE => __('editor.submission.decision.decline'),
            Decision::RECOMMEND_ACCEPT => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.accept')]),
            Decision::RECOMMEND_PENDING_REVISIONS => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.requestRevisions')]),
            Decision::RECOMMEND_RESUBMIT => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.resubmit')]),
            Decision::RECOMMEND_DECLINE => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.decline')]),
            Decision::RECOMMEND_EXTERNAL_REVIEW => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.sendExternalReview')]),
            Decision::NEW_EXTERNAL_ROUND => __('editor.submission.decision.newReviewRound'),
            Decision::REVERT_DECLINE => __('editor.submission.decision.revertDecline'),
            Decision::REVERT_INITIAL_DECLINE => __('editor.submission.decision.revertDecline'),
            Decision::SKIP_EXTERNAL_REVIEW => __('editor.submission.decision.skipReview'),
            Decision::SKIP_INTERNAL_REVIEW => __('editor.submission.decision.skipReview'),
            Decision::ACCEPT_INTERNAL => __('editor.submission.decision.accept'),
            Decision::PENDING_REVISIONS_INTERNAL => __('editor.submission.decision.requestRevisions'),
            Decision::RESUBMIT_INTERNAL => __('editor.submission.decision.resubmit'),
            Decision::DECLINE_INTERNAL => __('editor.submission.decision.decline'),
            Decision::RECOMMEND_ACCEPT_INTERNAL => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.accept')]),
            Decision::RECOMMEND_PENDING_REVISIONS_INTERNAL => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.requestRevisions')]),
            Decision::RECOMMEND_RESUBMIT_INTERNAL => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.resubmit')]),
            Decision::RECOMMEND_DECLINE_INTERNAL => __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.decline')]),
            Decision::REVERT_INTERNAL_DECLINE => __('editor.submission.decision.decline'),
            Decision::NEW_INTERNAL_ROUND => __('editor.submission.decision.newReviewRound'),
            Decision::BACK_FROM_PRODUCTION => __('editor.submission.decision.backToCopyediting'),
            Decision::BACK_FROM_COPYEDITING => __('editor.submission.decision.backFromCopyediting'),
            Decision::CANCEL_REVIEW_ROUND => __('editor.submission.decision.cancelReviewRound'),
            Decision::CANCEL_INTERNAL_REVIEW_ROUND => __('editor.submission.decision.cancelReviewRound'),
            default => ''
        };
    }

    /**
     * Retrieves the report header
     */
    private function buildHeaders(): array
    {
        // Build and display the column headers.
        $columns = [
            __('common.id'),
            __('common.title'),
            __('common.abstract'),
            __('series.series'),
            __('submission.submit.seriesPosition'),
            __('common.language'),
            __('submission.coverage'),
            __('submission.rights'),
            __('submission.source'),
            __('common.subjects'),
            __('common.type'),
            __('search.discipline'),
            __('common.keywords'),
            __('submission.supportingAgencies'),
            __('common.status'),
            __('common.url'),
            __('catalog.manage.series.onlineIssn'),
            __('catalog.manage.series.printIssn'),
            __('metadata.property.displayName.doi'),
            __('catalog.categories'),
            __('submission.identifiers'),
            __('common.dateSubmitted'),
            __('submission.lastModified'),
            __('submission.firstPublished'),
        ];

        foreach (range(1, $this->maxAuthors) as $i) {
            array_push(
                $columns,
                __('user.givenName') . ' (' . __('user.role.author') . " ${i})",
                __('user.familyName') . ' (' . __('user.role.author') . " ${i})",
                __('user.orcid') . ' (' . __('user.role.author') . " ${i})",
                __('common.country') . ' (' . __('user.role.author') . " ${i})",
                __('user.affiliation') . ' (' . __('user.role.author') . " ${i})",
                __('user.email') . ' (' . __('user.role.author') . " ${i})",
                __('user.url') . ' (' . __('user.role.author') . " ${i})",
                __('user.biography') . ' (' . __('user.role.author') . " ${i})"
            );
        }
        foreach (range(1, $this->maxEditors) as $i) {
            array_push(
                $columns,
                __('user.givenName') . ' (' . __('user.role.editor') . " ${i})",
                __('user.familyName') . ' (' . __('user.role.editor') . " ${i})",
                __('user.orcid') . ' (' . __('user.role.editor') . " ${i})",
                __('user.email') . ' (' . __('user.role.editor') . " ${i})",
            );

            foreach (range(1, $this->maxDecisions) as $j) {
                array_push(
                    $columns,
                    __('manager.setup.editorDecision') . " ${j} (" . __('user.role.editor') . " ${i})",
                    __('common.dateDecided') . " ${j} (" . __('user.role.editor') . " ${i})"
                );
            }
        }

        return $columns;
    }

    /**
     * Retrieves a cached user
     */
    private function getUser(int $userId): ?User
    {
        static $users = [];
        return $users[$userId] ??= Repo::user()->get($userId, true);
    }

    /**
     * Collapses the list of authors and ensures all pre-allocated cells are filled
     *
     * @var string[] $authors
     */
    private function transformAuthors(array $authors): array
    {
        $authorColumnCount = 8;
        return collect($authors)
            ->collapse()
            // Fill the remaining empty cells
            ->push(...array_fill(0, ($this->maxAuthors - count($authors)) * $authorColumnCount, ''))
            ->toArray();
    }

    /**
     * Collapses the list of editors and ensures all pre-allocated cells are filled
     *
     * @var array<array{'editor': User, 'decisions': string[]}> $editors
     */
    private function transformEditors(array $editors): array
    {
        return collect($editors)
            // Insert placeholder editors, notice the editor might be null in the map ahead
            ->push(...array_fill(0, $this->maxEditors - count($editors), ['editor' => null, 'decisions' => []]))
            ->map(function (array $editorWithDecisions): array {
                $decisionColumnCount = 2;
                ['editor' => $editor, 'decisions' => $decisions] = $editorWithDecisions;
                return [
                    $editor?->getLocalizedGivenName(),
                    $editor?->getLocalizedFamilyName(),
                    $editor?->getData('orcid'),
                    $editor?->getEmail(),
                    ...array_merge(...$decisions),
                    // Fill the remaining empty cells
                    ...array_fill(0, ($this->maxDecisions - count($decisions)) * $decisionColumnCount, '')
                ];
            })
            ->collapse()
            ->toArray();
    }

    /**
     * Retrieves a SplFileObject and sends HTTP headers to enforce the report download
     */
    private function createOutputStream(Press $press): SplFileObject
    {
        $acronym = PKPString::regexp_replace('/[^A-Za-z0-9 ]/', '', $press->getLocalizedAcronym());
        $date = (new DateTimeImmutable())->format('Ymd');

        // Prepare for UTF8-encoded CSV output.
        header('content-type: text/comma-separated-values');
        header("content-disposition: attachment; filename=monographs-{$acronym}-{$date}.csv");

        $output = new SplFileObject('php://output', 'w');
        // UTF-8 BOM to force the file to be read with the right encoding
        $output->fwrite("\xEF\xBB\xBF");
        return $output;
    }

    /**
     * Builds the dataset and setup the max authors/editors/decisions
     */
    private function buildDataset(): void
    {
        $this->dataSet = [];
        $this->maxAuthors = $this->maxEditors = $this->maxDecisions = 0;

        $request = Application::get()->getRequest();
        $pressId = $request->getContext()->getId();

        /** @var StageAssignmentDAO */
        $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
        /** @var SeriesDAO */
        $seriesDao = DAORegistry::getDAO('SeriesDAO');
        /** @var SubmissionKeywordDAO */
        $submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
        /** @var SubmissionSubjectDAO */
        $submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO');
        /** @var SubmissionDisciplineDAO */
        $submissionDisciplineDao = DAORegistry::getDAO('SubmissionDisciplineDAO');
        /** @var SubmissionAgencyDAO */
        $submissionAgencyDao = DAORegistry::getDAO('SubmissionAgencyDAO');

        /** @var UserGroup[] */
        $userGroups = Repo::userGroup()->getCollector()->filterByContextIds([$pressId])->getMany()->toArray();
        /** @var Series[] */
        $seriesList = $seriesDao->getByContextId($pressId)->toAssociativeArray();
        /** @var Category[] */
        $categoryList = Repo::category()->getCollector()
            ->filterByContextIds([$pressId])
            ->getMany()
            ->keyBy(fn (Category $category) => $category->getId())
            ->toArray();

        $editorUserGroupIds = array_map(
            fn (UserGroup $userGroup) => $userGroup->getId(),
            array_filter($userGroups, fn (UserGroup $userGroup) => in_array($userGroup->getRoleId(), [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR]))
        );

        // Load the data from the database and store it in an array.
        // (This must be stored before display because we won't know the data dimensions until it has all been loaded.)
        /** @var Submission */
        foreach (Repo::submission()->getCollector()->filterByContextIds([$pressId])->getMany() as $submission) {
            $statusMap ??= $submission->getStatusMap();
            $publication = $submission->getCurrentPublication();
            $decisionsByEditor = collect(Repo::decision()->getCollector()->filterBySubmissionIds([$submission->getId()])->getMany())
                ->mapToGroups(fn (Decision $decision) => [$decision->getData('editorId') => [$this->getDecisionMessage($decision->getData('decision')), $decision->getData('dateDecided')]]);
            $editors = collect($stageAssignmentDao->getBySubmissionAndStageId($submission->getId())->toIterator())
                ->filter(fn (StageAssignment $stageAssignment) => in_array($stageAssignment->getUserGroupId(), $editorUserGroupIds))
                ->map(fn (StageAssignment $stageAssignment) => $this->getUser($stageAssignment->getUserId()))
                ->unique(fn (User $user) => $user->getId());

            // Keeps track of the highest number
            $this->maxDecisions = max($this->maxDecisions, $decisionsByEditor->max(fn (Collection $decisions) => $decisions->count()));
            $this->maxAuthors = max($this->maxAuthors, count($publication->getData('authors')));
            $this->maxEditors = max($this->maxEditors, $editors->count());

            // Store the submission results
            $this->dataSet[] = [
                'submissionId' => $submission->getId(),
                'title' => $publication->getLocalizedFullTitle(),
                'abstract' => html_entity_decode(strip_tags($publication->getLocalizedData('abstract'))),
                'seriesTitle' => $seriesList[$publication->getData('seriesId')]?->getLocalizedTitle() ?: '',
                'seriesPosition' => $publication->getData('seriesPosition'),
                'language' => $publication->getData('locale'),
                'coverage' => $publication->getLocalizedData('coverage'),
                'rights' => $publication->getLocalizedData('rights'),
                'source' => $publication->getLocalizedData('source'),
                'subjects' => collect([$submissionSubjectDao->getSubjects($publication->getId())])
                    ->map(fn (array $subjects) => $subjects[Locale::getLocale()] ?? $subjects[$submission->getData('locale')] ?? [])
                    ->flatten()
                    ->join(', '),
                'type' => $publication->getLocalizedData('type'),
                'disciplines' => collect([$submissionDisciplineDao->getDisciplines($publication->getId())])
                    ->map(fn (array $disciplines) => $disciplines[Locale::getLocale()] ?? $disciplines[$submission->getData('locale')] ?? [])
                    ->flatten()
                    ->join(', '),
                'keywords' => collect([$submissionKeywordDao->getKeywords($publication->getId())])
                    ->map(fn (array $keywords) => $keywords[Locale::getLocale()] ?? $keywords[$submission->getData('locale')] ?? [])
                    ->flatten()
                    ->join(', '),
                'agencies' => collect([$submissionAgencyDao->getAgencies($publication->getId())])
                    ->map(fn (array $agencies) => $agencies[Locale::getLocale()] ?? $agencies[$submission->getData('locale')] ?? [])
                    ->flatten()
                    ->join(', '),
                'status' => $submission->getData('status') === Submission::STATUS_QUEUED
                    ? $this->getStageLabel($submission->getData('stageId'))
                    : __($statusMap[$submission->getData('status')]),
                'url' => $request->url(null, 'workflow', 'access', $submission->getId()),
                'onlineIssn' => $seriesList[$publication->getData('seriesId')]?->getOnlineISSN(),
                'offlineIssn' => $seriesList[$publication->getData('seriesId')]?->getPrintISSN(),
                'doi' => $publication->getDoi(),
                'categories' => collect($publication->getData('categoryIds'))
                    ->map(fn (int $id) => $categoryList[$id]?->getLocalizedTitle())
                    ->implode("\n"),
                'onixIdentifiers' => collect($publication->getData('publicationFormats'))
                    ->map(
                        fn (PublicationFormat $pf) => collect($pf->getIdentificationCodes()->toIterator())
                            ->map(fn (IdentificationCode $ic) => [$ic->getNameForONIXCode(), $ic->getValue()])
                    )
                    ->flatten(1)
                    ->filter(fn (array $identifier) => trim(end($identifier)))
                    ->map(fn (array $identifier) => __('plugins.reports.monographReport.identifierFormat', ['name' => reset($identifier), 'value' => end($identifier)]))
                    ->implode("\n"),
                'dateSubmitted' => $submission->getData('dateSubmitted'),
                'lastModified' => $submission->getData('lastModified'),
                'firstPublished' => $submission->getOriginalPublication()?->getData('datePublished') ?? '',
                'authors' => collect($publication->getData('authors'))
                    ->map(
                        fn (Author $author) => [
                            $author->getLocalizedGivenName(),
                            $author->getLocalizedFamilyName(),
                            $author->getData('orcid'),
                            $author->getData('country'),
                            $author->getLocalizedData('affiliation'),
                            $author->getData('email'),
                            $author->getData('url'),
                            html_entity_decode(strip_tags($author->getLocalizedData('biography'))),
                        ]
                    )->toArray(),
                'editors' => $editors
                    ->map(fn (User $user) => ['editor' => $user, 'decisions' => $decisionsByEditor->get($user->getId())?->toArray() ?? []])
                    ->toArray()
            ];
        }
    }
}
