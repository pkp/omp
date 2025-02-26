<?php

/**
 * @file plugins/importexport/csv/classes/caches/CachedEntities.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CachedEntities
 *
 * @ingroup plugins_importexport_csv
 *
 * @brief Cached entities
 */

namespace APP\plugins\importexport\csv\classes\caches;

use APP\facades\Repo;
use APP\press\Press;
use Exception;
use PKP\security\Role;
use PKP\user\User;

class CachedEntities
{
    private static array $presses = [];

    private static array $genreIds = [];

    private static array $userGroupIds = [];

    private static array $seriesIds = [];

    private static ?User $user = null;

    /**
     * Returns a cached Press or create a new one, if it isn't retrieved yet.
     */
    public static function getCachedPress(string $pressPath): ?Press
    {
        $pressDao = CachedDaos::getPressDao();
        return self::$presses[$pressPath] ??= $pressDao->getByPath($pressPath);
    }

    public static function getCachedGenreId(int $pressId, string $genreName): ?int
    {
        $customKey = "{$genreName}_{$pressId}";

        if (key_exists($customKey, self::$genreIds)) {
            return self::$genreIds[$customKey];
        }

        $genreDao = CachedDaos::getGenreDao();
        $genre = $genreDao->getByKey($genreName, $pressId);

        return self::$genreIds[$customKey] = $genre?->getId();
    }

    public static function getCachedUserGroupId(int $pressId, string $pressPath): ?int
    {
        return self::$userGroupIds[$pressPath] ??= Repo::userGroup()
            ->getArrayIdByRoleId(Role::ROLE_ID_AUTHOR, $pressId)[0] ?? null;
    }

    public static function getCachedSeriesId(string $seriesPath, int $pressId): ?int
    {
        $customKey = "{$seriesPath}_{$pressId}";

        if (self::$seriesIds[$customKey]) {
            return self::$seriesIds[$customKey];
        }

        $seriesDao = CachedDaos::getSeriesDao();
        $series = $seriesDao->getByPath($seriesPath, $pressId);

        return self::$seriesIds[$customKey] = $series?->getId();
    }

    public static function getCachedUser(?string $username = null): ?User
    {
        if (self::$user) {
            return self::$user;
        }

        if (!$username && !self::$user) {
            throw new Exception('User not found');
        }

        return self::$user = CachedDaos::getUserDao()->getByUsername($username);
    }
}
