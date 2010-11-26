<?php

/**
 * @file classes/monograph/ArtworkFileDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFileDAO
 * @ingroup monograph
 * @see ArtworkFile
 *
 * @brief Operations for retrieving and modifying ArtworkFile objects.
 */


import('classes.monograph.ArtworkFile');

class ArtworkFileDAO extends DAO {

	/**
	 * Retrieve a monograph artwork file by ID.
	 * @param $artworkFileId int
	 * @return ArtworkFile
	 */
	function &getById($artworkFileId) {
		$returner = null;

		$result =& $this->retrieve(
			'SELECT * FROM monograph_artwork_files WHERE artwork_id = ?', $artworkFileId
		);

		if (isset($result) && $result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve a monograph artwork file by file ID (associated with monograph_files table).
	 * @param $artworkFileId int
	 * @return ArtworkFile
	 */
	function &getByFileId($fileId) {
		$returner = null;

		$result =& $this->retrieve(
			'SELECT * FROM monograph_artwork_files WHERE file_id = ?', $fileId
		);

		if (isset($result) && $result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all artwork files for a monograph.
	 * @param $monographId int
	 * @return array ArtworkFiles
	 */
	function &getByMonographId($monographId, $rangeInfo = null) {
		$artworkFiles = array();

		$result =& $this->retrieve(
			'SELECT * FROM monograph_artwork_files WHERE monograph_id = ?', $monographId, $rangeInfo
		);

		while (!$result->EOF) {
			$artworkFiles[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $artworkFiles;
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
	function updateLocaleFields(&$artworkFile) {
		$this->updateDataObjectSettings('monograph_file_settings', $artworkFile, array(
			'file_id' => $artworkFile->getFileId()
		));
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return ArtworkFile
	 */
	function newDataObject() {
		return new ArtworkFile();
	}

	/**
	 * Internal function to return a ArtworkFile object from a row.
	 * @param $row array
	 * @return ArtworkFile
	 */
	function &_fromRow(&$row) {
		$artworkFile = $this->newDataObject();

		$artworkFile->setCredit($row['credit']);
		$artworkFile->setId($row['artwork_id']);
		$artworkFile->setFileId($row['file_id']);
		$artworkFile->setCaption($row['caption']);
		$artworkFile->setPlacement($row['placement']);
		$artworkFile->setMonographId($row['monograph_id']);
		$artworkFile->setChapterId($row['chapter_id']);
		$artworkFile->setContactAuthor($row['contact_author']);
		$artworkFile->setCopyrightOwner($row['copyright_owner']);
		$artworkFile->setPermissionTerms($row['permission_terms']);
		$artworkFile->setPermissionFileId($row['permission_file_id']);
		$artworkFile->setCopyrightOwnerContactDetails($row['copyright_owner_contact']);

		$this->getDataObjectSettings('monograph_file_settings', 'file_id', $row['file_id'], $artworkFile);

		HookRegistry::call('ArtworkFileDAO::_fromRow', array(&$artworkFile, &$row));

		return $artworkFile;
	}

	/**
	 * Insert a new ArtworkFile.
	 * @param $artworkFile ArtworkFile
	 * @return int
	 */
	function insertObject(&$artworkFile) {
		$this->update(
			'INSERT INTO monograph_artwork_files
			(caption, chapter_id, contact_author, copyright_owner, copyright_owner_contact, credit, file_id, monograph_id, permission_file_id, permission_terms, placement)
			VALUES
			(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				$artworkFile->getCaption(),
				$artworkFile->getChapterId(),
				$artworkFile->getContactAuthor(),
				$artworkFile->getCopyrightOwner(),
				$artworkFile->getCopyrightOwnerContactDetails(),
				$artworkFile->getCredit(),
				$artworkFile->getFileId(),
				$artworkFile->getMonographId(),
				$artworkFile->getPermissionFileId(),
				$artworkFile->getPermissionTerms(),
				$artworkFile->getPlacement()
			)
		);

		$artworkFile->setId($this->getInsertArtworkFileId());
		$this->updateLocaleFields($artworkFile);
		return $artworkFile->getFileId();
	}

	/**
	 * Update an existing monograph file.
	 * @param $artworkFile ArtworkFile
	 */
	function updateObject(&$artworkFile) {
		$this->update(
			'UPDATE monograph_artwork_files
				SET
					caption = ?,
					chapter_id = ?,
					contact_author = ?,
					copyright_owner = ?,
					copyright_owner_contact = ?,
					credit = ?,
					monograph_id = ?,
					permission_file_id = ?,
					permission_terms = ?,
					placement = ?
				WHERE artwork_id = ?',
			array(
				$artworkFile->getCaption(),
				$artworkFile->getChapterId(),
				$artworkFile->getContactAuthor(),
				$artworkFile->getCopyrightOwner(),
				$artworkFile->getCopyrightOwnerContactDetails(),
				$artworkFile->getCredit(),
				$artworkFile->getMonographId(),
				$artworkFile->getPermissionFileId(),
				$artworkFile->getPermissionTerms(),
				$artworkFile->getPlacement(),
				$artworkFile->getId()
			)
		);
		$this->updateLocaleFields($artworkFile);
		return $artworkFile->getId();
	}

	/**
	 * Delete a monograph artwork file.
	 * @param $artworkFile ArtworkFile
	 */
	function deleteObject(&$artworkFile) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFileDao->deleteMonographFileById($artworkFile->getFileId());

		return $this->update(
			'DELETE FROM monograph_artwork_files WHERE artwork_id = ?', $artworkFile->getId()
		);
	}

	/**
	 * Delete all monograph artwork files for a monograph.
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {

		$artworkFiles =& $this->getByMonographId($monographId);

		foreach ($artworkFiles as $artwork) {
			$this->deleteObject($artwork);
		}
	}

	/**
	 * Get the ID of the last inserted monograph artwork file.
	 * @return int
	 */
	function getInsertArtworkFileId() {
		return $this->getInsertId('monograph_artwork_files', 'artwork_id');
	}
}

?>