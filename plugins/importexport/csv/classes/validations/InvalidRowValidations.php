<?php

/**
 * @file plugins/importexport/csv/classes/validations/InvalidRowValidations.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class InvalidRowValidations
 * @ingroup plugins_importexport_csv
 *
 * @brief A class with static methods for validating invalid rows.
 */

namespace APP\plugins\importexport\csv\classes\validations;

use APP\press\Press;

class InvalidRowValidations
{
    /** @var string[] */
    public static array $coverImageAllowedTypes = ['gif', 'jpg', 'png', 'webp'];

    /**
     * Validates whether the CSV row contains all fields. Returns the reason if an error occurred,
     * or null if everything is correct.
     */
    public static function validateRowContainAllFields(array $fields): ?string
    {
        return count($fields) < count(SubmissionHeadersValidation::$expectedHeaders)
            ? __('plugins.importexport.csv.csvDoesntContainAllFields')
            : null;
    }

    /**
     * Validates whether the CSV row contains all required fields. Returns the reason if an error occurred,
     * or null if everything is correct.
     */
    public static function validateRowHasAllRequiredFields(object $data): ?string
    {
        foreach (SubmissionHeadersValidation::$requiredHeaders as $requiredHeader) {
            if (!$data->{$requiredHeader}) {
                return __('plugins.importexport.csv.requiredFieldsMissing');
            }
        }

        return null;
    }

    public static function validatePresIsValid(string $pressPath, ?Press $press = null): ?string
    {
        return !$press
            ? __('plugins.importexport.csv.unknownPress', ['contextPath' => $pressPath])
            : null;
    }

    public static function validatePressLocales(string $locale, Press $press): ?string
    {
        $supportedLocales = $press->getSupportedSubmissionLocales();
        if (!is_array($supportedLocales) || count($supportedLocales) < 1) {
            $supportedLocales = [$press->getPrimaryLocale()];
        }

        return !in_array($locale, $supportedLocales)
            ? __('plugins.importexport.csv.unknownLocale', ['locale' => $locale])
            : null;
    }

    public static function validateGenreIsValid(int $genreId, string $genreName): ?string
    {
        return !$genreId
            ? __('plugins.importexport.csv.noGenre', ['manuscript' => $genreName])
            : null;
    }

    public static function validateUserGroupId(?int $userGroupId, string $pressPath): ?string
    {
        return !$userGroupId
            ? __('plugins.importexport.csv.noAuthorGroup', ['press' => $pressPath])
            : null;
    }

    public static function validateAssetFile(string $filePath, string $title): ?string
    {
        return !file_exists($filePath)
            ? __('plugins.importexport.csv.invalidAssetFilename', ['title' => $title])
            : null;
    }

    public static function validateSeriesId(int $seriesId, string $seriesPath): ?string
    {
        return !$seriesId
            ? __('plugin.importexport.csv.seriesPathNotFound', ['seriesPath' => $seriesPath])
            : null;
    }

    public static function validateBookCoverImageInRightFormat(string $bookCoverImage): ?string
    {
        $coverImgExtension = pathinfo(mb_strtolower($bookCoverImage), PATHINFO_EXTENSION);
        return !in_array($coverImgExtension, self::$coverImageAllowedTypes)
            ? __('plugins.importexport.common.error.invalidFileExtension')
            : null;
    }

    public static function validateBookCoverImageIsReadable(string $srcFilePath, string $title): ?string
    {
        return !is_readable($srcFilePath)
            ? __('plugins.importexport.csv.invalidCoverImageFilename', ['title' => $title])
            : null;
    }

    public static function validateAllCategoriesExists(?array $categories): ?string
    {
        return !$categories
            ? __('plugins.importexport.csv.allCategoriesMustExists')
            : null;
    }
}
