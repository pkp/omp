<?php

/**
 * @file classes/monograph/MonographPublicationFormatAssignmentDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterDAO
 * @inchapter monograph
 *
 * @brief operations for retrieving, assigning and removing Publication Formats to Monograph objects.
 *
 */

import('classes.monograph.Monograph');
import('classes.publicationFormat.PublicationFormat');

class MonographPublicationFormatAssignmentDAO extends DAO {
	/**
	 * Constructor
	 */
	function MonographPublicationFormatAssignmentDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve publication format ids assigned to a particular
	 * published monograph.
	 * @param int $monogaphId
	 * @return array the ids of the assigned formats
	 */
	function &getFormatIdsByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT pf.publication_format_id FROM
			publication_formats pf
			JOIN published_monograph_publication_formats pmpf ON pf.publication_format_id = pmpf.publication_format_id
			WHERE pf.enabled = ? AND pmpf.monograph_id = ?', array(1, $monographId)
		);

		$formatIds = array();
		while (!$result->EOF) {
			$formatIds[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $formatIds;
	}

	/**
	 * Retrieve monograph ids that have been assigned a specific publication format
	 * @param int $monogaphId
	 * @param optional int $pressId
	 * @return array the ids of the monographs
	 */
	function &getMonographIdsbyFormatId($formatId, $pressId = null) {
		$params = array(1, (int) $formatId);

		if ($pressId !== null) {
			$params[] = (int) $pressId;
		}

		$result =& $this->retrieve(
			'SELECT pmpf.monograph_id FROM
			published_monograph_publication_formats pmpf
			JOIN publication_formats pf ON pf.publication_format_id = pmpf.publication_format_id
			JOIN monographs m ON pmpf.monograph_id = m.monograph_id
			WHERE pf.enabled = ? AND pmpf.publication_format_id = ?'
			. ($pressId?' AND m.press_id = ?':'' ), $params
		);

		$monographIds = array();
		while (!$result->EOF) {
			$monographIds[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $monographIds;
	}

	/**
	 * Assigns a set of Publication Formats to a specific monograph
	 * @param array int $publicationFormats
	 * @param int $monographId
	 * @param boolean $deleteFirst
	 */
	function assignPublicationFormats($formatIds, $monographId, $deleteFirst = true) {

		$seq = 0;

		if ($deleteFirst) {
			$this->update(
				'DELETE FROM published_monograph_publication_formats WHERE monograph_id = ?', array($monographId)
			);
		} else { // retrieve the highest sequence currently assigned
			$result =& $this->retrieve(
				'SELECT max(pmpf.seq) FROM published_monograph_publication_formats pmpf
				WHERE pmpf.monograph_id = ?', array((int) $monographId));

			while (!$result->EOF) {
				$seq = $result->fields[0];
				$result->MoveNext();
			}
		}

		foreach ($formatIds as $formatId) {
			$seq++;
			$this->update(
				'INSERT INTO published_monograph_publication_formats
				(monograph_id, publication_format_id, seq)
				VALUES
				(?, ?, ?)',
				array((int) $monographId, (int) $formatId, $seq)
			);
		}
	}
}