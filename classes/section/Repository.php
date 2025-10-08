<?php
/**
 * @file classes/section/Repository.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to find and manage series.
 */

namespace APP\section;

use APP\facades\Repo;
use Illuminate\Support\Enumerable;

class Repository extends \PKP\section\Repository
{
    public string $schemaMap = maps\Schema::class;

    /** @copydoc DAO::addToCategory() */
    public function addToCategory(int $seriesId, int $categoryId): void
    {
        $this->dao->addToCategory($seriesId, $categoryId);
    }

    /** @copydoc DAO::removeFromCategory() */
    public function removeFromCategory(int $seriesId): void
    {
        $this->dao->removeFromCategory($seriesId);
    }

    /** @copydoc DAO::getAssignedCategoryIds() */
    public function getAssignedCategoryIds(int $seriesId): Enumerable
    {
        return $this->dao->getAssignedCategoryIds($seriesId);
    }

    /** @copydoc DAO::getAssignedCategories() */
    public function getAssignedCategories(int $seriesId, ?int $contextId = null): Enumerable
    {
        return $this->dao->getAssignedCategories($seriesId, $contextId);
    }

    /** @copydoc DAO::categoryAssociationExists() */
    public function categoryAssociationExists(int $seriesId, int $categoryId): bool
    {
        return $this->dao->categoryAssociationExists($seriesId, $categoryId);
    }

    /**
     * Check if the section has any submissions assigned to it.
     */
    public function isEmpty(int $seriesId, int $contextId): bool
    {
        return Repo::submission()
            ->getCollector()
            ->filterByContextIds([$contextId])
            ->filterBySeriesIds([$seriesId])
            ->getCount() === 0;
    }
}
