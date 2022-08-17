<?php

/**
 * @file classes/migration/upgrade/v3_4_0/I6091_AddFilterNamespaces.inc.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I6091_AddFilterNamespaces
 * @brief Describe upgrade/downgrade operations for introducing namespaces to the built-in set of filters.
 */

namespace APP\migration\upgrade\v3_4_0;

use Illuminate\Support\Facades\DB;

class I6091_AddFilterNamespaces extends \PKP\migration\Migration
{
    public const FILTER_RENAME_MAP = [
        // Application filters
        'plugins.metadata.dc11.filter.Dc11SchemaPublicationFormatAdapter' => 'APP\plugins\metadata\dc11\filter\Dc11SchemaPublicationFormatAdapter',
        'plugins.importexport.native.filter.MonographNativeXmlFilter' => 'APP\plugins\importexport\native\filter\MonographNativeXmlFilter',
        'plugins.importexport.native.filter.NativeXmlMonographFilter' => 'APP\plugins\importexport\native\filter\NativeXmlMonographFilter',
        'plugins.importexport.native.filter.AuthorNativeXmlFilter' => 'APP\plugins\importexport\native\filter\AuthorNativeXmlFilter',
        'plugins.importexport.native.filter.NativeXmlAuthorFilter' => 'APP\plugins\importexport\native\filter\NativeXmlAuthorFilter',
        'plugins.importexport.native.filter.PublicationFormatNativeXmlFilter' => 'APP\plugins\importexport\native\filter\PublicationFormatNativeXmlFilter',
        'plugins.importexport.native.filter.NativeXmlPublicationFormatFilter' => 'APP\plugins\importexport\native\filter\NativeXmlPublicationFormatFilter',
        'plugins.importexport.native.filter.NativeXmlMonographFileFilter' => 'APP\plugins\importexport\native\filter\NativeXmlMonographFileFilter',
        'plugins.importexport.onix30.filter.MonographONIX30XmlFilter' => 'APP\plugins\importexport\native\filter\MonographONIX30XmlFilter',
        'plugins.importexport.native.filter.PublicationNativeXmlFilter' => 'APP\plugins\importexport\native\filter\PublicationNativeXmlFilter',
        'plugins.importexport.native.filter.NativeXmlPublicationFilter' => 'APP\plugins\importexport\native\filter\NativeXmlPublicationFilter',
        'plugins.importexport.native.filter.ChapterNativeXmlFilter' => 'APP\plugins\importexport\native\filter\ChapterNativeXmlFilter',
        'plugins.importexport.native.filter.NativeXmlChapterFilter' => 'APP\plugins\importexport\native\filter\NativeXmlChapterFilter',
        'plugins.importexport.onix30.filter.MonographONIX30XmlFilter' => 'APP\plugins\importexport\onix30\filter\MonographONIX30XmlFilter',

        // pkp-lib filters
        'lib.pkp.plugins.importexport.users.filter.PKPUserUserXmlFilter' => 'PKP\plugins\importexport\users\filter\PKPUserUserXmlFilter',
        'lib.pkp.plugins.importexport.users.filter.UserXmlPKPUserFilter' => 'PKP\plugins\importexport\users\filter\UserXmlPKPUserFilter',
        'lib.pkp.plugins.importexport.users.filter.UserGroupNativeXmlFilter' => 'PKP\plugins\importexport\users\filter\UserGroupNativeXmlFilter',
        'lib.pkp.plugins.importexport.users.filter.NativeXmlUserGroupFilter' => 'PKP\plugins\importexport\users\filter\NativeXmlUserGroupFilter',
        'lib.pkp.plugins.importexport.native.filter.SubmissionFileNativeXmlFilter' => 'PKP\plugins\importexport\native\filter\SubmissionFileNativeXmlFilter',
    ];

    /**
     * Run the migration.
     */
    public function up(): void
    {
        foreach (self::FILTER_RENAME_MAP as $oldName => $newName) {
            DB::statement('UPDATE filters SET class_name = ? WHERE class_name = ?', [$newName, $oldName]);
        }
        DB::statement('UPDATE filter_groups SET output_type=? WHERE output_type = ?', ['metadata::\APP\plugins\metadata\dc11\schema\Dc11Schema(PUBLICATION_FORMAT)', 'metadata::plugins.metadata.dc11.schema.Dc11Schema(PUBLICATION_FORMAT)']);
    }

    /**
     * Reverse the downgrades
     */
    public function down(): void
    {
        foreach (self::FILTER_RENAME_MAP as $oldName => $newName) {
            DB::statement('UPDATE filters SET class_name = ? WHERE class_name = ?', [$oldName, $newName]);
        }
        DB::statement('UPDATE filter_groups SET output_type=? WHERE output_type = ?', ['metadata::plugins.metadata.dc11.schema.Dc11Schema(PUBLICATION_FORMAT)', 'metadata::\APP\plugins\metadata\dc11\schema\Dc11Schema(PUBLICATION_FORMAT)']);
    }
}
