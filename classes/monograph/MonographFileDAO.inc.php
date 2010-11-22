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
		if ($fileId === null) {
			$returner = null;
			return $returner;
		}
		if ($revision == null) {
			if ($monographId != null) {
				$result =& $this->retrieveLimit(
					'SELECT a.* FROM monograph_files a WHERE file_id = ? AND monograph_id = ? ORDER BY revision DESC',
					array($fileId, $monographId),
					1
				);
			} else {
				$result =& $this->retrieveLimit(
					'SELECT a.* FROM monograph_files a WHERE file_id = ? ORDER BY revision DESC',
					$fileId,
					1
				);
			}

		} else {
			if ($monographId != null) {
				$result =& $this->retrieve(
					'SELECT a.* FROM monograph_files a WHERE file_id = ? AND revision = ? AND monograph_id = ?',
					array($fileId, $revision, $monographId)
				);
			} else {
				$result =& $this->retrieve(
					'SELECT a.* FROM monograph_files a WHERE file_id = ? AND revision = ?',
					array($fileId, $revision)
				);
			}
		}

		$returner = null;
		if (isset($result) && $result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all revisions of a monograph file.
	 * @param $fileId int
	 * @return MonographFile
	 */
	function &getMonographFileRevisions($fileId) {
		if ($fileId === null) {
			$returner = null;
			return $returner;
		}
		$monographFiles = array();

		$result =& $this->retrieve(
			'SELECT a.* FROM monograph_files a WHERE file_id = ? ORDER BY revision',
			$fileId
		);

		while (!$result->EOF) {
			$monographFiles[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

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
	 * Retrieve the current revision number for a file.
	 * @param $fileId int
	 * @return int
	 */
	function &getRevisionNumber($fileId) {
		if ($fileId === null) {
			$returner = null;
			return $returner;
		}
		$result =& $this->retrieve(
			'SELECT MAX(revision) AS max_revision FROM monograph_files a WHERE file_id = ?',
			$fileId
		);

		if ($result->RecordCount() == 0) {
			$returner = null;
		} else {
			$row = $result->FetchRow();
			$returner = $row['max_revision'];
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all monograph files for a monograph.
	 * @param $monographId int
	 * @param $type int
	 * @return array MonographFiles
	 */
	function &getByMonographId($monographId, $type = null, $hideAttachments = true) {
		$monographFiles = array();

		$sqlParams = array($monographId);
		$sqlExtra = '';

		if (isset($type)) {
			$sqlExtra .= ' AND type = ? ';
			$sqlParams[] = (int)$type;
		}

		// Prevent review attachments from showing up in submission file lists
		if ($type != MONOGRAPH_FILE_REVIEW && $hideAttachments) {
			$sqlExtra .= ' AND type != '.MONOGRAPH_FILE_REVIEW.' ';
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

		return $monographFiles;
	}

	/**
	 * Retrieve all monograph files for a type and assoc ID.
	 * @param $assocId int
	 * @param $type int
	 * @return array MonographFiles
	 */
	function &getMonographFilesByAssocId($assocId, $type) {
		$monographFiles = array();

		$result =& $this->retrieve(
			'SELECT * FROM monograph_files WHERE assoc_id = ? AND type = ?',
			array($assocId, $type)
		);

		while (!$result->EOF) {
			$row =& $result->getRowAssoc(false);
			$fileId = $row['file_id'];
			$monographFiles[$fileId] =& $this->_fromRow($row);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

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
		$monographFile->setType($row['type']);
		$monographFile->setUserGroupId($row['user_group_id']);
		$monographFile->setAssocId($row['assoc_id']);
		$monographFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));
		$monographFile->setDateModified($this->datetimeFromDB($row['date_modified']));
		$monographFile->setViewable($row['viewable']);
		$monographFile->setMonographFileTypeId($row['monograph_file_type_id']);

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
			$monographFile->getType(),
			$monographFile->getViewable(),
			$monographFile->getUserGroupId(),
			$monographFile->getAssocType(),
			$monographFile->getAssocId(),
			$monographFile->getMonographFileTypeId()
		);

		if ($fileId) {
			array_unshift($params, $fileId);
		}

		$this->update(
			sprintf('INSERT INTO monograph_files
				(' . ($fileId ? 'file_id, ' : '') . 'revision, monograph_id, source_file_id, source_revision, file_name, file_type, file_size, original_file_name, type, date_uploaded, date_modified, viewable, user_group_id, assoc_type, assoc_id, monograph_file_type_id)
				VALUES
				(' . ($fileId ? '?, ' : '') . '?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, ?, ?, ?, ?, ?)',
				$this->datetimeToDB($monographFile->getDateUploaded()), $this->datetimeToDB($monographFile->getDateModified())),
			$params
		);

		if (!$fileId) {
			$monographFile->setFileId($this->getInsertMonographFileId());
		}
		$this->updateLocaleFields($monographFile);
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
					type = ?,
					date_uploaded = %s,
					date_modified = %s,
					viewable = ?,
					user_group_id = ?,
					assoc_type = ?,
					assoc_id = ?,
					monograph_file_type_id = ?
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
				$monographFile->getType(),
				$monographFile->getViewable(),
				$monographFile->getUserGroupId(),
				$monographFile->getAssocType(),
				$monographFile->getAssocId(),
				$monographFile->getMonographFileTypeId(),
				$monographFile->getFileId(),
				$monographFile->getRevision()
			)
		);

		$this->updateLocaleFields($monographFile);
		return $monographFile->getFileId();
	}

	/**
	 * Set a file as the latest revision of an existing file
	 * @param $newFileId int
	 * @param $oldFileId int
	 * @return int revision number
	 */
	function setAsLatestRevision($newFileId, $oldFileId) {
		$revision = $this->getRevisionNumber($oldFileId) +1;
		$this->update(
			'UPDATE monograph_files SET file_id = ?, revision = ? WHERE file_id = ?', array($oldFileId, $revision, $newFileId)
		);
		return $revision;
	}

	/**
	 * Delete a monograph file.
	 * @param $monograph MonographFile
	 */
	function deleteMonographFile(&$monographFile) {
		return $this->deleteMonographFileById($monographFile->getFileId(), $monographFile->getRevision());
	}

	/**
	 * Delete a monograph file by ID.
	 * @param $monographId int
	 * @param $revision int
	 */
	function deleteMonographFileById($fileId, $revision = null) {

		if ($revision == null) {
			$this->update(
				'DELETE FROM monograph_files WHERE file_id = ?', $fileId
			);
		} else {
			$this->update(
				'DELETE FROM monograph_files WHERE file_id = ? AND revision = ?', array($fileId, $revision)
			);
		}
		$this->update('DELETE FROM monograph_file_settings WHERE file_id = ?', $fileId);
	}

	/**
	 * Delete all monograph files for a monograph.
	 * @param $monographId int
	 */
	function deleteMonographFiles($monographId) {
		return $this->update(
			'DELETE FROM monograph_files WHERE monograph_id = ?', $monographId
		);
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