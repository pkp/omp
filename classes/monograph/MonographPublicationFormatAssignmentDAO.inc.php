<?php

/**
 * @file classes/monograph/MonographPublicationFormatAssignmentDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographPublicationFormatAssignmentDAO
 * @ingroup monograph
 *
 * @brief operations for retrieving, assigning and removing Publication Formats to Monograph objects.
 *
 */

class MonographPublicationFormatAssignmentDAO extends DAO {
	/**
	 * Constructor
	 */
	function MonographPublicationFormatAssignmentDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve publication formats assigned to a particular
	 * published monograph.
	 * @param int $monogaphId
	 * @return array DAOResultFactory (publication formats)
	 */
	function &getFormatsByPublishedMonographId($pubId) {
		$result =& $this->retrieve(
			'SELECT pf.* FROM
			publication_formats pf
			JOIN published_monograph_publication_formats pmpf ON pf.publication_format_id = pmpf.publication_format_id
			WHERE pf.enabled = ? AND pmpf.pub_id = ?', array(1, $pubId)
		);

		// Delegate creation to the PublicationFormat DAO.
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$returner = new DAOResultFactory($result, $publicationFormatDao, '_fromRow');
		return $returner;
	}

	/**
	 * Retrieve publishedMonograph ids that have been assigned a specific publication format
	 * @param int $formatId
	 * @param optional int $pressId
	 * @return array the ids of the published monographs
	 */
	function &getPublishedMonographIdsbyFormatId($formatId, $pressId = null) {
		$params = array(1, (int) $formatId);

		if ($pressId !== null) {
			$params[] = (int) $pressId;
		}

		$result =& $this->retrieve(
			'SELECT pmpf.pub_id FROM
			published_monograph_publication_formats pmpf
			JOIN publication_formats pf ON pf.publication_format_id = pmpf.publication_format_id
			JOIN published_monographs pm ON pmpf.pub_id = pm.pub_id
			WHERE pf.enabled = ? AND pmpf.publication_format_id = ?'
			. ($pressId?' AND pf.press_id = ?':'' ), $params
		);

		$pubIds = array();
		while (!$result->EOF) {
			$pubIds[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $pubIds;
	}

	/**
	 * Assigns a set of Publication Formats to a specific monograph
	 * @param mixed $publicationFormats
	 * @param int $pubId
	 * @param boolean $deleteFirst
	 */
	function assignPublicationFormats($formatIds, $pubId, $deleteFirst = false) {

		$seq = 1;
		$ids = array();

		if (!is_array($formatIds)) { // just given an int? Make sure we have an array
			$ids[] = $formatIds;
		} else {
			$ids = $formatIds;
		}

		if ($deleteFirst) {
			$this->update(
				'DELETE FROM published_monograph_publication_formats WHERE pub_id = ?', array((int) $pubId)
			);
		}

		foreach ($ids as $formatId) {
			$this->replace('published_monograph_publication_formats',
					array('pub_id' => (int) $pubId, 'publication_format_id' => (int) $formatId, 'seq' => $seq),
					array('pub_id', 'publication_format_id'));
			$seq++;
		}
	}

	/**
	 * Removes a Publication Format from a published monograph
	 * @param int $formatId
	 * @param int $pubId
	 */
	function deletePublicationFormatById($formatId, $pubId) {
		$this->update(
				'DELETE FROM published_monograph_publication_formats WHERE publication_format_id = ? AND pub_id = ?',
				array((int) $formatId, (int) $pubId)
		);
	}

	/**
	 * Removes all formats from a published monograph
	 * @param int $pubId
	 */
	function deletePublicationFormatsById($pubId) {
		$this->update(
				'DELETE FROM published_monograph_publication_formats WHERE pub_id = ?',
				array((int) $pubId)
		);
	}

	/**
	 * Get the formats not associated with a given monograph.
	 * @param $pubId int
	 * @return DAOResultFactory
	 */
	function getUnassignedPublicationFormats($pubId, $pressId = null) {
		$params = array((int) $pubId);
		if ($pressId) $params[] = (int) $pressId;

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');

		$result =& $this->retrieve(
				'SELECT	pf.*
				FROM published_monographs pm
				JOIN monographs m ON (pm.monograph_id = m.monograph_id)
				JOIN publication_formats pf ON (pf.press_id = m.press_id)
				LEFT JOIN published_monograph_publication_formats pmpf ON (pm.pub_id = pmpf.pub_id AND pmpf.publication_format_id = pf.publication_format_id)
				WHERE	pm.pub_id = ? AND
				' . ($pressId?' m.press_id = ? AND':'') . '
				pmpf.pub_id IS NULL',
		$params
		);

		// Delegate category creation to the category DAO.
		$returner = new DAOResultFactory($result, $publicationFormatDao, '_fromRow');
		return $returner;
	}
}