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

// $Id: BookFileTypeDAO.inc.php,v 1.2 2009/10/15 17:18:56 tylerl Exp $


import('bookFile.BookFileType');

class BookFileTypeDAO extends DAO
{
	/**
	 * Retrieve a book file type by type id.
	 * @param $typeId int
	 * @return BookFileType
	 */
	function &getById($typeId){
		$result =& $this->retrieve('SELECT * FROM book_file_types WHERE type_id = ?', $typeId);
		
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
			'SELECT * FROM book_file_types_enabled bfte
			LEFT JOIN book_file_types bft ON (bft.type_id = bfte.type_id)
			WHERE bfte.press_id = ?', $pressId
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
	 * Retrieve all default book file types
	 */
	function &getDefaultTypeIds() {
		$result =& $this->retrieve(
			'SELECT type_id, type_key FROM book_file_types WHERE type_key IS NOT NULL'
		);

		$returner = null;
		while (!$result->EOF) {
			$returner[$result->fields['type_key']] =& $result->fields['type_id'];
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
			'type_id' => $bookFileType->getId()
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
		$bookFileType->setId($row['type_id']);
		$bookFileType->setSortable($row['sortable']);

		$this->getDataObjectSettings('book_file_type_settings', 'type_id', $row['type_id'], $bookFileType);

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
				(sortable)
			VALUES
				(?)',
			array(
				$bookFileType->getSortable() ? 1 : 0,
			)
		);

		$bookFileType->setId($this->getInsertBookFileTypeId());

		$this->update(
			'INSERT INTO book_file_types_enabled
			(press_id, type_id)
			VALUES (?, ?)',
			array($press->getId(), $bookFileType->getId())
		);

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
	 * @param $typeId int
	 */
	function deleteById($typeId) {
		$press =& Request::getPress();
		return $this->update(
			'DELETE FROM book_file_types_enabled WHERE type_id = ? AND press_id = ?', array($typeId, $press->getId())
		);
	}

	/**
	 * Delete all book file types for a specific locale.
	 * @param $locale string
	 */
	function deleteByLocale($locale) {
		$this->update(
			'DELETE FROM book_file_type_settings WHERE locale = ?', $locale
		);
	}

	/**
	 * Get the ID of the last inserted book file type.
	 * @return int
	 */
	function getInsertBookFileTypeId() {
		return $this->getInsertId('book_file_types', 'type_id');
	}

	function getMainBookFileTypeFilename() {
		return 'registry/bookFileTypes.xml';
	}

	function getMainBookFileTypeDataFilename($locale = null) {
		if ($locale !== null && !PKPLocale::isLocaleValid($locale)) return null;
		if ($locale === null) $locale = '{$installedLocale}';
		return "locale/$locale/bookFileTypes.xml";
	}

	/**
	 * Install or restore default book file type settings.
	 * @param $pressId int
	 */
	function installDefaultsForPress($pressId) {
		$defaultTypes =& $this->getDefaultTypeIds();

		$this->update(
			'DELETE FROM book_file_types_enabled WHERE press_id = ?', $pressId
		);

		foreach ($defaultTypes as $defaultType) {
			$this->update(
				'INSERT INTO book_file_types_enabled
				(press_id, type_id)
				VALUES (?, ?)',
				array(
					$pressId,
					$defaultType,
				)
			);
		}

	}

	/**
	 * Install book file types from an XML file.
	 * NOTE: Uses qstr instead of ? bindings so that SQL can be fetched
	 * rather than executed.
	 * @param $bookFileTypeFile string Filename to install
	 * @param $pressId int
	 * @return boolean
	 */
	function installBookFileTypes($bookFileTypeFile, $returnSql = false) {
		$xmlDao = new XMLDAO();
		$sql = '';
		$data = $xmlDao->parseStruct($bookFileTypeFile, array('bookFileType'));
		if (!isset($data['bookFileType'])) return false;
		foreach ($data['bookFileType'] as $entry) {
			$attrs = $entry['attributes'];
			$sql .=	'INSERT INTO book_file_types
				(type_key, sortable)
				VALUES
				(' .
				$this->_dataSource->qstr($attrs['key']) . ', ' .
				($attrs['sortable'] ? 1 : 0) .
				")\n";
			if (!$returnSql) {
				$this->update($sql);
				$sql = '';
			}
		}
		if ($returnSql) return $sql;
		return true;
	}

	/**
	 * Install book file type localized data from an XML file.
	 * NOTE: Uses qstr instead of ? bindings so that SQL can be fetched
	 * rather than executed.
	 * @param $bookFileTypeDataFile string Filename to install
	 * @param $returnSql boolean Whether or not to return SQL rather than
	 * executing it
	 * @return boolean
	 */
	function installBookFileTypeData($bookFileTypeDataFile, $returnSql = false) {
		$xmlDao = new XMLDAO();
		$sql = '';
		$data = $xmlDao->parse($bookFileTypeDataFile, array('bookFileTypes', 'bookFileType', 'name', 'designation'));
		if (!$data) return false;
		$locale = $data->getAttribute('locale');

		$defaultTypes = $this->getDefaultTypeIds();

		foreach ($data->getChildren() as $bookFileTypeNode) {
			$this->update(
				'DELETE FROM book_file_type_settings WHERE type_id = ? AND locale = ?',
				array($defaultTypes[$bookFileTypeNode->getAttribute('key')], $locale)
			);

			$settings = array(
				'name' => $bookFileTypeNode->getChildValue('name'), 
				'designation' => $bookFileTypeNode->getChildValue('designation')
			);
			foreach ($settings as $settingName => $settingValue) {

				if ($settingName == 'designation' && (!isset($settingValue) || $settingValue == '')) {
					$settingValue = BOOK_FILE_TYPE_SORTABLE_DESIGNATION;
				}

				$sql .=	'INSERT INTO book_file_type_settings
					(type_id, locale, setting_name, setting_value, setting_type)
					VALUES
					(' .
					$this->_dataSource->qstr($defaultTypes[$bookFileTypeNode->getAttribute('key')]) . ', ' .
					$this->_dataSource->qstr($locale) . ', ' .
					$this->_dataSource->qstr($settingName) . ', ' .
					$this->_dataSource->qstr($settingValue) . ', ' .
					$this->_dataSource->qstr('string') .
					")\n";
				if (!$returnSql) {
					$this->update($sql);
					$sql = '';
				}
			}
		}
		if ($returnSql) return $sql;
		return true;
	}
}

?>
