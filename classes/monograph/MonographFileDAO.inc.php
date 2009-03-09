<?php

/**
 * @file classes/monographMonographFileDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileDAO
 * @ingroup monograph
 * @see MonographFile
 *
 * @brief Operations for retrieving and modifying MonographFile objects.
 */

// $Id$


import('monograph.MonographFile');

define('INLINEABLE_TYPES_FILE', Config::getVar('general', 'registry_dir') . DIRECTORY_SEPARATOR . 'inlineTypes.txt');

class MonographFileDAO extends DAO {
	/**
	 * Array of MIME types that can be displayed inline in a browser
	 */
	var $inlineableTypes;

	/**
	 * Retrieve an monograph by ID.
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
				$result = &$this->retrieveLimit(
					'SELECT a.* FROM monograph_files a WHERE file_id = ? AND monograph_id = ? ORDER BY revision DESC',
					array($fileId, $monographId),
					1
				);
			} else {
				$result = &$this->retrieveLimit(
					'SELECT a.* FROM monograph_files a WHERE file_id = ? ORDER BY revision DESC',
					$fileId,
					1
				);
			}

		} else {
			if ($monographId != null) {
				$result = &$this->retrieve(
					'SELECT a.* FROM monograph_files a WHERE file_id = ? AND revision = ? AND monograph_id = ?',
					array($fileId, $revision, $monographId)
				);
			} else {
				$result = &$this->retrieve(
					'SELECT a.* FROM monograph_files a WHERE file_id = ? AND revision = ?',
					array($fileId, $revision)
				);
			}
		}

		$returner = null;
		if (isset($result) && $result->RecordCount() != 0) {
			$returner = &$this->_returnMonographFileFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all revisions of an monograph file.
	 * @param $monographId int
	 * @return MonographFile
	 */
	function &getMonographFileRevisions($fileId, $round = null) {
		if ($fileId === null) {
			$returner = null;
			return $returner;
		}
		$monographFiles = array();

		// FIXME If "round" is review-specific, it shouldn't be in this table
		if ($round == null) {
			$result = &$this->retrieve(
				'SELECT a.* FROM monograph_files a WHERE file_id = ? ORDER BY revision',
				$fileId
			);
		} else {
			$result = &$this->retrieve(
				'SELECT a.* FROM monograph_files a WHERE file_id = ? AND round = ? ORDER BY revision',
				array($fileId, $round)
			);
		}

		while (!$result->EOF) {
			$monographFiles[] = &$this->_returnMonographFileFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $monographFiles;
	}

	/**
	 * Retrieve revisions of an monograph file in a range.
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
			$result = &$this->retrieve(
				'SELECT a.* FROM monograph_files a WHERE file_id = ? AND revision >= ?',
				array($fileId, $start)
			);
		} else {
			$result = &$this->retrieve(
				'SELECT a.* FROM monograph_files a WHERE file_id = ? AND revision >= ? AND revision <= ?',
				array($fileId, $start, $end)
			);		
		}

		while (!$result->EOF) {
			$monographFiles[] = &$this->_returnMonographFileFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $monographFiles;
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
		$result = &$this->retrieve(
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
	 * Retrieve all monograph files for an monograph.
	 * @param $monographId int
	 * @return array MonographFiles
	 */
	function &getMonographFilesByMonograph($monographId) {
		$monographFiles = array();

		$result = &$this->retrieve(
			'SELECT * FROM monograph_files WHERE monograph_id = ?',
			$monographId
		);

		while (!$result->EOF) {
			$monographFiles[] = &$this->_returnMonographFileFromRow($result->GetRowAssoc(false));
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
		import('file.MonographFileManager');
		$monographFiles = array();

		$result = &$this->retrieve(
			'SELECT * FROM monograph_files WHERE assoc_id = ? AND type = ?',
			array($assocId, MonographFileManager::typeToPath($type))
		);

		while (!$result->EOF) {
			$monographFiles[] = &$this->_returnMonographFileFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $monographFiles;
	}

	/**
	 * Internal function to return an MonographFile object from a row.
	 * @param $row array
	 * @return MonographFile
	 */
	function &_returnMonographFileFromRow(&$row) {
		$monographFile = new MonographFile();
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
		$monographFile->setAssocId($row['assoc_id']);
		$monographFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));
		$monographFile->setDateModified($this->datetimeFromDB($row['date_modified']));
		$monographFile->setRound($row['round']);
		$monographFile->setViewable($row['viewable']);
		HookRegistry::call('MonographFileDAO::_returnMonographFileFromRow', array(&$monographFile, &$row));
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
			(int) $monographFile->getRound(),
			$monographFile->getViewable(),
			$monographFile->getAssocId()
		);

		if ($fileId) {
			array_unshift($params, $fileId);
		}

		$this->update(
			sprintf('INSERT INTO monograph_files
				(' . ($fileId ? 'file_id, ' : '') . 'revision, monograph_id, source_file_id, source_revision, file_name, file_type, file_size, original_file_name, type, date_uploaded, date_modified, round, viewable, assoc_id)
				VALUES
				(' . ($fileId ? '?, ' : '') . '?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, ?, ?, ?)',
				$this->datetimeToDB($monographFile->getDateUploaded()), $this->datetimeToDB($monographFile->getDateModified())),
			$params
		);

		if (!$fileId) {
			$monographFile->setFileId($this->getInsertMonographFileId());
		}

		return $monographFile->getFileId();
	}

	/**
	 * Update an existing monograph file.
	 * @param $monograph MonographFile
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
					round = ?,
					viewable = ?,
					assoc_id = ?
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
				$monographFile->getRound() == null ? 1 : $monographFile->getRound(),//temporary
				$monographFile->getViewable(),
				$monographFile->getAssocId(),
				$monographFile->getFileId(),
				$monographFile->getRevision()
			)
		);

		return $monographFile->getFileId();

	}

	/**
	 * Delete an monograph file.
	 * @param $monograph MonographFile
	 */
	function deleteMonographFile(&$monographFile) {
		return $this->deleteMonographFileById($monographFile->getFileId(), $monographFile->getRevision());
	}

	/**
	 * Delete an monograph file by ID.
	 * @param $monographId int
	 * @param $revision int
	 */
	function deleteMonographFileById($fileId, $revision = null) {
		if ($revision == null) {
			return $this->update(
				'DELETE FROM monograph_files WHERE file_id = ?', $fileId
			);
		} else {
			return $this->update(
				'DELETE FROM monograph_files WHERE file_id = ? AND revision = ?', array($fileId, $revision)
			);
		}
	}

	/**
	 * Delete all monograph files for an monograph.
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
