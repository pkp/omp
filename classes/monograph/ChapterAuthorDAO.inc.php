<?php

/**
 * @file classes/monograph/ChapterAuthorDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
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
	 * Constructor
	 */
	function ChapterAuthorDAO() {
		parent::DAO();
	}

	/**
	 * Get all authors for a given chapter.
	 * @param $chapterId int
	 * @param $monographId int
	 * @return DAOResultFactory
	 */
	function getAuthors($monographId = null, $chapterId = null) {
		$params = array(
			'affiliation', AppLocale::getPrimaryLocale(),
			'affiliation', AppLocale::getLocale()
		);
		if (isset($monographId)) $params[] = (int) $monographId;
		if (isset($chapterId)) $params[] = (int) $chapterId;
		// get all the monograph_author fields,
		// but replace the primary_contact and seq with submission_chapter_authors.primary_contact

		$sql = 'SELECT	a.author_id,
				a.submission_id,
				sca.chapter_id,
				sca.primary_contact,
				sca.seq,
				a.first_name,
				a.middle_name,
				a.last_name,
				a.suffix,
				a.include_in_browse,
				ug.show_title,
				asl.setting_value AS affiliation_l,
				asl.locale,
				aspl.setting_value AS affiliation_pl,
				aspl.locale AS primary_locale,
				a.country,
				a.email,
				a.url,
				a.user_group_id
			FROM	authors a
				JOIN submission_chapter_authors sca ON (a.author_id = sca.author_id)
				JOIN user_groups ug ON (a.user_group_id = ug.user_group_id)
				LEFT JOIN author_settings aspl ON (sca.author_id = aspl.author_id AND aspl.setting_name = ? AND aspl.locale = ?)
				LEFT JOIN author_settings asl ON (sca.author_id = asl.author_id AND asl.setting_name = ? AND asl.locale = ?)' .
			( (count($params)> 0)?' WHERE':'' ) .
			(  isset($monographId)?' a.submission_id = ?':'' ) .
			(  (isset($monographId) && isset($chapterId))?' AND':'' ) .
			(  isset($chapterId)?' sca.chapter_id = ?':'' ) .
			' ORDER BY sca.chapter_id, sca.seq';

		$result = $this->retrieve($sql, $params);
		return new DAOResultFactory($result, $this, '_returnFromRow', array('id'));
	}

	/**
	 * Get all authors IDs for a given chapter.
	 * @param $chapterId int
	 * @param $monographId int
	 * @return array
	 */
	function getAuthorIdsByChapterId($chapterId, $monographId = null) {
		$params = array((int) $chapterId);
		if ($monographId) $params[] = (int) $monographId;

		$result = $this->retrieve(
			'SELECT author_id
			FROM submission_chapter_authors
			WHERE chapter_id = ?
			' . ($monographId?' AND submission_id = ?':''),
			$params
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
	function insertChapterAuthor($authorId, $chapterId, $monographId, $isPrimary = false, $sequence = 0) {
		//FIXME: How to handle sequence?
		$this->update(
			'INSERT INTO submission_chapter_authors
				(author_id, chapter_id, submission_id, primary_contact, seq)
				VALUES
				(?, ?, ?, ?, ?)',
			array(
				(int) $authorId,
				(int) $chapterId,
				(int) $monographId,
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
	function _returnFromRow($row) {
		// Start with an Author object and copy the common elements
		$authorDao = DAORegistry::getDAO('AuthorDAO');
		$author = $authorDao->_fromRow($row);

		$chapterAuthor = $this->newDataObject();
		$chapterAuthor->setId($author->getId());
		$chapterAuthor->setSubmissionId($author->getSubmissionId());
		$chapterAuthor->setFirstName($author->getFirstName());
		$chapterAuthor->setMiddleName($author->getMiddleName());
		$chapterAuthor->setLastName($author->getLastName());
		$chapterAuthor->setAffiliation($author->getAffiliation(null), null);
		$chapterAuthor->setCountry($author->getCountry());
		$chapterAuthor->setEmail($author->getEmail());
		$chapterAuthor->setUrl($author->getUrl());
		$chapterAuthor->setUserGroupId($author->getUserGroupId());

		// Add additional data that is chapter author specific
		$chapterAuthor->setPrimaryContact($row['primary_contact']);
		$chapterAuthor->setSequence($row['seq']);		;
		$chapterAuthor->setChapterId($row['chapter_id']);

		return $chapterAuthor;
	}
}

?>
