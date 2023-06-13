<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I8333_AddMissingForeignKeys.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I8333_AddMissingForeignKeys
 *
 * @brief Upgrade/downgrade operations for introducing foreign key definitions to existing database relationships.
 */

namespace APP\migration\upgrade\v3_5_0;

class I8333_AddMissingForeignKeys extends \PKP\migration\upgrade\v3_5_0\I8333_AddMissingForeignKeys
{
    protected function getContextTable(): string
    {
        return 'presses';
    }

    protected function getContextKeyField(): string
    {
        return 'press_id';
    }

    protected function getContextSettingsTable(): string
    {
        return 'press_settings';
    }
}
