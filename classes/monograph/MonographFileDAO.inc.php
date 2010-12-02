<?php

/**
 * @file classes/monographMonographFileDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileDAO
 * @ingroup monograph
 * @see MonographFile
 *
 * @brief Operations for retrieving and modifying MonographFile objects.
 */


import('classes.monograph.MonographFile');
import('classes.file.MonographFileManager');

define('INLINEABLE_TYPES_FILE', Config::getVar('general', 'registry_dir') . DIRECTORY_SEPARATOR . 'inlineTypes.txt');

class MonographFileDAO extends DAO {
	/**
	 * Array of MIME types that can be displayed inline in a browser
	 */
	var $inlineableTypes;

	/**
	 * Retrieve a monograph file by ID.
	 * @param $fileId int
	 * @param $revision int optional, if omitted latest revision is used
	 * @param $monographId int optional
	 * @return MonographFile
	 */
	function &getMonographFile($fileId, $revision = null, $monographId = null) {
		$fileId = (int)$fileId;
		$revision = (int)$revision;
		$monographId = (int)$monographId;

		if (!$fileId) {
			$nullVar = null;
			return $nullVar;
		}

		// Build the query and parameter array.
		$sql = 'SELECT * FROM monograph_files WHERE file_id = ?';
		$params = array($fileId);

		if ($monographId) {
			$sql .= ' AND monograph_id = ?';
			array_push($params, $monographId);
		}

		if ($revision) {
			$sql .= ' AND revision = ?';
			array_push($params, $revision);
		} else {
			$sql .= ' ORDER BY revision DESC';
		}

		// Execute the query.
		$result =& $this->retrieve($sql, $params);

		$returner = null;
		if (isset($result) && $result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		// FIXME: Need to populate remaining fields if the file is an artwork file, see #6127.

		return $returner;
	}

	/**
	 * Retrieve all revisions of a monograph file.
	 * @param $fileId int
	 * @param $monographId int (optional) the monograph id the files must belong to
	 * @return MonographFile
	 */
	function &getMonographFileRevisions($fileId, $monographId = null) {
		$fileId = (int)$fileId;
		$monographId = (int)$monographId;

		if (!$fileId) {
			$nullVar = null;
			return $nullVar;
		}

		// Build the query.
		$sql = 'SELECT * FROM monograph_files WHERE file_id = ?';
		$params = array($fileId);
		if ($monographId) {
			$sql .= ' AND monograph_id = ?';
			array_push($params, $monographId);
		}
		$sql .= ' ORDER BY revision DESC';

		// Execute the query.
		$result =& $this->retrieve($sql, $params);

		$monographFiles = array();
		while (!$result->EOF) {
			$monographFiles[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		// FIXME: Need to populate remaining fields if the file is an artwork file, see #6127.

		return $monographFiles;
	}

	/**
	 * Retrieve revisions of a monograph file in a range.
	 * @param $monographId int
	 * @return MonographFile
	 */
	function &getMonographFileRevisionsInRange($fileId, $start = 1, $end = null) {
		if ($fileId === null) {
			$returner = null;
			return $returner;
		}
		$monographFiles = array();

		if ($end == null) {
			$result =& $this->retrieve(
				'SELECT a.* FROM monograph_files a WHERE file_id = ? AND revision >= ?',
				array($fileId, $start)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT a.* FROM monograph_files a WHERE file_id = ? AND revision >= ? AND revision <= ?',
				array($fileId, $start, $end)
			);
		}

		while (!$result->EOF) {
			$monographFiles[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		// FIXME: Need to populate remaining fields if the file is an artwork file, see #6127.

		return $monographFiles;
	}

	/**
	 * Get the list of fields for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name');
	}

	/**
	 * Update the localized fields for this supp file.
	 * @param $suppFile
	 */
	function updateLocaleFields(&$monographFile) {
		$this->updateDataObjectSettings('monograph_file_settings', $monographFile, array(
			'file_id' => $monographFile->getFileId()
		));
	}

	/**
	 * Set a file as the latest revision of an existing file
	 * @param $revisedFileId integer the revised file
	 * @param $newFileId integer the file that will become the
	 *  latest revision of the revised file.
	 * @param $monographId integer the monograph id the two files
	 *  must belong to.
	 * @param $fileStage integer the file stage the two files
	 *  must belong to.
	 * @return MonographFile the new revision or null if something went wrong.
	 */
	function &setAsLatestRevision(&$revisedFileId, $newFileId, $monographId, $fileStage) {
		$revisedFileId = (int)$revisedFileId;
		$newFileId = (int)$newFileId;
		$monographId = (int)$monographId;
		$fileStage = (int)$fileStage;

		// Check whether the two files are already revisions of each other.
		$nullVar = null;
		if ($revisedFileId == $newFileId) return $nullVar;

		// Retrieve the latest revisions of the two monograph files.
		$revisedFile =& $this->getMonographFile($revisedFileId, null, $monographId);
		$newFile =& $this->getMonographFile($newFileId, null, $monographId);
		if (!($revisedFile && $newFile)) return $nullVar;

		// Make sure that the files all belong to the correct file stage.
		if ($revisedFile->getFileStage() != $fileStage
				|| $newFile->getFileStage() != $fileStage) return $nullVar;

		// Copy data over to the new file.
		$newFile->setRevision($revisedFile->getRevision()+1);
		$newFile->setGenreId($revisedFile->getGenreId());
		$newFile->setAssocType($revisedFile->getAssocType());
		$newFile->setAssocId($revisedFile->getAssocId());

		// NB: We cannot use updateMonographFile() becase we have
		// to change the id of the file.
		$this->update(
			'UPDATE monograph_files
			 SET
			     file_id = ?,
			     revision = ?,
			     genre_id = ?,
			     assoc_type =?,
			     assoc_id = ?
			 WHERE file_id = ?',
			array(
				$revisedFile->getFileId(),
				$newFile->getRevision(),
				$newFile->getGenreId(),
				$newFile->getAssocType(),
				$newFile->getAssocId(),
				$newFile->getFileId())
		);

		$newFile->setFileId($revisedFile->getFileId());
		return $newFile;
	}

	/**
	 * Retrieve the current revision number for a file.
	 * @param $fileId int
	 * @return int
	 */
	function getLatestRevisionNumber($fileId) {
		assert(!is_null($fileId));

		$result =& $this->retrieve(
			'SELECT MAX(revision) AS max_revision FROM monograph_files WHERE file_id = ?',
			$fileId
		);
		if($result->RecordCount() != 1) return null;

		$row = $result->FetchRow();
		$result->Close();
		unset($result);

		$latestRevision = (int)$row['max_revision'];
		assert($latestRevision > 0);
		return $latestRevision;
	}

	/**
	 * Retrieve all monograph files for a monograph.
	 * @param $monographId int
	 * @param $fileStage int
	 * @return array MonographFiles
	 */
	function &getByMonographId($monographId, $fileStage = null) {
		$monographFiles = array();

		$sqlParams = array($monographId);
		$sqlExtra = '';

		if (isset($fileStage)) {
			$sqlExtra .= ' AND file_stage = ? ';
			$sqlParams[] = (int)$fileStage;
		}

		$result =& $this->retrieve(
			'SELECT * FROM monograph_files
			WHERE monograph_id = ?' . $sqlExtra .
			'ORDER BY file_id, revision ASC',
			$sqlParams
		);

		while (!$result->EOF) {
			$monographFiles[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		// FIXME: Need to populate remaining fields if the file is an artwork file, see #6127.

		return $monographFiles;
	}

	/**
	 * Retrieve all monograph files for a file stage and assoc ID.
	 * @param $assocId int
	 * @param $fileStage int
	 * @return array MonographFiles
	 */
	function &getMonographFilesByAssocId($assocId, $fileStage) {
		$monographFiles = array();

		$result =& $this->retrieve(
			'SELECT * FROM monograph_files WHERE assoc_id = ? AND file_stage = ?',
			array($assocId, $fileStage)
		);

		while (!$result->EOF) {
			$row =& $result->getRowAssoc(false);
			$fileId = $row['file_id'];
			$monographFiles[$fileId] =& $this->_fromRow($row);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		// FIXME: Need to populate remaining fields if the file is an artwork file, see #6127.

		return $monographFiles;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SignoffEntry
	 */
	function newDataObject() {
		return new MonographFile();
	}

	/**
	 * Internal function to return a MonographFile object from a row.
	 * @param $row array
	 * @return MonographFile
	 */
	function &_fromRow(&$row) {
		$monographFile = $this->newDataObject();

		$monographFile->setFileId($row['file_id']);
		$monographFile->setSourceFileId($row['source_file_id']);
		$monographFile->setSourceRevision($row['source_revision']);
		$monographFile->setRevision($row['revision']);
		$monographFile->setMonographId($row['monograph_id']);
		$monographFile->setFileName($row['file_name']);
		$monographFile->setFileType($row['file_type']);
		$monographFile->setFileSize($row['file_size']);
		$monographFile->setOriginalFileName($row['original_file_name']);
		$monographFile->setFileStage($row['file_stage']);
		$monographFile->setUserGroupId($row['user_group_id']);
		$monographFile->setAssocId($row['assoc_id']);
		$monographFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));
		$monographFile->setDateModified($this->datetimeFromDB($row['date_modified']));
		$monographFile->setViewable($row['viewable']);
		$monographFile->setGenreId($row['genre_id']);

		$this->getDataObjectSettings('monograph_file_settings', 'file_id', $row['file_id'], $monographFile);

		HookRegistry::call('MonographFileDAO::_fromRow', array(&$monographFile, &$row));

		return $monographFile;
	}

	/**
	 * Insert a new MonographFile.
	 * @param $monographFile MonographFile
	 * @return int
	 */
	function insertMonographFile(&$monographFile) {
		$fileId = $monographFile->getFileId();
		$params = array(
			$monographFile->getRevision() === null ? 1 : $monographFile->getRevision(),
			$monographFile->getMonographId(),
			$monographFile->getSourceFileId(),
			$monographFile->getSourceRevision(),
			$monographFile->getFileName(),
			$monographFile->getFileType(),
			$monographFile->getFileSize(),
			$monographFile->getOriginalFileName(),
			$monographFile->getFileStage(),
			$monographFile->getViewable(),
			$monographFile->getUserGroupId(),
			$monographFile->getAssocType(),
			$monographFile->getAssocId(),
			$monographFile->getGenreId()
		);

		if ($fileId) {
			array_unshift($params, $fileId);
		}

		$this->update(
			sprintf('INSERT INTO monograph_files
				(' . ($fileId ? 'file_id, ' : '') . 'revision, monograph_id, source_file_id, source_revision, file_name, file_type, file_size, original_file_name, file_stage, date_uploaded, date_modified, viewable, user_group_id, assoc_type, assoc_id, genre_id)
				VALUES
				(' . ($fileId ? '?, ' : '') . '?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monographFile->getDateUploaded()), $this->datetimeToDB($monographFile->getDateModified())),
			$params
		);

		if (!$fileId) {
			$monographFile->setFileId($this->getInsertMonographFileId());
		}
		$this->updateLocaleFields($monographFile);

		// Determine whether this is artwork and make an additional
		// entry to the artwork table if this is the case.
		if (is_a($monographFile, 'ArtworkFile')) {
			// This is artwork so persist the remaining fields via the artwork DAO.
			$artworkFileDao =& DAORegistry::getDAO('ArtworkFileDAO'); /* @var $artworkFileDao ArtworkFileDAO */
			$artworkFileDao->insertObject($monographFile);
		}

		return $monographFile->getFileId();
	}

	/**
	 * Update an existing monograph file.
	 * @param $monograph MonographFile
	 * @return int
	 */
	function updateMonographFile(&$monographFile) {
		$this->update(
			sprintf('UPDATE monograph_files
				SET
					monograph_id = ?,
					source_file_id = ?,
					source_revision = ?,
					file_name = ?,
					file_type = ?,
					file_size = ?,
					original_file_name = ?,
					file_stage = ?,
					date_uploaded = %s,
					date_modified = %s,
					viewable = ?,
					user_group_id = ?,
					assoc_type = ?,
					assoc_id = ?,
					genre_id = ?
				WHERE file_id = ? AND revision = ?',
				$this->datetimeToDB($monographFile->getDateUploaded()), $this->datetimeToDB($monographFile->getDateModified())),
			array(
				$monographFile->getMonographId(),
				$monographFile->getSourceFileId(),
				$monographFile->getSourceRevision(),
				$monographFile->getFileName(),
				$monographFile->getFileType(),
				$monographFile->getFileSize(),
				$monographFile->getOriginalFileName(),
				$monographFile->getFileStage(),
				$monographFile->getViewable(),
				$monographFile->getUserGroupId(),
				$monographFile->getAssocType(),
				$monographFile->getAssocId(),
				$monographFile->getGenreId(),
				$monographFile->getFileId(),
				$monographFile->getRevision()
			)
		);

		$this->updateLocaleFields($monographFile);

		// FIXME: Also call out to artwork DAO if $monographFile is an artwork file, see #6127.

		return $monographFile->getFileId();
	}

	/**
	 * Delete a monograph file.
	 * @param $monograph MonographFile
	 */
	function deleteMonographFile(&$monographFile) {
		return $this->deleteMonographFileById($monographFile->getFileId(), $monographFile->getRevision());
		// FIXME: Need to remove monograph file from artwork table if the file is an artwork file, see #6127.
	}

	/**
	 * Delete a monograph file by ID.
	 * @param $fileId int
	 * @param $revision int
	 * @param $monographId int (optional) the monograph id the file must belong to
	 */
	function deleteMonographFileById($fileId, $revision = null, $monographId = null) {
		$fileId = (int)$fileId;
		$revision = (int)$revision;
		$monographId = (int)$monographId;

		// Build the query.
		$sql = 'DELETE FROM monograph_files WHERE file_id = ?';
		$params = array($fileId);

		if ($monographId) {
			$sql .= ' AND monograph_id = ?';
			array_push($params, $monographId);
		}

		if ($revision) {
			$sql .= ' AND revision = ?';
			array_push($params, $revision);
		}

		// Execute the query.
		$this->update($sql, $params);

		// Only delete the settings if we deleted all revisions of the file.
		if (is_null($this->getLatestRevisionNumber($fileId))) {
			$this->update('DELETE FROM monograph_file_settings WHERE file_id = ?', $fileId);
		}

		// FIXME: Need to remove monograph file from artwork table if the file is an artwork file, see #6127.
	}

	/**
	 * Delete all monograph files for a monograph.
	 * @param $monographId int
	 */
	function deleteMonographFiles($monographId) {
		return $this->update(
			'DELETE FROM monograph_files WHERE monograph_id = ?', $monographId
		);
		// FIXME: Need to remove monograph file from artwork table if the file is an artwork file, see #6127.
	}

	/**
	 * Get the ID of the last inserted monograph file.
	 * @return int
	 */
	function getInsertMonographFileId() {
		return $this->getInsertId('monograph_files', 'file_id');
	}

	/**
	 * Check whether a file may be displayed inline.
	 * @param $monographFile object
	 * @return boolean
	 */
	function isInlineable(&$monographFile) {
		if (!isset($this->inlineableTypes)) {
			$this->inlineableTypes = array_filter(file(INLINEABLE_TYPES_FILE), create_function('&$a', 'return ($a = trim($a)) && !empty($a) && $a[0] != \'#\';'));
		}
		return in_array($monographFile->getFileType(), $this->inlineableTypes);
	}
}

?>