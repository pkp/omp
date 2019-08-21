<?php

/**
 * @file classes/monograph/ChapterAuthorDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterDAO
 * @inchapter monograph
 * @see Chapter
 * @see ChapterDAO
 * @see AuthorDAO - This DAO does NOT offer operations for manipulating Author Objects.  AuthorDAO should be used for that instead.
 *
 * @brief Operations for retrieving and modifying ChapterAuthor objects.
 *
 */

import('classes.monograph.Chapter');
import('classes.monograph.ChapterAuthor');

class ChapterAuthorDAO extends DAO {
	/**
	 * Get all authors for a given chapter.
	 * @param $publicationId int
	 * @param $chapterId int
	 * @return DAOResultFactory
	 */
	function getAuthors($publicationId = null, $chapterId = null) {
		$params = array();
		if (isset($publicationId)) $params[] = (int) $publicationId;
		if (isset($chapterId)) $params[] = (int) $chapterId;
		// get all the monograph_author fields,
		// but replace the primary_contact and seq with submission_chapter_authors.primary_contact

		$sql = 'SELECT	a.*,
				sca.chapter_id,
				sca.primary_contact,
				sca.seq,
				ug.show_title,
				p.locale
			FROM	authors a
				JOIN publications p ON (p.publication_id = a.publication_id)
				JOIN submission_chapter_authors sca ON (a.author_id = sca.author_id)
				JOIN user_groups ug ON (a.user_group_id = ug.user_group_id)' .
			( (count($params)> 0)?' WHERE':'' ) .
			(  isset($publicationId)?' a.publication_id = ?':'' ) .
			(  (isset($publicationId) && isset($chapterId))?' AND':'' ) .
			(  isset($chapterId)?' sca.chapter_id = ?':'' ) .
			' ORDER BY sca.chapter_id, sca.seq';

		$result = $this->retrieve($sql, $params);
		return new DAOResultFactory($result, $this, '_fromRow', array('id'));
	}

	/**
	 * Get all authors IDs for a given chapter.
	 * @param $chapterId int
	 * @return array
	 */
	function getAuthorIdsByChapterId($chapterId) {

		$result = $this->retrieve(
			'SELECT author_id
			FROM submission_chapter_authors
			WHERE chapter_id = ?',
			[(int) $chapterId]
		);

		$authorIds = array();
		while (!$result->EOF) {
			$authorIds[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $authorIds;
	}

	/**
	 * Add an author to a chapter.
	 * @param $chapterId int
	 * @param $monographId int
	 * @return array
	 */
	function insertChapterAuthor($authorId, $chapterId, $isPrimary = false, $sequence = 0) {
		//FIXME: How to handle sequence?
		$this->update(
			'INSERT INTO submission_chapter_authors
				(author_id, chapter_id, primary_contact, seq)
				VALUES
				(?, ?, ?, ?)',
			array(
				(int) $authorId,
				(int) $chapterId,
				(int) $isPrimary,
				(int) $sequence
			)
		);
	}

	/**
	 * Remove an author from a chapter
	 * @param $chapterId int
	 */
	function deleteChapterAuthorById($authorId, $chapterId = null) {
		$params = array((int) $authorId);
		if ($chapterId) $params[] = (int) $chapterId;

		$this->update(
			'DELETE FROM submission_chapter_authors WHERE author_id = ?' .
			($chapterId?' AND chapter_id = ?':''),
			$params
		);
	}

	/**
	 * Construct and return a new data object.
	 * @return ChapterAuthor
	 */
	function newDataObject() {
		return new ChapterAuthor();
	}

	/**
	 * Internal function to return an Author object from a row.
	 * @param $row array
	 * @return Author
	 */
	function _fromRow($row) {
		// Start with an Author object and copy the common elements
		$authorDao = DAORegistry::getDAO('AuthorDAO');
		$author = $authorDao->_fromRow($row);

		$chapterAuthor = $this->newDataObject();
		$chapterAuthor->setId((int) $author->getId());
		$chapterAuthor->setGivenName($author->getGivenName(null), null);
		$chapterAuthor->setFamilyName($author->getFamilyName(null), null);
		$chapterAuthor->setAffiliation($author->getAffiliation(null), null);
		$chapterAuthor->setCountry($author->getCountry());
		$chapterAuthor->setEmail($author->getEmail());
		$chapterAuthor->setUrl($author->getUrl());
		$chapterAuthor->setUserGroupId($author->getUserGroupId());

		// Add additional data that is chapter author specific
		$chapterAuthor->setPrimaryContact($row['primary_contact']);
		$chapterAuthor->setSequence((int) $row['seq']);		;
		$chapterAuthor->setChapterId((int) $row['chapter_id']);

		return $chapterAuthor;
	}
}


