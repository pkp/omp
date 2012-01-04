<?php

/**
 * @file classes/press/LibraryFileDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileDAO
 * @ingroup press
 * @see LibraryFile
 *
 * @brief Operations for retrieving and modifying LibraryFile objects.
 */

import('classes.press.LibraryFile');

class LibraryFileDAO extends DAO {
	/**
	 * Constructor
	 */
	function LibraryFileDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a library file by ID.
	 * @param $fileId int
	 * @param $revision int optional, if omitted latest revision is used
	 * @param $libraryId int optional
	 * @return LibraryFile
	 */
	function &getById($fileId) {
		$result =& $this->retrieve(
			'SELECT file_id, press_id, file_name, original_file_name, file_type, file_size, type, date_uploaded FROM library_files WHERE file_id = ?',
			array((int) $fileId)
		);

		$returner = null;
		if (isset($result) && $result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all library files for a press.
	 * @param $pressId int
	 * @param $type (optional)
	 * @return array LibraryFiles
	 */
	function &getByPressId($pressId, $type = null) {
		$params = array((int) $pressId);
		if (isset($type)) $params[] = (int) $type;

		$result =& $this->retrieve(
			'SELECT	*
			FROM	library_files
			WHERE	press_id = ?' . (isset($type)?' AND type = ?' : ''),
			$params
		);
		$returner = new DAOResultFactory($result, $this, '_fromRow', array('id'));
		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SignoffEntry
	 */
	function newDataObject() {
		return new LibraryFile();
	}


	/**
	 * Get the list of fields for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name');
	}

	/**
	 * Update the localized fields for this file.
	 * @param $libraryFile
	 */
	function updateLocaleFields(&$libraryFile) {
		$this->updateDataObjectSettings(
			'library_file_settings',
			$libraryFile,
			array('file_id' => $libraryFile->getId())
		);
	}

	/**
	 * Internal function to return a LibraryFile object from a row.
	 * @param $row array
	 * @return LibraryFile
	 */
	function &_fromRow(&$row) {
		$libraryFile = $this->newDataObject();

		$libraryFile->setId($row['file_id']);
		$libraryFile->setPressId($row['press_id']);
		$libraryFile->setFileName($row['file_name']);
		$libraryFile->setOriginalFileName($row['original_file_name']);
		$libraryFile->setFileType($row['file_type']);
		$libraryFile->setFileSize($row['file_size']);
		$libraryFile->setType($row['type']);
		$libraryFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));

		$this->getDataObjectSettings('library_file_settings', 'file_id', $row['file_id'], $libraryFile);

		HookRegistry::call('LibraryFileDAO::_fromRow', array(&$libraryFile, &$row));

		return $libraryFile;
	}

	/**
	 * Insert a new LibraryFile.
	 * @param $libraryFile LibraryFile
	 * @return int
	 */
	function insertObject(&$libraryFile) {
		$params = array(
			(int) $libraryFile->getPressId(),
			$libraryFile->getFileName(),
			$libraryFile->getOriginalFileName(),
			$libraryFile->getFileType(),
			(int) $libraryFile->getFileSize(),
			(int) $libraryFile->getType()
		);

		if ($libraryFile->getId()) $params[] = (int) $libraryFile->getId();

		$this->update(
			sprintf('INSERT INTO library_files
				(press_id, file_name, original_file_name, file_type, file_size, type, date_uploaded' . ($libraryFile->getId()?', file_id':'') . ')
				VALUES
				(?, ?, ?, ?, ?, ?, %s' . ($libraryFile->getId()?', ?':'') . ')',
				$this->datetimeToDB($libraryFile->getDateUploaded())
			),
			$params
		);

		if (!$libraryFile->getId()) $libraryFile->setId($this->getInsertLibraryFileId());

		$this->updateLocaleFields($libraryFile);
		return $libraryFile->getId();
	}

	/**
	 * Update a LibraryFile
	 * @param $monograph MonographFile
	 * @return int
	 */
	function updateObject(&$libraryFile) {
		$this->update(
			sprintf('UPDATE	library_files
				SET	press_id = ?,
					file_name = ?,
					original_file_name = ?,
					file_type = ?,
					file_size = ?,
					type = ?,
					date_uploaded = %s
				WHERE	file_id = ?',
				$this->datetimeToDB($libraryFile->getDateUploaded())
			), array(
				(int) $libraryFile->getPressId(),
				$libraryFile->getFileName(),
				$libraryFile->getOriginalFileName(),
				$libraryFile->getFileType(),
				(int) $libraryFile->getFileSize(),
				(int) $libraryFile->getType(),
				(int) $libraryFile->getId()
			)
		);

		$this->updateLocaleFields($libraryFile);
		return $libraryFile->getId();
	}

	/**
	 * Delete a library file by ID.
	 * @param $libraryId int
	 * @param $revision int
	 */
	function deleteById($fileId, $revision = null) {
		$this->update(
			'DELETE FROM library_files WHERE file_id = ?',
			(int) $fileId
		);
		$this->update(
			'DELETE FROM library_file_settings WHERE file_id = ?',
			(int) $fileId
		);
	}

	/**
	 * Check if a file with this filename already exists
	 * @param $filename String the filename to be checked
	 * @return bool
	 */
	function filenameExists($pressId, $fileName) {
		$result = $this->retrieve(
			'SELECT COUNT(*) FROM library_files WHERE press_id = ? AND file_name = ?',
			array((int) $pressId, $fileName)
		);

		$returner = (isset($result->fields[0]) && $result->fields[0] > 0) ? true : false;
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the ID of the last inserted library file.
	 * @return int
	 */
	function getInsertLibraryFileId() {
		return $this->getInsertId('library_files', 'file_id');
	}
}

?>
