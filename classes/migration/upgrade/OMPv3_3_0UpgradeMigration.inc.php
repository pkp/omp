<?php

/**
 * @file classes/migration/upgrade/OMPv3_3_0UpgradeMigration.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsMigration
 * @brief Describe database table structures.
 */

namespace APP\migration\upgrade;

use APP\core\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PKP\core\EntityDAO;
use PKP\services\PKPSchemaService;
use PKP\submission\SubmissionFile;

class OMPv3_3_0UpgradeMigration extends \PKP\migration\Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('press_settings', function (Blueprint $table) {
            // pkp/pkp-lib#6096 DB field type TEXT is cutting off long content
            $table->mediumText('setting_value')->nullable()->change();
        });
        if (!Schema::hasColumn('series', 'is_inactive')) {
            Schema::table('series', function (Blueprint $table) {
                $table->smallInteger('is_inactive')->default(0);
            });
        }
        Schema::table('review_forms', function (Blueprint $table) {
            $table->bigInteger('assoc_type')->nullable(false)->change();
            $table->bigInteger('assoc_id')->nullable(false)->change();
        });

        $this->_settingsAsJSON();

        $this->_migrateSubmissionFiles();

        // Delete the old MODS34 filters
        DB::statement("DELETE FROM filters WHERE class_name='plugins.metadata.mods34.filter.Mods34SchemaMonographAdapter'");
        DB::statement("DELETE FROM filter_groups WHERE symbolic IN ('monograph=>mods34', 'mods34=>monograph')");

        // pkp/pkp-lib#6604 ONIX filters still refer to Monograph rather than Submission
        DB::statement("UPDATE filter_groups SET input_type = 'class::classes.submission.Submission' WHERE input_type = 'class::classes.monograph.Monograph';");
        DB::statement("UPDATE filter_groups SET output_type = 'class::classes.submission.Submission[]' WHERE input_type = 'class::classes.monograph.Monograph[]';");

        // pkp/pkp-lib#6609 ONIX filters does not take array of submissions as input
        DB::statement("UPDATE filter_groups SET input_type = 'class::classes.submission.Submission[]' WHERE symbolic = 'monograph=>onix30-xml';");

        // pkp/pkp-lib#6807 Make sure all submission last modification dates are set
        DB::statement('UPDATE submissions SET last_modified = NOW() WHERE last_modified IS NULL');
    }

    /**
     * Reverse the downgrades
     */
    public function down()
    {
        Schema::table('press_settings', function (Blueprint $table) {
            // pkp/pkp-lib#6096 DB field type TEXT is cutting off long content
            $table->text('setting_value')->nullable()->change();
        });
    }

    /**
     * @brief reset serialized arrays and convert array and objects to JSON for serialization, see pkp/pkp-lib#5772
     */
    private function _settingsAsJSON()
    {

        // Convert settings where type can be retrieved from schema.json
        $schemaDAOs = [
            'SiteDAO',
            \PKP\announcement\DAO::class,
            \PKP\author\DAO::class,
            'PressDAO',
            \PKP\emailTemplate\DAO::class,
            \APP\publication\DAO::class,
            \APP\submission\DAO::class
        ];
        $processedTables = [];
        $application = Application::get();
        foreach ($schemaDAOs as $daoName) {
            $dao = null;
            if ($application->getQualifiedDAOName($daoName)) {
                $dao = DAORegistry::getDAO($daoName);
            }

            // Account for new EntityDAOs
            if (!$dao) {
                $dao = App::make($daoName);
                if (!$dao) {
                    throw new Exception("${daoName} could not be created when migrating serialized settings");
                }
            }
            $schemaService = Services::get('schema');

            if (is_a($dao, 'SchemaDAO')) {
                $schema = $schemaService->get($dao->schemaName);
                $tableName = $dao->settingsTableName;
            } elseif (is_a($dao, EntityDAO::class)) {
                $schema = $schemaService->get($dao->schema);
                $tableName = $dao->settingsTable;
            } elseif ($daoName === 'SiteDAO') {
                $schema = $schemaService->get(PKPSchemaService::SCHEMA_SITE);
                $tableName = 'site_settings';
            } else {
                continue; // if parent class changes, the table is processed with other settings tables
            }

            $processedTables[] = $tableName;
            foreach ($schema->properties as $propName => $propSchema) {
                if (empty($propSchema->readOnly)) {
                    if ($propSchema->type === 'array' || $propSchema->type === 'object') {
                        DB::table($tableName)->where('setting_name', $propName)->get()->each(function ($row) use ($tableName) {
                            $this->_toJSON($row, $tableName, ['setting_name', 'locale'], 'setting_value');
                        });
                    }
                }
            }
        }

        // Convert settings where only setting_type column is available
        $tables = DB::getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $tableName) {
            if (substr($tableName, -9) !== '_settings' || in_array($tableName, $processedTables)) {
                continue;
            }
            if ($tableName === 'plugin_settings') {
                DB::table($tableName)->where('setting_type', 'object')->get()->each(function ($row) use ($tableName) {
                    $this->_toJSON($row, $tableName, ['plugin_name', 'context_id', 'setting_name'], 'setting_value');
                });
            } else {
                DB::table($tableName)->where('setting_type', 'object')->get()->each(function ($row) use ($tableName) {
                    $this->_toJSON($row, $tableName, ['setting_name', 'locale'], 'setting_value');
                });
            }
        }

        // Finally, convert values of other tables dependent from DAO::convertToDB
        DB::table('review_form_responses')->where('response_type', 'object')->get()->each(function ($row) {
            $this->_toJSON($row, 'review_form_responses', ['review_id'], 'response_value');
        });

        DB::table('site')->get()->each(function ($row) {
            $localeToConvert = function ($localeType) use ($row) {
                $serializedValue = $row->{$localeType};
                if (@unserialize($serializedValue) === false) {
                    return;
                }
                $oldLocaleValue = unserialize($serializedValue);

                if (is_array($oldLocaleValue) && $this->_isNumerical($oldLocaleValue)) {
                    $oldLocaleValue = array_values($oldLocaleValue);
                }

                $newLocaleValue = json_encode($oldLocaleValue, JSON_UNESCAPED_UNICODE);
                DB::table('site')->take(1)->update([$localeType => $newLocaleValue]);
            };

            $localeToConvert('installed_locales');
            $localeToConvert('supported_locales');
        });
    }

    /**
     * @param $row stdClass row representation
     * @param $tableName string name of a settings table
     * @param $searchBy array additional parameters to the where clause that should be combined with AND operator
     * @param $valueToConvert string column name for values to convert to JSON
     */
    private function _toJSON($row, $tableName, $searchBy, $valueToConvert)
    {
        // Check if value can be unserialized
        $serializedOldValue = $row->{$valueToConvert};
        if (@unserialize($serializedOldValue) === false) {
            return;
        }
        $oldValue = unserialize($serializedOldValue);

        // Reset arrays to avoid keys being mixed up
        if (is_array($oldValue) && $this->_isNumerical($oldValue)) {
            $oldValue = array_values($oldValue);
        }
        $newValue = json_encode($oldValue, JSON_UNESCAPED_UNICODE); // don't convert utf-8 characters to unicode escaped code

        $id = array_key_first((array)$row); // get first/primary key column

        // Remove empty filters
        $searchBy = array_filter($searchBy, function ($item) use ($row) {
            if (empty($row->{$item})) {
                return false;
            }
            return true;
        });

        $queryBuilder = DB::table($tableName)->where($id, $row->{$id});
        foreach ($searchBy as $key => $column) {
            $queryBuilder = $queryBuilder->where($column, $row->{$column});
        }
        $queryBuilder->update([$valueToConvert => $newValue]);
    }

    /**
     * @param $array array to check
     *
     * @return bool
     * @brief checks unserialized array; returns true if array keys are integers
     * otherwise if keys are mixed and sequence starts from any positive integer it will be serialized as JSON object instead of an array
     * See pkp/pkp-lib#5690 for more details
     */
    private function _isNumerical($array)
    {
        foreach ($array as $item => $value) {
            if (!is_integer($item)) {
                return false;
            } // is an associative array;
        }

        return true;
    }

    /**
     * Complete submission file migrations specific to OMP
     *
     * The main submission file migration is done in
     * PKPv3_3_0UpgradeMigration and that migration must
     * be run before this one.
     */
    private function _migrateSubmissionFiles()
    {

        // Update file stage for all internal review files
        DB::table('submission_files as sf')
            ->leftJoin('review_round_files as rrf', 'sf.submission_file_id', '=', 'rrf.submission_file_id')
            ->where('sf.file_stage', '=', SubmissionFile::SUBMISSION_FILE_REVIEW_FILE)
            ->where('rrf.stage_id', '=', WORKFLOW_STAGE_ID_INTERNAL_REVIEW)
            ->update(['sf.file_stage' => SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_FILE]);
        DB::table('submission_files as sf')
            ->leftJoin('review_round_files as rrf', 'sf.submission_file_id', '=', 'rrf.submission_file_id')
            ->where('sf.file_stage', '=', SubmissionFile::SUBMISSION_FILE_REVIEW_REVISION)
            ->where('rrf.stage_id', '=', WORKFLOW_STAGE_ID_INTERNAL_REVIEW)
            ->update(['sf.file_stage' => SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION]);

        // Update the fileStage property for all event logs where the
        // file has been moved to an internal review file stage
        $internalStageIds = [
            SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_FILE,
            SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION,
        ];
        foreach ($internalStageIds as $internalStageId) {
            $submissionIds = DB::table('submission_files')
                ->where('file_stage', '=', $internalStageId)
                ->pluck('submission_file_id');
            $logIdsToChange = DB::table('event_log_settings')
                ->where('setting_name', '=', 'submissionFileId')
                ->whereIn('setting_value', $submissionIds)
                ->pluck('log_id');
            DB::table('event_log_settings')
                ->whereIn('log_id', $logIdsToChange)
                ->where('setting_name', '=', 'fileStage')
                ->update(['setting_value' => $internalStageId]);
        }
    }
}
