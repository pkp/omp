<?php

/**
 * @file classes/monograph/MonographArtworkDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographArtworkDAO
 * @ingroup monograph
 * @see MonographArtworkFile
 *
 * @brief Operations for retrieving and modifying MonographArtworkFile objects.
 */

// $Id$

import('monograph.MonographArtworkFile');

class MonographArtworkDAO extends DAO {

	/**
	 * Retrieve a monograph artwork file by ID.
	 * @param $artworkFileId int
	 * @return MonographArtworkFile
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
	 * Retrieve all artwork files for a monograph.
	 * @param $monographId int
	 * @return array MonographArtworkFiles
	 */
	function &getByMonographId($monographId) {
		$artworkFiles = array();

		$result =& $this->retrieve(
			'SELECT * FROM monograph_artwork_files WHERE monograph_id = ?', $monographId
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
	 * Construct a new data object corresponding to this DAO.
	 * @return MonographArtworkFile
	 */
	function newDataObject() {
		return new MonographArtworkFile();
	}

	/**
	 * Internal function to return a MonographArtworkFile object from a row.
	 * @param $row array
	 * @return MonographArtworkFile
	 */
	function &_fromRow(&$row) {
		$artworkFile = $this->newDataObject();

		$artworkFile->setType($row['type_id']);
		$artworkFile->setCredit($row['credit']);
		$artworkFile->setId($row['artwork_id']);
		$artworkFile->setFileId($row['file_id']);
		$artworkFile->setCaption($row['caption']);
		$artworkFile->setPlacement($row['placement']);
		$artworkFile->setCustomType($row['custom_type']);
		$artworkFile->setMonographId($row['monograph_id']);
		$artworkFile->setComponentId($row['component_id']);
		$artworkFile->setContactAuthor($row['contact_author']);
		$artworkFile->setCopyrightOwner($row['copyright_owner']);
		$artworkFile->setPermissionTerms($row['permission_terms']);
		$artworkFile->setPermissionFileId($row['permission_file_id']);
		$artworkFile->setCopyrightOwnerContactDetails($row['copyright_owner_contact']);

		return $artworkFile;
	}

	/**
	 * Insert a new MonographArtworkFile.
	 * @param $artworkFile MonographArtworkFile
	 * @return int
	 */	
	function insertObject(&$artworkFile) {
		$this->update(
			'INSERT INTO monograph_artwork_files
			(caption, component_id, contact_author, copyright_owner, copyright_owner_contact, credit, custom_type, file_id, monograph_id, permission_file_id, permission_terms, type_id, placement)
			VALUES
			(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				$artworkFile->getCaption(),
				$artworkFile->getComponentId(),
				$artworkFile->getContactAuthor(),
				$artworkFile->getCopyrightOwner(),
				$artworkFile->getCopyrightOwnerContactDetails(),
				$artworkFile->getCredit(),
				$artworkFile->getCustomType(),
				$artworkFile->getFileId(),
				$artworkFile->getMonographId(),
				$artworkFile->getPermissionFileId(),
				$artworkFile->getPermissionTerms(),
				$artworkFile->getType(),
				$artworkFile->getPlacement()
			)
		);

		$artworkFile->setId($this->getInsertMonographArtworkFileId());

		return $artworkFile->getFileId();
	}

	/**
	 * Update an existing monograph file.
	 * @param $artworkFile MonographArtworkFile
	 */
	function updateObject(&$artworkFile) {
		$this->update(
			'UPDATE monograph_artwork_files
				SET
					caption = ?,
					component_id = ?,
					contact_author = ?,
					copyright_owner = ?,
					copyright_owner_contact = ?,
					credit = ?,
					custom_type = ?,
					monograph_id = ?,
					permission_file_id = ?,
					permission_terms = ?,
					placement = ?,
					type_id = ?
				WHERE artwork_id = ?',
			array(
				$artworkFile->getCaption(),
				$artworkFile->getComponentId(),
				$artworkFile->getContactAuthor(),
				$artworkFile->getCopyrightOwner(),
				$artworkFile->getCopyrightOwnerContactDetails(),
				$artworkFile->getCredit(),
				$artworkFile->getCustomType(),
				$artworkFile->getMonographId(),
				$artworkFile->getPermissionFileId(),
				$artworkFile->getPermissionTerms(),
				$artworkFile->getPlacement(),
				$artworkFile->getType(),
				$artworkFile->getId()
			)
		);

		return $artworkFile->getId();

	}

	/**
	 * Delete a monograph artwork file.
	 * @param $artworkFile MonographArtworkFile
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
	function getInsertMonographArtworkFileId() {
		return $this->getInsertId('monograph_artwork_files', 'artwork_id');
	}
}

?>