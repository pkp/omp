<?php

/**
 * @file classes/install/Upgrade.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Upgrade
 * @ingroup install
 *
 * @brief Perform system upgrade.
 */

namespace APP\install;

use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use Exception;
use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\file\FileManager;
use PKP\identity\Identity;
use PKP\install\Installer;

use PKP\submissionFile\SubmissionFile;

class Upgrade extends Installer
{
    protected $appEmailTemplateVariableNames = [
        'contextName' => 'pressName',
        'contextUrl' => 'pressUrl',
        'contextSignature' => 'pressSignature',
    ];

    /**
     * Constructor.
     *
     * @param array $params upgrade parameters
     */
    public function __construct($params, $installFile = 'upgrade.xml', $isPlugin = false)
    {
        parent::__construct($installFile, $params, $isPlugin);
    }


    /**
     * Returns true iff this is an upgrade process.
     *
     * @return bool
     */
    public function isUpgrade()
    {
        return true;
    }


    //
    // Specific upgrade actions
    //
    /**
     * If StaticPages table exists we should port the data as NMIs
     *
     * @return bool
     */
    public function migrateStaticPagesToNavigationMenuItems()
    {
        if ($this->tableExists('static_pages')) {
            $contextDao = Application::getContextDAO();
            $navigationMenuItemDao = DAORegistry::getDAO('NavigationMenuItemDAO'); /** @var NavigationMenuItemDAO $navigationMenuItemDao */

            $staticPagesDao = new \APP\plugins\generic\staticPages\classes\StaticPagesDAO();

            $contexts = $contextDao->getAll();
            while ($context = $contexts->next()) {
                $contextStaticPages = $staticPagesDao->getByContextId($context->getId())->toAssociativeArray();
                foreach ($contextStaticPages as $staticPage) {
                    $retNMIId = $navigationMenuItemDao->portStaticPage($staticPage);
                    if ($retNMIId) {
                        $staticPagesDao->deleteById($staticPage->getId());
                    } else {
                        error_log('WARNING: The StaticPage "' . $staticPage->getLocalizedTitle() . '" uses a path (' . $staticPage->getPath() . ') that conflicts with an existing Custom Navigation Menu Item path. Skipping this StaticPage.');
                    }
                }
            }
        }

        return true;
    }

