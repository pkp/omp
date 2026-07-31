<?php

/**
 * @file classes/testing/BootstrapSeeder.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BootstrapSeeder
 *
 * @brief OMP base seed: series (identified by path — no abbrev on series).
 */

namespace APP\testing;

use APP\facades\Repo;
use PKP\context\Context;
use PKP\testing\PKPBootstrapSeeder;
use PKP\testing\Spec;

class BootstrapSeeder extends PKPBootstrapSeeder
{
    protected function structureKey(): string
    {
        return 'series';
    }

    protected function parseStructure(Spec $spec): array
    {
        return [
            'path' => (string) $spec->require('path'),
            'title' => $spec->get('title'),
            'description' => $spec->get('description'),
        ];
    }

    protected function addStructure(Context $context, array $plan, int $sequence): int
    {
        return self::addSeries($context, $plan, $sequence);
    }

    protected function resolveStructureId(Context $context, string $identifier): ?int
    {
        return self::findSeriesId($context, $identifier);
    }

    public static function addSeries(Context $context, array $plan, int $sequence): int
    {
        $locale = $context->getPrimaryLocale();
        $localize = fn ($value, $default = null) => is_array($value) ? $value : [$locale => $value ?? $default];

        $series = Repo::section()->newDataObject();
        $series->setData('contextId', $context->getId());
        $series->setData('sequence', $sequence);
        $series->setData('editorRestricted', false);
        $series->setData('path', $plan['path']);
        foreach ($localize($plan['title'], $plan['path']) as $l => $value) {
            $series->setData('title', $value, $l);
        }
        if (($plan['description'] ?? null) !== null) {
            foreach ($localize($plan['description']) as $l => $value) {
                $series->setData('description', $value, $l);
            }
        }
        return Repo::section()->add($series);
    }

    /** Match a series by its path. */
    public static function findSeriesId(Context $context, string $identifier): ?int
    {
        $seriesList = Repo::section()->getCollector()
            ->filterByContextIds([$context->getId()])
            ->getMany();
        foreach ($seriesList as $series) {
            if ((string) $series->getData('path') === $identifier) {
                return $series->getId();
            }
        }
        return null;
    }
}
