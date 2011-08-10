<?php
/**
 * @file classes/cataloguingMetadata/CataloguingMetadataFieldDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CataloguingMetadataFieldDAO
 * @ingroup cataloguingMetadata
 * @see CataloguingMetadataField
 *
 * @brief Operations for retrieving and modifying CataloguingMetadataField objects.
 */


import('classes.cataloguingMetadata.CataloguingMetadataField');
import('classes.press.DefaultSettingDAO');

class CataloguingMetadataFieldDAO extends DefaultSettingDAO
{
	/**
	 * @see DefaultSettingsDAO::getPrimaryKeyColumnName()
	 */
	function getPrimaryKeyColumnName() {
		return 'field_id';
	}

	/**
	 * Retrieve a cataloguing metadata field by id.
	 * @param $cataloguingMetadataFieldId int
	 * @param $pressId int
	 * @return CataloguingMetadataField
	 */
	function &getById($cataloguingMetadataFieldId, $pressId = null) {
		$params = array((int) $cataloguingMetadataFieldId);
		if ($pressId) $params[] = (int) $pressId;

		$result =& $this->retrieve(
			'SELECT	*
			FROM	cataloguing_metadata_fields
			WHERE field_id = ?
			' . ($pressId?' AND press_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all enabled metadata fields
	 * @return array CataloguingMetadataField
	 */
	function &getEnabledByPressId($pressId) {
		$result =& $this->retrieve(
			'SELECT	*
			FROM	cataloguing_metadata_fields
			WHERE	enabled = ? AND
				press_id = ?',
			array(1, (int) $pressId)
		);

		$returner = null;
		while (!$result->EOF) {
			$returner[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get a list of field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name');
	}

	/**
	 * Update the settings for this object
	 * @param $cataloguingMetadataField object
	 */
	function updateLocaleFields(&$cataloguingMetadataField) {
		$this->updateDataObjectSettings('cataloguing_metadata_field_settings', $cataloguingMetadataField, array(
			'field_id' => $cataloguingMetadataField->getId()
		));
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return CataloguingMetadataField
	 */
	function newDataObject() {
		return new CataloguingMetadataField();
	}

	/**
	 * Internal function to return a CataloguingMetadataField object from a row.
	 * @param $row array
	 * @return CataloguingMetadataField
	 */
	function &_fromRow(&$row) {
		$cataloguingMetadataField = $this->newDataObject();
		$cataloguingMetadataField->setPressId($row['press_id']);
		$cataloguingMetadataField->setId($row['field_id']);
		$cataloguingMetadataField->setEnabled($row['enabled']);

		$this->getDataObjectSettings('cataloguing_metadata_field_settings', 'field_id', $row['field_id'], $cataloguingMetadataField);

		return $cataloguingMetadataField;
	}

	/**
	 * Insert a new field.
	 * @param $cataloguingMetadataField CataloguingMetadataField
	 */
	function insertObject(&$cataloguingMetadataField) {
		$this->update(
			'INSERT INTO cataloguing_metadata_fields
				(press_id, enabled)
			VALUES
				(?, ?)',
			array(
				(int) $cataloguingMetadataField->getPressId(),
				$cataloguingMetadataField->getEnabled()?1:0
			)
		);

		$cataloguingMetadataField->setId($this->getInsertCataloguingMetadataFieldId());

		$this->updateLocaleFields($cataloguingMetadataField);

		return $cataloguingMetadataField->getId();
	}

	/**
	 * Update an existing field.
	 * @param $cataloguingMetadataField CataloguingMetadataField
	 */
	function updateObject(&$cataloguingMetadataField) {

		$this->updateLocaleFields($cataloguingMetadataField);
	}

	/**
	 * Soft delete a field by id.
	 * @param $cataloguingMetadataFieldId int
	 * @param $pressId int optional
	 */
	function deleteById($cataloguingMetadataFieldId, $pressId = null) {
		$params = array(0, (int) $cataloguingMetadataFieldId);
		if ($pressId) $params[] = (int) $pressId;

		return $this->update(
			'UPDATE	cataloguing_metadata_fields
			SET	enabled = ?
			WHERE	field_id = ?
				' . ($pressId?' AND press_id = ?':''),
			$params
		);
	}

	/**
	 * Get the ID of the last inserted field.
	 * @return int
	 */
	function getInsertCataloguingMetadataFieldId() {
		return $this->getInsertId('cataloguing_metadata_fields', 'field_id');
	}

	/**
	 * Get the name of the settings table.
	 * @return string
	 */
	function getSettingsTableName() {
		return 'cataloguing_metadata_field_settings';
	}

	/**
	 * Get the name of the main table for this setting group.
	 * @return string
	 */
	function getTableName() {
		return 'cataloguing_metadata_fields';
	}

	/**
	 * Get the default type constant.
	 * @return int
	 */
	function getDefaultType() {
		return DEFAULT_SETTING_PUBLICATION_FORMATS;
	}

	/**
	 * Get the path of the setting data file.
	 * @return string
	 */
	function getDefaultBaseFilename() {
		return 'registry/cataloguingMetadataFields.xml';
	}

	/**
	 * Install fields from an XML file.
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBase($pressId) {
		$xmlDao = new XMLDAO();

		$data = $xmlDao->parseStruct($this->getDefaultBaseFilename(), array('cataloguingMetadataField'));
		if (!isset($data['cataloguingMetadataField'])) return false;

		foreach ($data['cataloguingMetadataField'] as $entry) {
			$attrs = $entry['attributes'];
			$this->update(
				'INSERT INTO cataloguing_metadata_fields
				(press_id, enabled)
				VALUES
				(?, ?)',
				array((int) $pressId, 1)
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
			$settings = array('name');
		} else {
			$localeKey = $node->getAttribute('localeKey');

			$settings = array(
				'name' => Locale::translate($localeKey, array(), $locale)
			);
		}
		return $settings;
	}
}

?>
