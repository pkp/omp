<?php

/**
 * @file classes/monograph/AuthorDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorDAO
 * @ingroup monograph
 * @see Author
 * @see ChapterDAO (uses AuthorDAO)
 *
 * @brief Operations for retrieving and modifying Author objects.
 */

// $Id$


import('classes.monograph.Author');
import('classes.monograph.Monograph');
import('lib.pkp.classes.submission.PKPAuthorDAO');

class AuthorDAO extends PKPAuthorDAO {
	/**
	 * Constructor
	 */
	function AuthorDAO() {
		parent::PKPAuthorDAO();
	}

	/**
	 * Retrieve all authors for a submission.
	 * @param $submissionId int
	 * @return array Authors ordered by sequence
	 */
	function &getAuthorsByMonographId($submissionId) {
		$result =& $this->retrieve(
			'SELECT * FROM authors WHERE submission_id = ? ORDER BY seq',
			(int) $submissionId
		);

		$returner = new DAOResultFactory($result, $this, '_returnAuthorFromRow', array('id'));
		return $returner;
	}

	/**
	 * Retrieve all published authors for a press in an associative array by
	 * the first letter of the last name, for example:
	 * $returnedArray['S'] gives array($misterSmithObject, $misterSmytheObject, ...)
	 * Keys will appear in sorted order. Note that if pressId is null,
	 * alphabetized authors for all presses are returned.
	 * @param $pressId int
	 * @param $initial An initial the last names must begin with
	 * @return array Authors ordered by sequence
	 */
	function &getAuthorsAlphabetizedByPress($pressId = null, $initial = null, $rangeInfo = null) {
		$authors = array();
		$params = array();

		if (isset($pressId)) $params[] = $pressId;
		if (isset($initial)) {
			$params[] = String::strtolower($initial) . '%';
			$initialSql = ' AND LOWER(ma.last_name) LIKE LOWER(?)';
		} else {
			$initialSql = '';
		}

		$result =& $this->retrieveRange(
			'SELECT DISTINCT
				CAST(\'\' AS CHAR) AS url,
				0 AS author_id,
				0 AS submission_id,
				CAST(\'\' AS CHAR) AS email,
				0 AS primary_contact,
				0 AS seq,
				ma.first_name AS first_name,
				ma.middle_name AS middle_name,
				ma.last_name AS last_name,
				ma.affiliation AS affiliation,
				ma.country
			FROM	authors ma,
				monographs a
			WHERE	ma.submission_id = a.monograph_id ' .
				(isset($pressId)?'AND a.press_id = ? ':'') . '
				AND a.status = ' . STATUS_PUBLISHED . '
				AND (ma.last_name IS NOT NULL AND ma.last_name <> \'\')' .
				$initialSql . '
			ORDER BY ma.last_name, ma.first_name',
			empty($params)?false:$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnAuthorFromRow');
		return $returner;
	}

	/**
	 * Retrieve the IDs of all authors for a submission.
	 * @param $submissionId int
	 * @return array int ordered by sequence
	 */
	function &getAuthorIdsByMonographId($submissionId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		$returner =& $this->getAuthorIdsBySubmissionId($submissionId);
		return $returner;
	}

	/**
	 * Retrieve the number of authors assigned to a submission
	 * @param $submissionId int
	 * @return int
	 */
	function getAuthorCountByMonographId($submissionId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->getAuthorCountBySubmissionId($submissionId);
	}

	/**
	 * Get a new data object
	 * @return DataObject
	 */
	function newDataObject() {
		return new Author();
	}

	/**
	 * Insert a new Author.
	 * @param $author Author
	 */
	function insertAuthor(&$author) {
		// Set author sequence to end of author list
		if(!$author->getSequence()) {
			$authorCount = $this->getAuthorCountByMonographId($author->getMonographId());
			$author->setSequence($authorCount + 1);
		}
		// Reset primary contact for monograph to this author if applicable
		if ($author->getPrimaryContact()) {
			$this->resetPrimaryContact($author->getId(), $author->getMonographId());
		}

		$this->update(
			'INSERT INTO authors
				(submission_id, first_name, middle_name, last_name, affiliation, country, email, url, user_group_id, primary_contact, seq)
				VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				$author->getSubmissionId(),
				$author->getFirstName(),
				$author->getMiddleName() . '', // make non-null
				$author->getLastName(),
				$author->getAffiliation() . '', // make non-null
				$author->getCountry(),
				$author->getEmail(),
				$author->getUrl(),
				(int) $author->getUserGroupId(),
				(int) $author->getPrimaryContact(),
				(float) $author->getSequence()
			)
		);

		$author->setId($this->getInsertAuthorId());
		$this->updateLocaleFields($author);

		return $author->getId();
	}

	/**
	 * Update an existing Author.
	 * @param $author Author
	 */
	function updateAuthor($author) {
		// Reset primary contact for monograph to this author if applicable
		if ($author->getPrimaryContact()) {
			$this->resetPrimaryContact($author->getId(), $author->getMonographId());
		}
		$returner = $this->update(
			'UPDATE	authors
			SET	first_name = ?,
				middle_name = ?,
				last_name = ?,
				affiliation = ?,
				country = ?,
				email = ?,
				url = ?,
				user_group_id = ?,
				primary_contact = ?,
				seq = ?
			WHERE	author_id = ?',
			array(
				$author->getFirstName(),
				$author->getMiddleName() . '', // make non-null
				$author->getLastName(),
				$author->getAffiliation() . '', // make non-null
				$author->getCountry(),
				$author->getEmail(),
				$author->getUrl(),
				(int) $author->getUserGroupId(),
				(int) $author->getPrimaryContact(),
				(float) $author->getSequence(),
				(int) $author->getId()
			)
		);
		$this->updateLocaleFields($author);
		return $returner;
	}

	/**
	 * Delete authors by submission.
	 * @param $submissionId int
	 */
	function deleteAuthorsByMonograph($submissionId) {
		$authors =& $this->getAuthorsByMonographId($submissionId);
		foreach ($authors as $author) {
			$this->deleteAuthor($author);
		}
	}
}

?>
