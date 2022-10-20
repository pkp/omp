<?php

/**
 * @file plugins/reports/monographReport/MonographReport.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographReport
 * @ingroup plugins_reports_monographReport
 *
 * @brief The MonographReport class implements an iterator which will retrieve basic information (title, DOI, etc.) from all monographs in a press
 */

use Illuminate\Support\Collection;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\LazyCollection;

class MonographReport implements IteratorAggregate
{
	/** @var int Maximum quantity of authors in a submission */
	private $maxAuthors;
	/** @var int Maximum quantity of editors in a submission */
	private $maxEditors;
	/** @var int Maximum quantity of decisions in a submission */
	private $maxDecisions;
	/** @var Submission The current submission being processed */
	private $submission;
	/** @var Publication The current publication being processed */
	private $publication;
	/** @var Author[] The list of authors */
	private $authors;
	/** @@var array var array<string, string> Map */
	private $statusMap;
	/** @var User[] Editor list */
	private $editors;
	/** @var array<int, array<array{editDecisionId: int,reviewRoundId: int, stageId: int, round: int, editorId: int, decision: int, dateDecided: string}>> Decisions grouped by editor ID */
	private $decisionsByEditor;
	/** @@var array var array<int, Category> Categories keyed by ID */
	private $categories;
	/** @var LazyCollection<int, bool> Editor user groups keyed by ID for faster access, the value is "true" */
	private $editorUserGroups;
	/** @var Series[] Series keyed by ID */
	private $series;
	/** @var User[] Users keyed by ID */
	private $users;
	/** @var Press */
	private $press;
	/** @var Request */
	private $request;

	/**
	 * Constructor
	 */
	public function __construct(Press $press, Request $request)
	{
		$this->press = $press;
		$this->request = $request;
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

		$submissions = Services::get('submission')->getMany(['contextId' => $this->press->getId()]);
		foreach ($submissions as $this->submission) {
			// Shared data, related to the current submission being processed, which is available for all the getters.
			$this->statusMap ?? $this->statusMap = $this->submission->getStatusMap();
			$this->publication = $this->submission->getCurrentPublication();
			$this->authors = $this->publication->getData('authors');
			$this->decisionsByEditor = $this->getDecisionsByEditor();
			$this->editors = $this->getEditors();
			// Calls the getter for each field and yields an array/row
			yield array_map(function (callable $getter) {
				return $getter();
			}, $fieldMapper);
		}
	}

