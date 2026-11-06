<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I13562_RepairWeblateUILocales.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I13562_RepairWeblateUILocales
 *
 * @brief Repair locales left behind or overwritten by I9707_WeblateUILocales.
 */

namespace APP\migration\upgrade\v3_5_0;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class I13562_RepairWeblateUILocales extends \PKP\migration\upgrade\v3_5_0\I13562_RepairWeblateUILocales
{
    protected function getContextTable(): string
    {
        return 'presses';
    }

    protected function getContextSettingsTable(): string
    {
        return 'press_settings';
    }

    protected function getContextIdColumn(): string
    {
        return 'press_id';
    }

    protected function getSubmissionLocaleTables(): array
    {
        return parent::getSubmissionLocaleTables() + [
            'submission_chapter_settings' => fn (Builder $query, int $submissionId) => $query->whereIn(
                'chapter_id',
                DB::table('submission_chapters')->select('chapter_id')->whereIn('publication_id', $this->getPublicationIds($submissionId))
            ),
            'publication_format_settings' => fn (Builder $query, int $submissionId) => $query->whereIn(
                'publication_format_id',
                DB::table('publication_formats')->select('publication_format_id')->whereIn('publication_id', $this->getPublicationIds($submissionId))
            ),
        ];
    }
}
