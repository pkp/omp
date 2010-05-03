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

class AuthorDAO extends DAO {
	/**
	 * Retrieve an author by ID.
	 * @param $authorId int
	 * @return Author
	 */
	function &getAuthor($authorId) {
		$result =& $this->retrieve(
			'SELECT * FROM monograph_authors WHERE author_id = ?', $authorId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnAuthorFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}


	/**
	 * Retrieve all authors for a monograph.
	 * @param $monographId int
	 * @return array Authors ordered by sequence
	 */
	function &getAuthorsByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT * FROM monograph_authors WHERE monograph_id = ? ORDER BY seq',
			$monographId
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
				0 AS monograph_id,
				CAST(\'\' AS CHAR) AS email,
				0 AS primary_contact,
				0 AS seq,
				ma.first_name AS first_name,
				ma.middle_name AS middle_name,
				ma.last_name AS last_name,
				ma.affiliation AS affiliation,
				ma.country
			FROM	monograph_authors ma,
				monographs a
			WHERE	ma.monograph_id = a.monograph_id ' .
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
	 * Retrieve the IDs of all authors for a monograph.
	 * @param $monographId int
	 * @return array int ordered by sequence
	 */
	function &getAuthorIdsByMonographId($monographId) {
		$authors = array();

		$result =& $this->retrieve(
			'SELECT author_id FROM monograph_authors WHERE monograph_id = ? ORDER BY seq',
			$monographId
		);

		while (!$result->EOF) {
			$authors[] = $result->fields[0];
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $authors;
	}

	/**
	 * Retrieve the number of authors assigned to a monograph
	 * @param $monographId int
	 * @return int
	 */
	function getAuthorCountByMonographId($monographId) {
		$result =& $this->retrieve(
			'SELECT count(*) FROM monograph_authors WHERE monograph_id = ?',
			$monographId
		);

		$returner = $result->fields[0];

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('biography', 'competingInterests');
	}

	/**
	 * Update the localized data for this object
	 * @param $author object
	 */
	function updateLocaleFields(&$author) {
		$this->updateDataObjectSettings('monograph_author_settings', $author, array(
			'author_id' => $author->getId()
		));

	}

	/**
	 * Internal function to return an Author object from a row.
	 * @param $row array
	 * @return Author
	 */
	function &_returnAuthorFromRow(&$row) {
		$author = new Author();
		$author->setId($row['author_id']);
		$author->setMonographId($row['monograph_id']);
		$author->setFirstName($row['first_name']);
		$author->setMiddleName($row['middle_name']);
		$author->setLastName($row['last_name']);
		$author->setAffiliation($row['affiliation']);
		$author->setCountry($row['country']);
		$author->setEmail($row['email']);
		$author->setUrl($row['url']);
		$author->setUserGroupId($row['user_group_id']);
		$author->setPrimaryContact($row['primary_contact']);
		$author->setSequence($row['seq']);

		$this->getDataObjectSettings('monograph_author_settings', 'author_id', $row['author_id'], $author);

		HookRegistry::call('AuthorDAO::_returnAuthorFromRow', array(&$author, &$row));

		return $author;
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
			'INSERT INTO monograph_authors
				(monograph_id, first_name, middle_name, last_name, affiliation, country, email, url, user_group_id, primary_contact, seq)
				VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				$author->getMonographId(),
				$author->getFirstName(),
				$author->getMiddleName() . '', // make non-null
				$author->getLastName(),
				$author->getAffiliation() . '', // make non-null
				$author->getCountry(),
				$author->getEmail(),
				$author->getUrl(),
				$author->getUserGroupId(),
				$author->getPrimaryContact(),
				$author->getSequence()
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
			'UPDATE monograph_authors
				SET
					first_name = ?,
					middle_name = ?,
					last_name = ?,
					affiliation = ?,
					country = ?,
					email = ?,
					url = ?,
					user_group_id = ?,
					primary_contact = ?,
					seq = ?
				WHERE author_id = ?',
			array(
				$author->getFirstName(),
				$author->getMiddleName() . '', // make non-null
				$author->getLastName(),
				$author->getAffiliation() . '', // make non-null
				$author->getCountry(),
				$author->getEmail(),
				$author->getUrl(),
				$author->getUserGroupId(),
				$author->getPrimaryContact(),
				$author->getSequence(),
				$author->getId()
			)
		);
		$this->updateLocaleFields($author);
		return $returner;
	}

	/**
	 * Delete an Author.
	 * @param $author Author
	 */
	function deleteAuthor(&$author) {
		return $this->deleteAuthorById($author->getId());
	}

	/**
	 * Delete an author by ID.
	 * @param $authorId int
	 * @param $monographId int optional
	 */
	function deleteAuthorById($authorId, $monographId = null) {
		$params = array($authorId);
		if ($monographId) $params[] = $monographId;
		$returner = $this->update(
			'DELETE FROM monograph_authors WHERE author_id = ?' .
			($monographId?' AND monograph_id = ?':''),
			$params
		);
		if ($monographId) $this->resequenceAuthors($monographId);
		if ($returner) $this->update('DELETE FROM monograph_author_settings WHERE author_id = ?', array($authorId));

		return $returner;
	}

	/**
	 * Delete authors by monograph.
	 * @param $monographId int
	 */
	function deleteAuthorsByMonograph($monographId) {
		$authors =& $this->getAuthorsByMonographId($monographId);
		foreach ($authors as $author) {
			$this->deleteAuthor($author);
		}
	}

	/**
	 * Sequentially renumber a monograph's authors in their sequence order.
	 * @param $monographId int
	 */
	function resequenceAuthors($monographId) {
		$result =& $this->retrieve(
			'SELECT author_id FROM monograph_authors WHERE monograph_id = ? ORDER BY seq', $monographId
		);

		for ($i=1; !$result->EOF; $i++) {
			list($authorId) = $result->fields;
			$this->update(
				'UPDATE monograph_authors SET seq = ? WHERE author_id = ?',
				array(
					$i,
					$authorId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Remove other primary contacts from a monograph and set to authorId
	 * @param $authorId int
	 * @param $monographId int
	 */
	function resetPrimaryContact($authorId, $monographId) {
		$this->update(
				'UPDATE monograph_authors SET primary_contact = 0 WHERE primary_contact = 1 AND monograph_id = ?',
				array($monographId)
			);
		$this->update(
				'UPDATE monograph_authors SET primary_contact = 1 WHERE author_id = ? AND monograph_id = ?',
				array($authorId, $monographId)
			);
	}

	/**
	 * Get the ID of the last inserted author.
	 * @return int
	 */
	function getInsertAuthorId() {
		return $this->getInsertId('monograph_authors', 'author_id');
	}
}

?>
