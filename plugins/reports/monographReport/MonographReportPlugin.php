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
use APP\decision\Decision;
use APP\facades\Repo;
use APP\press\Press;
use APP\press\Series;
use APP\press\SeriesDAO;
use APP\publication\Publication;
use APP\publicationFormat\IdentificationCode;
use APP\publicationFormat\PublicationFormat;
use APP\submission\Submission;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\DB;
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
    private Press $press;
    private Submission $submission;
    private Publication $publication;
    /** @var Author[] */
    private array $authors;
    private array $statusMap;
    /** @var User[] */
    private array $editors;
    /** @var array<int,Decision[]> */
    private array $decisionsByEditor;

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
        $this->press = $this->getRequest()->getContext();
        if (!$this->press) {
            throw new Exception('The monograph report requires a context');
        }

        $output = $this->createOutputStream();

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
        /** @var StageAssignmentDAO */
        $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
        /** @var UserGroup[] */
        $userGroups = Repo::userGroup()->getCollector()->filterByContextIds([$this->press->getId()])->getMany()->toArray();
        $editorUserGroupIds = array_map(
            fn (UserGroup $userGroup) => $userGroup->getId(),
            array_filter($userGroups, fn (UserGroup $userGroup) => in_array($userGroup->getRoleId(), [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR]))
        );

        $this->retrieveLimits();

        $dataMapper = $this->getDataMapper();
        yield array_keys($dataMapper);

        /** @var Submission */
        foreach (Repo::submission()->getCollector()->filterByContextIds([$this->press->getId()])->getMany() as $this->submission) {
            $this->statusMap ??= $this->submission->getStatusMap();
            $this->publication = $this->submission->getCurrentPublication();
            $this->authors = $this->publication->getData('authors')->values()->toArray();
            $this->decisionsByEditor = collect(Repo::decision()->getCollector()->filterBySubmissionIds([$this->submission->getId()])->getMany())
                ->groupBy(fn (Decision $decision) => $decision->getData('editorId'))
                ->toArray();
            $this->editors = collect($stageAssignmentDao->getBySubmissionAndStageId($this->submission->getId())->toIterator())
                ->filter(fn (StageAssignment $stageAssignment) => in_array($stageAssignment->getUserGroupId(), $editorUserGroupIds))
                ->map(fn (StageAssignment $stageAssignment) => $this->getUser($stageAssignment->getUserId()))
                ->unique(fn (User $user) => $user->getId())
                ->values()
                ->toArray();
            yield array_map(fn (callable $callable) => $callable(), $dataMapper);
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
    private function getDecisionMessage(?int $decision): string
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
    private function getDataMapper(): array
    {
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

        /** @var Series[] */
        $seriesList = $seriesDao->getByContextId($this->press->getId())->toAssociativeArray();
        /** @var Category[] */
        $categoryList = Repo::category()->getCollector()
            ->filterByContextIds([$this->press->getId()])
            ->getMany()
            ->keyBy(fn (Category $category) => $category->getId())
            ->toArray();

        return [
            __('common.id') => fn () => $this->submission->getId(),
            __('common.title') => fn () => $this->publication->getLocalizedFullTitle(),
            __('common.abstract') => fn () => html_entity_decode(strip_tags($this->publication->getLocalizedData('abstract'))),
            __('series.series') => fn () => $this->seriesList[$this->publication->getData('seriesId')]?->getLocalizedTitle() ?: '',
            __('submission.submit.seriesPosition') => fn () => $this->publication->getData('seriesPosition'),
            __('common.language') => fn () => $this->publication->getData('locale'),
            __('submission.coverage') => fn () => $this->publication->getLocalizedData('coverage'),
            __('submission.rights') => fn () => $this->publication->getLocalizedData('rights'),
            __('submission.source') => fn () => $this->publication->getLocalizedData('source'),
            __('common.subjects') => fn () => collect([$submissionSubjectDao->getSubjects($this->publication->getId())])
                ->map(fn (array $subjects) => $subjects[Locale::getLocale()] ?? $subjects[$this->submission->getData('locale')] ?? [])
                ->flatten()
                ->join(', '),
            __('common.type') => fn () => $this->publication->getLocalizedData('type'),
            __('search.discipline') => fn () => collect([$submissionDisciplineDao->getDisciplines($this->publication->getId())])
                ->map(fn (array $disciplines) => $disciplines[Locale::getLocale()] ?? $disciplines[$this->submission->getData('locale')] ?? [])
                ->flatten()
                ->join(', '),
            __('common.keywords') => fn () => collect([$submissionKeywordDao->getKeywords($this->publication->getId())])
                ->map(fn (array $keywords) => $keywords[Locale::getLocale()] ?? $keywords[$this->submission->getData('locale')] ?? [])
                ->flatten()
                ->join(', '),
            __('submission.supportingAgencies') => fn () => collect([$submissionAgencyDao->getAgencies($this->publication->getId())])
                ->map(fn (array $agencies) => $agencies[Locale::getLocale()] ?? $agencies[$this->submission->getData('locale')] ?? [])
                ->flatten()
                ->join(', '),
            __('common.status') => fn () => $this->submission->getData('status') === Submission::STATUS_QUEUED
                ? $this->getStageLabel($this->submission->getData('stageId'))
                : __($this->statusMap[$this->submission->getData('status')]),
            __('common.url') => fn () => $this->getRequest()->url(null, 'workflow', 'access', $this->submission->getId()),
            __('catalog.manage.series.onlineIssn') => fn () => $seriesList[$this->publication->getData('seriesId')]?->getOnlineISSN(),
            __('catalog.manage.series.printIssn') => fn () => $seriesList[$this->publication->getData('seriesId')]?->getPrintISSN(),
            __('metadata.property.displayName.doi') => fn () => $this->publication->getDoi(),
            __('catalog.categories') => fn () => collect($this->publication->getData('categoryIds'))
                ->map(fn (int $id) => $categoryList[$id]?->getLocalizedTitle())
                ->implode("\n"),
            __('submission.identifiers') => fn () => collect($this->publication->getData('publicationFormats'))
                ->map(
                    fn (PublicationFormat $pf) => collect($pf->getIdentificationCodes()->toIterator())
                        ->map(fn (IdentificationCode $ic) => [$ic->getNameForONIXCode(), $ic->getValue()])
                )
                ->flatten(1)
                ->filter(fn (array $identifier) => trim(end($identifier)))
                ->map(fn (array $identifier) => __('plugins.reports.monographReport.identifierFormat', ['name' => reset($identifier), 'value' => end($identifier)]))
                ->implode("\n"),
            __('common.dateSubmitted') => fn () => $this->submission->getData('dateSubmitted'),
            __('submission.lastModified') => fn () => $this->submission->getData('lastModified'),
            __('submission.firstPublished') => fn () => $this->submission->getOriginalPublication()?->getData('datePublished') ?? '',
            ...collect(range(0, $this->maxAuthors - 1))
                ->map(fn ($i) => [
                    __('user.givenName') . ' (' . __('user.role.author') . ' ' . ($i + 1) . ')' => fn () => $this->getAuthor($i)?->getLocalizedGivenName(),
                    __('user.familyName') . ' (' . __('user.role.author') . ' ' . ($i + 1) . ')' => fn () => $this->getAuthor($i)?->getLocalizedFamilyName(),
                    __('user.orcid') . ' (' . __('user.role.author') . ' ' . ($i + 1) . ')' => fn () => $this->getAuthor($i)?->getData('orcid'),
                    __('common.country') . ' (' . __('user.role.author') . ' ' . ($i + 1) . ')' => fn () => $this->getAuthor($i)?->getData('country'),
                    __('user.affiliation') . ' (' . __('user.role.author') . ' ' . ($i + 1) . ')' => fn () => $this->getAuthor($i)?->getLocalizedData('affiliation'),
                    __('user.email') . ' (' . __('user.role.author') . ' ' . ($i + 1) . ')' => fn () => $this->getAuthor($i)?->getData('email'),
                    __('user.url') . ' (' . __('user.role.author') . ' ' . ($i + 1) . ')' => fn () => $this->getAuthor($i)?->getData('url'),
                    __('user.biography') . ' (' . __('user.role.author') . ' ' . ($i + 1) . ')' => fn () => html_entity_decode(strip_tags($this->getAuthor($i)?->getLocalizedData('biography')))
                ])
                ->collapse(),
            ...collect(range(0, $this->maxEditors - 1))
                ->map(fn ($i) => [
                    __('user.givenName') . ' (' . __('user.role.editor') . ' ' . ($i + 1) . ')' => fn () => $this->getEditor($i)?->getLocalizedGivenName(),
                    __('user.familyName') . ' (' . __('user.role.editor') . ' ' . ($i + 1) . ')' => fn () => $this->getEditor($i)?->getLocalizedFamilyName(),
                    __('user.orcid') . ' (' . __('user.role.editor') . ' ' . ($i + 1) . ')' => fn () => $this->getEditor($i)?->getData('orcid'),
                    __('user.email') . ' (' . __('user.role.editor') . ' ' . ($i + 1) . ')' => fn () => $this->getEditor($i)?->getEmail(),
                    ...collect(range(0, $this->maxDecisions - 1))
                        ->map(fn ($j) => [
                            __('manager.setup.editorDecision') . ' ' . ($j + 1) . ' (' . __('user.role.editor') . ' ' . ($i + 1) . ')' => fn () => $this->getDecisionMessage($this->getDecision($i, $j)?->getData('decision')),
                            __('common.dateDecided') . ' ' . ($j + 1) . ' (' . __('user.role.editor') . ' ' . ($i + 1) . ')' => fn () => $this->getDecision($i, $j)?->getData('dateDecided')
                        ])
                        ->collapse()
                ])
                ->collapse()
        ];
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
     * Retrieves a SplFileObject and sends HTTP headers to enforce the report download
     */
    private function createOutputStream(): SplFileObject
    {
        $acronym = PKPString::regexp_replace('/[^A-Za-z0-9 ]/', '', $this->press->getLocalizedAcronym());
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
     * Retrieves the maximum amount of authors, editors and decisions that a submission may have
     */
    private function retrieveLimits(): void
    {
        $query = DB::selectOne(
            'SELECT MAX(tmp.authors) AS authors, MAX(tmp.editors) AS editors, MAX(tmp.decisions) AS decisions
            FROM (
                SELECT (
                    SELECT COUNT(0)
                    FROM authors a
                    WHERE a.publication_id = s.current_publication_id
                ) AS authors,
                (
                    SELECT COUNT(sa.user_id)
                    FROM stage_assignments sa
                    WHERE sa.submission_id = s.submission_id
                    AND sa.user_group_id IN (
                        SELECT
                            ug.user_group_id
                            FROM user_groups ug
                            WHERE ug.role_id IN (?, ?)
                    )
                ) AS editors,
                (
                    SELECT MAX(count)
                    FROM (
                        SELECT COUNT(0) AS count
                        FROM edit_decisions ed
                        WHERE ed.submission_id = s.submission_id
                        GROUP BY ed.editor_id
                    ) AS tmp
                ) AS decisions
                FROM submissions s
            ) AS tmp',
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR]
        );
        $this->maxAuthors = (int) $query->authors;
        $this->maxEditors = (int) $query->editors;
        $this->maxDecisions = (int) $query->decisions;
    }

    /**
     * Retrieves an author from the current submission
     */
    private function getAuthor(int $index): ?Author
    {
        return $this->authors[$index] ?? null;
    }

    /**
     * Retrieves an editor from the current submission
     */
    private function getEditor(int $index): ?User
    {
        return $this->editors[$index] ?? null;
    }

    /**
     * Retrieves a decision from the current submission
     */
    private function getDecision(int $editorIndex, int $decisionIndex): ?Decision
    {
        return $this->decisionsByEditor[$this->getEditor($editorIndex)?->getId()][$decisionIndex] ?? null;
    }
}
