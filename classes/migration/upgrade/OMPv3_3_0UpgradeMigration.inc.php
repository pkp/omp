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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class OMPv3_3_0UpgradeMigration extends Migration {
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up() {
		Capsule::schema()->table('press_settings', function (Blueprint $table) {
			// pkp/pkp-lib#6096 DB field type TEXT is cutting off long content
			$table->mediumText('setting_value')->nullable()->change();
		});
		if (!Capsule::schema()->hasColumn('series', 'is_inactive')) {
			Capsule::schema()->table('series', function (Blueprint $table) {
				$table->smallInteger('is_inactive')->default(0);
			});
		}
		Capsule::schema()->table('review_forms', function (Blueprint $table) {
			$table->bigInteger('assoc_type')->nullable(false)->change();
			$table->bigInteger('assoc_id')->nullable(false)->change();
		});

		$this->_settingsAsJSON();

		$this->_migrateSubmissionFiles();

		// Delete the old MODS34 filters
		Capsule::statement("DELETE FROM filters WHERE class_name='plugins.metadata.mods34.filter.Mods34SchemaMonographAdapter'");
		Capsule::statement("DELETE FROM filter_groups WHERE symbolic IN ('monograph=>mods34', 'mods34=>monograph')");

		// pkp/pkp-lib#6604 ONIX filters still refer to Monograph rather than Submission
		Capsule::statement("UPDATE filter_groups SET input_type = 'class::classes.submission.Submission' WHERE input_type = 'class::classes.monograph.Monograph';");
		Capsule::statement("UPDATE filter_groups SET output_type = 'class::classes.submission.Submission[]' WHERE input_type = 'class::classes.monograph.Monograph[]';");

		// pkp/pkp-lib#6609 ONIX filters does not take array of submissions as input
		Capsule::statement("UPDATE filter_groups SET input_type = 'class::classes.submission.Submission[]' WHERE symbolic = 'monograph=>onix30-xml';");

		// pkp/pkp-lib#6807 Make sure all submission last modification dates are set
		Capsule::statement('UPDATE submissions SET last_modified = NOW() WHERE last_modified IS NULL');
	}

	/**
	 * Reverse the downgrades
	 * @return void
	 */
	public function down() {
		Capsule::schema()->table('press_settings', function (Blueprint $table) {
			// pkp/pkp-lib#6096 DB field type TEXT is cutting off long content
			$table->text('setting_value')->nullable()->change();
		});
	}

	/**
	 * @return void
	 * @brief reset serialized arrays and convert array and objects to JSON for serialization, see pkp/pkp-lib#5772
	 */
	private function _settingsAsJSON() {

		// Convert settings where type can be retrieved from schema.json
		$schemaDAOs = ['SiteDAO', 'AnnouncementDAO', 'AuthorDAO', 'PressDAO', 'EmailTemplateDAO', 'PublicationDAO', 'SubmissionDAO'];
		$processedTables = [];
		foreach ($schemaDAOs as $daoName) {
			$dao = DAORegistry::getDAO($daoName);
			$schemaService = Services::get('schema');

			if (is_a($dao, 'SchemaDAO')) {
				$schema = $schemaService->get($dao->schemaName);
				$tableName = $dao->settingsTableName;
			} else if ($daoName === 'SiteDAO') {
				$schema = $schemaService->get(SCHEMA_SITE);
				$tableName = 'site_settings';
			} else {
				continue; // if parent class changes, the table is processed with other settings tables
			}

			$processedTables[] = $tableName;
			foreach ($schema->properties as $propName => $propSchema) {
				if (empty($propSchema->readOnly)) {
					if ($propSchema->type === 'array' || $propSchema->type === 'object') {
						Capsule::table($tableName)->where('setting_name', $propName)->get()->each(function ($row) use ($tableName) {
							$this->_toJSON($row, $tableName, ['setting_name', 'locale'], 'setting_value');
						});
					}
				}
			}
		}

		// Convert settings where only setting_type column is available
		$tables = Capsule::connection()->getDoctrineSchemaManager()->listTableNames();
		foreach ($tables as $tableName) {
			if (substr($tableName, -9) !== '_settings' || in_array($tableName, $processedTables)) continue;
			if ($tableName === 'plugin_settings') {
				Capsule::table($tableName)->where('setting_type', 'object')->get()->each(function ($row) use ($tableName) {
					$this->_toJSON($row, $tableName, ['plugin_name', 'context_id', 'setting_name'], 'setting_value');
				});
			} elseif ($tableName == 'review_form_element_settings') {
				Capsule::table('review_form_element_settings')->where('setting_type', 'object')->get()->each(function ($row) {
					$this->_toJSON($row, 'review_form_element_settings', ['setting_name', 'locale', 'review_form_element_id'], 'setting_value');
				});
			} else {
				try {
					$settings = Capsule::table($tableName, 's')->where('setting_type', 'object')->get(['setting_name', 'setting_value', 's.*']);
				} catch (Exception $e) {
					error_log("Failed to migrate the settings entity \"{$tableName}\"\n" . $e);
					continue;
				}
				$settings->each(function ($row) use ($tableName) {
					$this->_toJSON($row, $tableName, ['setting_name', 'locale'], 'setting_value');
				});
			}
		}

		// Finally, convert values of other tables dependent from DAO::convertToDB
		Capsule::table('review_form_responses')->where('response_type', 'object')->get()->each(function ($row) {
			$this->_toJSON($row, 'review_form_responses', ['review_id'], 'response_value');
		});

		Capsule::table('site')->get()->each(function ($row) {
			$localeToConvert = function($localeType) use($row) {
				$serializedValue = $row->{$localeType};
				if (@unserialize($serializedValue) === false) return;
				$oldLocaleValue = unserialize($serializedValue);

				if (is_array($oldLocaleValue) && $this->_isNumerical($oldLocaleValue)) $oldLocaleValue = array_values($oldLocaleValue);

				$newLocaleValue = json_encode($oldLocaleValue, JSON_UNESCAPED_UNICODE);
				Capsule::table('site')->take(1)->update([$localeType => $newLocaleValue]);
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
	 * @return void
	 */
	private function _toJSON($row, $tableName, $searchBy, $valueToConvert) {
		// Check if value can be unserialized
		$serializedOldValue = $row->{$valueToConvert};
		if (@unserialize($serializedOldValue) === false) return;
		$oldValue = unserialize($serializedOldValue);

		// Reset arrays to avoid keys being mixed up
		if (is_array($oldValue) && $this->_isNumerical($oldValue)) $oldValue = array_values($oldValue);
		$newValue = json_encode($oldValue, JSON_UNESCAPED_UNICODE); // don't convert utf-8 characters to unicode escaped code

		// Ensure ID fields are included on the filter to avoid updating similar rows
		$tableDetails = Capsule::connection()->getDoctrineSchemaManager()->listTableDetails($tableName);
		$primaryKeys = [];
		try {
			$primaryKeys = $tableDetails->getPrimaryKeyColumns();
		} catch(Exception $e) {
			foreach ($tableDetails->getIndexes() as $index) {
					if($index->isPrimary() || $index->isUnique()) {
						$primaryKeys = $index->getColumns();
						break;
					}
				}
		}

		if (!count($primaryKeys)) {
			foreach (array_keys($row) as $column) {
				if (substr($column, -3, '_id')) {
					$primaryKeys[] = $column;
				}
			}
		}

		$searchBy = array_merge($searchBy, $primaryKeys);

		$queryBuilder = Capsule::table($tableName);
		foreach (array_unique($searchBy) as $column) {
			if ($row->{$column} !== null) {
				$queryBuilder->where($column, $row->{$column});
			} else {
				$queryBuilder->whereNull($column);
			}
		}
		$queryBuilder->update([$valueToConvert => $newValue]);
	}

	/**
	 * @param $array array to check
	 * @return bool
	 * @brief checks unserialized array; returns true if array keys are integers
	 * otherwise if keys are mixed and sequence starts from any positive integer it will be serialized as JSON object instead of an array
	 * See pkp/pkp-lib#5690 for more details
	 */
	private function _isNumerical($array) {
		foreach ($array as $item => $value) {
			if (!is_integer($item)) return false; // is an associative array;
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
	private function _migrateSubmissionFiles() {

		// Update file stage for all internal review files
		Capsule::table('submission_files as sf')
			->leftJoin('review_round_files as rrf', 'sf.submission_file_id', '=', 'rrf.submission_file_id')
			->where('sf.file_stage', '=', SUBMISSION_FILE_REVIEW_FILE)
			->where('rrf.stage_id', '=', WORKFLOW_STAGE_ID_INTERNAL_REVIEW)
			->update(['sf.file_stage' => SUBMISSION_FILE_INTERNAL_REVIEW_FILE]);
		Capsule::table('submission_files as sf')
			->leftJoin('review_round_files as rrf', 'sf.submission_file_id', '=', 'rrf.submission_file_id')
			->where('sf.file_stage', '=', SUBMISSION_FILE_REVIEW_REVISION)
			->where('rrf.stage_id', '=', WORKFLOW_STAGE_ID_INTERNAL_REVIEW)
			->update(['sf.file_stage' => SUBMISSION_FILE_INTERNAL_REVIEW_REVISION]);

		// Update the fileStage property for all event logs where the
		// file has been moved to an internal review file stage
		$internalStageIds = [
			SUBMISSION_FILE_INTERNAL_REVIEW_FILE,
			SUBMISSION_FILE_INTERNAL_REVIEW_REVISION,
		];
		foreach ($internalStageIds as $internalStageId) {
			$submissionIds = Capsule::table('submission_files')
				->where('file_stage', '=', $internalStageId)
				->pluck('submission_file_id');
			$logIdsToChange = Capsule::table('event_log_settings')
				->where('setting_name', '=', 'submissionFileId')
				->whereIn('setting_value', $submissionIds)
				->pluck('log_id');
			Capsule::table('event_log_settings')
				->whereIn('log_id', $logIdsToChange)
				->where('setting_name', '=', 'fileStage')
				->update(['setting_value' => $internalStageId]);
		}
	}
}
