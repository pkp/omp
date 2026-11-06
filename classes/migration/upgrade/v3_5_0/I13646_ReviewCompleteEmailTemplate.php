<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I13646_ReviewCompleteEmailTemplate.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I13646_ReviewCompleteEmailTemplate
 *
 * @brief Reinstall the default review complete email, which no longer names a reviewer recommendation
 */

namespace APP\migration\upgrade\v3_5_0;

use APP\facades\Repo;
use PKP\install\DowngradeNotSupportedException;
use PKP\migration\Migration;

class I13646_ReviewCompleteEmailTemplate extends Migration
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        Repo::emailTemplate()->dao->installEmailTemplates(
            Repo::emailTemplate()->dao->getMainEmailTemplatesFilename(),
            [],
            'REVIEW_COMPLETE',
        );
    }

    /**
     * @inheritDoc
     *
     * @throws DowngradeNotSupportedException
     */
    public function down(): void
    {
        throw new DowngradeNotSupportedException();
    }
}