	/**
	 * Retrieves the decision message
	 */
	private function getDecisionMessage(?int $decision): string
	{
		import('classes.workflow.EditorDecisionActionsManager'); // SUBMISSION_EDITOR_...
		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				return __('editor.submission.decision.accept');
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
				return __('editor.submission.decision.requestRevisions');
			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
				return __('editor.submission.decision.resubmit');
			case SUBMISSION_EDITOR_DECISION_DECLINE:
				return __('editor.submission.decision.decline');
			case SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION:
				return __('editor.submission.decision.sendToProduction');
			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				return __('editor.submission.decision.sendExternalReview');
			case SUBMISSION_EDITOR_DECISION_INITIAL_DECLINE:
				return __('editor.submission.decision.decline');
			case SUBMISSION_EDITOR_RECOMMEND_ACCEPT:
				return __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.accept')]);
			case SUBMISSION_EDITOR_RECOMMEND_DECLINE:
				return __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.decline')]);
			case SUBMISSION_EDITOR_RECOMMEND_PENDING_REVISIONS:
				return __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.requestRevisions')]);
			case SUBMISSION_EDITOR_RECOMMEND_RESUBMIT:
				return __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.resubmit')]);
			case SUBMISSION_EDITOR_DECISION_REVERT_DECLINE:
				return __('editor.submission.decision.revertDecline');
			case SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW:
				return __('workflow.review.internalReview');
			case SUBMISSION_EDITOR_DECISION_NEW_ROUND:
				return __('editor.submission.decision.newRound');
			case SUBMISSION_EDITOR_RECOMMEND_EXTERNAL_REVIEW:
				return __('editor.submission.recommendation.display', ['recommendation' => __('editor.submission.decision.requestRevisions')]);;
			default:
				return '';
		}
	}

	/**
	 * Retrieves the report header
	 */
	private function getFieldMapper(): array
	{
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_READER, LOCALE_COMPONENT_APP_MANAGER);

		$roleHeader = function (string $title, string $role, int $index) {
			return "{$title} ({$role} " . ($index + 1) . ')';
		};
		$authorHeader = function (string $title, int $index) use ($roleHeader) {
			return $roleHeader($title, __('user.role.author'), $index);
		};
		$editorHeader = function (string $title, int $index) use ($roleHeader) {
			return $roleHeader($title, __('user.role.editor'), $index);
		};
		$decisionHeader = function (string $title, int $editorIndex, int $decisionIndex) use ($editorHeader) {
			return $editorHeader("{$title} " . ($decisionIndex + 1), $editorIndex);
		};

		return [
			__('common.id') => function () {
				return $this->submission->getId();
			},
			__('common.title') => function () {
				return $this->publication->getLocalizedFullTitle();
			},
			__('common.abstract') => function () {
				return $this->toPlainText($this->publication->getLocalizedData('abstract'));
			},
			__('series.series') => function () {
				return ($series = $this->getSeries()) ? $series->getLocalizedTitle() : null;
			},
			__('submission.submit.seriesPosition') => function () {
				return $this->publication->getData('seriesPosition');
			},
			__('common.language') => function () {
				return $this->publication->getData('locale');
			},
			__('submission.coverage') => function () {
				return $this->publication->getLocalizedData('coverage');
			},
			__('submission.rights') => function () {
				return $this->publication->getLocalizedData('rights');
			},
			__('submission.source') => function () {
				return $this->publication->getLocalizedData('source');
			},
			__('common.subjects') => function () {
				return $this->getSubjects();
			},
			__('common.type') => function () {
				return $this->publication->getLocalizedData('type');
			},
			__('search.discipline') => function () {
				return $this->getDisciplines();
			},
			__('common.keywords') => function () {
				return $this->getKeywords();
			},
			__('submission.supportingAgencies') => function () {
				return $this->getAgencies();
			},
			__('common.status') => function () {
				return $this->getStatus();
			},
			__('common.url') => function () {
				return $this->request->url(null, 'workflow', 'access', $this->submission->getId());
			},
			__('catalog.manage.series.onlineIssn') => function () {
				return ($series = $this->getSeries()) ? $series->getOnlineISSN() : null;
			},
			__('catalog.manage.series.printIssn') => function () {
				return ($series = $this->getSeries()) ? $series->getPrintISSN() : null;
			},
			__('metadata.property.displayName.doi') => function () {
				return $this->publication->getStoredPubId('doi');
			},
			__('catalog.categories') => function () {
				return $this->getCategories();
			},
			__('submission.identifiers') => function () {
				return $this->getIdentifiers();
			},
			__('common.dateSubmitted') => function () {
				return $this->submission->getData('dateSubmitted');
			},
			__('submission.lastModified') => function () {
				return $this->submission->getData('lastModified');
			},
			__('submission.firstPublished') => function () {
				return ($publication = $this->submission->getOriginalPublication()) ? $publication->getData('datePublished') : null;
			}
		]
		+ collect($this->maxAuthors ? range(0, $this->maxAuthors - 1) : [])
			->map(
				function ($i) use ($authorHeader) {
					return [
						$authorHeader(__('user.givenName'), $i) => function () use ($i) {
							return ($author = $this->getAuthor($i)) ? $author->getLocalizedGivenName() : null;
						},
						$authorHeader(__('user.familyName'), $i) => function () use ($i) {
							return ($author = $this->getAuthor($i)) ? $author->getLocalizedFamilyName() : null;
						},
						$authorHeader(__('user.orcid'), $i) => function () use ($i) {
							return ($author = $this->getAuthor($i)) ? $author->getData('orcid') : null;
						},
						$authorHeader(__('common.country'), $i) => function () use ($i) {
							return ($author = $this->getAuthor($i)) ? $author->getData('country') : null;
						},
						$authorHeader(__('user.affiliation'), $i) => function () use ($i) {
							return ($author = $this->getAuthor($i)) ? $author->getLocalizedData('affiliation') : null;
						},
						$authorHeader(__('user.email'), $i) => function () use ($i) {
							return ($author = $this->getAuthor($i)) ? $author->getData('email') : null;
						},
						$authorHeader(__('user.url'), $i) => function () use ($i) {
							return ($author = $this->getAuthor($i)) ? $author->getData('url') : null;
						},
						$authorHeader(__('user.biography'), $i) => function () use ($i) {
							return ($author = $this->getAuthor($i)) ? $this->toPlainText($author->getLocalizedData('biography')) : null;
						}
					];
				}
			)
			->collapse()
			->toArray()
		+ collect($this->maxEditors ? range(0, $this->maxEditors - 1) : [])
			->map(
				function ($i) use ($editorHeader, $decisionHeader) {
					return [
						$editorHeader(__('user.givenName'), $i) => function () use ($i) {
							return ($editor = $this->getEditor($i)) ? $editor->getLocalizedGivenName() : null;
						},
						$editorHeader(__('user.familyName'), $i) => function () use ($i) {
							return ($editor = $this->getEditor($i)) ? $editor->getLocalizedFamilyName() : null;
						},
						$editorHeader(__('user.orcid'), $i) => function () use ($i) {
							return ($editor = $this->getEditor($i)) ? $editor->getData('orcid') : null;
						},
						$editorHeader(__('user.email'), $i) => function () use ($i) {
							return ($editor = $this->getEditor($i)) ? $editor->getEmail() : null;
						}
					]
					+ collect($this->maxDecisions ? range(0, $this->maxDecisions - 1) : [])
						->map(
							function ($j) use ($decisionHeader, $i) {
								return [
									$decisionHeader(__('manager.setup.editorDecision'), $i, $j) => function () use ($i, $j) {
										return $this->getDecisionMessage($this->getDecision($i, $j)['decision'] ?? null);
									},
									$decisionHeader(__('common.dateDecided'), $i, $j) => function () use ($i, $j) {
										return $this->getDecision($i, $j)['dateDecided'] ?? null;
									}
								];
							}
						)
						->collapse()
						->toArray();
				}
			)
			->collapse()
			->toArray();
	}

	/**
	 * Retrieves a cached user
	 */
	private function getUser(int $userId): ?User
	{
		/** @var UserDAO */
		$userDao = DAORegistry::getDAO('UserDAO');
		return $this->users[$userId] ?? $this->users[$userId] = $userDao->getById($userId, true);
	}

	/**
	 * Retrieves the maximum amount of authors, editors and decisions that a submission may have
	 */
	private function retrieveLimits(): void
	{
		$editorUserGroupIds = $this->getEditorUserGroups()->keys()->toArray();
		$max = Capsule::selectOne(
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
	 * @return array{editDecisionId: int,reviewRoundId: int, stageId: int, round: int, editorId: int, decision: int, dateDecided: string}
	 */
	private function getDecision(int $editorIndex, int $decisionIndex): ?array
	{
		if (!($editor = $this->getEditor($editorIndex))) {
			return null;
		}
		return $this->decisionsByEditor[$editor->getId()][$decisionIndex] ?? null;
	}

	/**
	 * Retrieves the editor user groups
	 */
	private function getEditorUserGroups(): Collection
	{
		/** @var UserGroupDAO */
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		return $this->editorUserGroups ?? $this->editorUserGroups = collect($userGroupDao->getByContextId($this->press->getId())->toArray())
			->filter(function (UserGroup $userGroup) {
				return in_array($userGroup->getRoleId(), [ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR]);
			})
			->mapWithKeys(function (UserGroup $userGroup) {
				return [$userGroup->getId() => true];
			});
	}

	/**
	 * Retrieves a category
	 */
	private function getCategory(int $id): ?Category
	{
		/** @var CategoryDAO */
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$this->categories ?? $this->categories = $categoryDao->getByContextId($this->press->getId())->toAssociativeArray();
		return $this->categories[$id] ?? null;
	}

	/**
	 * Flatten a controlled vocabulary list into a comma separated list of values
	 */
	private function flattenKeywords(array $keywords): string
	{
		return collect([$keywords])
			->map(function (array $keywords) {
				return $keywords[AppLocale::getLocale()] ?? $keywords[$this->submission->getData('locale')] ?? [];
			})
			->flatten()
			->join(', ');
	}

	/**
	 * Retrieves the series of the current publication
	 */
	private function getSeries(): ?Series
	{
		/** @var SeriesDAO */
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$this->series ?? $this->series = $seriesDao->getByContextId($this->press->getId())->toAssociativeArray();
		return $this->series[$this->publication->getData('seriesId')] ?? null;
	}

	/**
	 * Retrieve all identifiers, separated by newlines
	 */
	private function getIdentifiers(): string
	{
		return collect($this->publication->getData('publicationFormats'))
			->map(function (PublicationFormat $pf) {
				return collect($pf->getIdentificationCodes()->toIterator())
					->map(function (IdentificationCode $ic) {
						return [$ic->getNameForONIXCode(), $ic->getValue()];
					});
			})
			->flatten(1)
			->filter(function (array $identifier) {
				return trim(end($identifier));
			})
			->map(function (array $identifier) {
				return reset($identifier) . ': ' . end($identifier);
			})
			->implode("\n");
	}

	/**
	 * Retrieves the submission status
	 */
	private function getStatus(): string
	{
		return $this->submission->getData('status') === STATUS_QUEUED
			? __(Application::getWorkflowStageName($this->submission->getData('stageId')))
			: __($this->statusMap[$this->submission->getData('status')]);
	}

	/**
	 * Retrieves the keywords separated by commas
	 */
	private function getKeywords(): string
	{
		/** @var SubmissionKeywordDAO */
		$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
		return $this->flattenKeywords($submissionKeywordDao->getKeywords($this->publication->getId()));
	}

	/**
	 * Retrieves the subjects separated by commas
	 */
	private function getSubjects(): string
	{
		/** @var SubmissionSubjectDAO */
		$submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO');
		return $this->flattenKeywords($submissionSubjectDao->getSubjects($this->publication->getId()));
	}

	/**
	 * Retrieves the disciplines separated by commas
	 */
	private function getDisciplines(): string
	{
		/** @var SubmissionDisciplineDAO */
		$submissionDisciplineDao = DAORegistry::getDAO('SubmissionDisciplineDAO');
		return $this->flattenKeywords($submissionDisciplineDao->getDisciplines($this->publication->getId()));
	}

	/**
	 * Retrieves the agencies separated by commas
	 */
	private function getAgencies(): string
	{
		/** @var SubmissionAgencyDAO */
		$submissionAgencyDao = DAORegistry::getDAO('SubmissionAgencyDAO');
		return $this->flattenKeywords($submissionAgencyDao->getAgencies($this->publication->getId()));
	}

	/**
	 * Retrieves categories separated by newlines
	 */
	private function getCategories(): string
	{
		return collect($this->publication->getData('categoryIds'))
			->map(function (int $id) {
				return ($category = $this->getCategory($id)) ? $category->getLocalizedTitle() : null;
			})
			->implode("\n");
	}

	/**
	 * Retrieves the list of editors
	 *
	 * @return User[]
	 */
	private function getEditors(): array
	{
		/** @var StageAssignmentDAO */
		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');

		return collect($stageAssignmentDao->getBySubmissionAndStageId($this->submission->getId())->toIterator())
			->filter(function (StageAssignment $stageAssignment) {
				return $this->getEditorUserGroups()->get($stageAssignment->getUserGroupId());
			})
			->map(function (StageAssignment $stageAssignment) {
				return $this->getUser($stageAssignment->getUserId());
			})
			->unique(function (User $user) {
				return $user->getId();
			})
			->values()
			->toArray();
	}

	/**
	 * Retrieves the decisions grouped by editor ID
	 *
	 * @return array<int, array<array{editDecisionId: int,reviewRoundId: int, stageId: int, round: int, editorId: int, decision: int, dateDecided: string}>>
	 */
	private function getDecisionsByEditor(): array
	{
		/** @var EditDecisionDAO */
		$editDecisionDao = DAORegistry::getDAO('EditDecisionDAO');
		return collect($editDecisionDao->getEditorDecisions($this->submission->getId()))
			->groupBy('editorId')
			->toArray();
	}

	/**
	 * Strips tags and converts entities
	 */
	private function toPlainText(?string $html): string
	{
		return html_entity_decode(strip_tags($html ?? ''));
	}
}
