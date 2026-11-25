<?php

/**
 * @file plugins/importexport/csv/classes/caches/CachedDaos.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CachedDaos
 * @ingroup plugins_importexport_csv
 *
 * @brief Cached DAOs
 */

namespace APP\plugins\importexport\csv\classes\caches;

use APP\facades\Repo;
use APP\press\PressDAO;
use APP\publicationFormat\PublicationDateDAO;
use APP\publicationFormat\PublicationFormatDAO;
use APP\section\DAO as SectionDAO;
use APP\submission\DAO as SubmissionDAO;
use PKP\category\DAO as CategoryDAO;
use PKP\author\DAO as AuthorDAO;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\publication\DAO as PublicationDAO;
use PKP\submission\GenreDAO;
use PKP\submission\SubmissionKeywordDAO;
use PKP\submission\SubmissionSubjectDAO;
use PKP\submissionFile\DAO as SubmissionFileDAO;
use PKP\user\DAO as UserDAO;
use PKP\userGroup\DAO as UserGroupDAO;

class CachedDaos
{
    /**
     * @var DAO[] Array for caching already initialized DAOs.
     */
    private static array $daos = [];

    public static function getCategoryDao(): CategoryDAO
    {
        return self::$daos['CategoryDAO'] ??= Repo::category()->dao;
    }

    public static function getSubmissionDao(): SubmissionDAO
    {
        return self::$daos['SubmissionDAO'] ??= Repo::submission()->dao;
    }

    public static function getUserDao(): UserDAO
    {
        return self::$daos['UserDAO'] ??= Repo::user()->dao;
    }

    public static function getPressDao(): PressDAO
    {
        return self::$daos['PressDAO'] ??= DAORegistry::getDAO('PressDAO');
    }

    public static function getGenreDao(): GenreDAO
    {
        return self::$daos['GenreDAO'] ??= DAORegistry::getDAO('GenreDAO');
    }

    public static function getUserGroupDao(): UserGroupDAO
    {
        return self::$daos['UserGroupDAO'] ??= Repo::userGroup()->dao;
    }

    public static function getSeriesDao(): SectionDAO
    {
        return self::$daos['SeriesDAO'] ??= Repo::section()->dao;
    }

    public static function getPublicationDao(): PublicationDAO
    {
        return self::$daos['PublicationDAO'] ??= Repo::publication()->dao;
    }

    public static function getAuthorDao(): AuthorDAO
    {
        return self::$daos['AuthorDAO'] ??= Repo::author()->dao;
    }

    public static function getPublicationFormatDao(): PublicationFormatDAO
    {
        return self::$daos['PublicationFormatDAO'] ??= DAORegistry::getDAO('PublicationFormatDAO');
    }

    public static function getPublicationDateDao(): PublicationDateDAO
    {
        return self::$daos['PublicationDateDAO'] ??= DAORegistry::getDAO('PublicationDateDAO');
    }

    public static function getSubmissionFileDao(): SubmissionFileDAO
    {
        return self::$daos['SubmissionFileDAO'] ??= Repo::submissionFile()->dao;
    }

    public static function getSubmissionKeywordDao(): SubmissionKeywordDAO
    {
        return self::$daos['SubmissionKeywordDAO'] ??= DAORegistry::getDAO('SubmissionKeywordDAO');
    }

    public static function getSubmissionSubjectDao(): SubmissionSubjectDAO
    {
        return self::$daos['SubmissionSubjectDAO'] ??= DAORegistry::getDAO('SubmissionSubjectDAO');
    }
}
