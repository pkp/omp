<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I8933_EventLogLocalized.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I8933_EventLogLocalized.php
 *
 * @brief Extends the event log migration with the correct table names for OJS.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class I8933_EventLogLocalized extends \PKP\migration\upgrade\v3_4_0\I8933_EventLogLocalized
{
    protected function getContextTable(): string
    {
        return 'presses';
    }

    protected function getContextIdColumn(): string
    {
        return 'press_id';
    }

    /**
     * Run the migration.
     */
    public function up(): void
    {
        parent::up();

        // Get contexts with their primary locales
        $contexts = DB::table('presses')->get(['press_id', 'primary_locale']);
        // Add site primary locale at the end of the collection; to be used when associated context (based on a submission) cannot be determined
        $contexts->push((object) [
            'press_id' => null,
            'primary_locale' => DB::table('site')->value('primary_locale')
        ]);
        /**
         * Update locale for localized settings by context primary locale.
         * All event types using settings that require update have submission assoc type,
         * we can join event logs with submissions table to get the context ID
         */
        foreach ($contexts as $context) {
            $idsToUpdate = DB::table('event_log as e')
                ->leftJoin('submissions as s', 'e.assoc_id', '=', 's.submission_id')
                ->where('assoc_type', 0x0100009) // PKPApplication::ASSOC_TYPE_SUBMISSION
                ->whereIn('event_type', [
                    268435475, // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_REMOVE
                    268435474, // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE
                    268435464, // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_PUBLISH
                    268435465, // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_UNPUBLISH
                    268435476, // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_AVAILABLE
                    268435477, // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_UNAVAILABLE
                ])
                ->whereIn('e.log_id', function (Builder $qb) {
                    $qb->select('es.log_id')
                        ->from('event_log_settings as es')
                        ->whereIn('setting_name', ['publicationFormatName', 'filename']);
                })
                ->where('s.context_id', $context->press_id)
                ->pluck('log_id');

            foreach ($idsToUpdate->chunk(parent::CHUNK_SIZE) as $ids) {
                DB::table('event_log_settings')->whereIn('log_id', $ids)->update(['locale' => $context->primary_locale]);
            }
        }
    }

    /**
     * Add setting to the map for renaming
     */
    protected function mapSettings(): Collection
    {
        $map = parent::mapSettings();
        $map->put(268435475, [ // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_REMOVE
            'formatName' => 'publicationFormatName'
        ]);
        $map->put(0x50000007, [ // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE
            'file' => 'filename',
            'name' => 'userFullName'
        ]);
        $map->put(268435474, [ // SubmissionEventLogEntry::SUBMISSION_LOG_PUBLICATION_FORMAT_CREATE
            'formatName' => 'publicationFormatName'
        ]);
        return $map;
    }
}
