<?php

/**
 * @file api/v1/_test/PressScenarioController.php
 *
 * Copyright (c) 2023-2026 Simon Fraser University
 * Copyright (c) 2023-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PressScenarioController
 *
 * @ingroup api_v1__test
 *
 * @brief OMP overlay for the shared context scenario endpoint.
 *
 * The app-neutral spec knows about a "context"; this subclass declares the OMP
 * concepts on top of it — SERIES and the press-only settings — as OVERLAY
 * PROPERTIES, so a spec that names them is validated here and rejected as an
 * unknown key on an app that has no such concept. The reverse holds too: an OJS
 * spec key (`sections`, `issues`, `onlineIssn`) sent here is rejected, because
 * this controller does not declare it.
 *
 * Two facts about OMP shape the code below:
 *
 * 1. **A press is created with NO series.** OJS's context service creates a
 *    default section and the OJS overlay therefore matches-and-edits it;
 *    APP\services\ContextService::afterAddContext() adds only contributor roles,
 *    so every series named in a spec is genuinely created here.
 * 2. **A series has no abbreviation.** OMP's section schema (`schemas/section.json`,
 *    "A press series") replaces OJS's `abbrev` with `path`, which is unique per
 *    press and is what the front end addresses a series by — so `path` is this
 *    overlay's identifier everywhere, including `users[].series`.
 */

namespace APP\API\v1\_test;

use APP\core\Application;
use APP\facades\Repo;
use PKP\API\v1\_test\PKPContextScenarioController;
use PKP\context\Context;
use PKP\context\SubEditorsDAO;
use PKP\db\DAORegistry;
use PKP\security\Role;
use PKP\testing\scenario\ScenarioException;
use PKP\user\User;
use PKP\userGroup\UserGroup;