    /**
     * Migrate first and last user names as multilingual into the DB table user_settings.
     *
     * @return bool
     */
    public function migrateUserAndAuthorNames()
    {
        // the user names will be saved in the site's primary locale
        DB::update("INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT u.user_id, s.primary_locale, ?, u.first_name, 'string' FROM users_tmp u, site s", [Identity::IDENTITY_SETTING_GIVENNAME]);
        DB::update("INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT u.user_id, s.primary_locale, ?, u.last_name, 'string' FROM users_tmp u, site s", [Identity::IDENTITY_SETTING_FAMILYNAME]);
        // the author names will be saved in the submission's primary locale
        DB::update("INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT a.author_id, s.locale, ?, a.first_name, 'string' FROM authors_tmp a, submissions s WHERE s.submission_id = a.submission_id", [Identity::IDENTITY_SETTING_GIVENNAME]);
        DB::update("INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT a.author_id, s.locale, ?, a.last_name, 'string' FROM authors_tmp a, submissions s WHERE s.submission_id = a.submission_id", [Identity::IDENTITY_SETTING_FAMILYNAME]);

        // middle name will be migrated to the given name
        // note that given names are already migrated to the settings table
        switch (Config::getVar('database', 'driver')) {
            case 'mysql':
            case 'mysqli':
                // the alias for _settings table cannot be used for some reason -- syntax error
                DB::update("UPDATE user_settings, users_tmp u SET user_settings.setting_value = CONCAT(user_settings.setting_value, ' ', u.middle_name) WHERE user_settings.setting_name = ? AND u.user_id = user_settings.user_id AND u.middle_name IS NOT NULL AND u.middle_name <> ''", [Identity::IDENTITY_SETTING_GIVENNAME]);
                DB::update("UPDATE author_settings, authors_tmp a SET author_settings.setting_value = CONCAT(author_settings.setting_value, ' ', a.middle_name) WHERE author_settings.setting_name = ? AND a.author_id = author_settings.author_id AND a.middle_name IS NOT NULL AND a.middle_name <> ''", [Identity::IDENTITY_SETTING_GIVENNAME]);
                break;
            case 'postgres':
            case 'postgres64':
            case 'postgres7':
            case 'postgres8':
            case 'postgres9':
                DB::update("UPDATE user_settings SET setting_value = CONCAT(setting_value, ' ', u.middle_name) FROM users_tmp u WHERE user_settings.setting_name = ? AND u.user_id = user_settings.user_id AND u.middle_name IS NOT NULL AND u.middle_name <> ''", [Identity::IDENTITY_SETTING_GIVENNAME]);
                DB::update("UPDATE author_settings SET setting_value = CONCAT(setting_value, ' ', a.middle_name) FROM authors_tmp a WHERE author_settings.setting_name = ? AND a.author_id = author_settings.author_id AND a.middle_name IS NOT NULL AND a.middle_name <> ''", [Identity::IDENTITY_SETTING_GIVENNAME]);
                break;
            default: throw new Exception('Unknown database type!');
        }

        // salutation and suffix will be migrated to the preferred public name
        // user preferred public names will be inserted for each supported site locales
        $siteDao = DAORegistry::getDAO('SiteDAO'); /** @var SiteDAO $siteDao */
        $site = $siteDao->getSite();
        $supportedLocales = $site->getSupportedLocales();
        $userResult = DB::select(
            "SELECT user_id, first_name, last_name, middle_name, salutation, suffix FROM users_tmp
            WHERE (salutation IS NOT NULL AND salutation <> '') OR
                (suffix IS NOT NULL AND suffix <> '')"
        );
        foreach ($userResult as $row) {
            $userId = $row->user_id;
            $firstName = $row->first_name;
            $lastName = $row->last_name;
            $middleName = $row->middle_name;
            $salutation = $row->salutation;
            $suffix = $row->suffix;
            foreach ($supportedLocales as $siteLocale) {
                $preferredPublicName = ($salutation != '' ? "{$salutation} " : '') . "{$firstName} " . ($middleName != '' ? "{$middleName} " : '') . $lastName . ($suffix != '' ? ", {$suffix}" : '');
                DB::update(
                    "INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) VALUES (?, ?, 'preferredPublicName', ?, 'string')",
                    [(int) $userId, $siteLocale, $preferredPublicName]
                );
            }
        }

        // author suffix will be migrated to the author preferred public name
        // author preferred public names will be inserted for each press supported locale
        // get supported locales for the press (there should actually be only one press)
        $pressDao = DAORegistry::getDAO('PressDAO'); /** @var PressDAO $pressDao */
        $presses = $pressDao->getAll();
        $pressessSupportedLocales = [];
        while ($press = $presses->next()) {
            $pressessSupportedLocales[$press->getId()] = $press->getSupportedLocales();
        }
        // get all authors with a suffix
        $authorResult = DB::select(
            "SELECT a.author_id, a.first_name, a.last_name, a.middle_name, a.suffix, p.press_id FROM authors_tmp a
            LEFT JOIN submissions s ON (s.submission_id = a.submission_id)
            LEFT JOIN presses p ON (p.press_id = s.context_id)
            WHERE suffix IS NOT NULL AND suffix <> ''"
        );
        foreach ($authorResult as $row) {
            $authorId = $row->author_id;
            $firstName = $row->first_name;
            $lastName = $row->last_name;
            $middleName = $row->middle_name;
            $suffix = $row->suffix;
            $pressId = $row->press_id;
            $supportedLocales = $pressessSupportedLocales[$pressId];
            foreach ($supportedLocales as $locale) {
                $preferredPublicName = "{$firstName} " . ($middleName != '' ? "{$middleName} " : '') . $lastName . ($suffix != '' ? ", {$suffix}" : '');
                DB::update(
                    "INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) VALUES (?, ?, 'preferredPublicName', ?, 'string')",
                    [(int) $authorId, $locale, $preferredPublicName]
                );
            }
        }

        // remove temporary table
        $siteDao->update('DROP TABLE users_tmp');
        $siteDao->update('DROP TABLE authors_tmp');
        return true;
    }

    /**
     * Update permit_metadata_edit and can_change_metadata for user_groups and stage_assignments tables.
     *
     * @return bool True indicates success.
     */
    public function changeUserRolesAndStageAssignmentsForStagePermitSubmissionEdit()
    {
        $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO'); /** @var StageAssignmentDAO $stageAssignmentDao */

        $roles = Repo::userGroup()::NOT_CHANGE_METADATA_EDIT_PERMISSION_ROLES;
        $roleString = '(' . implode(',', $roles) . ')';

        DB::table('user_groups')
            ->whereIn('role_id', $roles)
            ->update(['permit_metadata_edit' => 1]);

        switch (Config::getVar('database', 'driver')) {
            case 'mysql':
            case 'mysqli':
                $stageAssignmentDao->update('UPDATE stage_assignments sa JOIN user_groups ug on sa.user_group_id = ug.user_group_id SET sa.can_change_metadata = 1 WHERE ug.role_id IN ' . $roleString);
                break;
            case 'postgres':
            case 'postgres64':
            case 'postgres7':
            case 'postgres8':
            case 'postgres9':
                $stageAssignmentDao->update('UPDATE stage_assignments sa SET can_change_metadata=1 FROM user_groups ug WHERE sa.user_group_id = ug.user_group_id AND ug.role_id IN ' . $roleString);
                break;
            default: throw new Exception('Unknown database type!');
        }

        return true;
    }

