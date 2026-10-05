<?php

/**
 * @file classes/migration/upgrade/v3_6_0/I7527_IdentityMetadata.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7527_IdentityMetadata
 *
 * @brief Add the press-specific identity (publisher, publisher location, publisher code) to the
 *   stamped publication metadata.
 */

namespace APP\migration\upgrade\v3_6_0;

use Illuminate\Support\Facades\DB;

class I7527_IdentityMetadata extends \PKP\migration\upgrade\v3_6_0\I7527_IdentityMetadata
{
    /**
     * @copydoc \PKP\migration\upgrade\v3_6_0\I7527_IdentityMetadata::getIdentitySettings()
     *
     * Adds the publisher, the publisher location and the publisher code.
     */
    protected function getIdentitySettings(string $settingsTable, string $idColumn, int $contextId): array
    {
        $settings = parent::getIdentitySettings($settingsTable, $idColumn, $contextId);

        $rename = ['location' => 'publisherLocation'];
        $rows = DB::table($settingsTable)
            ->where($idColumn, $contextId)
            ->whereIn('setting_name', ['publisher', 'codeType', 'codeValue', 'location'])
            ->where('setting_value', '!=', '')
            ->pluck('setting_value', 'setting_name');

        foreach ($rows as $name => $value) {
            $settings[$rename[$name] ?? $name] = $value;
        }

        return $settings;
    }
}
