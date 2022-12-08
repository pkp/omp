<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I7191_InstallSubmissionHelpDefaults.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I7191_InstallSubmissionHelpDefaults
 * @brief Migrate the submissionChecklist setting from an array to a HTML string
 */

namespace APP\migration\upgrade\v3_4_0;

class I7191_InstallSubmissionHelpDefaults extends \PKP\migration\upgrade\v3_4_0\I7191_InstallSubmissionHelpDefaults
{
    protected string $CONTEXT_TABLE = 'presses';
    protected string $CONTEXT_SETTINGS_TABLE = 'press_settings';
    protected string $CONTEXT_COLUMN = 'press_id';
}
