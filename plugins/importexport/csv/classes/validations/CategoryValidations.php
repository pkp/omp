<?php

/**
 * @file plugins/importexport/csv/classes/validations/CategoryValidations.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CategoryValidations
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for processing publications.
 */

namespace APP\plugins\importexport\csv\classes\validations;

use APP\plugins\importexport\csv\classes\caches\CachedDaos;
use Illuminate\Support\Facades\DB;

class CategoryValidations
{
    /**
	 * Retrieves all category IDs by an array of category titles, a Press ID and
	 * a locale. If any of categories provided doesn't exist, the result will
	 * be false. If all categories are registered on the database, the method will
	 * return all Category database IDs and the CategoryDAO.
	 *
	 * @return ?int[] Null if not found
	 */
	public static function getCategoryDataForValidRow(string $categories, int $pressId, string $locale): ?array
    {
		$categoryDao = CachedDaos::getCategoryDao();
		$cachedCategories = [];

		$categoriesList = array_map('trim', explode(';', $categories));
		$dbCategoryIds = [];

		foreach($categoriesList as $categoryTitle) {
			$categoryCacheKey = "{$categoryTitle}_{$pressId}";
			$dbCategory = $cachedCategories[$categoryCacheKey] ??= (function() use ($categoryDao, $pressId, $locale, $categoryTitle) {
			    $result = DB::table($categoryDao->table)
			        ->join($categoryDao->settingsTable, $categoryDao->table . '.' . $categoryDao->primaryKeyColumn, '=', $categoryDao->settingsTable . '.' . $categoryDao->primaryKeyColumn)
			        ->where($categoryDao->settingsTable . '.setting_name', '=', 'title')
			        ->where($categoryDao->settingsTable . '.setting_value', '=', trim($categoryTitle))
			        ->where($categoryDao->settingsTable . '.locale', '=', $locale)
			        ->where($categoryDao->table . '.context_id', '=', $pressId)
			        ->first();
			    return $result ? $categoryDao->fromRow($result) : null;
			})();

			if (!is_null($dbCategory)) {
				$dbCategoryIds[] = $dbCategory->getId();
			}
		}

		$countsMatch = count($categoriesList) === count($dbCategoryIds);
		return $countsMatch ? $dbCategoryIds : null;
	}
}