    /**
     * Update how submission cover images are stored
     *
     * 1. Move the cover images into /public and rename them.
     *
     * 2. Change the coverImage setting to a multilingual setting
     *    stored under the submission_settings table, which will
     *    be migrated to the publication_settings table once it
     *    is created.
     */
    public function migrateSubmissionCoverImages()
    {
        $fileManager = new FileManager();
        $publicFileManager = new PublicFileManager();
        $contexts = [];

        $result = Repo::submission()->dao->deprecatedDao->retrieve(
            'SELECT	ps.submission_id as submission_id,
                ps.cover_image as cover_image,
                s.context_id as context_id
            FROM	published_submissions ps
            LEFT JOIN	submissions s ON (s.submission_id = ps.submission_id)'
        );
        foreach ($result as $row) {
            if (empty($row->cover_image)) {
                continue;
            }
            $coverImage = unserialize($row->cover_image);
            if (empty($coverImage)) {
                continue;
            }

            if (!isset($contexts[$row->context_id])) {
                $contexts[$row->context_id] = Services::get('context')->get($row->context_id);
            };
            $context = $contexts[$row->context_id];

            // Get existing image paths
            $basePath = Repo::submissionFile()
                ->getSubmissionDir($row->context_id, $row->submission_id);
            $coverPath = Config::getVar('files', 'files_dir') . '/' . $basePath . '/simple/' . $coverImage['name'];
            $coverPathInfo = pathinfo($coverPath);
            $thumbPath = Config::getVar('files', 'files_dir') . '/' . $basePath . '/simple/' . $coverImage['thumbnailName'];
            $thumbPathInfo = pathinfo($thumbPath);

            // Copy the files to the public directory
            $newCoverPath = join('_', [
                'submission',
                $row->submission_id,
                $row->submission_id,
                'coverImage',
            ]) . '.' . $coverPathInfo['extension'];
            $publicFileManager->copyContextFile(
                $row->context_id,
                $coverPath,
                $newCoverPath
            );
            $newThumbPath = join('_', [
                'submission',
                $row->submission_id,
                $row->submission_id,
                'coverImage',
                't'
            ]) . '.' . $thumbPathInfo['extension'];
            $publicFileManager->copyContextFile(
                $row->context_id,
                $thumbPath,
                $newThumbPath
            );

            // Create a submission_settings entry for each locale
            if (isset($context)) {
                foreach ($context->getSupportedFormLocales() as $localeKey) {
                    $newCoverPathInfo = pathinfo($newCoverPath);
                    Repo::submission()->dao->deprecatedDao->update(
                        'INSERT INTO submission_settings (submission_id, setting_name, setting_value, setting_type, locale)
                        VALUES (?, ?, ?, ?, ?)',
                        [
                            $row->submission_id,
                            'coverImage',
                            serialize([
                                'uploadName' => $newCoverPathInfo['basename'],
                                'dateUploaded' => $coverImage['dateUploaded'],
                                'altText' => '',
                            ]),
                            'object',
                            $localeKey,
                        ]
                    );
                }
            }

            // Delete the old images
            $fileManager->deleteByPath($coverPath);
            $fileManager->deleteByPath($thumbPath);
        }

        return true;
    }

    /**
     * Get the directory of a file based on its file stage
     *
     * @param int $fileStage One of SubmissionFile::SUBMISSION_FILE_ constants
     *
     * @return string
     */
    public function _fileStageToPath($fileStage)
    {
        static $fileStagePathMap = [
            SubmissionFile::SUBMISSION_FILE_SUBMISSION => 'submission',
            SubmissionFile::SUBMISSION_FILE_NOTE => 'note',
            SubmissionFile::SUBMISSION_FILE_REVIEW_FILE => 'submission/review',
            SubmissionFile::SUBMISSION_FILE_REVIEW_ATTACHMENT => 'submission/review/attachment',
            SubmissionFile::SUBMISSION_FILE_REVIEW_REVISION => 'submission/review/revision',
            SubmissionFile::SUBMISSION_FILE_FINAL => 'submission/final',
            SubmissionFile::SUBMISSION_FILE_COPYEDIT => 'submission/copyedit',
            SubmissionFile::SUBMISSION_FILE_DEPENDENT => 'submission/proof',
            SubmissionFile::SUBMISSION_FILE_PROOF => 'submission/proof',
            SubmissionFile::SUBMISSION_FILE_PRODUCTION_READY => 'submission/productionReady',
            SubmissionFile::SUBMISSION_FILE_ATTACHMENT => 'attachment',
            SubmissionFile::SUBMISSION_FILE_QUERY => 'submission/query',
        ];

        if (!isset($fileStagePathMap[$fileStage])) {
            throw new Exception('A file assigned to the file stage ' . $fileStage . ' could not be migrated.');
        }

        return $fileStagePathMap[$fileStage];
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\install\Upgrade', '\Upgrade');
}
