<?php

/**
 * @file classes/migration/install/ReviewerRecommendationsMigration.php
 *
 * Copyright (c) 2025 Simon Fraser University
 * Copyright (c) 2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ReviewerRecommendationsMigration
 *
 * @brief Describe database table structures .
 */

namespace APP\migration\install;

class ReviewerRecommendationsMigration extends \PKP\migration\install\ReviewerRecommendationsMigration
{
    /**
     * @copydoc \PKP\migration\install\ReviewerRecommendationsMigration::contextTable()
     */
    public function contextTable(): string
    {
        return 'presses';
    }

    /**
     * @copydoc \PKP\migration\install\ReviewerRecommendationsMigration::settingTable()
     */
    public function settingTable(): string
    {
        return 'press_settings';
    }

    /**
     * @copydoc \PKP\migration\install\ReviewerRecommendationsMigration::contextPrimaryKey()
     */
    public function contextPrimaryKey(): string
    {
        return 'press_id';
    }
}
