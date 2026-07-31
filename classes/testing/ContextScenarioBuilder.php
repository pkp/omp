<?php

/**
 * @file classes/testing/ContextScenarioBuilder.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ContextScenarioBuilder
 *
 * @brief OMP scratch-press scenario (note: OMP's Context::add hook creates NO
 * default series; user series assignments resolve by path).
 */

namespace APP\testing;

use PKP\context\Context;
use PKP\testing\scenario\PKPContextScenarioBuilder;

class ContextScenarioBuilder extends PKPContextScenarioBuilder
{
    protected function structureKey(): string
    {
        return 'series';
    }

    protected function resolveStructureId(Context $context, string $identifier): ?int
    {
        return BootstrapSeeder::findSeriesId($context, $identifier);
    }
}
