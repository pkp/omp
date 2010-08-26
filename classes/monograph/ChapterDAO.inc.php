<?php

/**
 * @file classes/monograph/ChapterDAO.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterDAO
 * @inchapter monograph
 * @see Chapter
 * @see AuthorDAO
 *
 * @brief Operations for retrieving and modifying Chapter objects.
 */

import('classes.monograph.Chapter');
import('classes.monograph.ChapterAuthor');

class ChapterDAO extends DAO {
	/**
	 * Constructor.
	 */
	function ChapterDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a chapter by ID.
	 * @param $chapterId int
	 * @param $assocType int optional
	 * @param $monographId int optional
	 * @return Chapter
	 */
	function &getChapter($chapterId, $monographId = null) {
		$params = array((int) $chapterId);
		if ($monographId !== null) {
			$params[] = (int) $monographId;
		}

		$result =& $this->retrieve(
			'SELECT * FROM monograph_chapters WHERE chapter_id = ?' . ($monographId !== null?' AND monograph_id = ? ':''), $params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Get all chapters for a given monograph.
	 * @param $assocType int
	 * @param $monographId int
	 * @param $context int (optional)
	 * @param $rangeInfo object RangeInfo object (optional)
	 * @return array
	 */
	function &getChapters($monographId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT chapter_id, monograph_id, chapter_seq FROM monograph_chapters WHERE monograph_id = ? ORDER BY chapter_seq',
			$monographId, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnFromRow', array('id'));
		return $returner;
	}

	/**
	 * Get all chapters for a given monograph with the author objects set
	 * @param unknown_type $monographId
	 * @param unknown_type $rangeInfo
	 */
	function getChaptersWithAuthors($monographId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT	mc.chapter_id,
				mc.monograph_id,
				mc.chapter_seq,
				ma.author_id,
				ma.submission_id,
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
			FROM	monograph_chapters mc
				LEFT JOIN monograph_chapter_authors mca ON (mc.chapter_id = mca.chapter_id)
				LEFT JOIN author_settings aspl ON (mca.author_id = aspl.author_id AND aspl.setting_name = ? AND aspl.locale = ?)
				LEFT JOIN author_settings asl ON (mca.author_id = asl.author_id AND asl.setting_name = ? AND asl.locale = ?)
				LEFT JOIN authors ma ON (ma.author_id = mca.author_id)
			WHERE	mc.monograph_id = ?
			ORDER BY mc.chapter_seq, mca.seq',
			array(
				'affiliation', Locale::getPrimaryLocale(),
				'affiliation', Locale::getLocale(),
				(int) $monographId,
			),
			$rangeInfo
		);

		import('lib.pkp.classes.core.ArrayItemIterator');
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
		$chapters = array();
		$authors = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			// initialize $currentChapterId for the first row
			if ( !isset($currentChapterId) ) $currentChapterId = $row['chapter_id'];

			if ( $row['chapter_id'] != $currentChapterId) {
				// we're on a new row. create a chapter from the previous one
				$chapter =& $this->_returnFromRow($prevRow);
				// set the authors with all the authors found so far
				$authorIterator =& new ArrayItemIterator($authors);
				$chapter->setAuthors($authorIterator);
				// clear the authors array
				unset($authors);
				unset($authorIterator);
				$authors = array();
				// add the chapters to the returner
				$chapters[$currentChapterId] =& $chapter;

				// set the current id for this row
				$currentChapterId = $row['chapter_id'];
			}

			// add every author to the authors array
			if ( $row['author_id'] )
				$authors[$row['author_id']] =& $chapterAuthorDao->_returnFromRow($row);

			// keep a copy of the previous row for creating the chapter once we're on a new chapter row
			$prevRow = $row;
			$result->MoveNext();

			if ( $result->EOF ) {
				// The iterator is at th end
				$chapter =& $this->_returnFromRow($row);
				// set the authors with all the authors found so far
				$authorIterator =& new ArrayItemIterator($authors);
				$chapter->setAuthors($authorIterator);
				unset($authors);
				unset($authorIterator);
				// add the chapters to the returner
				$chapters[$currentChapterId] =& $chapter;
			}
		}

		$result->Close();
		unset($result);
		return $chapters;
	}

