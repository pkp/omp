<?php

/**
 * @file classes/monograph/MonographGalleyDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographGalleyDAO
 * @ingroup monograph
 * @see MonographGalley
 *
 * @brief Operations for retrieving and modifying MonographGalley/MonographHTMLGalley objects.
 */



import('classes.monograph.MonographGalley');

class MonographGalleyDAO extends DAO {
	/** @var SubmissionFileDAO Helper file DAOs. */
	var $_submissionFileDao;

	/**
	 * Constructor.
	 */
	function MonographGalleyDAO() {
		parent::DAO();
		$this->_submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
	}

	/**
	 * Retrieve a galley by ID.
	 * @param $galleyId int
	 * @param $monographId int optional
	 * @return MonographGalley
	 */
	function &getGalley($galleyId, $monographId = null) {
		$params = array($galleyId);
		if ($monographId !== null) $params[] = (int) $monographId;
		$result =& $this->retrieve(
			'SELECT	g.*,
				mf.file_name, mf.original_file_name, mf.file_type, mf.file_size, mf.date_uploaded, mf.date_modified,
				pa.label
			FROM	monograph_galleys g
			LEFT JOIN monograph_files mf ON (g.file_id = mf.file_id)
			LEFT JOIN production_assignments pa ON (pa.assignment_id = g.assignment_id)
			WHERE	g.galley_id = ?' .
			($monographId !== null?' AND g.monograph_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		} else {
			HookRegistry::call('MonographGalleyDAO::getGalley', array(&$galleyId, &$monographId, &$returner));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Checks if public identifier exists (other than for the specified
	 * galley ID, which is treated as an exception)
	 * @param $publicGalleyId string
	 * @param $galleyId int
	 * @return boolean
	 */
	function publicGalleyIdExists($publicGalleyId, $galleyId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM monograph_galleys WHERE public_galley_id = ? AND galley_id <> ?', array($publicGalleyId, $galleyId)
		);
		$returner = $result->fields[0] ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve a galley by ID.
	 * @param $publicGalleyId string
	 * @param $monographId int optional
	 * @return MonographGalley
	 */
	function &getGalleyByPublicGalleyId($publicGalleyId, $monographId) {
		$result =& $this->retrieve(
			'SELECT	g.*,
				mf.file_name, mf.original_file_name, mf.file_type, mf.file_size, mf.date_uploaded, mf.date_modified
			FROM	monograph_galleys g
			LEFT JOIN monograph_files mf ON (g.file_id = mf.file_id)
			LEFT JOIN production_assignments pa ON (pa.assignment_id = g.assignment_id)
			WHERE	g.public_galley_id = ? AND
				g.monograph_id = ?',
			array($publicGalleyId, (int) $monographId)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		} else {
			HookRegistry::call('MonographGalleyDAO::getNewGalley', array(&$galleyId, &$monographId, &$returner));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all galleys for a production assignment.
	 * @param $assignmentId int
	 * @return array MonographGalleys
	 */
	function &getByAssignmentId($assignmentId) {
		$galleys = array();

		$result =& $this->retrieve(
			'SELECT g.*,
			mf.file_name, mf.original_file_name, mf.file_type, mf.file_size, mf.date_uploaded, mf.date_modified
			FROM monograph_galleys g
			LEFT JOIN monograph_files mf ON (g.file_id = mf.file_id)
			LEFT JOIN production_assignments pa ON (pa.assignment_id = g.assignment_id)
			WHERE g.assignment_id = ? ORDER BY g.seq',
			$monographId
		);

		while (!$result->EOF) {
			$galleys[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		HookRegistry::call('MonographGalleyDAO::getByMonographId', array(&$galleys, &$monographId));

		return $galleys;
	}

	/**
	 * Retrieve all galleys for a monograph.
	 * @param $monographId int
	 * @param $userId int optional - filter by associated user
	 * @return array MonographGalleys
	 */
	function &getByMonographId($monographId, $userId = null) {
		$galleys = array();
		$sqlExtra = '';
		$sqlParams = array();

		if ($userId !== null) {
			$sqlExtra .= 'LEFT JOIN signoffs s ON (s.assoc_type = ? AND s.assoc_id = pa.assignment_id AND s.date_notified IS NOT NULL)';
			$sqlParams[] = ASSOC_TYPE_PRODUCTION_ASSIGNMENT;
			$sqlParams[] = $userId;
		}
		$sqlParams[] = $monographId;

		$result =& $this->retrieve(
			'SELECT g.*,
			mf.file_name, mf.original_file_name, mf.file_type, mf.file_size, mf.date_uploaded, mf.date_modified
			FROM monograph_galleys g
			LEFT JOIN monograph_files mf ON (g.file_id = mf.file_id)
			LEFT JOIN production_assignments pa ON (pa.assignment_id = g.assignment_id) '. $sqlExtra .'
			WHERE '. ($userId != null ? 's.user_id = ? AND ' : '') .'g.monograph_id = ?
			ORDER BY g.seq',
			$sqlParams
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$galleys[$row['assignment_id']][] =& $this->_fromRow($row);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		HookRegistry::call('MonographGalleyDAO::getByMonographId', array(&$galleys, &$monographId));

		return $galleys;
	}

	/**
	 * Retrieve monograph galley by public galley id or, failing that,
	 * internal galley ID; public galley ID takes precedence.
	 * @param $galleyId string
	 * @param $monographId int
	 * @return galley object
	 */
	function &getGalleyByBestGalleyId($galleyId, $monographId) {
		if ($galleyId != '') $galley =& $this->getGalleyByPublicGalleyId($galleyId, $monographId);
		if (!isset($galley)) $galley =& $this->getGalley((int) $galleyId, $monographId);
		return $galley;
	}

 	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SignoffEntry
	 */
	function newDataObject() {
		return new MonographGalley();
	}

	/**
	 * Internal function to return a MonographGalley object from a row.
	 * @param $row array
	 * @return MonographGalley
	 */
	function &_fromRow(&$row) {
		if ($row['html_galley']) {
			$galley = new MonographHTMLGalley();

			// HTML-specific settings
			$galley->setStyleFileId($row['style_file_id']);
			if ($row['style_file_id']) {
				$galley->setStyleFile($this->_submissionFileDao->getLatestRevision($row['style_file_id']));
			}

			// Retrieve images
			$images =& $this->getGalleyImages($row['galley_id']);
			$galley->setImageFiles($images);

		} else {
			$galley = $this->newDataObject();
		}

		$productionAssignmentDao =& DAORegistry::getDAO('ProductionAssignmentDAO');
		$designAssignment =& $productionAssignmentDao->getById($row['assignment_id']);

		$galley->setId($row['galley_id']);
		$galley->setPublicGalleyId($row['public_galley_id']);
		$galley->setMonographId($row['monograph_id']);
		$galley->setLabel($row['label']);
		$galley->setLocale($row['locale']);
		$galley->setFileId($row['file_id']);
		$galley->setAssignmentId($row['assignment_id']);
		$galley->setSequence($row['seq']);
		$galley->setViews($row['views']);

		// MonographFile set methods
		$galley->setFileName($row['file_name']);
		$galley->setOriginalFileName($row['original_file_name']);
		$galley->setFileType($row['file_type']);
		$galley->setFileSize($row['file_size']);
		$galley->setDateModified($this->datetimeFromDB($row['date_modified']));
		$galley->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));

		HookRegistry::call('MonographGalleyDAO::_fromRow', array(&$galley, &$row));

		return $galley;
	}

	/**
	 * Insert a new MonographGalley.
	 * @param $galley MonographGalley
	 */
	function insertObject(&$galley) {
		$this->update(
			'INSERT INTO monograph_galleys
				(public_galley_id, monograph_id, file_id, assignment_id, locale, html_galley, style_file_id, seq)
				VALUES
				(?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				$galley->getPublicGalleyId(),
				$galley->getMonographId(),
				$galley->getFileId(),
				$galley->getAssignmentId(),
				$galley->getLocale(),
				(int)$galley->isHTMLGalley(),
				$galley->isHTMLGalley() ? $galley->getStyleFileId() : null,
				$galley->getSequence() == null ? $this->getNextGalleySequence($galley->getMonographId(), $galley->getAssignmentId()) : $galley->getSequence()
			)
		);
		$galley->setId($this->getInsertGalleyId());

		HookRegistry::call('MonographGalleyDAO::insertNewGalley', array(&$galley, $galley->getId()));

		return $galley->getId();
	}

	/**
	 * Update an existing MonographGalley.
	 * @param $galley MonographGalley
	 */
	function updateObject(&$galley) {
		return $this->update(
			'UPDATE monograph_galleys
				SET
					public_galley_id = ?,
					file_id = ?,
					assignment_id = ?,
					locale = ?,
					html_galley = ?,
					style_file_id = ?,
					seq = ?
				WHERE galley_id = ?',
			array(
				$galley->getPublicGalleyId(),
				$galley->getFileId(),
				$galley->getAssignmentId(),
				$galley->getLocale(),
				(int)$galley->isHTMLGalley(),
				$galley->isHTMLGalley() ? $galley->getStyleFileId() : null,
				$galley->getSequence(),
				$galley->getId()
			)
		);
	}

	/**
	 * Delete a MonographGalley.
	 * @param $galley MonographGalley
	 */
	function deleteObject(&$galley) {
		return $this->deleteById($galley->getId());
	}

	/**
	 * Delete galleys by assignment Id.
	 * @param $assignmentId int
	 */
	function deleteByAssignmentId($assignmentId) {
		$galleys =& $this->getByAssignmentId($assignmentId);

		foreach ($galleys as $galley) {
			$this->deleteObject($galley);
		}
	}

	/**
	 * Delete a galley by ID.
	 * @param $galleyId int
	 * @param $monographId int optional
	 */
	function deleteById($galleyId, $monographId = null) {

		HookRegistry::call('MonographGalleyDAO::deleteById', array(&$galleyId, &$monographId));

		$this->deleteImagesByGalley($galleyId);
		if (isset($monographId)) {
			return $this->update(
				'DELETE FROM monograph_galleys WHERE galley_id = ? AND monograph_id = ?',
				array($galleyId, $monographId)
			);

		} else {
			return $this->update(
				'DELETE FROM monograph_galleys WHERE galley_id = ?', $galleyId
			);
		}
	}

	/**
	 * Delete galleys (and dependent galley image entries) by monograph.
	 * NOTE that this will not delete monograph_file entities or the respective files.
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {
		$galleys =& $this->getByMonographId($monographId);
		foreach ($galleys as $galley) {
			$this->deleteById($galley->getId(), $monographId);
		}
	}

	/**
	 * Check if a galley exists with the associated file ID.
	 * @param $monographId int
	 * @param $fileId int
	 * @return boolean
	 */
	function galleyExistsByFileId($monographId, $fileId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM monograph_galleys
			WHERE monograph_id = ? AND file_id = ?',
			array($monographId, $fileId)
		);

		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Increment the views count for a galley.
	 * @param $galleyId int
	 */
	function incrementViews($galleyId) {
		if ( !HookRegistry::call('MonographGalleyDAO::incrementGalleyViews', array(&$galleyId)) ) {
			return $this->update(
				'UPDATE monograph_galleys SET views = views + 1 WHERE galley_id = ?',
				$galleyId
			);
		} else return false;
	}

	/**
	 * Sequentially renumber galleys for a monograph in their sequence order.
	 * @param $monographId int
	 * @param $assignmentId int
	 */
	function resequenceGalleys($monographId, $assignmentId) {
		$result =& $this->retrieve(
			'SELECT galley_id FROM monograph_galleys WHERE monograph_id = ? AND assignment_id = ? ORDER BY seq',
			array($monographId, $assignmentId)
		);

		for ($i=1; !$result->EOF; $i++) {
			list($galleyId) = $result->fields;
			$this->update(
				'UPDATE monograph_galleys SET seq = ? WHERE galley_id = ?',
				array($i, $galleyId)
			);
			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Get the the next sequence number for a monograph's galleys (i.e., current max + 1).
	 * @param $monographId int
	 * @param $assignmentId int
	 * @return int
	 */
	function getNextGalleySequence($monographId, $assignmentId) {
		$result =& $this->retrieve(
			'SELECT MAX(seq) + 1 FROM monograph_galleys WHERE monograph_id = ? AND assignment_id = ?',
			array($monographId, $assignmentId)
		);
		$returner = floor($result->fields[0]);

		$returner = $returner < 1 ? 1 : $returner;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the ID of the last inserted gallery.
	 * @return int
	 */
	function getInsertGalleyId() {
		return $this->getInsertId('monograph_galleys', 'galley_id');
	}


	//
	// Extra routines specific to HTML galleys.
	//

	/**
	 * Retrieve array of the images for an HTML galley.
	 * FIXME: Move to SubmissionFileDAO.
	 * @param $galleyId int
	 * @return array MonographFile
	 */
	function &getGalleyImages($galleyId) {
		$images = array();

		$result =& $this->retrieve(
			'SELECT
			  mf.file_id AS monograph_file_id, mf.revision AS monograph_revision,
			  af.file_id AS artwork_file_id, af.revision AS artwork_revision,
			  mf.*, af.*
			FROM monograph_html_galley_images i
			  INNER JOIN monograph_files mf ON i.file_id = mf.file_id
			  LEFT JOIN monograph_artwork_files af ON mf.file_id = af.file_id AND mf.revision = af.revision
			WHERE i.galley_id = ?',
			$galleyId
		);

		while (!$result->EOF) {
			$images[] =& $this->submissionFileDao->fromRow($result->GetRowAssoc(false));
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $images;
	}

	/**
	 * Attach an image to an HTML galley.
	 * @param $galleyId int
	 * @param $fileId int
	 */
	function insertGalleyImage($galleyId, $fileId) {
		return $this->update(
			'INSERT INTO monograph_html_galley_images
			(galley_id, file_id)
			VALUES
			(?, ?)',
			array($galleyId, $fileId)
		);
	}

	/**
	 * Delete an image from an HTML galley.
	 * @param $galleyId int
	 * @param $fileId int
	 */
	function deleteGalleyImage($galleyId, $fileId) {
		return $this->update(
			'DELETE FROM monograph_html_galley_images
			WHERE galley_id = ? AND file_id = ?',
			array($galleyId, $fileId)
		);
	}

	/**
	 * Delete HTML galley images by galley.
	 * @param $galleyId int
	 */
	function deleteImagesByGalley($galleyId) {
		return $this->update(
			'DELETE FROM monograph_html_galley_images WHERE galley_id = ?',
			$galleyId
		);
	}
}

?>
