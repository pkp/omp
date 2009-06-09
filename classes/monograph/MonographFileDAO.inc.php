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

import('monograph.MonographArtworkFile');
import('file.MonographFileManager');

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
			$returner =& $this->_returnMonographFileFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}
	/**
	 * Retrieve a monograph by ID.
	 * @param $fileId int
	 * @param $revision int optional, if omitted latest revision is used
	 * @param $monographId int optional
	 * @return MonographFile
	 */
	function &getMonographArtworkFile($fileId, $revision = null, $monographId = null) {
		$returner = null;

		if ($fileId === null) {
			return $returner;
		}

		$sql = 'SELECT *
			FROM monograph_files mf
			LEFT JOIN monograph_artwork_files maf ON maf.file_id = mf.file_id
			LEFT JOIN monograph_components mc ON maf.component_id = mc.component_id
			LEFT JOIN monograph_component_settings mcs ON mc.component_id = mcs.component_id
			WHERE mf.file_id = ?';
		$sqlParams = array($fileId);

		if ($revision != null) {
			$sql .= ' AND mf.revision = ?';
			$sqlParams[] = $revision;
		}
		if ($monographId != null) {
			$sql .= ' AND mf.monograph_id = ?';
			$sqlParams[] = $monographId;
		}
		$sql .= ' ORDER BY mc.seq, maf.seq';

		$result =& $this->retrieve($sql, $sqlParams);

		if (isset($result) && $result->RecordCount() != 0) {
			$returner =& $this->_returnMonographArtworkFileFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}
	/**
	 * Retrieve all revisions of a monograph file.
	 * @param $monographId int
	 * @return MonographFile
	 */
	function &getMonographFileRevisions($fileId, $reviewType = null, $organizeByReview = true) {
		if ($fileId === null) {
			$returner = null;
			return $returner;
		}
		$monographFiles = array();

		// FIXME If "round" is review-specific, it shouldn't be in this table
		if ($reviewType == null) {
			$result =& $this->retrieve(
				'SELECT a.* FROM monograph_files a WHERE file_id = ? ORDER BY revision',
				$fileId
			);
		} else {
			$result =& $this->retrieve(
				'SELECT a.* FROM monograph_files a WHERE file_id = ? AND review_type = ? ORDER BY revision',
				array($fileId, $reviewType)
			);
		}
		if ($organizeByReview) {
			while (!$result->EOF) {
				$file =& $this->_returnMonographFileFromRow($result->GetRowAssoc(false));

				$monographFiles[$result->fields['review_type']][$result->fields['round']][] = $file;

				unset($file);
				$result->moveNext();
			}
		} else {
			while (!$result->EOF) {
				$monographFiles[] =& $this->_returnMonographFileFromRow($result->GetRowAssoc(false));
				$result->moveNext();
			}
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
			$monographFiles[] =& $this->_returnMonographFileFromRow($result->GetRowAssoc(false));
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
	 * @return array MonographFiles
	 */
	function &getMonographFilesByMonograph($monographId) {
		$monographFiles = array();

		$result =& $this->retrieve(
			'SELECT * FROM monograph_artwork_files maf  
			RIGHT JOIN monograph_files mf ON mf.file_id = maf.file_id
			WHERE mf.monograph_id = ?',
			$monographId
		);

		while (!$result->EOF) {
			$monographFiles[] =& $this->_returnMonographArtworkFileFromRow($result->GetRowAssoc(false));
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
	 * @param $monographId int
	 * @return array MonographFiles
	 */
	function &getMonographFilesByAssocId($assocId, $type, $monographId) {

		$locale = Locale::getLocale();
		$primaryLocale = Locale::getPrimaryLocale();

		$monographFiles = array();

		$result =& $this->retrieve(
			'SELECT mf.*, maf.*,
				COALESCE(mcs.setting_value, mcs0.setting_value) AS component_title
			FROM monograph_files mf
			LEFT JOIN monograph_artwork_files maf ON maf.file_id = mf.file_id
			LEFT JOIN monograph_components mc ON maf.component_id = mc.component_id
			LEFT JOIN monograph_component_settings mcs ON (mcs.component_id = mc.component_id AND mcs.setting_name = ? AND mcs.locale = ?)
			LEFT JOIN monograph_component_settings mcs0 ON (mcs0.component_id = mc.component_id AND mcs0.setting_name = ? AND mcs0.locale = ?)
			WHERE mf.type = ? AND mf.monograph_id = ?',
			array('title', $primaryLocale, 'title', $locale, MonographFileManager::typeToPath($type), $monographId)
		);

		while (!$result->EOF) {
			$monographFiles[] =& $this->_returnMonographArtworkFileFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $monographFiles;
	}

	/**
	 * Internal function to return a MonographFile object from a row.
	 * @param $row array
	 * @return MonographFile
	 */
	function &_returnMonographArtworkFileFromRow(&$row) {
		$monographFile = new MonographArtworkFile();

		$monographFile->setPermission($row['permission']);
		$monographFile->setPermissionFileId($row['permission_file_id']);
		$monographFile->setMonographComponentId($row['component_id']);
		$monographFile->setSeq($row['seq']);
		$monographFile->setIdentifier($row['identifier']);

		if (isset($row['component_title'])) $monographFile->setMonographComponentTitle($row['component_title']);

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
		$monographFile->setLocaleKeyForType(MonographFileManager::pathToLocaleKey($row['type']));
		$monographFile->setAssocId($row['assoc_id']);
		$monographFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));
		$monographFile->setDateModified($this->datetimeFromDB($row['date_modified']));
		$monographFile->setRound($row['round']);
		$monographFile->setReviewType($row['review_type']);
		return $monographFile;
	}
	/**
	 * Internal function to return a MonographFile object from a row.
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
		$monographFile->setLocaleKeyForType(MonographFileManager::pathToLocaleKey($row['type']));
		$monographFile->setAssocId($row['assoc_id']);
		$monographFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));
		$monographFile->setDateModified($this->datetimeFromDB($row['date_modified']));
		$monographFile->setRound($row['round']);
		$monographFile->setViewable($row['viewable']);
		$monographFile->setReviewType($row['review_type']);
		HookRegistry::call('MonographFileDAO::_returnMonographFileFromRow', array(&$monographFile, &$row));
		return $monographFile;
	}

	/**
	 * Insert a new MonographArtworkFile entry.
	 * @param $monographFile MonographFile
	 * @return int
	 */	
	function insertMonographArtworkFile(&$monographFile) {
		$this->update('INSERT INTO monograph_artwork_files
				(file_id, permission, permission_file_id, identifier, component_id, seq)
				VALUES
				(?, ?, ?, ?, ?, ?)', 
			array(
				$monographFile->getFileId(),
				$monographFile->getPermission() == null ? false : true,
				$monographFile->getPermissionFileId(),
				$monographFile->getIdentifier(),
				$monographFile->getMonographComponentId(),
				$monographFile->getSeq() == null ? 0 : $monographFile->getSeq()
			)
		);
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
			$monographFile->getAssocId(),
			$monographFile->getReviewType(),
			$monographFile->getRound()
		);

		if ($fileId) {
			array_unshift($params, $fileId);
		}

		$this->update(
			sprintf('INSERT INTO monograph_files
				(' . ($fileId ? 'file_id, ' : '') . 'revision, monograph_id, source_file_id, source_revision, file_name, file_type, file_size, original_file_name, type, date_uploaded, date_modified, viewable, assoc_id, review_type, round)
				VALUES
				(' . ($fileId ? '?, ' : '') . '?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, ?, ?, ?, ?)',
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
	function updateMonographArtworkFile(&$monographFile) {

		$this->update('UPDATE monograph_artwork_files
				SET
					permission = ?,
					permission_file_id = ?, 
					identifier = ?,
					component_id = ?,
					seq = ?
				WHERE file_id = ?',
			array(
				$monographFile->getPermission(),
				$monographFile->getPermissionFileId(),
				$monographFile->getIdentifier(),
				$monographFile->getMonographComponentId(),
				$monographFile->getSeq(),
				$monographFile->getFileId(),
			)
		);
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
					review_type = ?,
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
				$monographFile->getReviewType(),
				$monographFile->getViewable(),
				$monographFile->getAssocId(),
				$monographFile->getFileId(),
				$monographFile->getRevision()
			)
		);

		return $monographFile->getFileId();

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

		$this->update('DELETE FROM monograph_artwork_files WHERE file_id = ?', $fileId);

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