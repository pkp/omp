<?php

/**
 * @file classes/press/LibraryFileDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileDAO
 * @ingroup press
 * @see LibraryFile
 *
 * @brief Operations for retrieving and modifying LibraryFile objects.
 */


import('classes.press.LibraryFile');
import('classes.file.LibraryFileManager');

class LibraryFileDAO extends DAO {
	/**
	 * Retrieve a library file by ID.
	 * @param $fileId int
	 * @param $revision int optional, if omitted latest revision is used
	 * @param $libraryId int optional
	 * @return LibraryFile
	 */
	function &getById($fileId) {
		$result =& $this->retrieve(
			'SELECT file_id, press_id, file_name, file_type, file_size, type, date_uploaded FROM library_files WHERE file_id = ?',
			array($fileId)
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
		$libraryFiles = array();

		$sqlParams = array($pressId);
		$sqlExtra = '';

		if (isset($type)) {
			$sqlExtra .= ' AND type = ? ';
			$sqlParams[] = $type;
		}

		$result =& $this->retrieve(
			'SELECT file_id, press_id, file_name, file_type, file_size, type, date_uploaded FROM library_files
			WHERE press_id = ?'.$sqlExtra, $sqlParams
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
	 * @param $suppFile
	 */
	function updateLocaleFields(&$libraryFile) {
		$this->updateDataObjectSettings('library_file_settings', $libraryFile, array(
			'file_id' => $libraryFile->getId()
		));
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
			$libraryFile->getPressId(),
			$libraryFile->getFileName(),
			$libraryFile->getFileType(),
			$libraryFile->getFileSize(),
			$libraryFile->getType()
		);
		
		if ( $libraryFile->getId() ) {
			$params[] = $libraryFile->getId();
			$this->update(
				sprintf('INSERT INTO library_files
					(press_id, file_name, file_type, file_size, type, date_uploaded, file_id)
					VALUES
					(?, ?, ?, ?, ?, %s, ?)',
					$this->datetimeToDB($libraryFile->getDateUploaded())
					),
				$params
			);
			
		} else {
			$this->update(
				sprintf('INSERT INTO library_files
					(press_id, file_name, file_type, file_size, type, date_uploaded)
					VALUES
					(?, ?, ?, ?, ?, %s)',
					$this->datetimeToDB($libraryFile->getDateUploaded())
					),
				$params
			);

			$libraryFile->setId($this->getInsertLibraryFileId());
		}
		
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
			sprintf('UPDATE library_files
				SET press_id = ?, 
					file_name = ?, 
					file_type = ?, 
					file_size = ?, 
					type = ?, 
					date_uploaded = %s
				WHERE file_id = ?', $this->datetimeToDB($libraryFile->getDateUploaded())
				), array(
					$libraryFile->getPressId(),
					$libraryFile->getFileName(), 
					$libraryFile->getFileType(), 
					$libraryFile->getFileSize(),
					$libraryFile->getType(),
					$libraryFile->getId()
					));
					
		$this->updateLocaleFields($libraryFile);
		return $libraryFile->getId();
	}

	/**
	 * Delete a library file.
	 * @param $library LibraryFile
	 */
	function deleteLibraryFile(&$libraryFile) {
		return $this->deleteLibraryFileById($libraryFile->getId(), $libraryFile->getRevision());
	}

	/**
	 * Delete a library file by ID.
	 * @param $libraryId int
	 * @param $revision int
	 */
	function deleteById($fileId, $revision = null) {
		$this->update(
			'DELETE FROM library_files WHERE file_id = ?', $fileId
		);
		$this->update('DELETE FROM library_file_settings WHERE file_id = ?', $fileId);
	}

	/**
	 * Delete all library files for a library.
	 * @param $libraryId int
	 */
	function deleteLibraryFiles($libraryId) {
		return $this->update(
			'DELETE FROM library_files WHERE library_id = ?', $libraryId
		);
	}

	/**
	 * Check if a file with this filename already exists
	 * @param $filename String the filename to be checked
	 * @return bool 
	 */
	function filenameExists($pressId, $fileName) {
		$result = $this->retrieve('SELECT COUNT(*) FROM library_files WHERE press_id = ? AND file_name = ?', array($pressId, $fileName) );

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