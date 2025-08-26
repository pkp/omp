<?php

/**
 * @file classes/section/DAO.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DAO
 *
 * @ingroup section
 *
 * @see Section
 *
 * @brief Operations for retrieving and modifying series (Section objects).
 */

namespace APP\section;

use APP\facades\Repo;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use PKP\services\PKPSchemaService;

class DAO extends \PKP\section\DAO
{
    /** @copydoc EntityDAO::$schema */
    public $schema = PKPSchemaService::SCHEMA_SECTION;

    /** @copydoc EntityDAO::$table */
    public $table = 'series';

    /** @copydoc EntityDAO::$settingsTable */
    public $settingsTable = 'series_settings';

    /** @copydoc EntityDAO::$primaryKeyColumn */
    public $primaryKeyColumn = 'series_id';

    /** @copydoc EntityDAO::$primaryTableColumns */
    public $primaryTableColumns = [
        'id' => 'series_id',
        'contextId' => 'press_id',
        'reviewFormId' => 'review_form_id',
        'sequence' => 'seq',
        'featured' => 'featured',
        'editorRestricted' => 'editor_restricted',
        'urlPath' => 'url_path',
        'image' => 'image',
        'isInactive' => 'is_inactive'
    ];

    /**
     * Get the parent object ID column name
     */
    public function getParentColumn(): string
    {
        return 'press_id';
    }

    /**
     * Associate a category with a series.
     */
    public function addToCategory(int $seriesId, int $categoryId): void
    {
        DB::table('series_categories')
            ->insert(['series_id' => $seriesId, 'category_id' => $categoryId]);
    }

    /**
     * Disassociate all categories with a series
     */
    public function removeFromCategory(int $seriesId): void
    {
        DB::table('series_categories')
            ->where('series_id', $seriesId)
            ->delete();
    }

    /**
     * Get the category IDs associated with a series
     */
    public function getAssignedCategoryIds(int $seriesId): Enumerable
    {
        return DB::table('series_categories')
            ->where('series_id', $seriesId)
            ->pluck('category_id');
    }

    /**
     * Get the categories associated with a series.
     */
    public function getAssignedCategories(int $seriesId, ?int $contextId = null): Enumerable
    {
        return $this
            ->getAssignedCategoryIds($seriesId)
            ->map(fn ($categoryId) => Repo::category()->get($categoryId, $contextId));
    }

    /**
     * Check if an association between a series and a category exists.
     */
    public function categoryAssociationExists(int $seriesId, int $categoryId): bool
    {
        return DB::table('series_categories')
            ->where('series_id', $seriesId)
            ->where('category_id', $categoryId)
            ->exists();
    }
}
