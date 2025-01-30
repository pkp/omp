<?php

/**
 * @file plugins/reports/monographReport/Report.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Report
 *
 * @ingroup plugins_reports_monographReport
 *
 * @brief The Report class implements an iterator which will retrieve basic information (title, DOI, etc.) from all monographs in a press
 */

namespace APP\plugins\reports\monographReport;

use APP\author\Author;
use APP\core\Application;
use APP\core\Request;
use APP\decision\Decision;
use APP\facades\Repo;
use APP\press\Press;
use APP\publication\Publication;
use APP\publicationFormat\IdentificationCode;
use APP\publicationFormat\PublicationFormat;
use APP\section\Section;
use APP\submission\Submission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use IteratorAggregate;
use PKP\category\Category;
use PKP\controlledVocab\ControlledVocab;
use PKP\facades\Locale;
use PKP\security\Role;
use PKP\stageAssignment\StageAssignment;
use PKP\user\User;
use PKP\userGroup\UserGroup;
use Traversable;

class Report implements IteratorAggregate
{
    /** Maximum quantity of authors in a submission */
    private int $maxAuthors;
    /** Maximum quantity of editors in a submission */
    private int $maxEditors;
    /** Maximum quantity of decisions in a submission */
    private int $maxDecisions;
    /** The current submission being processed */
    private Submission $submission;
    /** The current publication being processed */
    private Publication $publication;
    /** @var Author[] The list of authors */
    private array $authors;
    /** @var array<string, string> Map */
    private array $statusMap;
    /** @var User[] Editor list */
    private array $editors;
    /** @var array<int, Decision[]> Decisions grouped by editor ID */
    private array $decisionsByEditor;
    /** @var LazyCollection<int, Category> Categories keyed by ID */
    private LazyCollection $categories;
    /** @var LazyCollection<int, bool> Editor user groups keyed by ID for faster access, the value is "true" */
    private Collection $editorUserGroups;
    /** @var Section[] Series keyed by ID */
    private array $series;
    /** @var User[] Users keyed by ID */
    private array $users;

    /**
     * Constructor
     */
    public function __construct(private Press $press, private Request $request)
    {
    }

