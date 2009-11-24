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
define('DEFAULT_SETTING_PUBLICATION_FORMATS',	3);

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
	 * Get the column name of the primary key
	 * @return string
	 */
	function getPrimaryKeyColumnName() {
		return 'entry_id';
	}

	/**
	 * Get the column name of the constant key identifier.
	 * @return string
	 */
	function getDefaultKey() {
		return 'entry_key';
	}

	/**
	 * Get the names and values for setting attributes. 
	 * In subclasses: if $node is null, return only the attribute names.
	 * @param $node XMLNode
	 * @param $onlyNames bool
	 * @return array key=>value
	 */
	function getSettingAttributes($node = null) {
		return array();
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
	 * Install book file type localized data from an XML file.
	 * @param $dataFile string Filename to install
	 * @param $pressId int
	 * @param $skipLoad bool
	 * @param $localInstall bool
	 * @return boolean
	 */
	function installDefaultBaseData($dataFile, $pressId, $skipLoad = true, $localeInstall = false) {
		$xmlDao = new XMLDAO();

		$data = $xmlDao->parse($dataFile);
		if (!$data) return false;
  
		$locale = $data->getAttribute('locale');
		$defaultIds = $this->getDefaultSettingIds($pressId);

		foreach ($data->getChildren() as $formatNode) {

			$settings =& $this->getSettingAttributes($formatNode);

			if (empty($defaultIds[$formatNode->getAttribute('key')])) { // ignore keys not associated with this press
				continue;
			} else { // prepare a list of attributes not defined in the current settings xml file
				unset($defaultIds[$formatNode->getAttribute('key')]);
			}

			foreach ($settings as $settingName => $settingValue) {

				$this->update(
					'INSERT INTO press_defaults
					(press_id, assoc_type, entry_key, locale, setting_name, setting_value, setting_type)
					VALUES
					(?, ?, ?, ?, ?, ?, ?)',
					array(
						$pressId,
						$this->getDefaultType(),
						$formatNode->getAttribute('key'),
						$locale,
						$settingName,
						$settingValue,
						'string'
					)
				);
			}
		}

		$attributeNames =& $this->getSettingAttributes();

		// install defaults for keys not defined in the xml
		foreach ($defaultIds as $key => $id) {
			foreach ($attributeNames as $setting) {
				$this->update(
					'INSERT INTO press_defaults
					(press_id, assoc_type, entry_key, locale, setting_name, setting_value, setting_type)
					VALUES
					(?, ?, ?, ?, ?, ?, ?)',
					array(
						$pressId,
						$this->getDefaultType(),
						$key,
						$locale,
						$setting,
						'##',
						'string'
					)
				);
			}
		}

		if ($skipLoad) {
			return true;
		}

		if ($localeInstall) {
			$this->restoreByPressId($pressId, $locale);
		} else {
			$this->restoreByPressId($pressId);
		}

		return true;
	}

	/**
	 * Retrieve ids for all default setting entries
	 * @param $pressId int
	 */
	function &getDefaultSettingIds($pressId) {
		$result =& $this->retrieve(
			'SELECT '. $this->getPrimaryKeyColumnName() .', '. $this->getDefaultKey() .' FROM '. $this->getTableName() .' 
			WHERE press_id = ? AND '. $this->getDefaultKey() .' IS NOT NULL', $pressId
		);

		$returner = null;
		while (!$result->EOF) {
			$returner[$result->fields[$this->getDefaultKey()]] =& $result->fields[$this->getPrimaryKeyColumnName()];
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Restore settings.
	 * @param $pressId int
	 * @param $locale string
	 */
	function restoreByPressId($pressId, $locale = null) {

		$defaultIds = $this->getDefaultSettingIds($pressId);

		if ($locale) {
			foreach ($defaultIds as $key => $id) {
				$this->update('DELETE FROM '. $this->getSettingsTableName() .' WHERE '. $this->getPrimaryKeyColumnName() .' = ? AND locale = ?', array($id, $locale));
			}
		} else {
			foreach ($defaultIds as $key => $id) {
				$this->update('DELETE FROM '. $this->getSettingsTableName() .' WHERE '. $this->getPrimaryKeyColumnName() .' = ?', $id);
			}
		}

		if (!$locale) {
			$this->update('UPDATE '. $this->getTableName() .' SET enabled = ? WHERE press_id = ? AND '. $this->getDefaultKey() .' IS NOT NULL', array(1, $pressId));
			$this->update('UPDATE '. $this->getTableName() .' SET enabled = ? WHERE press_id = ? AND '. $this->getDefaultKey() .' IS NULL', array(0, $pressId));
		}

		$sql = 'SELECT * FROM press_defaults WHERE press_id = ? AND assoc_type = ?';
		$sqlParams = array($pressId, $this->getDefaultType());
		if ($locale) {
			$sql .= ' AND locale = ?';
			$sqlParams[] = $locale;
		}

		$result =& $this->retrieve($sql, $sqlParams);

		$returner = null;
		while (!$result->EOF) {
			$row =& $result->GetRowAssoc(false);
			$this->update(
				'INSERT INTO '. $this->getSettingsTableName() .'
				('. $this->getPrimaryKeyColumnName() .', locale, setting_name, setting_value, setting_type)
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

	/**
	 * Install default data for settings.
	 * @param $pressId int
	 * @param $locales array
	 */
	function installDefaults($pressId, $locales) {
		$this->installDefaultBase($pressId);
		foreach ($locales as $locale) {
			$this->installDefaultBaseData($this->getDefaultBaseDataFilename($locale), $pressId);
		}
		$this->restoreByPressId($pressId);
	}

	/**
	 * Install locale specific items for a locale.
	 * @param $locale string
	 */
	function installLocale($locale) {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPressNames();

		foreach ($presses as $id => $name) {
			$fileName = $this->getDefaultBaseDataFilename($locale);
			$this->installDefaultBaseData($fileName, $id, false, true);
		}

	}

	/**
	 * Delete locale specific items from the settings table.
	 * @param $locale string
	 */
	function uninstallLocale($locale) {
		$this->update('DELETE FROM '. $this->getSettingsTableName() .' WHERE locale = ?', array($locale));
		$this->update('DELETE FROM press_defaults WHERE locale = ?', array($locale));
	}
}

?>