<?php
/**
 * @file classes/monograph/MonographFileTypeDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileTypeDAO
 * @ingroup monograph
 * @see MonographFileType
 *
 * @brief Operations for retrieving and modifying MonographFileType objects.
 */


import('classes.monograph.MonographFileType');
import('classes.press.DefaultSettingDAO');

class MonographFileTypeDAO extends DefaultSettingDAO
{
	/**
	 * Retrieve a monograph file type by type id.
	 * @param $typeId int
	 * @return MonographFileType
	 */
	function &getById($typeId, $pressId = null){
		$sqlParams = array($typeId);
		if ($pressId) {
			$sqlParams[] = $pressId;
		}

		$result =& $this->retrieve('SELECT * FROM monograph_file_types WHERE entry_id = ?'. ($pressId ? ' AND press_id = ?' : ''), $sqlParams);
		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all enabled monograph file types
	 * @return DAOResultFactory containing matching MonographFileTypes
	 */
	function &getEnabledByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM monograph_file_types WHERE enabled = ? AND press_id = ?', array(1, $pressId), $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow', array('id'));
		return $returner;
	}

	/**
	 * Get a list of field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name', 'designation');
	}

	/**
	 * Update the settings for this object
	 * @param $monographFileType object
	 */
	function updateLocaleFields(&$monographFileType) {
		$this->updateDataObjectSettings('monograph_file_type_settings', $monographFileType, array(
			'entry_id' => $monographFileType->getId()
		));
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return MonographFileType
	 */
	function newDataObject() {
		return new MonographFileType();
	}

	/**
	 * Internal function to return a MonographFileType object from a row.
	 * @param $row array
	 * @return MonographFileType
	 */
	function &_fromRow(&$row) {
		$monographFileType = $this->newDataObject();
		$monographFileType->setId($row['entry_id']);
		$monographFileType->setSortable($row['sortable']);
		$monographFileType->setCategory($row['category']);

		$this->getDataObjectSettings('monograph_file_type_settings', 'entry_id', $row['entry_id'], $monographFileType);

		HookRegistry::call('MonographFileTypeDAO::_fromRow', array(&$monographFileType, &$row));

		return $monographFileType;
	}

	/**
	 * Insert a new monograph file type.
	 * @param $monographFileType MonographFileType
	 */
	function insertObject(&$monographFileType) {
		$press =& Request::getPress();

		$this->update(
			'INSERT INTO monograph_file_types
				(sortable, press_id, category)
			VALUES
				(?, ?, ?)',
			array(
				$monographFileType->getSortable() ? 1 : 0, $press->getId(), $monographFileType->getCategory()
			)
		);

		$monographFileType->setId($this->getInsertMonographFileTypeId());

		$this->updateLocaleFields($monographFileType);

		return $monographFileType->getId();
	}

	/**
	 * Update an existing monograph file type.
	 * @param $monographFileType MonographFileType
	 */
	function updateObject(&$monographFileType) {

		$this->updateLocaleFields($monographFileType);
	}

	/**
	 * Delete a monograph file type by id.
	 * @param $monographFileType MonographFileType
	 */
	function deleteObject($monographFileType) {
		return $this->deleteById($monographFileType->getId());
	}

	/**
	 * Soft delete a monograph file type by id.
	 * @param $entryId int
	 */
	function deleteById($entryId) {
		return $this->update(
			'UPDATE monograph_file_types SET enabled = ? WHERE entry_id = ?', array(0, $entryId)
		);
	}

	/**
	 * Get the ID of the last inserted monograph file type.
	 * @return int
	 */
	function getInsertMonographFileTypeId() {
		return $this->getInsertId('monograph_file_types', 'entry_id');
	}

	/**
	 * Get the name of the settings table.
	 * @return string
	 */
	function getSettingsTableName() {
		return 'monograph_file_type_settings';
	}

	/**
	 * Get the name of the main table for this setting group.
	 * @return string
	 */
	function getTableName() {
		return 'monograph_file_types';
	}

	/**
	 * Get the default type constant.
	 * @return int
	 */
	function getDefaultType() {
		return DEFAULT_SETTING_MONOGRAPH_FILE_TYPES;
	}

	/**
	 * Get the path of the setting data file.
	 * @return string
	 */
	function getDefaultBaseFilename() {
		return 'registry/monographFileTypes.xml';
	}

	/**
	 * Install monograph file types from an XML file.
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBase($pressId) {
		$xmlDao = new XMLDAO();

		$data = $xmlDao->parseStruct($this->getDefaultBaseFilename(), array('monographFileType'));
		if (!isset($data['monographFileType'])) return false;

		foreach ($data['monographFileType'] as $entry) {
			$attrs = $entry['attributes'];
			$this->update(
				'INSERT INTO monograph_file_types
				(entry_key, sortable, press_id, category)
				VALUES
				(?, ?, ?, ?)',
				array($attrs['key'], $attrs['sortable'] ? 1 : 0, $pressId, $attrs['category'])
			);
		}
		return true;
	}

	/**
	 * Get setting names and values.
	 * @param $node XMLNode
	 * @param $locale string
	 * @return array
	 */
	function &getSettingAttributes($node = null, $locale = null) {

		if ($node == null) {
			$settings = array('name', 'designation');
		} else {
			$localeKey = $node->getAttribute('localeKey');
			$sortable = $node->getAttribute('sortable');

			$designation = $sortable ? MONOGRAPH_FILE_TYPE_SORTABLE_DESIGNATION : Locale::translate($localeKey.'.designation', array(), $locale);

			$settings = array(
				'name' => Locale::translate($localeKey, array(), $locale),
				'designation' => $designation
			);
		}
		return $settings;
	}
}

?>
