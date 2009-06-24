<?php

/**
 * @file classes/monograph/MonographFileSettingsDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileSettingsDAO
 * @ingroup monograph
 *
 * @brief Operations for retrieving and modifying monograph file settings.
 */

// $Id$


class MonographFileSettingsDAO extends DAO {

	/**
	 * Retrieve all settings for a monograph file.
	 * @param $fileId int
	 * @return array
	 */
	function &getSettingsByFileId($fileId, $revision = null) {
		$monographFileSettings = array();

		if ($revision !== null) {
			$result =& $this->retrieve(
				'SELECT setting_name, setting_value, setting_type, locale 
				FROM monograph_file_settings 
				WHERE file_id = ? AND revision = ?', array($fileId, $revision)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT setting_name, setting_value, setting_type, locale 
				FROM monograph_file_settings 
				WHERE file_id = ?', $fileId
			);
		}

		while (!$result->EOF) {
			$row =& $result->getRowAssoc(false);
			$value = $this->convertFromDB($row['setting_value'], $row['setting_type']);
			if ($row['locale'] == '') $monographFileSettings[$row['setting_name']] = $value;
			else $monographFileSettings[$row['setting_name']][$row['locale']] = $value;
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $monographFileSettings;
	}

	/**
	 * Add/update a monograph file setting.
	 * @param $fileId int
	 * @param $revision int
	 * @param $name string
	 * @param $value mixed
	 * @param $type string data type of the setting. If omitted, type will be guessed
	 * @param $isLocalized boolean
	 */
	function updateSetting($fileId, $revision = null, $name, $value, $type = null, $isLocalized = false) {
		$keyFields = array('setting_name', 'locale', 'file_id');
		
		if (!$isLocalized) {
			$value = $this->convertToDB($value, $type);
			$settings = array(
				'file_id' => $fileId,
				'setting_name' => $name,
				'setting_value' => $value,
				'setting_type' => $type,
				'locale' => ''
			);

			if ($revision !== null) {
				$settings['revision'] = $revision;
			}

			$this->replace('monograph_file_settings', $settings, $keyFields);

		} else {
			if (is_array($value)) foreach ($value as $locale => $localeValue) {
				$sqlParams = array($fileId, $name, $locale);
				$sql = 'DELETE FROM monograph_file_settings WHERE file_id = ? AND setting_name = ? AND locale = ?';
				if ($revision !== null) {
					$sqlParams[] = $revision;
					$sql .= ' AND revision = ?';
				}
				$this->update($sql, $sqlParams);
				if (empty($localeValue)) continue;
				$type = null;
				$this->update('INSERT INTO monograph_file_settings
					(file_id, revision, setting_name, setting_value, setting_type, locale)
					VALUES (?, ?, ?, ?, ?, ?)',
					array(
						$fileId, $revision, $name, $this->convertToDB($localeValue, $type), $type, $locale
					)
				);
			}
		}
	}

	/**
	 * Delete a monograph file setting.
	 * @param $fileId int
	 * @param $name string
	 */
	function deleteSetting($fileId, $revision = null, $name, $locale = null) {
		$params = array($fileId, $name);
		$sql = 'DELETE FROM monograph_file_settings WHERE file_id = ? AND setting_name = ?';

		if ($revision !== null) {
			$params[] = $revision;
			$sql .= ' AND revision = ?';
		}

		if ($locale !== null) {
			$params[] = $locale;
			$sql .= ' AND locale = ?';
		}

		return $this->update($sql, $params);
	}

	/**
	 * Delete all settings for a monograph file.
	 * @param $fileId int
	 */
	function deleteSettingsByFileId($fileId, $revision = null) {
		if ($revision !== null) {
			return $this->update(
				'DELETE FROM monograph_file_settings WHERE file_id = ? AND revision = ?', array($fileId, $revision)
			);
		} else {
			return $this->update(
				'DELETE FROM monograph_file_settings WHERE file_id = ?', $fileId
			);
		}
	}
}

?>