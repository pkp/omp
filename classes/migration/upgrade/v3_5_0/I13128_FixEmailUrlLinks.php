<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I13128_FixEmailUrlLinks.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I13128_FixEmailUrlLinks
 *
 * @brief Adds the replacements that only occur in the OMP translations
 *
 * See pkp/pkp-lib#13128.
 */

namespace APP\migration\upgrade\v3_5_0;

class I13128_FixEmailUrlLinks extends \PKP\migration\upgrade\v3_5_0\I13128_FixEmailUrlLinks
{
    public function up(): void
    {
        parent::up();

        $this->replace('INDEX_REQUEST', '{$contextUrl}', '<a href="{$contextUrl}">{$contextUrl}</a>', 'href="{$contextUrl}"');
        $this->replace('INDEX_REQUEST', '{$submissionUrl}', '<a href="{$submissionUrl}">{$submissionUrl}</a>', 'href="{$submissionUrl}"');
        $this->replace('REVIEW_RESPONSE_OVERDUE_AUTO', '{$reviewAssignmentUrl}', '<a href="{$reviewAssignmentUrl}">{$reviewAssignmentUrl}</a>', 'href="{$reviewAssignmentUrl}"');
    }

    public function down(): void
    {
        $this->replace('INDEX_REQUEST', '<a href="{$contextUrl}">{$contextUrl}</a>', '{$contextUrl}');
        $this->replace('INDEX_REQUEST', '<a href="{$submissionUrl}">{$submissionUrl}</a>', '{$submissionUrl}');
        $this->replace('REVIEW_RESPONSE_OVERDUE_AUTO', '<a href="{$reviewAssignmentUrl}">{$reviewAssignmentUrl}</a>', '{$reviewAssignmentUrl}');

        parent::down();
    }
}
