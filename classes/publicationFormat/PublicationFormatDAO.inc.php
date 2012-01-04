<?php
/**
 * @file classes/publicationFormat/PublicationFormatDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatDAO
 * @ingroup publicationFormat
 * @see PublicationFormat
 *
 * @brief Operations for retrieving and modifying PublicationFormat objects.
 */

import('classes.publicationFormat.PublicationFormat');
import('classes.press.DefaultSettingDAO');

class PublicationFormatDAO extends DefaultSettingDAO {
	/**
	 * Constructor
	 */
	function PublicationFormatDAO() {
		parent::DefaultSettingDAO();
	}

	/**
	 * @see DefaultSettingsDAO::getPrimaryKeyColumnName()
	 */
	function getPrimaryKeyColumnName() {
		return 'publication_format_id';
	}

	/**
	 * Retrieve a publication format by type id.
	 * @param $publicationFormatId int
	 * @return PublicationFormat
	 */
	function &getById($publicationFormatId, $pressId = null) {
		$params = array((int) $publicationFormatId);
		if ($pressId) $params[] = (int) $pressId;

		$result =& $this->retrieve(
			'SELECT	*
			FROM	publication_formats
			WHERE	publication_format_id = ?
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
	 * Retrieve all enabled publication formats
	 * @return array PublicationFormat
	 */
	function &getEnabledByPressId($pressId) {
		$result =& $this->retrieve(
			'SELECT * FROM publication_formats WHERE enabled = ? AND press_id = ?', array(1, $pressId)
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
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
	 * @param $publicationFormat object
	 */
	function updateLocaleFields(&$publicationFormat) {
		$this->updateDataObjectSettings('publication_format_settings', $publicationFormat, array(
			'publication_format_id' => $publicationFormat->getId()
		));
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return PublicationFormat
	 */
	function newDataObject() {
		return new PublicationFormat();
	}

	/**
	 * Internal function to return a PublicationFormat object from a row.
	 * @param $row array
	 * @return PublicationFormat
	 */
	function &_fromRow(&$row) {
		$publicationFormat = $this->newDataObject();
		$publicationFormat->setPressId($row['press_id']);
		$publicationFormat->setId($row['publication_format_id']);
		$publicationFormat->setEnabled($row['enabled']);
		$publicationFormat->setEntryKey($row['entry_key']);

		$this->getDataObjectSettings('publication_format_settings', 'publication_format_id', $row['publication_format_id'], $publicationFormat);

		return $publicationFormat;
	}

	/**
	 * Insert a new publication format.
	 * @param $publicationFormat PublicationFormat
	 */
	function insertObject(&$publicationFormat) {
		$this->update(
			'INSERT INTO publication_formats
				(press_id, enabled)
			VALUES
				(?, ?)',
			array(
				(int) $publicationFormat->getPressId(),
				$publicationFormat->getEnabled()?1:0
			)
		);

		$publicationFormat->setId($this->getInsertPublicationFormatId());

		$this->updateLocaleFields($publicationFormat);

		return $publicationFormat->getId();
	}

	/**
	 * Update an existing publication format.
	 * @param $publicationFormat PublicationFormat
	 */
	function updateObject(&$publicationFormat) {

		$this->updateLocaleFields($publicationFormat);
	}

	/**
	 * Soft delete a publication format by id.
	 * @param $publicationFormatId int
	 */
	function deleteById($publicationFormatId, $pressId = null) {
		$params = array(0, (int) $publicationFormatId);
		if ($pressId) $params[] = (int) $pressId;

		return $this->update(
			'UPDATE	publication_formats
			SET	enabled = ?
			WHERE	publication_format_id = ?
			' . ($pressId?' AND press_id = ?':''),
			$params
		);
	}

	/**
	 * Get the ID of the last inserted publication format.
	 * @return int
	 */
	function getInsertPublicationFormatId() {
		return $this->getInsertId('publication_formats', 'publication_format_id');
	}

	/**
	 * Get the name of the settings table.
	 * @return string
	 */
	function getSettingsTableName() {
		return 'publication_format_settings';
	}

	/**
	 * Get the name of the main table for this setting group.
	 * @return string
	 */
	function getTableName() {
		return 'publication_formats';
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
		return 'registry/publicationFormats.xml';
	}

	/**
	 * Install publication formats from an XML file.
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBase($pressId) {
		$xmlDao = new XMLDAO();

		$data = $xmlDao->parseStruct($this->getDefaultBaseFilename(), array('publicationFormat'));
		if (!isset($data['publicationFormat'])) return false;

		foreach ($data['publicationFormat'] as $entry) {
			$attrs = $entry['attributes'];
			$this->update(
				'INSERT INTO publication_formats
				(entry_key, press_id, enabled)
				VALUES
				(?, ?, ?)',
				array($attrs['key'], (int) $pressId, 1)
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

			$settings = array(
				'name' => __($localeKey, array(), $locale),
				'designation' => __($localeKey.'.designation', array(), $locale)
			);
		}
		return $settings;
	}
}

?>
