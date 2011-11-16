<?php

/**
 * @file classes/monograph/ChapterAuthorDAO.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
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
	function &getAuthors($monographId = null, $chapterId = null) {
		$params = array(
			'affiliation', AppLocale::getPrimaryLocale(),
			'affiliation', AppLocale::getLocale()
		);
		if (isset($monographId)) $params[] = (int) $monographId;
		if (isset($chapterId)) $params[] = (int) $chapterId;
		// get all the monograph_author fields,
		// but replace the primary_contact and seq with monograph_chapter_authors.primary_contact

		$sql = 'SELECT	ma.author_id,
				ma.submission_id,
				mca.chapter_id,
				mca.primary_contact,
				mca.seq,
				ma.first_name,
				ma.middle_name,
				ma.last_name,
				asl.setting_value AS affiliation_l,
				asl.locale,
				aspl.setting_value AS affiliation_pl,
				aspl.locale AS primary_locale,
				ma.country,
				ma.email,
				ma.url,
				ma.user_group_id
			FROM	authors ma
				JOIN monograph_chapter_authors mca ON (ma.author_id = mca.author_id)
				LEFT JOIN author_settings aspl ON (mca.author_id = aspl.author_id AND aspl.setting_name = ? AND aspl.locale = ?)
				LEFT JOIN author_settings asl ON (mca.author_id = asl.author_id AND asl.setting_name = ? AND asl.locale = ?)' .
			( (count($params)> 0)?' WHERE':'' ) .
			(  isset($monographId)?' ma.submission_id = ?':'' ) .
			(  (isset($monographId) && isset($chapterId))?' AND':'' ) .
			(  isset($chapterId)?' mca.chapter_id = ?':'' ) .
			' ORDER BY mca.chapter_id, mca.seq';

		$result =& $this->retrieve($sql, $params);

		$returner = new DAOResultFactory($result, $this, '_returnFromRow', array('id'));
		return $returner;
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

		$result =& $this->retrieve(
			'SELECT author_id
			FROM monograph_chapter_authors
			WHERE chapter_id = ?
			' . ($monographId?' AND monograph_id = ?':''),
			$params
		);

		$authorIds = array();
		while (!$result->EOF) {
			$authorIds[] = $result->fields[0];
			$result->moveNext();
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
			'INSERT INTO monograph_chapter_authors
				(author_id, chapter_id, monograph_id, primary_contact, seq)
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
			'DELETE FROM monograph_chapter_authors WHERE author_id = ?' .
			($chapterId?' AND chapter_id = ?':''),
			$params
		);
	}

	/**
	 * Internal function to return an Author object from a row.
	 * @param $row array
	 * @return Author
	 */
	function _returnFromRow(&$row) {
		// Start with an Author object and copy the common elements
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author =& $authorDao->_returnAuthorFromRow($row);

		$chapterAuthor = new ChapterAuthor();
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
