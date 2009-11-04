<?php
/**	
 * @file classes/press/DefaultSettingDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DefaultSettingDAO
 * @ingroup press
 * @see PressSettingsDAO
 *
 * @brief Operations for retrieving and modifying press default settings.
 */

define('DEFAULT_SETTING_FLEXIBLE_ROLES',	1);
define('DEFAULT_SETTING_BOOK_FILE_TYPES',	2);
define('DEFAULT_SETTING_PUBLICATION_FORMAT',	3);

class DefaultSettingDAO extends DAO
{
	/**
	 * Install book file types from an XML file.
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBase($pressId) {
		return null;
	}

	/**
	 * Get the name/path of the setting data file for a locale.
	 * @param $locale string
	 * @return string
	 */
	function getDefaultBaseDataFilename($locale = null) {
		return null;
	}

	/**
	 * Install book file type localized data from an XML file.
	 * @param $dataFile string Filename to install
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBaseData($dataFile, $pressId) {
		return null;
	}

	/**
	 * Get the name of the settings table.
	 * @return string
	 */
	function getSettingsTableName() {
		return null;
	}

	/**
	 * Get the name of the main table for this setting group.
	 * @return string
	 */
	function getTableName() {
		return null;
	}

	/**
	 * Get the default type constant.
	 * @return int
	 */
	function getDefaultType() {
		return null;
	}

	/**
	 * Retrieve all default book file types
	 * @param $pressId int
	 */
	function &getDefaultSettingIds($pressId) {
		$result =& $this->retrieve(
			'SELECT entry_id, entry_key FROM '. $this->getTableName() .' WHERE press_id = ? AND entry_key IS NOT NULL', $pressId
		);

		$returner = null;
		while (!$result->EOF) {
			$returner[$result->fields['entry_key']] =& $result->fields['entry_id'];
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Restore settings.
	 * @param $pressId int
	 * @param $setting int
	 */
	function restoreByPressId($pressId) {

		$defaultIds = $this->getDefaultSettingIds($pressId);

		foreach ($defaultIds as $key => $id) {
			$this->update('DELETE FROM '. $this->getSettingsTableName() .' WHERE entry_id = ?', $id);
		}

		$this->update('UPDATE '. $this->getTableName() .' SET enabled = ? WHERE press_id = ? AND entry_key IS NOT NULL', array(1, $pressId));
		$this->update('UPDATE '. $this->getTableName() .' SET enabled = ? WHERE press_id = ? AND entry_key IS NULL', array(0, $pressId));

		$result =& $this->retrieve(
			'SELECT * FROM press_defaults WHERE press_id = ? AND assoc_type = ?', array($pressId, $this->getDefaultType())
		);

		$returner = null;
		while (!$result->EOF) {
			$row =& $result->GetRowAssoc(false);
			$this->update(
				'INSERT INTO '. $this->getSettingsTableName() .'
				(entry_id, locale, setting_name, setting_value, setting_type)
				VALUES
				(?, ?, ?, ?, ?)',
				array($defaultIds[$row['entry_key']], $row['locale'], $row['setting_name'], $row['setting_value'], $row['setting_type'])
			);
			unset($row);
			$result->moveNext();
		}
		$result->Close();
		unset($result);
	}
}

?>