class PressScenarioController extends PKPContextScenarioController
{
    /**
     * @copydoc \PKP\API\v1\_test\PKPTestApiController::schemaOverlayProperties()
     */
    public function schemaOverlayProperties(): array
    {
        return [
            'series' => [
                'type' => 'array',
                'description' => 'OMP overlay. Series of the press. Unlike OJS sections, no series exists until one is named here: creating a press creates none.',
                'items' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['path', 'title'],
                    'properties' => [
                        'path' => ['type' => 'string', 'minLength' => 1, 'description' => 'Unique per press; how the catalog addresses the series. OMP series have no abbreviation.'],
                        'title' => ['type' => 'string', 'minLength' => 1],
                        'description' => ['type' => 'string'],
                        'prefix' => ['type' => 'string'],
                        'subtitle' => ['type' => 'string'],
                        'featured' => ['type' => 'boolean'],
                        'editorRestricted' => ['type' => 'boolean'],
                        'isInactive' => ['type' => 'boolean'],
                        'onlineIssn' => ['type' => 'string'],
                        'printIssn' => ['type' => 'string'],
                        'sortOption' => ['type' => 'string'],
                    ],
                ],
            ],

            // Press-only settings. The app-neutral spec carries what every app
            // has (contact, review deadlines, categories); these are the press
            // settings a realistic OMP base seed configures and OJS has no
            // counterpart for.
            'publisher' => ['type' => 'string', 'description' => 'OMP overlay. Publisher name, used in the ONIX metadata a press exports.'],
            'location' => ['type' => 'string', 'description' => 'OMP overlay. Publisher location (ONIX).'],
            'codeType' => ['type' => 'string', 'description' => 'OMP overlay. ONIX publisher code type (list 44).'],
            'codeValue' => ['type' => 'string', 'description' => 'OMP overlay. ONIX publisher code value.'],
            'restrictMonographAccess' => ['type' => 'boolean', 'description' => 'OMP overlay. Require login to read a monograph.'],
            'displayNewReleases' => ['type' => 'boolean', 'description' => 'OMP overlay. Show the New Releases block on the catalog home page.'],
            'displayFeaturedBooks' => ['type' => 'boolean', 'description' => 'OMP overlay. Show the Featured Books block on the catalog home page.'],
            'catalogSortOption' => ['type' => 'string', 'description' => 'OMP overlay. Default catalog ordering, "sortBy-sortDir".'],
            'internalReviewGuidelines' => ['type' => 'string', 'description' => 'OMP overlay. Guidance shown to internal reviewers — the OMP-unique Internal Review stage has its own guidelines field.'],
            'reviewerSuggestionEnabled' => ['type' => 'boolean', 'description' => 'OMP overlay. Offer the reviewer-suggestion step in the submission wizard.'],
        ];
    }

    /**
     * @copydoc \PKP\API\v1\_test\PKPTestApiController::userSchemaOverlayProperties()
     */
    public function userSchemaOverlayProperties(): array
    {
        return [
            'series' => [
                'type' => 'array',
                'description' => 'OMP overlay. Paths of the series this user edits, as the Series settings form assigns series editors. The user must already be enrolled in an editorially assignable role.',
                'items' => ['type' => 'string', 'minLength' => 1],
            ],
        ];
    }

    /**
     * Assign a seeded user as a series editor of the series it names.
     *
     * Mirrors SeriesForm::execute(), which delegates the assignment to
     * PKPSectionForm::execute(): a row in subeditor_submission_group keyed by the
     * user group it was made under, and only for users enrolled in one of that
     * form's assignableRoles. Assignments made this way are what
     * SubEditorsDAO::assignEditors() reads when a submission enters the series
     * (OMP resolves the publication's series through
     * Application::getSectionIdPropName() === 'seriesId'), so a seeded series
     * editor is a participant on new submissions exactly as one configured
     * through the UI would be.
     *
     * @throws ScenarioException
     */
    protected function afterUserSeeded(Context $context, array $userSpec, User $user, string $specKey): void
    {
        $paths = $userSpec['series'] ?? [];

        if (empty($paths)) {
            return;
        }

        $group = $this->assignableUserGroupFor($context, $user, "{$specKey}.series");
        /** @var SubEditorsDAO $subEditorsDao */
        $subEditorsDao = DAORegistry::getDAO('SubEditorsDAO');

        foreach (array_values($paths) as $index => $path) {
            $series = Repo::section()->getByPath($path, $context->getId());

            if (!$series) {
                throw new ScenarioException(
                    "Press '{$context->getPath()}' has no series with path '{$path}'. Available: "
                        . $this->seriesPaths($context) . '.',
                    "{$specKey}.series.{$index}"
                );
            }

            $assigned = $subEditorsDao
                ->getBySubmissionGroupIds([$series->getId()], Application::ASSOC_TYPE_SERIES, $context->getId())
                ->contains(fn ($row) => (int) $row->userId === $user->getId());

            if ($assigned) {
                continue;
            }

            $subEditorsDao->insertEditor(
                $context->getId(),
                $series->getId(),
                $user->getId(),
                Application::ASSOC_TYPE_SERIES,
                $group->id
            );
        }
    }

    /**
     * The user group a series assignment is recorded under.
     *
     * The Series form offers only groups in PKPSectionForm::$assignableRoles and
     * stores the assignment against one of them; the sub-editor slot (OMP's
     * "Series editor") is the natural one, with manager and assistant groups as
     * the fallbacks the form also offers. A user with none of those roles is a
     * spec error, not a silent skip.
     *
     * @throws ScenarioException
     */
    protected function assignableUserGroupFor(Context $context, User $user, string $specKey): UserGroup
    {
        $preference = [Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_ASSISTANT];

        $group = UserGroup::withContextIds([$context->getId()])
            ->withUserIds([$user->getId()])
            ->withRoleIds($preference)
            ->get()
            ->sortBy(fn (UserGroup $group) => array_search($group->roleId, $preference))
            ->first();

        if (!$group) {
            throw new ScenarioException(
                "User '{$user->getUsername()}' cannot be assigned to a series: it is not enrolled in any "
                    . 'editorially assignable role (series editor, manager or assistant) in press '
                    . "'{$context->getPath()}'.",
                $specKey
            );
        }

        return $group;
    }

    /**
     * @copydoc \PKP\API\v1\_test\PKPContextScenarioController::nonSettingOverlayKeys()
     */
    protected function nonSettingOverlayKeys(): array
    {
        return ['series'];
    }

    /**
     * @copydoc \PKP\API\v1\_test\PKPContextScenarioController::afterContextCreated()
     */
    protected function afterContextCreated(Context $context, array $spec, string $specKeyPrefix): void
    {
        $key = fn (string $name) => $specKeyPrefix === '' ? $name : "{$specKeyPrefix}.{$name}";

        $this->seededSeriesIds = $this->seedSeries($context, $spec['series'] ?? [], $key('series'));
    }

    /** @var array<string, int> path => seriesId */
    protected array $seededSeriesIds = [];

    /**
     * @copydoc \PKP\API\v1\_test\PKPContextScenarioController::contextEcho()
     */
    protected function contextEcho(Context $context, array $spec): array
    {
        return array_filter(['series' => $this->seededSeriesIds]);
    }

    /**
     * Seed series through the real section repository — which is what OMP's
     * series ARE: `schemas/section.json` in this repository is titled "A press
     * series" and the DAO writes the `series` table.
     *
     * Unlike the OJS overlay there is no default to match and edit, so a
     * duplicate path is a spec error rather than an update: the table's unique
     * (press_id, path) index would reject it anyway, and a silent update would
     * hide a typo in a base seed.
     *
     * @throws ScenarioException
     *
     * @return array<string, int> path => seriesId
     */
    protected function seedSeries(Context $context, array $seriesSpecs, string $specKeyPrefix): array
    {
        if (empty($seriesSpecs)) {
            return [];
        }

        $locale = $context->getPrimaryLocale();
        $ids = [];

        foreach (array_values($seriesSpecs) as $index => $seriesSpec) {
            $path = $seriesSpec['path'];

            if (isset($ids[$path]) || Repo::section()->getByPath($path, $context->getId())) {
                throw new ScenarioException(
                    "Press '{$context->getPath()}' already has a series at path '{$path}'.",
                    "{$specKeyPrefix}.{$index}.path"
                );
            }

            $props = [
                'title' => [$locale => $seriesSpec['title']],
                'path' => $path,
                'featured' => (bool) ($seriesSpec['featured'] ?? false),
                'editorRestricted' => (bool) ($seriesSpec['editorRestricted'] ?? false),
                'isInactive' => (bool) ($seriesSpec['isInactive'] ?? false),
                // SeriesForm::execute() always writes an image value; leaving it
                // unset makes the catalog's cover lookup read a missing property.
                'image' => [],
            ];

            foreach (['description', 'prefix', 'subtitle'] as $multilingual) {
                if (isset($seriesSpec[$multilingual])) {
                    $props[$multilingual] = [$locale => $seriesSpec[$multilingual]];
                }
            }

            foreach (['onlineIssn', 'printIssn', 'sortOption'] as $plain) {
                if (isset($seriesSpec[$plain])) {
                    $props[$plain] = $seriesSpec[$plain];
                }
            }

            $series = Repo::section()->newDataObject($props);
            $series->setContextId($context->getId());
            $series->setSequence(REALLY_BIG_NUMBER);

            $ids[$path] = Repo::section()->add($series);
        }

        Repo::section()->resequence($context->getId());

        return $ids;
    }

    /**
     * The series a press has, for an error message.
     */
    protected function seriesPaths(Context $context): string
    {
        return Repo::section()->getCollector()
            ->filterByContextIds([$context->getId()])
            ->getMany()
            ->map(fn ($series) => $series->getPath())
            ->filter()
            ->join(', ') ?: '(none)';
    }
}