    /**
     * Retrieves a generator which yields report rows (string[]), the first row contains the report header.
     */
    public function getIterator(): Traversable
    {
        $this->retrieveLimits();
        $fieldMapper = $this->getFieldMapper();

        // Yields the report header
        yield array_keys($fieldMapper);

        $submissions = Repo::submission()->getCollector()->filterByContextIds([$this->press->getId()])->getMany();
        foreach ($submissions as $this->submission) {
            // Shared data, related to the current submission being processed, which is available for all the getters.
            $this->statusMap ??= $this->submission->getStatusMap();
            $this->publication = $this->submission->getCurrentPublication();
            $this->authors = $this->publication->getData('authors')->values()->toArray();
            $this->decisionsByEditor = $this->getDecisionsByEditor();
            $this->editors = $this->getEditors();
            // Calls the getter for each field and yields an array/row
            yield array_map(fn (callable $getter) => $getter(), $fieldMapper);
        }
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
    private function getFieldMapper(): array
    {
        $roleHeader = fn (string $title, string $role, int $index) => "{$title} ({$role} " . ($index + 1) . ')';
        $authorHeader = fn (string $title, int $index) => $roleHeader($title, __('user.role.author'), $index);
        $editorHeader = fn (string $title, int $index) => $roleHeader($title, __('user.role.editor'), $index);
        $decisionHeader = fn (string $title, int $editorIndex, int $decisionIndex) => $editorHeader("{$title} " . ($decisionIndex + 1), $editorIndex);

        return [
            __('common.id') => fn () => $this->submission->getId(),
            __('common.title') => fn () => $this->publication->getLocalizedFullTitle(),
            __('common.abstract') => fn () => $this->toPlainText($this->publication->getLocalizedData('abstract')),
            __('series.series') => fn () => $this->getSeries()?->getLocalizedTitle(),
            __('submission.submit.seriesPosition') => fn () => $this->publication->getData('seriesPosition'),
            __('common.language') => fn () => $this->publication->getData('locale'),
            __('submission.coverage') => fn () => $this->publication->getLocalizedData('coverage'),
            __('submission.rights') => fn () => $this->publication->getLocalizedData('rights'),
            __('submission.source') => fn () => $this->publication->getLocalizedData('source'),
            __('common.subjects') => fn () => $this->getSubjects(),
            __('common.type') => fn () => $this->publication->getLocalizedData('type'),
            __('search.discipline') => fn () => $this->getDisciplines(),
            __('common.keywords') => fn () => $this->getKeywords(),
            __('submission.supportingAgencies') => fn () => $this->getAgencies(),
            __('common.status') => fn () => $this->getStatus(),
            __('common.url') => fn () => $this->request->url(null, 'workflow', 'access', [$this->submission->getId()]),
            __('catalog.manage.series.onlineIssn') => fn () => $this->getSeries()?->getOnlineISSN(),
            __('catalog.manage.series.printIssn') => fn () => $this->getSeries()?->getPrintISSN(),
            __('metadata.property.displayName.doi') => fn () => $this->publication->getDoi(),
            __('catalog.categories') => fn () => $this->getCategories(),
            __('submission.identifiers') => fn () => $this->getIdentifiers(),
            __('common.dateSubmitted') => fn () => $this->submission->getData('dateSubmitted'),
            __('submission.lastModified') => fn () => $this->submission->getData('lastModified'),
            __('submission.firstPublished') => fn () => $this->submission->getOriginalPublication()?->getData('datePublished')
        ]
        /** @todo: PHP 8.0 doesn't support unpacking arrays with string keys (PHP 8.1 does, this way the "collects" below can be [...unpacked] into the array above) */
        + collect($this->maxAuthors ? range(0, $this->maxAuthors - 1) : [])
            ->map(
                fn ($i) => [
                    $authorHeader(__('user.givenName'), $i) => fn () => $this->getAuthor($i)?->getLocalizedGivenName(),
                    $authorHeader(__('user.familyName'), $i) => fn () => $this->getAuthor($i)?->getLocalizedFamilyName(),
                    $authorHeader(__('user.orcid'), $i) => fn () => $this->getAuthor($i)?->getData('orcid'),
                    $authorHeader(__('common.country'), $i) => fn () => $this->getAuthor($i)?->getData('country'),
                    $authorHeader(__('user.affiliation'), $i) => fn () => $this->getAuthor($i)?->getLocalizedAffiliationNamesAsString(),
                    $authorHeader(__('user.email'), $i) => fn () => $this->getAuthor($i)?->getData('email'),
                    $authorHeader(__('user.url'), $i) => fn () => $this->getAuthor($i)?->getData('url'),
                    $authorHeader(__('user.biography'), $i) => fn () => $this->toPlainText($this->getAuthor($i)?->getLocalizedData('biography'))
                ]
            )
            ->collapse()
            ->toArray()
        + collect($this->maxEditors ? range(0, $this->maxEditors - 1) : [])
            ->map(
                fn ($i) => [
                    $editorHeader(__('user.givenName'), $i) => fn () => $this->getEditor($i)?->getLocalizedGivenName(),
                    $editorHeader(__('user.familyName'), $i) => fn () => $this->getEditor($i)?->getLocalizedFamilyName(),
                    $editorHeader(__('user.orcid'), $i) => fn () => $this->getEditor($i)?->getData('orcid'),
                    $editorHeader(__('user.email'), $i) => fn () => $this->getEditor($i)?->getEmail()
                ]
                + collect($this->maxDecisions ? range(0, $this->maxDecisions - 1) : [])
                    ->map(
                        fn ($j) => [
                            $decisionHeader(__('manager.setup.editorDecision'), $i, $j) => fn () => $this->getDecisionMessage($this->getDecision($i, $j)?->getData('decision')),
                            $decisionHeader(__('common.dateDecided'), $i, $j) => fn () => $this->getDecision($i, $j)?->getData('dateDecided')
                        ]
                    )
                    ->collapse()
                    ->toArray()
            )
            ->collapse()
            ->toArray();
    }

    /**
     * Retrieves a cached user
     */
    private function getUser(int $userId): ?User
    {
        return $this->users[$userId] ??= Repo::user()->get($userId, true);
    }

    /**
     * Retrieves the maximum amount of authors, editors and decisions that a submission may have
     */
    private function retrieveLimits(): void
    {
        $editorUserGroupIds = $this->getEditorUserGroups()->keys()->toArray();
        $max = DB::selectOne(
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
                    AND sa.user_group_id IN (0' . str_repeat(',?', count($editorUserGroupIds)) . ')
                ) AS editors,
                (
                    SELECT COUNT(0) AS count
                    FROM edit_decisions ed
                    WHERE ed.submission_id = s.submission_id
                    GROUP BY ed.editor_id
                    ORDER BY count DESC
                    LIMIT 1
                ) AS decisions
                FROM submissions s
            ) AS tmp',
            $editorUserGroupIds
        );
        $this->maxAuthors = (int) $max->authors;
        $this->maxEditors = (int) $max->editors;
        $this->maxDecisions = (int) $max->decisions;
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

    /**
     * Retrieves the editor user groups
     */
    private function getEditorUserGroups(): Collection
    {
        $userGroups = UserGroup::withContextIds([$this->press->getId()])->get();

        return $this->editorUserGroups ??= $userGroups
            ->filter(fn (UserGroup $userGroup) => in_array($userGroup->roleId, [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR]))
            ->mapWithKeys(fn (UserGroup $userGroup) => [$userGroup->id => true]);
    }

