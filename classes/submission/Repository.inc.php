<?php
/**
 * @file classes/submission/Repository.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class submission
 *
 * @brief A repository to find and manage submissions.
 */

namespace APP\submission;

class Repository extends \PKP\submission\Repository
{
    /** @copydoc \PKP\submission\Repository::$schemaMap */
    public $schemaMap = maps\Schema::class;

    /** @copydoc \PKP\submission\Repository::getSortSelectOptions() */
    public function getSortSelectOptions(): array
    {
        return array_merge(
            parent::getSortSelectOptions(),
            [
                $this->getSortOption(Collector::ORDERBY_SERIES_POSITION, Collector::ORDER_DIR_ASC) => __('catalog.sortBy.seriesPositionAsc'),
                $this->getSortOption(Collector::ORDERBY_SERIES_POSITION, Collector::ORDER_DIR_DESC) => __('catalog.sortBy.seriesPositionDesc'),
            ]
        );
    }
}
