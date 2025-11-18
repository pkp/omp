<?php

/**
 * @file classes/migration/upgrade/v3_6_0/I857_ContributorRolesTypes.php
 *
 * Copyright (c) 2025 Simon Fraser University
 * Copyright (c) 2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I857_ContributorRolesTypes
 *
 * @brief OMP specific actions related to contributor roles and types
 */

namespace APP\migration\upgrade\v3_6_0;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use PKP\author\contributorRole\ContributorRoleIdentifier;
use PKP\install\DowngradeNotSupportedException;
use PKP\migration\upgrade\v3_6_0\I857_ContributorRolesTypes as PKP_I857_ContributorRolesTypes;

class I857_ContributorRolesTypes extends PKP_I857_ContributorRolesTypes
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Run parent
        parent::up();

        /**
         * Add editor role if isVolumeEditor is 1, and not set as editor already, i.e. is author
         */

        $roleIds = DB::table('contributor_roles')
            ->select(['contributor_role_id', 'context_id'])
            ->where('contributor_role_identifier', ContributorRoleIdentifier::EDITOR->getName())
            ->get()
            ->groupBy('context_id')
            ->map(fn ($rows): int => $rows->first()->contributor_role_id); // Take first editor role

        DB::table('credit_contributor_roles as ccr')
            ->select(['ccr.contributor_id', 'cr.context_id'])
            ->join('author_settings as as', function (JoinClause $join) {
                $join->on('as.author_id', 'ccr.contributor_id')
                    ->where('as.setting_name', '=', 'isVolumeEditor')
                    ->where('as.setting_value', '=', 1);
            })
            ->join('contributor_roles as cr', function (JoinClause $join) {
                $join->on('cr.contributor_role_id', 'ccr.contributor_role_id')
                    ->where('cr.contributor_role_identifier', '<>', ContributorRoleIdentifier::EDITOR->getName());
            })
            ->orderBy('ccr.contributor_id')
            ->chunk(1000, function ($chunk) use ($roleIds) {
                DB::table('credit_contributor_roles')
                    ->insert(
                        $chunk
                            ->filter(fn (\StdClass $row) => $roleIds->get($row->context_id)) // Remove rows when no editor role in context
                            ->map(
                                fn (\StdClass $row): array =>
                                ['contributor_id' => $row->contributor_id, 'contributor_role_id' => $roleIds->get($row->context_id)]
                            )
                            ->toArray()
                    );
            });

        // Remove isVolumeEditor from author settings
        DB::table('author_settings')
            ->select(['setting_name'])
            ->where('setting_name', '=', 'isVolumeEditor')
            ->delete();
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        throw new DowngradeNotSupportedException();
    }
}