    /**
     * Retrieves a category
     */
    private function getCategory(int $id): ?Category
    {
        $this->categories ??= Repo::category()->getCollector()
            ->filterByContextIds([$this->press->getId()])
            ->getMany()
            ->keyBy(fn (Category $category) => $category->getId());
        return $this->categories->get($id);
    }

    /**
     * Flatten a controlled vocabulary list into a comma separated list of values
     */
    private function flattenKeywords(array $keywords): string
    {
        return collect([$keywords])
            ->map(fn (array $keywords) => $keywords[Locale::getLocale()] ?? $keywords[$this->submission->getData('locale')] ?? [])
            ->flatten()
            ->join(', ');
    }

    /**
     * Retrieves the series of the current publication
     */
    private function getSeries(): ?Section
    {
        $this->series ??= Repo::section()
            ->getCollector()
            ->filterByContextIds([$this->press->getId()])
            ->getMany()
            ->toArray();
        return $this->series[$this->publication->getData('seriesId')] ?? null;
    }

    /**
     * Retrieve all identifiers, separated by newlines
     */
    private function getIdentifiers(): string
    {
        return collect($this->publication->getData('publicationFormats'))
            ->map(
                fn (PublicationFormat $pf) => collect($pf->getIdentificationCodes()->toIterator())
                    ->map(fn (IdentificationCode $ic) => [$ic->getNameForONIXCode(), $ic->getValue()])
            )
            ->flatten(1)
            ->filter(fn (array $identifier) => trim(end($identifier)))
            ->map(fn (array $identifier) => reset($identifier) . ': ' . end($identifier))
            ->implode("\n");
    }

    /**
     * Retrieves the submission status
     */
    private function getStatus(): string
    {
        return $this->submission->getData('status') === Submission::STATUS_QUEUED
            ? __(Application::getWorkflowStageName($this->submission->getData('stageId')))
            : __($this->statusMap[$this->submission->getData('status')]);
    }

    /**
     * Retrieves the keywords separated by commas
     */
    private function getKeywords(): string
    {
        return $this->flattenKeywords(
            Repo::controlledVocab()->getBySymbolic(
                ControlledVocab::CONTROLLED_VOCAB_SUBMISSION_KEYWORD,
                Application::ASSOC_TYPE_PUBLICATION,
                $this->publication->getId()
            )
        );
    }

    /**
     * Retrieves the subjects separated by commas
     */
    private function getSubjects(): string
    {
        return $this->flattenKeywords(
            Repo::controlledVocab()->getBySymbolic(
                ControlledVocab::CONTROLLED_VOCAB_SUBMISSION_SUBJECT,
                Application::ASSOC_TYPE_PUBLICATION,
                $this->publication->getId()
            )
        );
    }

    /**
     * Retrieves the disciplines separated by commas
     */
    private function getDisciplines(): string
    {
        return $this->flattenKeywords(
            Repo::controlledVocab()->getBySymbolic(
                ControlledVocab::CONTROLLED_VOCAB_SUBMISSION_DISCIPLINE,
                Application::ASSOC_TYPE_PUBLICATION,
                $this->publication->getId()
            )
        );
    }

    /**
     * Retrieves the agencies separated by commas
     */
    private function getAgencies(): string
    {
        return $this->flattenKeywords(
            Repo::controlledVocab()->getBySymbolic(
                ControlledVocab::CONTROLLED_VOCAB_SUBMISSION_AGENCY,
                Application::ASSOC_TYPE_PUBLICATION,
                $this->publication->getId()
            )
        );
    }

    /**
     * Retrieves categories separated by newlines
     */
    private function getCategories(): string
    {
        return collect($this->publication->getData('categoryIds'))
            ->map(fn (int $id) => $this->getCategory($id)?->getLocalizedTitle())
            ->implode("\n");
    }

    /**
     * Retrieves the list of editors
     *
     * @return User[]
     */
    private function getEditors(): array
    {
        $stageAssignments = StageAssignment::withSubmissionIds([$this->submission->getId()])
            ->get();

        return $stageAssignments
            ->filter(fn (StageAssignment $stageAssignment) => $this->getEditorUserGroups()->get($stageAssignment->userGroupId))
            ->map(fn (StageAssignment $stageAssignment) => $this->getUser($stageAssignment->userId))
            ->unique(fn (User $user) => $user->getId())
            ->values()
            ->toArray();
    }

    /**
     * Retrieves the decisions grouped by editor ID
     *
     * @return array<int, Decision[]>
     */
    private function getDecisionsByEditor(): array
    {
        return collect(Repo::decision()->getCollector()->filterBySubmissionIds([$this->submission->getId()])->getMany())
            ->groupBy(fn (Decision $decision) => $decision->getData('editorId'))
            ->toArray();
    }

    /**
     * Strips tags and converts entities
     */
    private function toPlainText(?string $html): string
    {
        return html_entity_decode(strip_tags($html));
    }
}
