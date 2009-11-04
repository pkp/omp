<?php
/**	
 * @file classes/bookFile/BookFileTypeDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookFileTypeDAO
 * @ingroup bookFile
 * @see BookFileType
 *
 * @brief Operations for retrieving and modifying BookFileType objects.
 */

// $Id: BookFileTypeDAO.inc.php,v 1.3 2009/11/04 19:01:19 tylerl Exp $


import('bookFile.BookFileType');
import('press.DefaultSettingDAO');

class BookFileTypeDAO extends DefaultSettingDAO
{
	/**
	 * Retrieve a book file type by type id.
	 * @param $typeId int
	 * @return BookFileType
	 */
	function &getById($typeId){
		$result =& $this->retrieve('SELECT * FROM book_file_types WHERE entry_id = ?', $typeId);
		
		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all enabled book file types
	 * @return array BookFileType
	 */
	function &getEnabledByPressId($pressId) {
		$result =& $this->retrieve(
			'SELECT * FROM book_file_types WHERE enabled = ? AND press_id = ?', array(1, $pressId)
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
		return array('name', 'designation');
	}

	/**
	 * Update the settings for this object
	 * @param $bookFileType object
	 */
	function updateLocaleFields(&$bookFileType) {
		$this->updateDataObjectSettings('book_file_type_settings', $bookFileType, array(
			'entry_id' => $bookFileType->getId()
		));
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return BookFileType
	 */
	function newDataObject() {
		return new BookFileType();
	}

	/**
	 * Internal function to return a BookFileType object from a row.
	 * @param $row array
	 * @return BookFileType
	 */
	function &_fromRow(&$row) {
		$bookFileType = $this->newDataObject();
		$bookFileType->setId($row['entry_id']);
		$bookFileType->setSortable($row['sortable']);

		$this->getDataObjectSettings('book_file_type_settings', 'entry_id', $row['entry_id'], $bookFileType);

		return $bookFileType;
	}  

	/**
	 * Insert a new book file type.
	 * @param $bookFileType BookFileType
	 */	
	function insertObject(&$bookFileType) {
		$press =& Request::getPress();

		$this->update(
			'INSERT INTO book_file_types
				(sortable, press_id)
			VALUES
				(?, ?)',
			array(
				$bookFileType->getSortable() ? 1 : 0, $press->getId()
			)
		);

		$bookFileType->setId($this->getInsertBookFileTypeId());

		$this->updateLocaleFields($bookFileType);

		return $bookFileType->getId();
	}

	/**
	 * Update an existing book file type.
	 * @param $bookFileType BookFileType
	 */
	function updateObject(&$bookFileType) {

		$this->updateLocaleFields($bookFileType);
	}

	/**
	 * Soft delete a book file type by id.
	 * @param $entryId int
	 */
	function deleteById($entryId) {
		return $this->update(
			'UPDATE book_file_types SET enabled = ? WHERE entry_id = ?', array(0, $entryId)
		);
	}

	/**
	 * Get the ID of the last inserted book file type.
	 * @return int
	 */
	function getInsertBookFileTypeId() {
		return $this->getInsertId('book_file_types', 'entry_id');
	}

	/**
	 * Get the name of the settings table.
	 * @return string
	 */
	function getSettingsTableName() {
		return 'book_file_type_settings';
	}

	/**
	 * Get the name of the main table for this setting group.
	 * @return string
	 */
	function getTableName() {
		return 'book_file_types';
	}

	/**
	 * Get the default type constant.
	 * @return int
	 */
	function getDefaultType() {
		return DEFAULT_SETTING_BOOK_FILE_TYPES;
	}

	/**
	 * Get the name/path of the setting data file for a locale.
	 * @param $locale string
	 * @return string
	 */
	function getDefaultBaseDataFilename($locale = null) {
		if ($locale !== null && !PKPLocale::isLocaleValid($locale)) return null;
		if ($locale === null) $locale = '{$installedLocale}';
		return "locale/$locale/bookFileTypes.xml";
	}

	/**
	 * Install book file types from an XML file.
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBase($pressId) {
		$xmlDao = new XMLDAO();

		$data = $xmlDao->parseStruct('registry/bookFileTypes.xml', array('bookFileType'));
		if (!isset($data['bookFileType'])) return false;

		foreach ($data['bookFileType'] as $entry) {
			$attrs = $entry['attributes'];
			$this->update(
				'INSERT INTO book_file_types
				(entry_key, sortable, press_id)
				VALUES
				(?, ?, ?)',
				array($attrs['key'], $attrs['sortable'] ? 1 : 0, $pressId)
			);
		}
		return true;
	}

	/**
	 * Install book file type localized data from an XML file.
	 * @param $bookFileTypeDataFile string Filename to install
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBaseData($bookFileTypeDataFile, $pressId) {
		$xmlDao = new XMLDAO();

		$data = $xmlDao->parse($bookFileTypeDataFile, array('entries', 'entry', 'name', 'designation'));
		if (!$data) return false;

		$locale = $data->getAttribute('locale');
		$defaultTypes = $this->getDefaultSettingIds($pressId);

		foreach ($data->getChildren() as $bookFileTypeNode) {

			$settings = array(
				'name' => $bookFileTypeNode->getChildValue('name'), 
				'designation' => $bookFileTypeNode->getChildValue('designation')
			);

			foreach ($settings as $settingName => $settingValue) {

				if ($settingName == 'designation' && (!isset($settingValue) || $settingValue == '')) {
					$settingValue = BOOK_FILE_TYPE_SORTABLE_DESIGNATION;
				}

				$this->update(
					'INSERT INTO press_defaults
					(press_id, assoc_type, entry_key, locale, setting_name, setting_value, setting_type)
					VALUES
					(?, ?, ?, ?, ?, ?, ?)',
					array(
						$pressId,
						$this->getDefaultType(),
						$bookFileTypeNode->getAttribute('key'),
						$locale,
						$settingName,
						$settingValue,
						'string'
					)
				);

				$this->restoreByPressId($pressId);
			}
		}
		return true;
	}
}

?>