	/**
	 * Get the list of fields for which locale data is stored.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
	}

	/**
	 * Internal function to return a Chapter object from a row.
	 * @param $row array
	 * @return Chapter
	 */
	function &_returnFromRow(&$row) {
		$chapter = new Chapter();
		$chapter->setId($row['chapter_id']);
		$chapter->setMonographId($row['monograph_id']);
		$chapter->setSequence($row['chapter_seq']);
		$this->getDataObjectSettings('monograph_chapter_settings', 'chapter_id', $row['chapter_id'], $chapter);

		HookRegistry::call('ChapterDAO::_returnFromRow', array(&$chapter, &$row));

		return $chapter;
	}

	/**
	 * Update the settings for this object
	 * @param $chapter object
	 */
	function updateLocaleFields(&$chapter) {
		$this->updateDataObjectSettings('monograph_chapter_settings', $chapter, array(
			'chapter_id' => $chapter->getId()
		));
	}

	/**
	 * Insert a new board chapter.
	 * @param $chapter Chapter
	 */
	function insertChapter(&$chapter) {
		$this->update(
			'INSERT INTO monograph_chapters
				(monograph_id, chapter_seq)
				VALUES
				(?, ?)',
			array(
				(int) $chapter->getMonographId(),
				(int) $chapter->getSequence()
			)
		);

		$chapter->setId($this->getInsertChapterId());
		$this->updateLocaleFields($chapter);
		return $chapter->getId();
	}

	/**
	 * Update an existing board chapter.
	 * @param $chapter Chapter
	 */
	function updateObject(&$chapter) {
		$returner = $this->update(
			'UPDATE monograph_chapters
				SET	monograph_id = ?,
					chapter_seq = ?
				WHERE
					chapter_id = ?',
			array(
				(int) $chapter->getMonographId(),
				(int) $chapter->getSequence(),
				(int) $chapter->getId()
			)
		);
		$this->updateLocaleFields($chapter);
		return $returner;
	}

	/**
	 * Delete a board chapter, including membership info
	 * @param $chapter Chapter
	 */
	function deleteObject(&$chapter) {
		return $this->deleteChapterById($chapter->getId());
	}

	/**
	 * Delete a board chapter, including membership info
	 * @param $chapterId int
	 */
	function deleteById($chapterId) {
		$returner1 = $this->update('DELETE FROM monograph_chapter_authors WHERE chapter_id = ?', $chapterId);
		$returner2 = $this->update('DELETE FROM monograph_chapter_settings WHERE chapter_id = ?', $chapterId);
		$returner3 = $this->update('DELETE FROM monograph_chapters WHERE chapter_id = ?', $chapterId);
		return ($returner1 && $returner2 && $returner3);
	}

	/**
	 * Delete board chapters by assoc ID, including membership info
	 * @param $assocType int
	 * @param $monographId int
	 */
	function deleteByMonographId($monographId) {
		$chapters =& $this->getChapters($monographId);
		while ($chapter =& $chapters->next()) {
			$this->deleteObject($chapter);
			unset($chapter);
		}
	}

	/**
	 * Sequentially renumber  chapters in their sequence order, optionally by monographId
	 * @param $monographId int
	 */
	function resequenceChapters($monographId = null) {
		$result =& $this->retrieve(
			'SELECT chapter_id FROM monograph_chapters' .
			($monographId !== null?' WHERE monograph_id = ?':'') .
			' ORDER BY seq',
			($monographId !== null)?$monographId:null
		);

		for ($i=1; !$result->EOF; $i++) {
			list($chapterId) = $result->fields;
			$this->update(
				'UPDATE monograph_chapters SET chapter_seq = ? WHERE chapter_id = ?',
				array(
					$i,
					$chapterId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Get the ID of the last inserted board chapter.
	 * @return int
	 */
	function getInsertChapterId() {
		return $this->getInsertId('chapters', 'chapter_id');
	}
}

?>
