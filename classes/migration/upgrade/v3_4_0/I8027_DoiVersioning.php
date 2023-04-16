<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I8027_DoiVersioning.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I8027_DoiVersioning
 *
 * @brief Add new DOI versioning context setting
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Facades\DB;

class I8027_DoiVersioning extends \PKP\migration\Migration
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $pressIds = DB::table('presses')
            ->distinct()
            ->get(['press_id']);
        $insertStatements = $pressIds->reduce(function ($carry, $item) {
            $carry[] = [
                'press_id' => $item->press_id,
                'setting_name' => 'doiVersioning',
                'setting_value' => 0
            ];

            return $carry;
        }, []);

        DB::table('press_settings')
            ->insert($insertStatements);
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        DB::table('press_settings')
            ->where('setting_name', '=', 'doiVersioning')
            ->delete();
    }
}
