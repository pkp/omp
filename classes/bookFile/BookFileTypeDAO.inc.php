<?php
/**	
 * @file classes/bookFile/BookFileTypeDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookFileTypeDAO
 * @ingroup bookFile
 * @see BookFileType
 *
 * @brief Operations for retrieving and modifying BookFileType objects.
 */



import('classes.bookFile.BookFileType');
import('classes.press.DefaultSettingDAO');

class BookFileTypeDAO extends DefaultSettingDAO
{
	/**
	 * Retrieve a book file type by type id.
	 * @param $typeId int
	 * @return BookFileType
	 */
	function &getById($typeId, $pressId = null){
		$sqlParams = array($typeId);
		if ($pressId) {
			$sqlParams[] = $pressId;
		}

		$result =& $this->retrieve('SELECT * FROM book_file_types WHERE entry_id = ?'. ($pressId ? ' AND press_id = ?' : ''), $sqlParams);
		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all enabled book file types
	 * @return DAOResultFactory containing matching BookFileTypes
	 */
	function &getEnabledByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM book_file_types WHERE enabled = ? AND press_id = ?', array(1, $pressId), $rangeInfo
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
		$bookFileType->setCategory($row['category']);		

		$this->getDataObjectSettings('book_file_type_settings', 'entry_id', $row['entry_id'], $bookFileType);

		HookRegistry::call('BookFileTypeDAO::_fromRow', array(&$bookFileType, &$row));
		
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
				(sortable, press_id, category)
			VALUES
				(?, ?, ?)',
			array(
				$bookFileType->getSortable() ? 1 : 0, $press->getId(), $bookFileType->getCategory()
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
	 * Delete a book file type by id.
	 * @param $bookFileType BookFileType
	 */
	function deleteObject($bookFileType) {
		return $this->deleteById($bookFileType->getId());
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
	 * Get the path of the setting data file.
	 * @return string
	 */
	function getDefaultBaseFilename() {
		return 'registry/bookFileTypes.xml';
	}

	/**
	 * Install book file types from an XML file.
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBase($pressId) {
		$xmlDao = new XMLDAO();

		$data = $xmlDao->parseStruct($this->getDefaultBaseFilename(), array('bookFileType'));
		if (!isset($data['bookFileType'])) return false;

		foreach ($data['bookFileType'] as $entry) {
			$attrs = $entry['attributes'];
			$this->update(
				'INSERT INTO book_file_types
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

			$designation = $sortable ? BOOK_FILE_TYPE_SORTABLE_DESIGNATION : Locale::translate($localeKey.'.designation', array(), $locale);

			$settings = array(
				'name' => Locale::translate($localeKey, array(), $locale), 
				'designation' => $designation
			);
		}
		return $settings;
	}
}

?>
